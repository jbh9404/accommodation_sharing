<?php


$current_user = wp_get_current_user();
$userID = $current_user->ID;
$user_login = $current_user->user_login;
$add_link = wpestate_get_dasboard_add_listing();
$dash_profile = wpestate_get_dashboard_profile_link();
$dash_pack = get_wpestate_packages_link();
$dash_favorite = wpestate_get_dashboard_favorites();
$dash_link = wpestate_get_dashboard_link();
$dash_searches = wpestate_get_searches_link();
$dash_inbox = get_inbox_wpestate_booking();
$dash_invoice = get_invoices_wpestate();
$dash_my_bookings = wpestate_my_booking_link();
$dash_my_reservations = wpestate_my_reservations_link();
$dash_allinone = wpestate_get_dashboard_allinone();
$activeprofile = '';
$activeedit = '';
$activedash = '';
$activeadd = '';
$activefav = '';
$activesearch = '';
$activemypack = '';
$activeallinone = '';
$activeedit = '';
$activeprice = '';
$activedetails = '';
$activeimages = '';
$activeamm = '';
$activecalendar = '';
$activemybookins = '';
$activemyreservations = '';
$activeinbox = '';
$activelocation = '';
$activeinvoice = '';

$user_pack = get_the_author_meta('package_id', $userID);
$clientId = esc_html(get_option('wp_estate_paypal_client_id', ''));
$clientSecret = esc_html(get_option('wp_estate_paypal_client_secret', ''));
$user_registered = get_the_author_meta('user_registered', $userID);
$user_package_activation = get_the_author_meta('package_activation', $userID);
$home_url = esc_html(home_url());

$usermeta_gender = get_user_meta($userID, "usergender", true);
$usermeta_birthday = get_user_meta($userID, "userbirthday", true);
$usermeta_livein = get_user_meta($userID, "live_in", true);
$usermeta_phone = get_user_meta($userID, "phone", true);
$usermeta_i_speak = get_user_meta($userID, "i_speak", true);
$usermeta_description = get_user_meta($userID, "description", true);

$usermeta_data = array();
array_push($usermeta_data, $usermeta_gender, $usermeta_birthday, $usermeta_livein, $usermeta_phone, $usermeta_i_speak, $usermeta_description);

if (count($usermeta_data) != 6) {
    $profile_finished = false;
} else {
    $not_finished = 0;

    for ($i = 0; $i < 6; $i++) {
        if ($usermeta_data[$i] == "") {
            $not_finished += 1;
        }
    }

    if ($not_finished > 0) {
        $profile_finished = false;
    } else if ($not_finished == 0) {
        $profile_finished = true;
    }
}

$allowed_html = array();

if (basename(get_page_template()) == 'user_dashboard.php') {
    $activedash = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_add_step1.php') {
    $activeadd = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_edit_listing.php') {

    $action = sanitize_text_field(wp_kses($_GET['action'], $allowed_html));
    if ($action == 'description') {
        $activeedit = 'user_tab_active';
    } else if ($action == 'location') {
        $activelocation = 'user_tab_active';
        $activeedit = '';
    } else if ($action == 'price') {
        $activeprice = 'user_tab_active';
        $activeedit = '';
    } else if ($action == 'details') {
        $activedetails = 'user_tab_active';
        $activeedit = '';
    } else if ($action == 'images') {
        $activeimages = 'user_tab_active';
        $activeedit = '';
    } else if ($action == 'amenities') {
        $activeamm = 'user_tab_active';
        $activeedit = '';
    } else if ($action == 'calendar') {
        $activecalendar = 'user_tab_active';
        $activeedit = '';
    }


} else if (basename(get_page_template()) == 'user_dashboard_profile.php') {
    $activeprofile = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_favorite.php') {
    $activefav = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_searches.php') {
    $activesearch = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_inbox.php') {
    $activeinbox = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_invoices.php') {
    $activeinvoice = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_my_bookings.php') {
    $activemybookins = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_my_reservations.php') {
    $activemyreservations = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_edit_listing.php') {
    $activemyreservations = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_packs.php') {
    $activemypack = 'user_tab_active';
} else if (basename(get_page_template()) == 'user_dashboard_allinone.php') {
    $activeallinone = 'user_tab_active';
}

$user_title = get_the_author_meta('title', $userID);
$user_custom_picture = get_the_author_meta('custom_picture', $userID);

$image_id = get_the_author_meta('small_custom_picture', $userID);
$user_small_picture = wp_get_attachment_image_src($image_id, 'thumbnail');
$user_small_picture_img = $user_small_picture[0];
if ($user_small_picture_img == '') {
    $user_small_picture_img = get_template_directory_uri() . '/img/default_user.png';
}


$about_me = get_the_author_meta('description', $userID);
if ($user_custom_picture == '') {
    $user_custom_picture = get_template_directory_uri() . '/img/default_user.png';
}

?>

<div id="user_tab_menu_trigger"><i class="fa fa-user"></i> <?php esc_html_e('User Menu', 'wpestate'); ?></div>

<div class="user_tab_menu col-md-3" id="user_tab_menu_container">

    <div class="profile-image-wrapper">
        <div id="profile-image-menu"
             data-profileurl="<?php echo $user_custom_picture; ?>"
             data-smallprofileurl="<?php echo $image_id; ?>"
             style="background-image: url('<?php echo $user_small_picture_img; ?>');"></div>

        <div class="profile_wellcome"><?php echo $user_login; ?></div>

    </div>


    <?php
    $paid_submission_status = esc_html(get_option('wp_estate_paid_submission', ''));
    $user_pack = get_the_author_meta('package_id', $userID);
    $user_registered = get_the_author_meta('user_registered', $userID);
    $user_package_activation = get_the_author_meta('package_activation', $userID);
    $is_membership = 0;
    if ($paid_submission_status == 'membership' && wpestate_check_user_level()) {
        wpestate_get_pack_data_for_user_top($userID, $user_pack, $user_registered, $user_package_activation);
        $is_membership = 1;
    }


    if ($is_membership == 1) {
        $stripe_profile_user = get_user_meta($userID, 'stripe', true);
        $subscription_id = get_user_meta($userID, 'stripe_subscription_id', true);
        $enable_stripe_status = esc_html(get_option('wp_estate_enable_stripe', ''));
        if ($stripe_profile_user != '' && $subscription_id != '' && $enable_stripe_status === 'yes') {
            echo '<span id="stripe_cancel" data-original-title="' . esc_html__('subscription will be cancel at the end of current period', 'wpestate') . '" data-stripeid="' . $userID . '">' . esc_html__('cancel stripe subscription', 'wpestate') . '</span>';
        }
    }

    ?>


    <div class="user_dashboard_links">
        <?php if ($dash_profile != $home_url) { ?>
            <a href="<?php print $dash_profile; ?>" class="<?php print $activeprofile; ?>"><i
                        class="fa fa-user"></i> <?php esc_html_e('My Profile', 'wpestate'); ?></a>
        <?php } ?>
        <?php if ($dash_pack != $home_url && $paid_submission_status == 'membership' && wpestate_check_user_level()) { ?>
            <a href="<?php print $dash_pack; ?>" class="<?php print $activemypack; ?>"><i
                        class="fa fa-tasks"></i> <?php esc_html_e('My Subscription', 'wpestate'); ?></a>
        <?php } ?>
        <?php if ($dash_link != $home_url && wpestate_check_user_level()) { ?>
            <a href="<?php print $dash_link; ?>" class="<?php print $activedash; ?>"> <i
                        class="fa fa-map-marker"></i> <?php esc_html_e('내 공간'); ?></a>
        <?php } ?>
        <?php if ($add_link != $home_url && wpestate_check_user_level()) {
            $edit_class = ""; ?>

            <?php if ($profile_finished == true) {
                print '<a href="'.$add_link.'" class="'.$activeadd.$edit_class.'"> <i class="fa fa-plus-circle"></i>'.'내 공간 올리기'.'</a>';
            } else {
                print '<a href="javascript:void(0);" class="'.$edit_class.'" onClick="alert(\'프로필을 완성해야 공간을 올릴 수 있습니다.\'); return false;"> <i class="fa fa-plus-circle"></i>'.'내 공간 올리기'.'</a>';
            } ?>

            <?php

            if (isset($_GET['listing_edit']) && is_numeric($_GET['listing_edit'])) {
                $edit_class = " edit_class ";
                $post_id = intval($_GET['listing_edit']);
                $edit_link = wpestate_get_dasboard_edit_listing();
                $edit_link = esc_url_raw(add_query_arg('listing_edit', $post_id, $edit_link));
                $edit_link_desc = esc_url_raw(add_query_arg('action', 'description', $edit_link));
                $edit_link_location = esc_url_raw(add_query_arg('action', 'location', $edit_link));
                $edit_link_price = esc_url_raw(add_query_arg('action', 'price', $edit_link));
                $edit_link_details = esc_url_raw(add_query_arg('action', 'details', $edit_link));
                $edit_link_images = esc_url_raw(add_query_arg('action', 'images', $edit_link));
                $edit_link_amenities = esc_url_raw(add_query_arg('action', 'amenities', $edit_link));
                $edit_link_calendar = esc_url_raw(add_query_arg('action', 'calendar', $edit_link));
                ?>


                <div class=" property_edit_menu">
                    <a href="<?php print $edit_link_desc; ?>"
                       class="<?php print $activeedit; ?>">        <?php esc_html_e('Description', 'wpestate'); ?></a>
                    <a href="<?php print $edit_link_images; ?>"
                       class="<?php print $activeimages; ?>">      <?php esc_html_e('Images', 'wpestate'); ?></a>
                    <a href="<?php print $edit_link_location; ?>"
                       class="<?php print $activelocation; ?>">    <?php esc_html_e('Location', 'wpestate'); ?></a>
                    <a href="<?php print $edit_link_amenities; ?>"
                       class="<?php print $activeamm; ?>">         <?php esc_html_e('Amenities', 'wpestate'); ?></a>
                </div>

                <?php
            } // secondary level
            ?>
        <?php } ?>


        <?php if ($dash_allinone != $home_url && wpestate_check_user_level()) { ?>
        <?php } ?>

        <?php if ($dash_favorite != $home_url) { ?>
            <a href="<?php print $dash_favorite; ?>" class="<?php print $activefav; ?>"><i
                        class="fa fa-heart"></i> <?php esc_html_e('Favorites', 'wpestate'); ?></a>
        <?php } ?>
        <?php if ($dash_searches != $home_url) { ?>
            <a href="<?php print $dash_searches; ?>" class="<?php print $activesearch; ?>"><i
                        class="fa fa-search"></i> <?php esc_html_e('Saved Searches', 'wpestate'); ?></a>
        <?php } ?>
        <?php if ($dash_my_bookings != $home_url && wpestate_check_user_level()) { ?>
            <a href="<?php print $dash_my_bookings; ?>" class="<?php print $activemybookins; ?>"><i
                        class="fa fa-folder-open-o"></i> <?php esc_html_e('My Bookings', 'wpestate'); ?></a>
        <?php } ?>
        <?php if ($dash_my_reservations != $home_url) { ?>
            <a href="<?php print $dash_my_reservations; ?>" class="<?php print $activemyreservations; ?>"><i
                        class="fa fa-folder-open"></i> <?php esc_html_e('My Reservations', 'wpestate'); ?></a>
        <?php } ?>

        <?php if ($dash_inbox != $home_url) { ?>
            <a href="<?php print $dash_inbox; ?>" class="<?php print $activeinbox; ?>"><i
                        class="fa fa-inbox"></i> <?php esc_html_e('My Inbox', 'wpestate'); ?></a>
        <?php } ?>

        <?php if ($dash_invoice != $home_url && wpestate_check_user_level()) { ?>
            <a href="<?php print $dash_invoice; ?>" class="<?php print $activeinvoice; ?>"><i class="fa fa-file-o"></i>
                티켓 리스트</a>
        <?php } ?>

        <a href="<?php echo wp_logout_url(); ?>" title="Logout"><i
                    class="fa fa-power-off"></i> <?php esc_html_e('Log Out', 'wpestate'); ?></a>
    </div>
    <?php // get_template_part('templates/user_memebership_profile');  ?>


</div>