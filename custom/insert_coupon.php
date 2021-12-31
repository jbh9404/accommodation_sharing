<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include 'db_connect.php';
//include '../wp-load.php'; // Wordpress Data load

//$current_user = wp_get_current_user(); // 현재 로그인된 계정 정보
//if ($current_user->ID == 0) { //idx가 없을 경우 (== 로그인된 상태가 아닌 경우)
//   echo 'not_logged_in_error';
//} else { // 로그인된 경우
$resultData = file_get_contents('php://input'); // http request content 가져오기
// {"action":"woocommerce_order_status_completed","arg":2000} 형태로 넘어옴.

$resultJson = json_decode($resultData); // json decoding
$order_id = $resultJson->arg; // arg data

//if ($post_author == $current_user->ID) { // 현재 로그인된 유저와 구매자 일치
$wp_woocommerce_order_items_query = "select order_item_id from wp_woocommerce_order_items where order_id = '$order_id'";
$wp_woocommerce_order_items_result = mysqli_query($db_connection, $wp_woocommerce_order_items_query);
if ($wp_woocommerce_order_items_result) {
    $order_item_id = mysqli_fetch_row($wp_woocommerce_order_items_result)[0];

    $wp_woocommerce_order_itemmeta_query = "select * from wp_woocommerce_order_itemmeta where order_item_id = '$order_item_id'";
    $wp_woocommerce_order_itemmeta_result = mysqli_query($db_connection, $wp_woocommerce_order_itemmeta_query);
    if ($wp_woocommerce_order_itemmeta_result) {
        $total_num = mysqli_num_rows($wp_woocommerce_order_itemmeta_result);
        $qty_num = 0;

        for ($i = 0; $i < $total_num; $i++) {
            $wp_woocommerce_order_itemmeta_row = mysqli_fetch_array($wp_woocommerce_order_itemmeta_result);
            if ($wp_woocommerce_order_itemmeta_row["meta_key"] == "_qty") {
                $qty_num = $wp_woocommerce_order_itemmeta_row["meta_value"];
                break;
            } else {
                continue;
            }
        }

        if ($qty_num > 0) {
            $wp_postmeta_query = "select meta_value from wp_postmeta where post_id = '$order_id' and meta_key = '_customer_user'";
            $wp_postmeta_result = mysqli_query($db_connection, $wp_postmeta_query);

            $userCode = mysqli_fetch_row($wp_postmeta_result)[0];

            $isSuccess = 0;
            //$userCode = $current_user;
            $current_datetime = date('Y-m-d H:i:s');

            for ($j = 0; $j < $qty_num; $j++) {
                $couponCode = md5(time() . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $j . $userCode);
                $couponInsert_Query = "insert into coupon_list (coupon_idx, order_id, user_idx, coupon_hash, coupon_registered, coupon_used) values (NULL ,'$order_id','$userCode', '$couponCode', '$current_datetime', 0)";
                if (mysqli_query($db_connection, $couponInsert_Query)) {
                    $isSuccess += 1;
                }
            }

            if ($isSuccess == $qty_num) {
                echo 'success';
            } else {
                echo 'coupon_insert_failed';
            }

        } else {
            echo 'no_qty_num_error';
        }

    } else {
        echo 'no_order_id_from_wp_woocommerce_order_itemmeta';
    }
} else {
    echo 'no_order_id_from_wp_woocommerce_order_items';
}
//  } else { // 현재 로그인된 유저와 구매자 일치
//      echo 'user_not_same_error';
//  }
mysqli_close($db_connection);
//}

?>