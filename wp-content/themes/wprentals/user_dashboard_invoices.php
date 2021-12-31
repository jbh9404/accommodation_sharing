<?php
// Template Name: User Dashboard Invoices
// Wp Estate Pack

if (!is_user_logged_in()) {
    wp_redirect(esc_html(home_url()));
    exit();
}

if (!wpestate_check_user_level()) {
    wp_redirect(esc_html(home_url()));
    exit();
}


$current_user = wp_get_current_user();
$paid_submission_status = esc_html(get_option('wp_estate_paid_submission', ''));
$price_submission = floatval(get_option('wp_estate_price_submission', ''));
$submission_curency_status = wpestate_curency_submission_pick();;
$userID = $current_user->ID;

$show_remove_fav = 1;
$show_compare = 1;
$show_compare_only = 'no';
$where_currency = esc_html(get_option('wp_estate_where_currency_symbol', ''));
get_header();
$options = wpestate_page_details($post->ID);
?>


    <div class="row is_dashboard">

        <?php
        if (wpestate_check_if_admin_page($post->ID)) {
            if (is_user_logged_in()) {
                get_template_part('templates/user_menu');

                include $_SERVER["DOCUMENT_ROOT"] . "/custom/db_connect.php";

                $current_user = wp_get_current_user();
                $user_idx = $current_user->ID;

                $couponListQuery = "select * from coupon_list where user_idx = '$user_idx' order by coupon_idx asc";
                $couponListResult = mysqli_query($db_connection, $couponListQuery);

                $total_num = mysqli_num_rows($couponListResult);

                mysqli_close($db_connection);
            }
        }
        ?>

        <div class=" dashboard-margin">

            <div class="dashboard-header">
                <?php if (esc_html(get_post_meta($post->ID, 'page_show_title', true)) != 'no') { ?>
                    <h1 class="entry-title listings-title-dash"><?php //the_title();
                        echo '쿠폰 리스트'; ?></h1>
                <?php } ?>

                <div class="back_to_home">
                    <a href="<?php echo home_url(); ?>"
                       title="home url"><?php esc_html_e('Front page', 'wpestate'); ?></a>
                </div>
            </div>


            <div class="row admin-list-wrapper invoices-wrapper">
                <?php
                /*
                    $args = array(
                        'post_type'        => 'wpestate_invoice',
                        'post_status'      => 'publish',
                        'posts_per_page'   => -1 ,
                        'author'           => $userID,
                        'meta_query'       => array(
                                array(
                                    'key'       => 'invoice_type',
                                    'value'     =>  esc_html__( 'Reservation fee','wpestate'),
                                    'type'      =>  'char',
                                    'compare'   =>  'LIKE'
                                    )
                        ),
                    );



                    $prop_selection = new WP_Query($args);
                    $counter                =   0;
                    $options['related_no']  =   4;
                    $total_confirmed        =   0;
                    $total_issued           =   0;
                    $templates              =   esc_html__( 'No invoices','wpestate');

                    if( $prop_selection->have_posts() ){
                        ob_start();
                        while ($prop_selection->have_posts()): $prop_selection->the_post();
                            get_template_part('templates/invoice_listing_unit');
                            $status = esc_html(get_post_meta($post->ID, 'invoice_status', true));
                            $type   = esc_html(get_post_meta($post->ID, 'invoice_type', true));
                            $price  = esc_html(get_post_meta($post->ID, 'item_price', true));

                            if( trim($type) == 'Reservation fee' || trim($type) == esc_html__( 'Reservation fee','wpestate') ){
                                if($status == 'confirmed' ){
                                    $total_confirmed = $total_confirmed + $price;
                                }
                                if($status == 'issued' ){
                                    $total_issued = $total_issued + $price;
                                }
                            }else{
                                $total_issued='-';
                                $total_confirmed = $total_confirmed + $price;
                            }



                        endwhile;
                        $templates = ob_get_contents();
                        ob_end_clean();
                    }

                        print '<div class="col-md-12 invoice_filters">
                            <div class="col-md-3">
                                <input type="text" id="invoice_start_date" class="form-control" name="invoice_start_date" placeholder="'.esc_html__( 'from date','wpestate').'">
                            </div>

                            <div class="col-md-3">
                                <input type="text" id="invoice_end_date" class="form-control"  name="invoice_end_date" placeholder="'.esc_html__( 'start date','wpestate').'">
                            </div>


                            <div class="col-md-3">
                                <select id="invoice_type" name="invoice_type" class="form-control">
                                    <option value="Upgrade to Featured">'.esc_html__( 'Upgrade to Featured','wpestate').'</option>
                                    <option value="Publish Listing with Featured">'.esc_html__( 'Publish Listing with Featured','wpestate').'</option>
                                    <option value="Package">'.esc_html__( 'Package','wpestate').'</option>
                                    <option value="Listing">'.esc_html__( 'Listing','wpestate').'</option>
                                    <option value="Reservation fee" selected="selected">'.esc_html__( 'Reservation fee','wpestate').'</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <select id="invoice_status" name="invoice_status" class="form-control">
                                    <option value="">'.esc_html__( 'Any','wpestate').'</option>
                                    <option value="confirmed">'.esc_html__( 'confirmed','wpestate').'</option>
                                    <option value="issued">'.esc_html__( 'issued','wpestate').'</option>
                                </select>

                            </div>

                        </div>

                        ';
                        */

                print ' <div class="invoices_explanation">' . $total_num . '개 있습니다.</div>';
                print '<div class="col-md-12 invoice_unit_title">
                    <div class="col-md-2">
                        <strong>#</strong> 
                    </div>
                    <div class="col-md-2">
                        <strong> ' . esc_html__('쿠폰 발급일시') . '</strong> 
                    </div>

                    <div class="col-md-4">
                         <strong> ' . esc_html__('쿠폰 코드') . '</strong> 
                    </div>

                    <div class="col-md-2">
                        <strong> ' . esc_html__('쿠폰 상태') . '</strong> 
                    </div>

                </div>
                ';

                print '<div id="container-invoices">';
                //print $templates;

                for ($i = $total_num; $i > 0; $i--) {
                    $couponListRow = mysqli_fetch_array($couponListResult);

                    $couponCode = $couponListRow["coupon_hash"];
                    $coupon_registered = $couponListRow["coupon_registered"];
                    $coupon_used = $couponListRow["coupon_used"];

                    print '<div class="col-md-12 invoice_unit">
                            <div class="col-md-2">' . $i . '</div>
                            <div class="col-md-2">' . $coupon_registered . '</div>
                            <div class="col-md-4">' . $couponCode . '</div>';

                    if ($coupon_used == 0) {
                        print '<div class="col-md-2">미사용</div>';
                    } else {
                        print '<div class="col-md-2">사용됨</div>';
                    }

                    print '</div>';

                }

                ?>
            </div>
        </div>
    </div>

<?php
wp_reset_query();
get_footer();
?>