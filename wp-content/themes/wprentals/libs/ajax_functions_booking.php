<?php   


add_action('wp_ajax_wpestate_send_full_pay_reminder', 'wpestate_send_full_pay_reminder' );  
if( !function_exists('wpestate_send_full_pay_reminder') ):
    function wpestate_send_full_pay_reminder(){
        //check owner before delete 
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }


        $userID             =   $current_user->ID;
        $user_email         =   $current_user->user_email;
        $invoice_id         =   intval($_POST['invoice_id']);
        $bookid             =   intval($_POST['booking_id']);
        $property_id        =   get_post_meta($bookid, 'booking_id', true);
        $booking_from_date  =   get_post_meta($bookid, 'booking_from_date', true);
        
        $the_post       =   get_post( $bookid);
        $book_author    =   $the_post->post_author;
        $user           =   get_user_by('id',$book_author); 
        $user_email     =   $user->user_email;
        
        
        $arguments=array(
            'invoice_id'        =>  $invoice_id,
            'booking_id'        =>  $bookid,
            'property_url'      =>  get_permalink($property_id),
            'property_title'    =>  get_the_title($property_id),
            'until_date'        =>  $booking_from_date
        );
           
        
        wpestate_select_email_type($user_email,'full_invoice_reminder',$arguments);    
      
        die();
    }
endif;





////////////////////////////////////////////////////////////////////////////////
/// Ajax  check booking
////////////////////////////////////////////////////////////////////////////////
add_action('wp_ajax_nopriv_wpestate_ajax_check_booking_valability_on_invoice', 'wpestate_ajax_check_booking_valability_on_invoice');  
add_action('wp_ajax_wpestate_ajax_check_booking_valability_on_invoice', 'wpestate_ajax_check_booking_valability_on_invoice' );  
 
if( !function_exists('wpestate_ajax_check_booking_valability_on_invoice') ):
    function wpestate_ajax_check_booking_valability_on_invoice(){
    exit();
        //  check_ajax_referer('booking_ajax_nonce_front','security');
       
        $bookid     =   intval($_POST['bookid']);
        $book_from  =   get_post_meta($bookid, 'booking_from_date', true);
        $book_to    =   get_post_meta($bookid, 'booking_to_date', true);
        $listing_id  =   get_post_meta($bookid, 'booking_id', true);
        
        
        $reservation_array  = get_post_meta($listing_id, 'booking_dates',true);
        if($reservation_array==''){
            $reservation_array = wpestate_get_booking_dates($listing_id);
        }
        
        $from_date      =   new DateTime($book_from);
        $from_date_unix =   $from_date->getTimestamp();

        $to_date        =   new DateTime($book_to);
        $to_date_unix_check   =   $to_date->getTimestamp();
        $to_date->modify('yesterday');
           
        $to_date_unix   =   $to_date->getTimestamp();

      
        
        // checking booking avalability
        while ($from_date_unix < $to_date_unix){
            $from_date->modify('tomorrow');
            $from_date_unix =   $from_date->getTimestamp();
           // print'check '. $from_date_unix.'</br>';
            if( array_key_exists($from_date_unix,$reservation_array ) ){
              //  print '</br> iteration from date'.$from_date_unix. ' / ' .date("Y-m-d", $from_date_unix);
                print 'stop';
                die();
            }
        }
        print 'run';
        die();

    }
endif;


  ////////////////////////////////////////////////////////////////////////////////
/// Ajax  check booking
////////////////////////////////////////////////////////////////////////////////
add_action('wp_ajax_nopriv_wpestate_ajax_check_booking_valability_internal', 'wpestate_ajax_check_booking_valability_internal');  
add_action('wp_ajax_wpestate_ajax_check_booking_valability_internal', 'wpestate_ajax_check_booking_valability_internal' );  
 
if( !function_exists('wpestate_ajax_check_booking_valability_internal') ):
    function wpestate_ajax_check_booking_valability_internal(){
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }
        
        //  check_ajax_referer('booking_ajax_nonce_front','security');
        $book_from  =   esc_html($_POST['book_from']);
        $book_to    =   esc_html($_POST['book_to']);  
        $listing_id =   intval($_POST['listing_id']);
        $internal   =   intval($_POST['internal']);
        
        
        
        $mega       =   wpml_mega_details_adjust($listing_id);
        
        $reservation_array = get_post_meta($listing_id, 'booking_dates',true);
        if($reservation_array==''){
            $reservation_array = wpestate_get_booking_dates($listing_id);
        }
        
        $from_date      =   new DateTime($book_from);
        $from_date_unix =   $from_date->getTimestamp();

        $to_date        =   new DateTime($book_to);
        $to_date_unix_check   =   $to_date->getTimestamp();
        $to_date->modify('yesterday');
           
        $to_date_unix   =   $to_date->getTimestamp();

        //check min days situation
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if($internal==0){
        
            $min_days_booking   =   intval   ( get_post_meta($listing_id, 'min_days_booking', true) );  
            $min_days_value     =   0;

            if (is_array($mega) && array_key_exists ($from_date_unix,$mega)){
                if( isset( $mega[$from_date_unix]['period_min_days_booking'] ) ){
                    $min_days_value=  $mega[$from_date_unix]['period_min_days_booking'];

                    if( ($from_date_unix + ($min_days_value-1)*86400) > $to_date_unix ) {
                        print 'stopdays';
                        die();
                    }

                }

            }else if($min_days_booking > 0 ){
                    if( ($from_date_unix + $min_days_booking*86400) > $to_date_unix ) {
                        print 'stopdays';
                        die();
                    }
            }
        }
        
      
        
        
        
        
        
        // checking booking avalability
        while ($from_date_unix < $to_date_unix){
            $from_date->modify('tomorrow');
            $from_date_unix =   $from_date->getTimestamp();
           // print'check '. $from_date_unix.'</br>';
            if( array_key_exists($from_date_unix,$reservation_array ) ){
              //  print '</br> iteration from date'.$from_date_unix. ' / ' .date("Y-m-d", $from_date_unix);
                print 'stop';
                die();
            }
        }
        print 'run';
        die();

    }
endif;




////////////////////////////////////////////////////////////////////////////////
/// Ajax  check booking
////////////////////////////////////////////////////////////////////////////////
add_action('wp_ajax_nopriv_wpestate_ajax_check_booking_valability', 'wpestate_ajax_check_booking_valability');  
add_action('wp_ajax_wpestate_ajax_check_booking_valability', 'wpestate_ajax_check_booking_valability' );  
 
if( !function_exists('wpestate_ajax_check_booking_valability') ):
    function wpestate_ajax_check_booking_valability(){
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }
        
        $book_from  =   esc_html($_POST['book_from']);
        $book_to    =   esc_html($_POST['book_to']);  
        $listing_id =   intval($_POST['listing_id']);
        $internal   =   intval($_POST['internal']);
        $mega       =   wpml_mega_details_adjust($listing_id);;
        $reservation_array = get_post_meta($listing_id, 'booking_dates',true);
        if($reservation_array==''){
            $reservation_array = wpestate_get_booking_dates($listing_id);
        }
        
        $from_date      =   new DateTime($book_from);
        $from_date_unix =   $from_date->getTimestamp();

        $to_date        =   new DateTime($book_to);
        $to_date_unix_check   =   $to_date->getTimestamp();
        $to_date->modify('yesterday');
           
        $to_date_unix   =   $to_date->getTimestamp();

        //check min days situation
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if($internal==0){
        
            $min_days_booking   =   intval   ( get_post_meta($listing_id, 'min_days_booking', true) );  
            $min_days_value     =   0;

            if (is_array($mega) && array_key_exists ($from_date_unix,$mega)){
                if( isset( $mega[$from_date_unix]['period_min_days_booking'] ) ){
                    $min_days_value=  $mega[$from_date_unix]['period_min_days_booking'];

                    if( ($from_date_unix + ($min_days_value-1)*86400) > $to_date_unix ) {
                        print 'stopdays';
                        die();
                    }

                }

            }else if($min_days_booking > 0 ){
                    if( ($from_date_unix + ($min_days_value-1)*86400) > $to_date_unix ) {
                        print 'stopdays';
                        die();
                    }
            }
        }
        
        // check in check out days
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $checkin_checkout_change_over   =   floatval   ( get_post_meta($listing_id, 'checkin_checkout_change_over', true) ); 
        $weekday                        =   date('N', $from_date_unix);
        $end_bookday                    =   date('N', $to_date_unix_check);
        if (is_array($mega) && array_key_exists ($from_date_unix,$mega)){
            if( isset( $mega[$from_date_unix]['period_checkin_checkout_change_over'] ) &&  $mega[$from_date_unix]['period_checkin_checkout_change_over']!=0 ){
                $period_checkin_checkout_change_over=  $mega[$from_date_unix]['period_checkin_checkout_change_over'];


                if($weekday!= $period_checkin_checkout_change_over || $end_bookday !=$period_checkin_checkout_change_over) {
                    print 'stopcheckinout';
                    die();
                }

            }

        }else if($checkin_checkout_change_over > 0 ){
         //   print ' $checkin_checkout_change_over '.$checkin_checkout_change_over;
         //   print ' $weekday '.$weekday;
         //   print ' $to_date_unix '.$to_date_unix;
         //   print ' $end_bookday'.$end_bookday;
            if($weekday!= $checkin_checkout_change_over || $end_bookday !=$checkin_checkout_change_over) {
                print 'stopcheckinout';
                die();
            }
        }
        
        // check in  days
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $checkin_change_over            =   floatval   ( get_post_meta($listing_id, 'checkin_change_over', true) );  
       
        if (is_array($mega) && array_key_exists ($from_date_unix,$mega)){
            if( isset( $mega[$from_date_unix]['period_checkin_change_over'] ) &&  $mega[$from_date_unix]['period_checkin_change_over']!=0){
                $period_checkin_change_over=  $mega[$from_date_unix]['period_checkin_change_over'];


                if($weekday!= $period_checkin_change_over) {
                    print 'stopcheckin';
                    die();
                }

            }

        }else if($checkin_change_over > 0 ){
            if($weekday!= $checkin_change_over) {
                print 'stopcheckin';
                die();
            }
        }
       
        
        
       // print_r($reservation_array);
        
        if( array_key_exists($from_date_unix,$reservation_array ) ){
              //  print '</br> iteration from date'.$from_date_unix. ' / ' .date("Y-m-d", $from_date_unix);
                print 'stop';
                die();
        }
            
        // checking booking avalability
        while ($from_date_unix < $to_date_unix){
            $from_date->modify('tomorrow');
            $from_date_unix =   $from_date->getTimestamp();
           // print'check '. $from_date_unix.'</br>';
            if( array_key_exists($from_date_unix,$reservation_array ) ){
              //  print '</br> iteration from date'.$from_date_unix. ' / ' .date("Y-m-d", $from_date_unix);
                print 'stop';
                die();
            }
        }
        print 'run';
        die();

    }
endif;


////////////////////////////////////////////////////////////////////////////////
/// Ajax message reply
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_message_reply', 'wpestate_message_reply');  
add_action('wp_ajax_wpestate_message_reply', 'wpestate_message_reply' );  
 
if( !function_exists('wpestate_message_reply') ):
    function wpestate_message_reply(){

        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;
        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }
        
        
        
        wp_reset_postdata();
        wp_reset_query();
        
        $messid         =   intval($_POST['messid']);
        $title          =   esc_html($_POST['title']);  
        $content        =   esc_html($_POST['content']);
        $receiver_id    =   wpsestate_get_author($messid);
        
        $message_to_user    = get_post_meta($messid,'message_to_user',true);
       
      
        if( $current_user->ID != $message_to_user && $current_user->ID != $receiver_id ) {
            exit('you don\'t have the right');
        }

        
        // print 'lala '.$messid. '/  receiver_id: '.$receiver_id.' /!!! ';
  

        $my_post = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_status'   => 'publish',
            'post_type'     => 'wpestate_message',
            'post_author'   => $userID, 
            'post_parent'   => $messid
        );

        $post_id = wp_insert_post( $my_post );
       
        //update_post_meta($post_id, 'message_status', 'unread');
        update_post_meta($post_id, 'delete_source', 0);
        update_post_meta($post_id, 'delete_destination', 0);
        update_post_meta($post_id, 'message_to_user', $receiver_id);

       // print $messid. '/  receiver_id'.$receiver_id.' / ';
        $mes_to =  get_post_meta($messid, 'message_to_user',true );
        $mess_from= get_post_meta($messid, 'message_from_user',true );
              
        update_post_meta($messid, 'message_status'.$mes_to, 'unread' );
        update_post_meta($messid, 'message_status'.$mess_from, 'unread' );
        update_post_meta($messid, 'message_status'.$userID, 'read' );
         
        $email_sender=get_userdata($userID);
        update_post_meta($post_id, 'message_from_user', $userID);


     //   $receiver       =   get_userdata($receiver_id);
     //   $receiver_email    =   $receiver->user_email;
     //   $receiver_login    =   $receiver->user_login;
        
        // decide who is receiver 
        if($userID == $mes_to ){
            $receiver       =   get_userdata($mess_from);
            $receiver_email    =   $receiver->user_email;
            wpestate_send_booking_email('inbox',$receiver_email,$content);
        }else{
            
            $receiver       =   get_userdata($mes_to);
            $receiver_email    =   $receiver->user_email;
            wpestate_send_booking_email('inbox',$receiver_email,$content);
        }

        //wpestate_send_booking_email('inbox',$receiver_email);
        print $post_id;
        die();
    }
endif;



////////////////////////////////////////////////////////////////////////////////
/// Ajax  delete invoice
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_booking_mark_as_read', 'wpestate_booking_mark_as_read');  
add_action('wp_ajax_wpestate_booking_mark_as_read', 'wpestate_booking_mark_as_read' );  
 
if( !function_exists('wpestate_booking_mark_as_read') ):
    function wpestate_booking_mark_as_read(){
        
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }
    
        $messid         =   intval($_POST['messid']);
        $receiver_id    =   wpsestate_get_author($messid);
        $message_to_user    = get_post_meta($messid,'message_to_user',true);
      
       
   
        if( $current_user->ID != $message_to_user && $current_user->ID != $receiver_id ) {
            exit('you don\'t have the right');
        }

        
    
        update_post_meta($messid, 'message_status'.$current_user->ID, 'read');
        die();
    }
endif;

////////////////////////////////////////////////////////////////////////////////
/// Ajax  delete invoice
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_booking_delete_mess', 'wpestate_booking_delete_mess');  
add_action('wp_ajax_wpestate_booking_delete_mess', 'wpestate_booking_delete_mess' );  
 
if( !function_exists('wpestate_booking_delete_mess') ):
    function wpestate_booking_delete_mess(){
       
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;

        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
      
        if($userID === 0 ){
            exit('out pls');
        }


        $userID             =   $current_user->ID;
        $messid             =   intval($_POST['messid']);
        $receiver_id        =   wpsestate_get_author($messid);
        $message_to_user    =   get_post_meta($messid,'message_to_user',true);
      
        if( $current_user->ID != $message_to_user && $current_user->ID != $receiver_id ) {
            exit('you don\'t have the right');
        }

        update_post_meta($messid, 'delete_destination'.$userID, 1);
        die();
    }
endif;

////////////////////////////////////////////////////////////////////////////////
/// Ajax  delete invoice
////////////////////////////////////////////////////////////////////////////////

//add_action('wp_ajax_nopriv_wpestate_create_pay_user_invoice_form', 'wpestate_create_pay_user_invoice_form');  
add_action('wp_ajax_wpestate_create_pay_user_invoice_form', 'wpestate_create_pay_user_invoice_form' );  
 
if( !function_exists('wpestate_create_pay_user_invoice_form') ):
    function wpestate_create_pay_user_invoice_form(){
        //check owner before delete 
        $current_user   =   wp_get_current_user();
        $userID         =   $current_user->ID;
        $user_email     =   $current_user->user_email;

        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }

        
        $bookid         =   intval($_POST['booking_id']); 
        $the_post       =   get_post( $bookid); 
        $is_full        =   intval($_POST['is_full']);
        $invoice_id     =   intval($_POST['invoice_id']);
        $bookid         =   intval($_POST['booking_id']);
        
        if( $current_user->ID != $the_post->post_author ) {
            exit('you don\'t have the right to see this');
        }


        if($is_full!=1){
            if( !wpestate_check_reservation_period($bookid)){
                die('');
            }
        }
    
     
    

        $currency               =   esc_html( get_post_meta($invoice_id, 'invoice_currency',true) );
        $default_price          =   get_post_meta($invoice_id, 'default_price', true);
        $where_currency         =   esc_html( get_option('wp_estate_where_currency_symbol', '') );      
        $booking_from_date      =   esc_html(get_post_meta($bookid, 'booking_from_date', true));
        $property_id            =   esc_html(get_post_meta($bookid, 'booking_id', true));
        $booking_to_date        =   esc_html(get_post_meta($bookid, 'booking_to_date', true)); 
        $booking_guests         =   floatval(get_post_meta($bookid, 'booking_guests', true));
        $booking_array          =   wpestate_booking_price($booking_guests,$invoice_id,$property_id, $booking_from_date, $booking_to_date);
        $price_per_weekeend     =   floatval(get_post_meta($property_id, 'price_per_weekeend', true));
       
     
        if($booking_array['numberDays']>7 && $booking_array['numberDays']< 30){
            $default_price=$booking_array['week_price'];
        }else if($booking_array['numberDays']>30){
            $default_price=$booking_array['month_price'];
        }
       
        $wp_estate_book_down            =   get_post_meta($invoice_id, 'invoice_percent', true);
        $wp_estate_book_down_fixed_fee  =   get_post_meta($invoice_id, 'invoice_percent_fixed_fee', true);
        $include_expeses                =   esc_html ( get_option('wp_estate_include_expenses','') );
        $invoice_price                  =   floatval( get_post_meta($invoice_id, 'item_price', true)) ;
      
        if($include_expeses=='yes'){
            $total_price_comp               =   $invoice_price;       
        }else{
            $total_price_comp               =   $invoice_price - $booking_array['city_fee'] - $booking_array['cleaning_fee'];  
        }
      
       
        
        $depozit                    =   wpestate_calculate_deposit($wp_estate_book_down,$wp_estate_book_down_fixed_fee,$total_price_comp);
        $balance                    =   $invoice_price-$depozit;
        $price_show                 =   wpestate_show_price_booking_for_invoice($default_price,$currency,$where_currency,0,1);
        $price_per_weekeend_show    =   wpestate_show_price_booking_for_invoice($price_per_weekeend,$currency,$where_currency,0,1);
        $total_price_show           =   wpestate_show_price_booking_for_invoice($invoice_price,$currency,$where_currency,0,1);
        $depozit_show               =   wpestate_show_price_booking_for_invoice($depozit,$currency,$where_currency,0,1);
        $balance_show               =   wpestate_show_price_booking_for_invoice($balance,$currency,$where_currency,0,1);
        $city_fee_show              =   wpestate_show_price_booking_for_invoice($booking_array['city_fee'],$currency,$where_currency,0,1);
        $cleaning_fee_show          =   wpestate_show_price_booking_for_invoice($booking_array['cleaning_fee'],$currency,$where_currency,0,1);
        $inter_price_show           =   wpestate_show_price_booking_for_invoice($booking_array['inter_price'],$currency,$where_currency,0,1);      
        $total_guest                =   wpestate_show_price_booking_for_invoice($booking_array['total_extra_price_per_guest'],$currency,$where_currency,1,1); 
        $guest_price                =   wpestate_show_price_booking_for_invoice($booking_array['extra_price_per_guest'],$currency,$where_currency,1,1); 
        $extra_price_per_guest      =   wpestate_show_price_booking($booking_array['extra_price_per_guest'],$currency,$where_currency,1);
       
      
      // print_r($booking_array);

        $depozit_stripe     =   $depozit*100;
        $details            =   get_post_meta($invoice_id, 'renting_details', true);

        
      

        // strip details generation
        require_once get_template_directory().'/libs/stripe/lib/Stripe.php';
        $stripe_secret_key              =   esc_html( get_option('wp_estate_stripe_secret_key','') );
        $stripe_publishable_key         =   esc_html( get_option('wp_estate_stripe_publishable_key','') );

        $stripe = array(
          "secret_key"      => $stripe_secret_key,
          "publishable_key" => $stripe_publishable_key
        );

        Stripe::setApiKey($stripe['secret_key']);


        $pages = get_pages(array(
        'meta_key' => '_wp_page_template',
        'meta_value' => 'stripecharge.php'
            ));

        if( $pages ){
            $processor_link = esc_url ( get_permalink( $pages[0]->ID) );
        }else{
            $processor_link=esc_html( home_url() );
        }

  


        print '              
            <div class="create_invoice_form">
                   <h3>'.esc_html__( 'Invoice INV','wpestate').$invoice_id.'</h3>

                   <div class="invoice_table">
                       <div class="invoice_data">
                            <span class="date_interval"><span class="invoice_data_legend">'.esc_html__( 'Period','wpestate').' : </span>'.$booking_from_date.' '.esc_html__( 'to','wpestate').' '.$booking_to_date.'</span>
                            <span class="date_duration"><span class="invoice_data_legend">'.esc_html__( 'No of nights','wpestate').': </span>'.$booking_array['numberDays'].'</span>
                            <span class="date_duration"><span class="invoice_data_legend">'.esc_html__( 'Guests','wpestate').': </span>'.$booking_guests.'</span>';
                            /*if($booking_array['price_per_guest_from_one']==1){
                                print'<span class="date_duration"><span class="invoice_data_legend">'.esc_html__( 'Price per Guest','wpestate').': </span>'; 
                                print $extra_price_per_guest;
                                print'</span>';
                            }else{
                                print '<span class="date_duration"><span class="invoice_data_legend">'.esc_html__( 'Price per night','wpestate').': </span>';
                          
                                print $price_show;
                                if($booking_array['has_custom']){
                                    print ', '.esc_html__('has custom price','wpestate');
                                }
                                
                                
                                if($booking_array['cover_weekend']){
                                    print ', '.esc_html__('has weekend price of','wpestate').' '.$price_per_weekeend_show;
                                }
                            
                                print'</span>';



                                print '</span>';

                                if($booking_array['has_custom']){
                                    print '<span class="invoice_data_legend">'.__('Price details:','wpestate').'</span>';
                                    foreach($booking_array['custom_price_array'] as $date=>$price){
                                        $day_price = wpestate_show_price_booking_for_invoice($price,$currency,$where_currency,1,1); 
                                        print '<span class="price_custom_explained">'.__('on','wpestate').' '.date("Y-m-d",$date).' '.__('price is','wpestate').' '.$day_price.'</span>';
                                    }
                                }
                            
                            }*/
                        print '    
                        </div>

                        <div class="invoice_details">
                            <div class="invoice_row header_legend">
                               <span class="inv_legend">'.esc_html__( 'Cost','wpestate').'</span>
                               <span class="inv_data">  '.esc_html__( 'Price','wpestate').'</span>
                               <span class="inv_exp">   '.esc_html__( 'Detail','wpestate').'</span>
                            </div>';
                        $computed_total=0;
                        
        

                        foreach($details as $detail){
                            print'<div class="invoice_row invoice_content">
                                    <span class="inv_legend">  '.$detail[0].'</span>
                                    <span class="inv_data">  '. wpestate_show_price_booking_for_invoice($detail[1],$currency,$where_currency,0,1).'</span>
                                    <span class="inv_exp">';
                                        if(trim($detail[0])==esc_html__('Security Deposit','wpestate') || trim($detail[0])==esc_html__('Security Depozit','wpestate')){ 
                                            esc_html_e('*refundable' ,'wpestate');
                                        }
                                        if(trim($detail[0])==esc_html__( 'Subtotal','wpestate')){ 
                                            if($booking_array['price_per_guest_from_one']==1){
                                                print  $extra_price_per_guest.' x '.$booking_array['count_days'].' '.esc_html__( 'nights','wpestate').' x '.$booking_array['curent_guest_no'].' '.esc_html__( 'guests','wpestate');
                                            
                                                if($booking_array['price_per_guest_from_one']==1 && $booking_array['custom_period_quest']==1){
                                                    echo " - ".esc_html__("period with custom price per guest","wpestate"); 
                                                }
                                                
                                                
                                            }else{
                                                echo $booking_array['numberDays'].' '.esc_html__( 'nights','wpestate').' x ';
                                                if($booking_array['cover_weekend']){
                                                    print esc_html__('has weekend price of','wpestate').' '.$price_per_weekeend_show;
                                                }else{
                                                    if ( $booking_array['has_custom']==1  ){
                                                        print esc_html__( 'custom price','wpestate');
                                                    }else{
                                                        print  $price_show;
                                                    }
                                                }
                                                
                                               
                                            }
                        
                                        }
                            
                                        if(trim($detail[0])==esc_html__( 'Extra Guests','wpestate')){ 
                                            print $booking_array['numberDays'].' '.esc_html__( 'nights','wpestate').' x '.$booking_array['extra_guests'].' '.esc_html__('extra guests','wpestate');
                                            if ( $booking_array['custom_period_quest']==1 ){
                                                echo  esc_html__(" , period with custom price per guest","wpestate");
                                            }
                                        }
                                        
                                        if(isset($detail[2])){
                                            echo $detail[2];
                                        }
                                        
                                        
                                    print'
                                    </span>
                                </div>';
                        }

                        print ' 
                            <div class="invoice_row invoice_total total_inv_span total_invoice_for_payment">
                               <span class="inv_legend"><strong>'.esc_html__( 'Total','wpestate').'</strong></span>
                               <span class="inv_data" id="total_amm" data-total="'.$invoice_price.'">'.$total_price_show.'</span></br>
                               
                               <span class="inv_legend">'.esc_html__( 'Reservation Fee Required','wpestate').':</span> <span class="inv_depozit depozit_show" data-value="'.$depozit.'"> '.$depozit_show.'</span></br>
                               <span class="inv_legend">'.esc_html__( 'Balance Owing','wpestate').':</span> <span class="inv_depozit balance_show"  data-value="'.$balance.'">'.$balance_show.'</span>
                           </div>
                       </div>';

                    $is_paypal_live= esc_html ( get_option('wp_estate_enable_paypal','') );
                    $is_stripe_live= esc_html ( get_option('wp_estate_enable_stripe','') );
                    $submission_curency_status  =   esc_html( get_option('wp_estate_submission_curency','') );

                    if($is_full!=1){
                        if( $balance>0 ){
                            print '<div class="invoice_pay_status_note">'.__('You are paying only the deposit required to confirm the booking:','wpestate').' '.$depozit_show.'</div>';
                            print '<div class="invoice_pay_status_note">'.__('You will need to pay the remain balance until the first day of your period!','wpestate').'</div>';
                       
                            }
                        
                    }else{
                        if( $balance>0 ){
                            $depozit_stripe =   $balance*100;
                            $depozit        =   $balance;
                            print '<div class="invoice_pay_status_note">'.__('쿠폰으로 결제해주세요.').' <a href="http://nagne.kr/shop/">(쿠폰 구매하기)</a></div><input type="hidden" id="is_full_pay" value="'.$balance.'">';
                        }
                    }
                    
                    
                   // print '<span class="pay_notice_booking">'.esc_html__( 'Pay Deposit & Confirm Reservation','wpestate').'</span>';
                   /* if ( $is_stripe_live=='yes'){
                        print ' 
                        <form action="'.$processor_link.'" method="post" class="booking_form_stripe">
                            <script src="https://checkout.stripe.com/checkout.js" 
                            class="stripe-button"
                            data-key="'. $stripe['publishable_key'].'"
                            data-amount="'.$depozit_stripe.'" 
                            data-zip-code="true"
                            data-email="'.$user_email.'"
                            data-currency="'.$submission_curency_status.'"
                            data-label="'.esc_html__( 'Pay with Credit Card','wpestate').'"
                            data-description="Reservation Payment">
                            </script>
                            <input type="hidden" name="booking_id" value="'.$bookid.'">
                            <input type="hidden" name="invoice_id" value="'.$invoice_id.'">
                            <input type="hidden" name="userID" value="'.$userID.'">
                            <input type="hidden" name="depozit" value="'.$depozit_stripe.'">
                        </form>';
                    }
                    if ( $is_paypal_live=='yes'){
                        print '<span id="paypal_booking" data-propid="'.$property_id.'" data-deposit="'.$depozit.'" data-bookid="'.$bookid.'" data-invoiceid="'.$invoice_id.'">'.esc_html__( 'Pay with Paypal','wpestate').'</span>';
                    }*/
                    $enable_direct_pay      =   esc_html ( get_option('wp_estate_enable_direct_pay','') );

                    if ( $enable_direct_pay=='yes'){
                        print '<span id="direct_pay_booking" data-propid="'.$property_id.'" data-bookid="'.$bookid.'" data-invoiceid="'.$invoice_id.'">'.esc_html__( '쿠폰으로 결제').'</span>';
                    }
                  print'
                  </div>


            </div>';
        die();
    }
endif;



if( !function_exists('wpestate_randomStringStripe') ):
    function wpestate_randomStringStripe()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 10; $i++) {
            $randstring = $characters[rand(0, strlen($characters))];
        }
        return $randstring;
    }
endif;

////////////////////////////////////////////////////////////////////////////////
/// Ajax  delete invoice
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_delete_invoice', 'wpestate_delete_invoice');  
add_action('wp_ajax_wpestate_delete_invoice', 'wpestate_delete_invoice' );  
if( !function_exists('wpestate_delete_invoice') ):
    function wpestate_delete_invoice(){

        //check owner before delete 
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }

        $userID         =   $current_user->ID;
        $invoice_id     =   intval($_POST['invoice_id']);
        $booking_id     =   intval($_POST['booking_id']); 
        $user_id        =   wpse119881_get_author($invoice_id);

        update_post_meta($booking_id, 'booking_invoice_no', '');
        update_post_meta($booking_id, 'booking_status', 'request');
        
        
        $rcapi_invoice_id = get_post_meta($invoice_id, 'rcapi_invoice_id', true);
        if($invoice_id!='' &&  $user_id == $userID ){
            wp_delete_post($invoice_id);
        }
        
       
        rcapi_delete_invoice($invoice_id,$rcapi_invoice_id,$original_user_id);
        die();   
    }
endif;

////////////////////////////////////////////////////////////////////////////////
/// Ajax  delete booking
////////////////////////////////////////////////////////////////////////////////

//add_action('wp_ajax_nopriv_wpestate_delete_booking_request', 'wpestate_delete_booking_request');  
add_action('wp_ajax_wpestate_delete_booking_request', 'wpestate_delete_booking_request' );  
if( !function_exists('wpestate_delete_booking_request') ): 
    function wpestate_delete_booking_request(){
        
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;

        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
   
        if($userID === 0 ){
            exit('out pls');
        }
        
        $bookid      =   intval($_POST['booking_id']);  
        $is_user     =   intval($_POST['isuser']);
        $invoice_id  =   get_post_meta($bookid, 'booking_invoice_no', 'true');

        $lisiting_id            =   get_post_meta($bookid, 'booking_id', true);
        $reservation_array      =   wpestate_get_booking_dates($lisiting_id);
        update_post_meta($lisiting_id, 'booking_dates', $reservation_array); 


        $user_id           =   wpse119881_get_author($lisiting_id);
        $boooking_owner    =   wpse119881_get_author($bookid);
        $receiver          =   get_userdata($boooking_owner);
        $receiver_email    =   $receiver->user_email;
        $receiver_name     =   $receiver->user_login;

        if( ($user_id!=$userID) && ($boooking_owner!=$userID) ){
            exit('out pls w2');
        }
        
       
        $from             =   $current_user->ID;

        if($is_user==1){

            $prop_id    =   get_post_meta($bookid, 'booking_id', true);
            $to_id      =   wpse119881_get_author($prop_id);   
            $to_userdata=   get_userdata($to_id);
            $to_email   =   $to_userdata->user_email;
       
            wpestate_send_booking_email('deletebookinguser',$to_email);
            $subject        =   esc_html__( 'Request Cancelled','wpestate');
            $description    =   esc_html__( 'User ','wpestate').$receiver_name.esc_html__( ' cancelled his booking request','wpestate');
            wpestate_add_to_inbox($userID,$from,$to_id,$subject,$description,"isfirst");
        }else{
            wpestate_send_booking_email('deletebooking',$receiver_email);
            $subject        =   esc_html__( 'Request Denied','wpestate');
            $description    =   esc_html__( 'Your booking request was denied.','wpestate');
            wpestate_add_to_inbox($userID,$from,$boooking_owner,$subject,$description,"isfirst");
        }


        if($invoice_id!=''){
            wp_delete_post($invoice_id);
        }
        echo $bookid.'/'.$userID;
        
      
        $rcapi_booking_id   =   get_post_meta($bookid,'rcapi_booking_id',true);
        $booking_details =array(
                'booking_status'            => 'canceled',
                'booking_status_full'        => 'canceled',
           
        );
        rcapi_edit_booking($bookid,$rcapi_booking_id,$booking_details);


        
        wp_delete_post($bookid);
       
       
                
                
        die();

    }

endif;


////////////////////////////////////////////////////////////////////////////////
/// Cancel own booking
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_cancel_own_booking', 'wpestate_cancel_own_booking');  
add_action('wp_ajax_wpestate_cancel_own_booking', 'wpestate_cancel_own_booking' );  
if( !function_exists('wpestate_cancel_own_booking') ): 
    function wpestate_cancel_own_booking(){
        $current_user = wp_get_current_user();
        $userID         =   $current_user->ID;
        
        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }

        
        $from           =   $current_user->user_login;
        $bookid         =   intval($_POST['booking_id']);  
        $listing_id     =   intval($_POST['listing_id']);     
        $invoice_id     =   get_post_meta($bookid, 'booking_invoice_no', 'true');
        
        if($listing_id==0 || $bookid==0 ){
            exit('buh');
        }
        
        $the_post= get_post( $listing_id); 
        if( $current_user->ID != $the_post->post_author ) {
            exit('you don\'t have the right to delete this');
        }    
            
        $user_id           =   wpse119881_get_author($bookid);
        $receiver          =   get_userdata($user_id);
        $receiver_email    =   $receiver->user_email;
        $receiver_name     =   $receiver->user_login;
         
        
        wpestate_send_booking_email('deletebookingconfirmed',$receiver_email);
        $to                 =   $userID;

        $subject    =esc_html__( 'Your reservation was canceled','wpestate');
        $description=esc_html__( 'Your reservation was canceled by property owner','wpestate');
        //wpestate_add_to_inbox($userID,$from,$to,$subject,$description);

        $rcapi_booking_id   =   get_post_meta($bookid,'rcapi_booking_id',true);
        $booking_details =array(
                'booking_status'            => 'canceled',
                'booking_status_full'        => 'canceled',
           
        );
        print $bookid.' $rcapi_booking_id '.$rcapi_booking_id;
        rcapi_edit_booking($bookid,$rcapi_booking_id,$booking_details);


        
        
        wp_delete_post($bookid);
        $reservation_array      =   wpestate_get_booking_dates($listing_id);
        
        foreach($reservation_array as $key=>$value){
            if ($value == $bookid){
               unset($reservation_array[$key]);
            }
        }
        
        update_post_meta($listing_id, 'booking_dates', $reservation_array); 
  
        if($invoice_id!=''){
            wp_delete_post($invoice_id);
        }

       //  print 'dda1';
       // rcapi_delete_booking($bookid,$rcapi_booking_id,$userID,1);
        
     
    

      
        die();

    }

endif;


////////////////////////////////////////////////////////////////////////////////
/// USER Cancel own booking
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_user_cancel_own_booking', 'wpestate_user_cancel_own_booking');  
add_action('wp_ajax_wpestate_user_cancel_own_booking', 'wpestate_user_cancel_own_booking' );  
if( !function_exists('wpestate_user_cancel_own_booking') ): 
    function wpestate_user_cancel_own_booking(){
        $current_user = wp_get_current_user();
        
        $userID         =   $current_user->ID;
        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }
        
        $bookid         =   intval($_POST['booking_id']);  
        $listing_id     =   intval($_POST['listing_id']);     
        $invoice_id     =   get_post_meta($bookid, 'booking_invoice_no', 'true');
        
        if($listing_id==0 || $bookid==0 ){
            exit('buh');
        }
        
        $the_post= get_post( $bookid); 
        if( $current_user->ID != $the_post->post_author ) {
            exit('you don\'t have the right to delete this');
        }    
        
        wp_delete_post($bookid);
        $reservation_array      =   wpestate_get_booking_dates($listing_id);
        
        foreach($reservation_array as $key=>$value){
            if ($value == $bookid){
               unset($reservation_array[$key]);
            }
        }
        
        update_post_meta($listing_id, 'booking_dates', $reservation_array); 
  
        if($invoice_id!=''){
            wp_delete_post($invoice_id);
        }




        die();

    }

endif;


if( !function_exists('wpestate_check_for_booked_time') ): 
    function wpestate_check_for_booked_time($book_from,$book_to,$reservation_array){
    
        $from_date      =   new DateTime($book_from);
        $from_date_unix =   $from_date->getTimestamp();

        $to_date                =   new DateTime($book_to);
        $to_date_unix_check     =   $to_date->getTimestamp();
       
           
        $to_date_unix   =   $to_date->getTimestamp();

         
        // checking booking avalability
        while ($from_date_unix < $to_date_unix){
            if( array_key_exists($from_date_unix,$reservation_array ) ){
                print 'stop';
                die();
            }
            $from_date->modify('tomorrow');
            $from_date_unix =   $from_date->getTimestamp();
        }
    }
endif;
    

////////////////////////////////////////////////////////////////////////////////
/// Ajax  add invoice
////////////////////////////////////////////////////////////////////////////////


//add_action('wp_ajax_nopriv_wpestate_add_booking_invoice', 'wpestate_add_booking_invoice');  
add_action('wp_ajax_wpestate_add_booking_invoice', 'wpestate_add_booking_invoice' );  
if( !function_exists('wpestate_add_booking_invoice') ): 
    function wpestate_add_booking_invoice(){
        $price =(double) round ( floatval($_POST['price']),2 )  ;  
    
         
         
        $current_user =     wp_get_current_user();
        $userID       =     $current_user->ID;
        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }


        
        check_ajax_referer('create_invoice_ajax_nonce','security');
        $is_confirmed   =   intval($_POST['is_confirmed']); 
        $bookid         =   intval($_POST['bookid']); 
        $book_from      =   get_post_meta($bookid, 'booking_from_date', true);
        $book_to        =   get_post_meta($bookid, 'booking_to_date', true);
        $listing_id     =   get_post_meta($bookid, 'booking_id', true);
        
        $the_post= get_post( $listing_id); 
  
        if( $current_user->ID != $the_post->post_author ) {
            exit('you don\'t have the right to see this');
        }

        // prepare
        $full_pay_invoice_id        =   0;
        $early_bird_percent         =   floatval(get_post_meta($listing_id, 'early_bird_percent', true));
        $early_bird_days            =   floatval(get_post_meta($listing_id, 'early_bird_days', true));
        $taxes_value                =   floatval(get_post_meta($listing_id, 'property_taxes', true));
        
        //check if period already reserverd
        $reservation_array  = get_post_meta($listing_id, 'booking_dates',true);
        if($reservation_array==''){
            $reservation_array = wpestate_get_booking_dates($listing_id);
        }
        
        wpestate_check_for_booked_time($book_from,$book_to,$reservation_array);
        // end check
       
       
        // we proceed with issuing the invoice
        $allowed_html   =   array();
        $details        =   $_POST['details'];
        $manual_expenses=   $_POST['manual_expenses'];
        $billing_for    =   esc_html__( 'Reservation fee','wpestate');
        $type           =   esc_html__( 'One Time','wpestate');
        $pack_id        =   $bookid; // booking id
       
        $time           =   time(); 
        $date           =   date('Y-m-d H:i:s',$time); 
        $user_id        =   wpse119881_get_author($bookid);
        $is_featured    =   '';
        $is_upgrade     =   '';
        $paypal_tax_id  =   '';

        
       
        // get the booking array
        $invoice_id          =   0;
        $booking_guests      =   get_post_meta($bookid, 'booking_guests', true);   
        $extra_options       =   esc_html(get_post_meta($bookid, 'extra_options', true));
        $extra_options_array =   explode(',', $extra_options);
        $booking_array       =   wpestate_booking_price($booking_guests,$invoice_id, $listing_id, $book_from, $book_to,$bookid,$extra_options_array,$manual_expenses);
        // done
                
  

        $invoice_id                 =  wpestate_booking_insert_invoice($billing_for,$type,$pack_id,$date,$user_id,$is_featured,$is_upgrade,$paypal_tax_id,$details,$price);       
        $submission_curency_status  = wpestate_curency_submission_pick();
      
        
       
        // update booking data
        update_post_meta($bookid, 'full_pay_invoice_id', $full_pay_invoice_id);
        update_post_meta($bookid, 'booking_taxes', $taxes_value);
        update_post_meta($bookid, 'early_bird_percent', $early_bird_percent);
        update_post_meta($bookid, 'early_bird_days', $early_bird_days);
        update_post_meta($bookid, 'security_deposit', $booking_array['security_deposit']);
        update_post_meta($bookid, 'booking_taxes', $booking_array['taxes']);
        update_post_meta($bookid, 'service_fee', $booking_array['service_fee']);
        update_post_meta($bookid, 'youearned', $booking_array['youearned']);
        update_post_meta($bookid, 'to_be_paid',$booking_array['deposit'] );
        update_post_meta($bookid, 'booking_status', 'waiting');
        update_post_meta($bookid, 'booking_invoice_no', $invoice_id);
        update_post_meta($bookid, 'total_price', $booking_array['total_price']);
        update_post_meta($bookid, 'balance'  , $booking_array['balance']);
  
        //update invoice data
        update_post_meta($invoice_id, 'booking_taxes', $taxes_value);
        update_post_meta($invoice_id, 'security_deposit', $booking_array['security_deposit']);
        update_post_meta($invoice_id, 'early_bird_percent', $early_bird_percent);
        update_post_meta($invoice_id, 'early_bird_days', $early_bird_days);
        update_post_meta($invoice_id, 'booking_taxes', $booking_array['taxes']);
        update_post_meta($invoice_id, 'service_fee', $booking_array['service_fee']);
        update_post_meta($invoice_id, 'youearned', $booking_array['youearned'] );
        update_post_meta($invoice_id, 'depozit_to_be_paid', $booking_array['deposit'] );
        update_post_meta($invoice_id, 'balance'  , $booking_array['balance']);
        update_post_meta($invoice_id, 'manual_expense',$manual_expenses);
        
        $cleaning_fee_per_day       =   floatval(get_post_meta($listing_id, 'cleaning_fee_per_day', true));
        $city_fee_per_day           =   floatval(get_post_meta($listing_id, 'city_fee_per_day', true));
        $city_fee_percent           =   floatval(get_post_meta($listing_id, 'city_fee_percent', true));
        
        update_post_meta($invoice_id, 'cleaning_fee_per_day',$cleaning_fee_per_day);
        update_post_meta($invoice_id, 'city_fee_per_day',$city_fee_per_day);
        update_post_meta($invoice_id, 'city_fee_percent',$city_fee_percent);
            
        
      
        
        $booking_details=array(
            'total_price'           =>  $booking_array['total_price'],
            'to_be_paid'            =>  $booking_array['deposit'],
            'youearned'             =>  $booking_array['youearned'],
            'full_pay_invoice_id'   =>  $full_pay_invoice_id,
            'service_fee'           =>  $booking_array['service_fee'],
            'booking_taxes'         =>  $booking_array['taxes'],
            'security_deposit'      =>  $booking_array['security_deposit'],
            'booking_status'        =>  'waiting',
            'booking_invoice_no'    =>  $booking_invoice_no,
            'balance'               =>  $booking_array['balance']
        );
        if($is_confirmed==1){
            update_post_meta($bookid, 'booking_status', 'confirmed'); 
            $booking_detail['booking_status']='confirmed';
        }
     
        
        $rcapi_booking_id = get_post_meta($bookid,'rcapi_booking_id',true);
        update_post_meta($invoice_id, 'custom_price_array',$booking_array['custom_price_array']);
        
       
        
        
        $invoice_details=array(
            "invoice_status"                =>  "issued",
            "purchase_date"                 =>  $date,
            "buyer_id"                      =>  $user_id,
            "item_price"                    =>  $booking_array['total_price'],
            "rcapi_booking_id"              =>  $rcapi_booking_id,
            "orignal_invoice_id"            =>  $invoice_id,
            "billing_for"                   =>  $billing_for,
            "type"                          =>  $type,
            "pack_id"                       =>  $pack_id,
            "date"                          =>  $date,
            "user_id"                       =>  $user_id,
            "is_featured"                   =>  $is_featured,
            "is_upgrade"                    =>  $is_upgrade,
            "paypal_tax_id"                 =>  $paypal_tax_id,
            "details"                       =>  $details,
            "price"                         =>  $price,
            "to_be_paid"                    =>  $booking_array['deposit'],
            "submission_curency_status"     =>  $submission_curency_status,
            "bookid"                        =>  $bookid,
            "author_id"                     =>  $author_id,
            "youearned"                     =>  $booking_array['youearned'],
            "service_fee"                   =>  $booking_array['service_fee'],
            "booking_taxes"                 =>  $booking_array['taxes'],
            "security_deposit"              =>  $booking_array['security_deposit'],
            "renting_details"               =>  $details,
            "custom_price_array"            =>  $booking_array['custom_price_array'],
            "balance"                       =>  $booking_array['balance'],
            "cleaning_fee_per_day"          =>  $cleaning_fee_per_day,
            "city_fee_per_day"              =>  $city_fee_per_day,
            "city_fee_percent"              =>  $city_fee_percent,
        );
        
        if($booking_array['balance'] > 0){
            update_post_meta($bookid, 'booking_status_full','waiting' );
            update_post_meta($invoice_id, 'invoice_status_full','waiting');
            $booking_details['booking_status_full'] =   'waiting';
            $booking_details['booking_invoice_no']  =   $invoice_id;
            $invoice_details['invoice_status_full'] =   'waiting';
        }
        
        $wp_estate_book_down            =   floatval( get_post_meta($invoice_id, 'invoice_percent', true) );
        $invoice_price                  =   floatval( get_post_meta($invoice_id, 'item_price', true)) ;
      
        if($wp_estate_book_down==100 ){
           $booking_details['booking_invoice_no']  =   $invoice_id;
        }
        
        
        
        if($is_confirmed==1){
            update_post_meta($bookid, 'booking_status', 'confirmed'); 
            $booking_details['booking_status']='confirmed';
            
            update_post_meta($invoice_id, 'invoice_status', 'confirmed');
            update_post_meta($invoice_id, 'depozit_paid', 0);
            update_post_meta($invoice_id, 'depozit_to_be_paid', 0);
            update_post_meta($invoice_id, 'balance'  , $booking_array['balance']);
            $invoice_details['invoice_status']  =   'confirmed';
            $invoice_details['to_be_paid']      =   0;
            $invoice_details['balance']         =   $booking_array['balance'];
        }
        
        
        rcapi_invoice_booking( $invoice_id,$invoice_details );
        rcapi_edit_booking($bookid,$rcapi_booking_id,$booking_details);
       
        
        
        if($is_confirmed==1){
            $curent_listng_id   =   get_post_meta($bookid,'booking_id',true);
            $reservation_array  =   wpestate_get_booking_dates($curent_listng_id);
            update_post_meta($curent_listng_id, 'booking_dates', $reservation_array); 
        }

      
        // send notification emails
        if($is_confirmed!==1){
            $receiver          =   get_userdata($user_id);
            $receiver_email    =   $receiver->user_email;
            $receiver_login    =   $receiver->user_login;
            $from               =   $current_user->user_login;
            $to                 =   $user_id;
            $subject            =   esc_html__( 'New Invoice','wpestate');
            $description        =   esc_html__( 'A new invoice was generated for your booking request','wpestate');

            wpestate_add_to_inbox($userID,$userID,$to,$subject,$description,1);
            wpestate_send_booking_email('newinvoice',$receiver_email);
        }else{
            //direct confirmation emails
            $user_email         =   $current_user->user_email;
            $receiver_id    =   wpsestate_get_author($booking_id);
            $receiver_email =   get_the_author_meta('user_email', $receiver_id); 
            $receiver_name  =   get_the_author_meta('user_login', $receiver_id); 
            wpestate_send_booking_email("bookingconfirmeduser",$receiver_email);// for user
            wpestate_send_booking_email("bookingconfirmed_nodeposit",$user_email);// for owner
            // add messages to inbox

            $subject=esc_html__( 'Booking Confirmation','wpestate');
            $description=esc_html__( 'A booking was confirmed','wpestate');
            wpestate_add_to_inbox($userID,$receiver_name,$userID,$subject,$description);

            $subject=esc_html__( 'Booking Confirmed','wpestate');
            $description=esc_html__( 'A booking was confirmed','wpestate');
            wpestate_add_to_inbox($receiver_id,$username,$receiver_id,$subject,$description);

        }
    
    
    
    
        print $invoice_id;
        die();

    }
endif;









if( !function_exists('wpestate_add_booking_invoice_no_deposit') ): 
    function wpestate_add_booking_invoice_no_deposit($bookid,$details,$price){
      

        $pack_id        =   $bookid; // booking id
        
        $billing_for    =   'Reservation fee';
        $type           =   esc_html__( 'One Time','wpestate');
      
        $date           =   time();
        $user_id        =   wpse119881_get_author($bookid);

        $is_featured    =   '';
        $is_upgrade     =   '';
        $paypal_tax_id  =   '';


        $invoice_id =  wpestate_booking_insert_invoice($billing_for,$type,$pack_id,$date,$user_id,$is_featured,$is_upgrade,$paypal_tax_id,$details,$price);       

      //  update_post_meta($bookid, 'booking_status', 'waiting');
        update_post_meta($bookid, 'booking_invoice_no', $invoice_id);

        $receiver          =   get_userdata($user_id);
        $receiver_email    =   $receiver->user_email;
        $receiver_login    =   $receiver->user_login;

        $current_user = wp_get_current_user();
        $userID             =   $current_user->ID;
        $from               =   $current_user->user_login;
        $to                 =   $user_id;
        $subject=esc_html__( 'New Invoice','wpestate');
        $description=esc_html__( 'A new invoice was generated for your booking request','wpestate');
        wpestate_add_to_inbox($userID,$userID,$to,$subject,$description,1);

       // wpestate_send_booking_email('newinvoice',$receiver_email);

        print $invoice_id;
        die();

    }
endif;
////////////////////////////////////////////////////////////////////////////////
/// Ajax  direct confirmation
////////////////////////////////////////////////////////////////////////////////


add_action('wp_ajax_nopriv_wpestate_direct_confirmation', 'wpestate_direct_confirmation');  
add_action('wp_ajax_wpestate_direct_confirmation', 'wpestate_direct_confirmation' );  
if( !function_exists('wpestate_direct_confirmation') ): 
    function wpestate_direct_confirmation(){
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }


        
        check_ajax_referer('create_invoice_ajax_nonce','security');
        $allowed_html=array();
        $bookid         =   $booking_id = intval($_POST['bookid']); 

        $lisiting_id    =   $listing_id            =   get_post_meta($bookid, 'booking_id', true);           
        $book_from      =   get_post_meta($bookid, 'booking_from_date', true);
        $book_to        =   get_post_meta($bookid, 'booking_to_date', true);
        
        $the_post= get_post( $lisiting_id); 
        if( $current_user->ID != $the_post->post_author ) {
            exit('you don\'t have the right to see this');
        }

        // double book change
        $reservation_array  = get_post_meta($listing_id, 'booking_dates',true);
        if($reservation_array==''){
            $reservation_array = wpestate_get_booking_dates($listing_id);
        }
        
        $from_date      =   new DateTime($book_from);
        $from_date_unix =   $from_date->getTimestamp();

        $to_date                =   new DateTime($book_to);
        $to_date_unix_check     =   $to_date->getTimestamp();
       
           
        $to_date_unix   =   $to_date->getTimestamp();

      
        
        // checking booking avalability
        while ($from_date_unix < $to_date_unix){
            
            // print'check '. $from_date_unix.'</br>';
            if( array_key_exists($from_date_unix,$reservation_array ) ){
            //  print '</br> iteration from date'.$from_date_unix. ' / ' .date("Y-m-d", $from_date_unix);
                print'doublebook';
                //print '<div class="create_invoice_form">';
                //print esc_html__('It seems that this period was already booked.','wpestate');
                //print '</div>';
                die();
            }
            $from_date->modify('tomorrow');
            $from_date_unix =   $from_date->getTimestamp();
        }
    
        
        ///
        
        
        
        

        if( isset($_POST['details']) ){
            $details  =     ( $_POST['details']);
        }else{
            $details  =    '';
        }

        $price          =   floatval($_POST['price']);
        $billing_for    =   esc_html__( 'Reservation fee','wpestate');
        $type           =   esc_html__( 'One Time','wpestate');
        $pack_id        =   $bookid; // booking id
        $date           =   time();
        $user_id        =   wpse119881_get_author($bookid);

        $is_featured    =   '';
        $is_upgrade     =   '';
        $paypal_tax_id  =   '';



        $receiver          =   get_userdata($user_id);
        $receiver_email    =   $receiver->user_email;
        $receiver_login    =   $receiver->user_login;


        $userID             =   $current_user->ID;
        $user_email         =   $current_user->user_email;
        $username           =   $current_user->user_login;
        $from               =   $current_user->user_login;
        $to                 =   $user_id;



        $invoice_id =  wpestate_booking_insert_invoice($billing_for,$type,$pack_id,$date,$user_id,$is_featured,$is_upgrade,$paypal_tax_id,$details,$price);       
       
        // get the booking array
        $listing_id=0;
        $booking_guests      =   get_post_meta($booking_id, 'booking_guests', true);   
        $extra_options       =   esc_html(get_post_meta($booking_id, 'extra_options', true));
        $extra_options_array =   explode(',', $extra_options);
        $booking_array       =   wpestate_booking_price($booking_guests,$invoice_id, $lisiting_id, $book_from, $book_to,$bookid,$extra_options_array);
      

        // confirm booking
        update_post_meta($booking_id, 'booking_status', 'confirmed');
        update_post_meta($booking_id, 'booking_invoice_no', $invoice_id);
        update_post_meta($bookid, 'balance'  , $booking_array['balance']);

        $curent_listng_id   =   get_post_meta($booking_id,'booking_id',true);
        $reservation_array  =   wpestate_get_booking_dates($curent_listng_id);


        update_post_meta($curent_listng_id, 'booking_dates', $reservation_array); 

        // set invoice to paid
        update_post_meta($invoice_id, 'invoice_status', 'confirmed');
        update_post_meta($invoice_id, 'depozit_paid', 0);
        update_post_meta($invoice_id, 'balance'  , $booking_array['balance']);


        /////////////////////////////////////////////////////////////////////////////
        // send confirmation emails
        /////////////////////////////////////////////////////////////////////////////



        $receiver_id    =   wpsestate_get_author($bookid);
        $receiver_email =   get_the_author_meta('user_email', $receiver_id); 
        $receiver_name  =   get_the_author_meta('user_login', $receiver_id); 
        wpestate_send_booking_email("bookingconfirmeduser",$receiver_email);// for user
        wpestate_send_booking_email("bookingconfirmed_nodeposit",$user_email);// for owner
        // add messages to inbox

        $subject=esc_html__( 'Booking Confirmation','wpestate');
        $description=esc_html__( 'A booking was confirmed','wpestate');
        wpestate_add_to_inbox($userID,$receiver_name,$userID,$subject,$description);

        $subject=esc_html__( 'Booking Confirmed','wpestate');
        $description=esc_html__( 'A booking was confirmed','wpestate');
        wpestate_add_to_inbox($receiver_id,$username,$receiver_id,$subject,$description);

        //print'email sent to '.$user_email.' / '.$receiver_email;
        print $invoice_id;
        exit();

    }
endif;
 
 
 
 
 
 
 
 
 ////////////////////////////////////////////////////////////////////////////////
/// Ajax  direct confirmation
////////////////////////////////////////////////////////////////////////////////
if( !function_exists('wpestate_booking_insert_invoice') ): 
    function wpestate_booking_insert_invoice($billing_for,$type,$pack_id,$date,$user_id,$is_featured,$is_upgrade,$paypal_tax_id,$details,$price,$author_id=''){
  
        $price =(double) round ( floatval($price),2 )  ; 

        $post = array(
                   'post_title'     => 'Invoice ',
                   'post_status'    => 'publish', 
                   'post_type'      => 'wpestate_invoice',
                
                );
        
        if($author_id!=''){
           $post[ 'post_author']       = intval($author_id);
        }
        
        $post_id =  wp_insert_post($post ); 

    

        update_post_meta($post_id, 'invoice_type', $billing_for);   
        update_post_meta($post_id, 'biling_type', $type);
        update_post_meta($post_id, 'item_id', $pack_id);
    
        update_post_meta($post_id, 'item_price',$price);
        update_post_meta($post_id, 'purchase_date', $date);
        update_post_meta($post_id, 'buyer_id', $user_id);
        update_post_meta($post_id, 'txn_id', '');
        update_post_meta($post_id, 'renting_details', $details);
        update_post_meta($post_id, 'invoice_status', 'issued');
        update_post_meta($post_id, 'invoice_percent',  floatval ( get_option('wp_estate_book_down', '') ));
        update_post_meta($post_id, 'invoice_percent_fixed_fee',  floatval ( get_option('wp_estate_book_down_fixed_fee', '') ));
        
        $service_fee_fixed_fee  =   floatval ( get_option('wp_estate_service_fee_fixed_fee','') );
        $service_fee            =   floatval ( get_option('wp_estate_service_fee','') );
        update_post_meta($post_id, 'service_fee_fixed_fee', $service_fee_fixed_fee );
        update_post_meta($post_id, 'service_fee', $service_fee);

        $property_id    = get_post_meta($pack_id, 'booking_id',true);
        update_post_meta($post_id, 'prop_taxed', floatval(get_post_meta($property_id, 'property_taxes', true)) );
        
        //$submission_curency_status = esc_html( get_option('wp_estate_submission_curency','') );
        $submission_curency_status = wpestate_curency_submission_pick();
        update_post_meta($post_id, 'invoice_currency', $submission_curency_status);
        
     
        
        $default_price  = get_post_meta($property_id, 'property_price', true);
        update_post_meta($post_id, 'default_price', $default_price);
        
        $week_price = floatval   ( get_post_meta($property_id, 'property_price_per_week', true) );
        update_post_meta($post_id, 'week_price', $week_price);
        
        $month_price = floatval   ( get_post_meta($property_id, 'property_price_per_month', true) );
        update_post_meta($post_id, 'month_price', $month_price);
        
        $cleaning_fee = floatval   ( get_post_meta($property_id, 'cleaning_fee', true) );
        update_post_meta($post_id, 'cleaning_fee', $cleaning_fee);
        
        $city_fee = floatval   ( get_post_meta($property_id, 'city_fee', true) );
        update_post_meta($post_id, 'city_fee', $city_fee);
        
        
        
        $my_post = array(
           'ID'           => $post_id,
           'post_title'	=> 'Invoice '.$post_id,
        );

        wp_update_post( $my_post );

        return $post_id;

    }
endif;
////////////////////////////////////////////////////////////////////////////////
/// Ajax  create invoice form
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_create_invoice_form', 'wpestate_create_invoice_form');  
add_action('wp_ajax_wpestate_create_invoice_form', 'wpestate_create_invoice_form' );  
if( !function_exists('wpestate_create_invoice_form') ):
    function wpestate_create_invoice_form(){
        $current_user   =   wp_get_current_user();
        $userID         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }

        
        $invoice_id=0;
        $bookid              =      intval($_POST['bookid']);
        $lisiting_id         =      get_post_meta($bookid, 'booking_id', true);
        $the_post            =      get_post( $lisiting_id); 
 
        if( $current_user->ID != $the_post->post_author ) {
            exit('you don\'t have the right to see this');
        }


        
        $booking_from_date   =   esc_html(get_post_meta($bookid, 'booking_from_date', true));
        $property_id         =   esc_html(get_post_meta($bookid, 'booking_id', true));
        $booking_to_date     =   esc_html(get_post_meta($bookid, 'booking_to_date', true));
        $extra_options       =   esc_html(get_post_meta($bookid, 'extra_options', true));
        $extra_options_array =   explode(',', $extra_options);
        $booking_guests      =   get_post_meta($bookid, 'booking_guests', true);
        $booking_array       =   wpestate_booking_price($booking_guests,$invoice_id,$property_id, $booking_from_date, $booking_to_date,$bookid,$extra_options_array);
        $where_currency      =   esc_html( get_option('wp_estate_where_currency_symbol', '') );
        $currency            =   esc_html( get_option('wp_estate_submission_curency', '') );
        $currency            =   wpestate_curency_submission_pick();
        $include_expeses     =   esc_html ( get_option('wp_estate_include_expenses','') );
        $security_depozit    =   floatval(get_post_meta($property_id, 'security_deposit', true));
        $price_per_weekeend  =   floatval(get_post_meta($property_id, 'price_per_weekeend', true));
       
      
        $total_price_comp = $booking_array['total_price'];
        
        if($include_expeses=='yes'){
            $total_price_comp2  =   $total_price_comp;
        }else{
            $total_price_comp2  =   $booking_array['total_price'] - $booking_array['city_fee'] - $booking_array['cleaning_fee'];
        }
        
        
       
        $wp_estate_book_down                      =   esc_html( get_option('wp_estate_book_down','') );
        $wp_estate_book_down_fixed_fee            =   esc_html( get_option('wp_estate_book_down_fixed_fee','') );
        
        $depozit                    =   wpestate_calculate_deposit($wp_estate_book_down,$wp_estate_book_down_fixed_fee,$total_price_comp2);
        $balance                    =   $total_price_comp - $depozit;
        $price_show                 =   wpestate_show_price_booking_for_invoice($booking_array['default_price'],$currency,$where_currency,0,1);
        $price_per_weekeend_show    =   wpestate_show_price_booking_for_invoice($price_per_weekeend,$currency,$where_currency,0,1);
        $total_price_show           =   wpestate_show_price_booking_for_invoice($total_price_comp,$currency,$where_currency,0,1);
        $security_depozit_show      =   wpestate_show_price_booking_for_invoice($security_depozit,$currency,$where_currency,1,1);
        $deposit_show               =   wpestate_show_price_booking_for_invoice($depozit,$currency,$where_currency,0,1);
        $balance_show               =   wpestate_show_price_booking_for_invoice($balance,$currency,$where_currency,0,1);
        $city_fee_show              =   wpestate_show_price_booking_for_invoice($booking_array['city_fee'],$currency,$where_currency,1,1);
        $cleaning_fee_show          =   wpestate_show_price_booking_for_invoice($booking_array['cleaning_fee'],$currency,$where_currency,1,1);
        $inter_price_show           =   wpestate_show_price_booking_for_invoice($booking_array['inter_price'],$currency,$where_currency,1,1); 
        $total_guest                =   wpestate_show_price_booking_for_invoice($booking_array['total_extra_price_per_guest'],$currency,$where_currency,1,1); 
        $guest_price                =   wpestate_show_price_booking_for_invoice($booking_array['extra_price_per_guest'],$currency,$where_currency,1,1); 
        $extra_price_per_guest      =   wpestate_show_price_booking($booking_array['extra_price_per_guest'],$currency,$where_currency,1);
        $early_bird_discount_show   =   wpestate_show_price_booking_for_invoice(  $booking_array['early_bird_discount'],$currency,$where_currency,1,1);
          
 
        if(trim($deposit_show)==''){
            $deposit_show=0;
        }
        
        
        //print_r($booking_array);
        
            print '              
            <div class="create_invoice_form">
                <h3>'.esc_html__( 'Create Invoice','wpestate').'</h3>

                <div class="invoice_table">
                    <div class="invoice_data">
                        <div style="display:none" id="property_details_invoice" data-taxes_value="'.floatval(get_post_meta($property_id, 'property_taxes', true)).'" data-earlyb="'.floatval(get_post_meta($property_id, 'early_bird_percent', true)).'"></div>
                        <span class="date_interval"><span class="invoice_data_legend">'.esc_html__( 'Period','wpestate').' : </span>'.$booking_from_date.' '.esc_html__( 'to','wpestate').' '.$booking_to_date.'</span>
                        <span class="date_duration"><span class="invoice_data_legend">'.esc_html__( 'No of nights','wpestate').': </span>'.$booking_array['count_days'].'</span>
                        <span class="date_duration"><span class="invoice_data_legend">'.esc_html__( 'No of guests','wpestate').': </span>'.$booking_guests.'</span>';
                        /*if($booking_array['price_per_guest_from_one']==1){
                            print'    
                            <span class="date_duration"><span class="invoice_data_legend">'.esc_html__( 'Price per Guest','wpestate').': </span>'; 
                                print $extra_price_per_guest;
                            print'</span>';
                        }else{
                            print'    
                            <span class="date_duration"><span class="invoice_data_legend">'.esc_html__( 'Price per night','wpestate').': </span>';
                            print __('default price:','wpestate').' '.$price_show;
                            if($booking_array['has_custom']){
                                print ', '.esc_html__('has custom price','wpestate');
                              
                            }
                            if($booking_array['cover_weekend']){
                                print ', '.esc_html__('has weekend price of','wpestate').' '.$price_per_weekeend_show;
                            }
                            print'</span>';
                            if($booking_array['has_custom']){
                                print '<span class="invoice_data_legend">'.__('Price details:','wpestate').'</span>';
                                foreach($booking_array['custom_price_array'] as $date=>$price){
                                    $day_price = wpestate_show_price_booking_for_invoice($price,$currency,$where_currency,1,1); 
                                    print '<span class="price_custom_explained">'.__('on','wpestate').' '.date("Y-m-d",$date).' '.__('price is','wpestate').' '.$day_price.'</span>';
                                }
                            }
                            
                            
                            
                        }*/
                 
                    print '    
                    </div>
                    <div class="invoice_details">
                        <div class="invoice_row header_legend">
                           <span class="inv_legend">    '.esc_html__( 'Cost','wpestate').'</span>
                           <span class="inv_data">      '.esc_html__( 'Price','wpestate').'</span>
                           <span class="inv_exp">       '.esc_html__( 'Detail','wpestate').' </span>
                        </div>
                        <div class="invoice_row invoice_content">
                            <span class="inv_legend">   '.esc_html__( 'Subtotal','wpestate').'</span>
                            <span class="inv_data">   '.$inter_price_show.'</span>';
                        
                            if($booking_array['price_per_guest_from_one']==1){
                                print  $extra_price_per_guest.' x '.$booking_array['count_days'].' '.esc_html__( 'nights','wpestate').' x '.$booking_array['curent_guest_no'].' '.esc_html__( 'guests','wpestate');
                            } else{ 
                                
                                if($booking_array['cover_weekend']){
                                    $new_price_to_show=esc_html__('has weekend price of','wpestate').' '.$price_per_weekeend_show;
                                }else{
                                    if($booking_array['has_custom']){
                                        $new_price_to_show=esc_html__("custom price","wpestate");
                                    }else{
                                        $new_price_to_show=$price_show.' '.esc_html__( 'per night','wpestate');
                                    }
                                }
                                
                                
                                
                                if($booking_array['numberDays']==1){
                                    print ' <span class="inv_exp">   ('.$booking_array['numberDays'].' '.esc_html__( 'night','wpestate').' | '.$new_price_to_show.') </span>';
                                }else{
                                    print ' <span class="inv_exp">   ('.$booking_array['numberDays'].' '.esc_html__( 'nights','wpestate').' | '.$new_price_to_show.') </span>';
                                }
                            }
                            
                            if($booking_array['price_per_guest_from_one']==1 && $booking_array['custom_period_quest']==1){
                               esc_html_e(" period with custom price per guest","wpestate");
                            }
                            
                            print'            

                            </div>';

                         
                            
                            if($booking_array['has_guest_overload']!=0 && $booking_array['total_extra_price_per_guest']!=0){
                                print'
                                <div class="invoice_row invoice_content">
                                    <span class="inv_legend">   '.esc_html__( 'Extra Guests','wpestate').'</span>
                                    <span class="inv_data" id="extra-guests" data-extra-guests="'.$booking_array['total_extra_price_per_guest'].'">  '.$total_guest.'</span>
                                    <span class="inv_exp">   ('.$booking_array['numberDays'].' '.esc_html__( 'nights','wpestate').' | '.$booking_array['extra_guests'].' '.esc_html__('extra guests','wpestate').' )';
                                
                                    if ( $booking_array['custom_period_quest']==1 ){
                                      echo  esc_html__(" period with custom price per guest","wpestate");
                                    }
                            
                                    print'</span>
                       
                                </div>';
                            }

                            if($booking_array['cleaning_fee']!=0 && $booking_array['cleaning_fee']!=''){
                               print'
                               <div class="invoice_row invoice_content">
                                   <span class="inv_legend">   '.esc_html__( 'Cleaning fee','wpestate').'</span>
                                   <span class="inv_data" id="cleaning-fee" data-cleaning-fee="'.$booking_array['cleaning_fee'].'">  '.$cleaning_fee_show.'</span>
                               </div>';
                            }

                            if($booking_array['city_fee']!=0 && $booking_array['city_fee']!=''){
                               print'
                               <div class="invoice_row invoice_content">
                                   <span class="inv_legend">   '.esc_html__( 'City fee','wpestate').'</span>
                                   <span class="inv_data" id="city-fee" data-city-fee="'.$booking_array['city_fee'].'">  '.$city_fee_show.'</span>
                               </div>';
                            }

                       
                            
                               
                            $extra_pay_options          =      ( get_post_meta($property_id,  'extra_pay_options', true) );
                            if($extra_options!=''){ 
                                $extra_options_array    =   explode(',',$extra_options);
                            }
                            
                          
                            $options_array_explanations=array(
                                0   =>  esc_html__('Single Fee','wpestate'),
                                1   =>  esc_html__('Per Night','wpestate'),
                                2   =>  esc_html__('Per Guest','wpestate'),
                                3   =>  esc_html__('Per Night per Guest','wpestate')
                            );

                            foreach ($extra_options_array as $key=>$value){
                                if(isset($extra_pay_options[$value][0])){
                                    $extra_option_value                 =   wpestate_calculate_extra_options_value($booking_array['count_days'],$booking_guests,$extra_pay_options[$value][2],$extra_pay_options[$value][1]);
                                    $extra_option_value_show            =   wpestate_show_price_booking_for_invoice($extra_option_value,$currency,$where_currency,1,1);
                                    $extra_option_value_show_single     =   wpestate_show_price_booking_for_invoice($extra_pay_options[$value][1],$currency,$where_currency,0,1);

                                    print'
                                    <div class="invoice_row invoice_content">
                                        <span class="inv_legend">   '.$extra_pay_options[$value][0].'</span>
                                        <span class="inv_data invoice_default_extra" data-value="'.$extra_option_value.'" >  '.$extra_option_value_show.'</span>
                                        <span class="inv_data inv_data_exp">'.$extra_option_value_show_single.' '.$options_array_explanations[$extra_pay_options[$value][2]].'</span>
                                    </div>';
                                }
                            }
                           
                            if($security_depozit!=0){
                                print'
                                <div class="invoice_row invoice_content">
                                    <span class="inv_legend">   '.__('Security Deposit','wpestate').'</span>
                                    <span id="security_depozit_row" data-val="'.$security_depozit.'" class="inv_data">  '.$security_depozit_show.'</span>
                                    <span  class="inv_data">'.__('*refundable','wpestate').'</span>
                                </div>';
                            }

                                
                         
                            if( $booking_array['early_bird_discount'] >0){
                                print'
                                <div class="invoice_row invoice_content">
                                    <span class="inv_legend">   '.__('Early Bird Discount','wpestate').'</span>
                                    <span id="erarly_bird_row" data-val="'.$booking_array['early_bird_discount'].'"  class="inv_data">  '.$early_bird_discount_show.'</span>
                                    <span class="inv_data"></span>
                                </div>';
                            }
                            
                            
                            
                            print'  
                            <div class="invoice_row invoice_total invoice_total_generate_invoice">
                                <div style="display:none;" id="inter_price" data-value="'.$booking_array ['inter_price'].'"></div>
                                <span class="inv_legend"><strong>'.esc_html__( 'Guest Pays','wpestate').'</strong></span>
                                <span class="inv_data" id="total_amm" data-total="'.$total_price_comp.'">'.$total_price_show.'</span>

                                <span class="total_inv_span"><span class="inv_legend"> '.esc_html__( 'Reservation Fee Required','wpestate').':</span> <span id="inv_depozit" data-value="'.$depozit.'">'.$deposit_show.'</span>
                                <div style="width:100%"></div>
                                <span class="inv_legend">'.esc_html__( 'Balance Owing','wpestate').':</span> <span id="inv_balance" data-val="'.$balance.'">'.$balance_show.'</span>
                            </div>';
                        
                            //   $total_price_show       =   wpestate_show_price_booking_for_invoice($total_price_comp,$currency,$where_currency,0,1);
                           
                            
                            
                     
                           
                            
                       
                            $taxes_show          =      wpestate_show_price_booking_for_invoice($booking_array ['taxes'],$currency,$where_currency,0,1);
                            $you_earn_show       =      wpestate_show_price_booking_for_invoice($booking_array ['youearned'],$currency,$where_currency,0,1);
                            $service_fee_show    =      wpestate_show_price_booking_for_invoice($booking_array ['service_fee'],$currency,$where_currency,0,1);
                            print'  
                            <div class="invoice_row invoice_totalx invoice_total_generate_invoice">
                                <span class="inv_legend"><strong>'.esc_html__( 'You Earn','wpestate').'</strong></span>
                                <span class="inv_data" id="youearned" data-youearned="'.$booking_array ['youearned'].'"><strong>'.$you_earn_show.'</strong></span>
                                

                                <div class="invoice_explantions">'.esc_html__('we deduct security deposit, city fees, cleaning fees and website service fee','wpestate').'</div>
                                
                                <span class="total_inv_span">
                                    <span class="inv_legend">'.esc_html__( 'Service Fee','wpestate').':</span>
                                    <span id="inv_service_fee" data-value="'.$booking_array ['service_fee'].'"  data-value-fixed-hidden="'.  floatval ( get_option('wp_estate_service_fee_fixed_fee','') ).'">'.$service_fee_show.'</span>
                                    
                                    <div style="width:100%"></div>
                                    
                                    <span class="inv_legend">'.esc_html__( 'Taxes','wpestate').':</span>
                                    <span id="inv_taxes" data-value="'.$booking_array ['taxes'].'" >'.$taxes_show.'</span>
                                </span>    
                                
                                <div class="invoice_explantions">'.esc_html__('*taxes are included in your earnings and you are responsible for paying these taxes','wpestate').'</div>
                            </div>';
                            
                        print'</div>   '; 
                            
                            
                            
                            
                            
                   
                    $book_down              =   floatval( get_option('wp_estate_book_down','') );
                    $book_down_fixed_fee    =   floatval( get_option('wp_estate_book_down_fixed_fee','') );
                    
                    if($book_down != 0 || $book_down_fixed_fee!=0){
                        $label          =   esc_html__( 'Send Invoice','wpestate');
                        $is_confirmed   =   0;
                    }else{
                        $label  =   esc_html__( '예약 승인');
                        $is_confirmed   =   1;
                        
                    }
                      
                    print '<div class="action1_booking" id="invoice_submit" data-is_confirmed="'.$is_confirmed.'" data-bookid="'.$bookid.'">'.$label.'</div>';
                     
                    print '</div>';


                    print '
                    <div class="invoice_actions">
                        <h4>'.esc_html__( 'Add extra expense','wpestate').'</h4>
                        <input type="text" id="inv_expense_name" size="40" name="inv_expense_name" placeholder="'.esc_html__("type expense name","wpestate").'">
                        <input type="text" id="inv_expense_value" size="40" name="inv_expense_value" placeholder="'.esc_html__("type expense value","wpestate").'">
                        <div class="action1_booking" id="add_inv_expenses" data-include_ex="'.$include_expeses.'">'.esc_html__( 'add','wpestate').'</div>

                        <h4>'.esc_html__( 'Add discount','wpestate').'</h4>
                        <input type="text" id="inv_expense_discount" size="40" name="inv_expense_discount" placeholder="'.esc_html__("type discount value","wpestate").'">
                        <div class="action1_booking" id="add_inv_discount" data-include_ex="'.$include_expeses.'">'.esc_html__( 'add','wpestate').'</div>
                    </div>';

                    print  wp_nonce_field( 'create_invoice_ajax_nonce', 'security-create_invoice_ajax_nonce' ).'
            </div>';
        die();
    }
endif;



if( !function_exists('wpestate_calculate_service_fee') ):
    function wpestate_calculate_service_fee($total,$invoice_id){
    
        if(intval($invoice_id)==0){
            $service_fee_fixed_fee  =   floatval ( get_option('wp_estate_service_fee_fixed_fee','') );
            $service_fee            =   floatval ( get_option('wp_estate_service_fee','') );
        }else{
            $service_fee_fixed_fee  =   floatval (  get_post_meta($invoice_id,  'service_fee_fixed_fee', true));
            $service_fee            =   floatval (  get_post_meta($invoice_id,  'service_fee', true) );
        }
        
       
            
        if($service_fee_fixed_fee!=0){
            return $service_fee_fixed_fee;
        }else{
            $fee = round( $total*$service_fee/100,2);
            return $fee;
            
        }
        
    }
endif;





if( !function_exists('wpestate_calculate_extra_options_value') ):
    function wpestate_calculate_extra_options_value($count_days=1,$guests=1,$type,$value){
        $return_values=0;
          
        switch ($type) {
            case 0:// single fee
                $return_value = $value;
                break;
            case 1://per night
                $return_value = $value*$count_days;
                break;
            case 2://per guest
                $return_value = $value*$guests;
                break;
            case 3://per guest and night
                $return_value = $value*$guests*$count_days;
                break;
        }
        return $return_value;
    
    }
endif;



////////////////////////////////////////////////////////////////////////////////
/// Ajax  add inbox
////////////////////////////////////////////////////////////////////////////////
if( !function_exists('wpestate_add_to_inbox') ):
    function wpestate_add_to_inbox($userID,$from,$to,$subject,$description,$first_content=''){
    
        if($subject!=''){
            $subject = $subject.' '.$from;
        }else{
            $subject = esc_html__( 'Message from ','wpestate').$from;
        }
        
        
        $user = get_user_by( 'id',$from );
       
        $post = array(
            'post_title'	=> esc_html__( 'Message from ','wpestate').$user->user_login,
            'post_content'	=> $description,
            'post_status'	=> 'publish', 
            'post_type'         => 'wpestate_message' ,
            'post_author'       => $userID
        );
        $post_id =  wp_insert_post($post );  
        update_post_meta($post_id, 'mess_status', 'new' );
        update_post_meta($post_id, 'message_from_user', $from );
        update_post_meta($post_id, 'message_to_user', $to );   
        update_post_meta($post_id, 'delete_destination'.$from,0  );
        update_post_meta($post_id, 'delete_destination'.$to, 0 );     
        update_post_meta($post_id, 'message_status', 'unread');
        update_post_meta($post_id, 'delete_source', 0);
        update_post_meta($post_id, 'delete_destination', 0);  
        if($first_content!=''){
            update_post_meta($post_id, 'first_content', 1);  
            update_post_meta($post_id, 'message_status'.$to, 'unread' );
            if($first_content==="external_book_req"){
                //  removed in 1.17
                //  update_post_meta($post_id, 'delete_destination'.$from,1  );
            }
        }
    }
endif;


////////////////////////////////////////////////////////////////////////////////
/// Ajax  add booking  FRONT END
////////////////////////////////////////////////////////////////////////////////
add_action('wp_ajax_nopriv_wpestate_mess_front_end', 'wpestate_mess_front_end');  
add_action('wp_ajax_wpestate_mess_front_end', 'wpestate_mess_front_end' );  
if( !function_exists('wpestate_mess_front_end') ):
    function wpestate_mess_front_end(){
        //  check_ajax_referer( 'mess_ajax_nonce_front', 'security-register' );       
        $current_user = wp_get_current_user();
        $allowed_html       =   array();
        $userID             =   $current_user->ID;
        $user_login         =   $current_user->user_login;
        $subject            =   esc_html__( 'Message from ','wpestate').$user_login;
        $message_from_user       =   esc_html($_POST['message']);
        $property_id        =   intval ( $_POST['agent_property_id']);
        $agent_id           =   intval ( $_POST['agent_id'] );
        
        if($agent_id === 0){
            $owner_id           =   wpsestate_get_author($property_id);
        }else{
            $owner_id           =   get_post_meta($agent_id, 'user_agent_id', true);
        }

        $owner              =   get_userdata($owner_id);
        $owner_email        =   $owner->user_email;
        $owner_login        =   $owner->ID;
        $subject            =   esc_html__( 'Message from ','wpestate').$user_login;
     

        $booking_guest_no   =   intval  ( $_POST['booking_guest_no'] );
        $booking_from_date  =   wp_kses ( $_POST['booking_from_date'],$allowed_html  );
        $booking_to_date    =   wp_kses ( $_POST['booking_to_date'],$allowed_html  );
        
        if($property_id!=0 && get_post_type($property_id) === 'estate_property' ){
            $message_user .= esc_html__(' Sent for property ','wpestate').get_the_title($property_id).', '.esc_html__('with the link:','wpestate').' '.get_permalink($property_id).' ';
        }
        $message_user .=    esc_html__( 'Selected dates: ','wpestate').$booking_from_date.esc_html__( ' to ','wpestate').$booking_to_date.", ".esc_html__( ' guests:','wpestate').$booking_guest_no." ".esc_html__('Content','wpestate').": ".$message_from_user;
        
       
        
        wpestate_send_booking_email('inbox',$owner_email,$message_user);

        // add into inbox
        wpestate_add_to_inbox($userID,$userID,$owner_login,$subject,$message_user,1);

        esc_html_e('Your message was sent! You will be notified by email when a reply is received.','wpestate'); 
        die();            
    }
endif;


////////////////////////////////////////////////////////////////////////////////
/// Ajax  add booking  FRONT END
////////////////////////////////////////////////////////////////////////////////
add_action( 'wp_ajax_nopriv_ajax_wpestate_ajax_add_booking_font_end', 'wpestate_ajax_add_booking_font_end' );  
add_action( 'wp_ajax_ajax_wpestate_ajax_add_booking_font_end', 'wpestate_ajax_add_booking_font_end' );  
if( !function_exists('wpestate_ajax_add_booking_font_end') ):
    function wpestate_ajax_add_booking_font_end(){
    exit();
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;

        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }

        
        check_ajax_referer( 'booking_ajax_nonce', 'security');
     
        $allowed_html       =   array();
        $userID             =   $current_user->ID;
        $user_login         =   $current_user->user_login;
        $comment            =   trim (  wp_kses ( $_POST['comment'],$allowed_html) ) ;     
        $property_id        =   intval( $_POST['property_id'] );
        $fromdate           =   trim (  wp_kses ( $_POST['fromdate'],$allowed_html) );
        $to_date            =   trim (  wp_kses ( $_POST['todate'],$allowed_html ) );
        $guests             =   trim (  wp_kses ( $_POST['guests'],$allowed_html) );
        $event_name         =   esc_html__( 'Booking Request','wpestate');
        
        $post = array(
            'post_title'	=> $event_name,
            'post_content'	=> $comment,
            'post_status'	=> 'publish', 
            'post_type'         => 'wpestate_booking' ,
            'post_author'       => $userID
        );
        $post_id =  wp_insert_post($post );  
      
        
        $post = array(
            'ID'                => $post_id,
            'post_title'	=> $event_name.' '.$post_id
        );
        wp_update_post( $post );
        
        update_post_meta($post_id, 'booking_id', $property_id);
        update_post_meta($post_id, 'booking_from_date', $fromdate);
        update_post_meta($post_id, 'booking_to_date', $to_date);
        update_post_meta($post_id, 'booking_status', 'pending');
        update_post_meta($post_id, 'booking_invoice_no', 0);
        update_post_meta($post_id, 'booking_pay_ammount', 0);
        update_post_meta($post_id, 'booking_guests', $guests);
        
        // build the reservation array 
        $reservation_array = wpestate_get_booking_dates($property_id);
        update_post_meta($property_id, 'booking_dates', $reservation_array); 
         
        // send the email   
        $property_title =   get_the_title($property_id);
        $owner_id       =   wpse119881_get_author($property_id);
        $owner          =   get_userdata($owner_id);
        $owner_email    =   $owner->user_email;
        $owner_login    =   $owner->user_login;
        wpestate_send_booking_email('newbook',$owner_email);
        
        // add into inbox
        $subject        =  'New Booking Request';
        $message_user   = 'New booking request for listing  '.$property_title;
        wpestate_add_to_inbox($userID,$user_login,$owner_login,$subject,$message_user);
        
        
        // add into inbox
        print esc_html__( 'Booking request sent! You will receive an email if the owner approves or rejects the request.','wpestate');
        
        die();
    }
endif;



if( !function_exists('wpse119881_get_author') ):
    function wpse119881_get_author($post_id){
        $post = get_post( $post_id );
        return $post->post_author;
    }
endif;



////////////////////////////////////////////////////////////////////////////////
/// Ajax  add booking  function
////////////////////////////////////////////////////////////////////////////////
add_action( 'wp_ajax_nopriv_wpestate_ajax_show_booking_costs', 'wpestate_ajax_show_booking_costs' );  
add_action( 'wp_ajax_wpestate_ajax_show_booking_costs', 'wpestate_ajax_show_booking_costs' );  
 
if( !function_exists('wpestate_ajax_show_booking_costs') ):
    function wpestate_ajax_show_booking_costs(){
      
        $allowed_html       =   array();
        $property_id        =   intval($_POST['property_id']);
        $guest_no           =   intval($_POST['guest_no']);
        $guest_fromone      =   intval ($_POST['guest_fromone']);
        $booking_from_date  =   wp_kses ( $_POST['fromdate'],$allowed_html);
        $booking_to_date    =   wp_kses ( $_POST['todate'],$allowed_html);
        $invoice_id         =   0;
      
        $price_per_day      =   floatval(get_post_meta($property_id, 'property_price', true));
        if($price_per_day==0 || $price_per_day==''){
         //   exit();
        }
        $booking_array = wpestate_booking_price($guest_no,$invoice_id, $property_id, $booking_from_date, $booking_to_date,$property_id);
   
       // print_r($booking_array);
        $deposit_show       =   '';
        $balance_show       =   '';
        $currency           =   esc_html( get_option('wp_estate_currency_label_main', '') ); //currency_symbol
        $where_currency     =   esc_html( get_option('wp_estate_where_currency_symbol', '') );//where_currency_symbol
      
        $price_show                         =   wpestate_show_price_booking($booking_array['default_price'],$currency,$where_currency,1);
        $total_price_show                   =   wpestate_show_price_booking($booking_array['total_price'],$currency,$where_currency,1);
        $deposit_show                       =   wpestate_show_price_booking($booking_array['deposit'],$currency,$where_currency,1);
        $balance_show                       =   wpestate_show_price_booking($booking_array['balance'],$currency,$where_currency,1);
        $city_fee_show                      =   wpestate_show_price_booking($booking_array['city_fee'],$currency,$where_currency,1);
        $cleaning_fee_show                  =   wpestate_show_price_booking($booking_array['cleaning_fee'],$currency,$where_currency,1);
        $total_extra_price_per_guest_show   =   wpestate_show_price_booking($booking_array['total_extra_price_per_guest'],$currency,$where_currency,1);
        $inter_price_show                   =   wpestate_show_price_booking($booking_array['inter_price'],$currency,$where_currency,1);
        $extra_price_per_guest              =   wpestate_show_price_booking($booking_array['extra_price_per_guest'],$currency,$where_currency,1);
        $security_fee_show                  =   wpestate_show_price_booking($booking_array['security_deposit'],$currency,$where_currency,1);
        $early_bird_discount_show           =   wpestate_show_price_booking($booking_array['early_bird_discount'],$currency,$where_currency,1);
   
        print '              
        <div class="show_cost_form" id="show_cost_form" >
            <div class="cost_row">
                <div class="cost_explanation">';
                if($booking_array['price_per_guest_from_one']==1){
                    
                    if( $booking_array['custom_period_quest'] != 1 ){
                        print $extra_price_per_guest.' x ';
                    }
                    
                    print $booking_array['count_days'].' '.esc_html__( 'nights','wpestate').' x '.$booking_array['curent_guest_no'].' '.esc_html__( 'guests','wpestate');
                    
                    if( $booking_array['custom_period_quest'] == 1 ){
                       echo ' - ';esc_html_e( ' period with custom price per guest','wpestate');
                    }
                    
                    
                    
                }else{
                    
                    if( $booking_array['has_custom'] == 1 ){
                        print  $booking_array['numberDays'].' '.esc_html__( 'nights with custom price','wpestate');
                    }else if( $booking_array['has_wkend_price']===1 && $booking_array['cover_weekend']===1) {
                        print  $booking_array['numberDays'].' '.esc_html__( 'nights with weekend price','wpestate');
                    }else{
                        print  $price_show.' x '.$booking_array['numberDays'].' '.esc_html__( 'nights','wpestate');
                    }
                    
                    
                    
                }
        

        print '</div>
                <div class="cost_value">'.$inter_price_show.'</div>
            </div>';
        
        
        if($booking_array['has_guest_overload']!=0 && $booking_array['total_extra_price_per_guest']!=0 ){
            print '              
                <div class="cost_row">
                    <div class="cost_explanation">'.esc_html__( 'Costs for ','wpestate').'  '.$booking_array['extra_guests'].' '.esc_html__('extra guests','wpestate').'</div>
                    <div class="cost_value">'.$total_extra_price_per_guest_show.'</div>
                </div>';
        }
        

        if($booking_array['cleaning_fee']!=0 && $booking_array['cleaning_fee']!=''){
            print '              
          
                <div class="cost_row">
                    <div class="cost_explanation">'.esc_html__( 'Cleaning Fee','wpestate').'</div>
                    <div class="cost_value">'.$cleaning_fee_show.'</div>
                </div>';
        }

        if($booking_array['city_fee']!=0 && $booking_array['city_fee']!=''){
            print '              
           
                <div class="cost_row">
                    <div class="cost_explanation">'.esc_html__( 'City Fee','wpestate').'</div>
                    <div class="cost_value">'.$city_fee_show.'</div>
                </div>';
        }

        
        
        if($booking_array['security_deposit']!=0 && $booking_array['security_deposit']!=''){
            print '              
                <div class="cost_row">
                    <div class="cost_explanation">'.esc_html__( 'Security Deposit (*refundable)','wpestate').'</div>
                    <div class="cost_value">'.$security_fee_show.'</div>
                </div>';
        }
  
        if($booking_array['early_bird_discount']!=0 && $booking_array['early_bird_discount']!=''){
            print '              
                <div class="cost_row">
                    <div class="cost_explanation">'.esc_html__( 'Early Bird Discount','wpestate').'</div>
                    <div class="cost_value" id="early_bird_discount" data-early-bird="'.$booking_array['early_bird_discount'].'">'.$early_bird_discount_show.'</div>
                </div>';
        }
        
        
        print '        
                <div class="cost_row" id="total_cost_row">
                    <div class="cost_explanation"><strong>'.esc_html__( 'TOTAL','wpestate').'</strong></div>
                    <div class="cost_value" data_total_price="'.$booking_array['total_price'].'" >'.$total_price_show.'</div>
                </div>
            </div>';
        
        $instant_booking=$instant_booking                 =   floatval   ( get_post_meta($property_id, 'instant_booking', true) ); 
          
      //  print_r($booking_array);
             
        if($instant_booking==1){   
            print '<div class="cost_row_instant instant_depozit">'.esc_html__( 'Deposit for instant booking','wpestate').': ';
            print '<span class="instant_depozit_value">';
                if(floatval($booking_array['deposit'])!=0){
                   echo $deposit_show;
                }else{
                    echo '0';
                }
            print '</span>';
            
            print '</div>';
            
            if(floatval($booking_array['balance'])!=0){
                print '<div class="cost_row_instant instant_balance">'.esc_html__( 'Balance remaining','wpestate').': <span class="instant_balance_value">'.$balance_show.'</span></div>';
            }
            
            print'<div class="instant_book_info" data-total_price="'.$booking_array['total_price'].'" data-deposit="'.$booking_array['deposit'].'" data-balance="'.$booking_array['balance'].'"> ';
        }
        
        die();
    }
endif; 
 


////////////////////////////////////////////////////////////////////////////////
/// Ajax  add booking  function
////////////////////////////////////////////////////////////////////////////////
add_action( 'wp_ajax_nopriv_wpestate_new_ajax_add_booking', 'wpestate_new_ajax_add_booking' );  
add_action( 'wp_ajax_wpestate_new_ajax_add_booking', 'wpestate_new_ajax_add_booking' );  
if( !function_exists('wpestate_new_ajax_add_booking') ):
    function wpestate_new_ajax_add_booking(){
    exit();
        $allowed_html=array();
        check_ajax_referer( 'booking_ajax_nonce','security');
        $current_user = wp_get_current_user();
        $userID             =   $current_user->ID;
        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }

        
        $comment            =   trim ( wp_kses ( $_POST['comment'],$allowed_html)) ;
        $guests             =   intval(  wp_kses ( $_POST['guests'],$allowed_html));
        $property_id        =   intval( $_POST['property_name'] );
        $fromdate           =   trim (  wp_kses ( $_POST['fromdate'],$allowed_html));
        $to_date            =   trim (  wp_kses ( $_POST['todate'],$allowed_html) );
        $event_name         =   esc_html__( 'Booking Request','wpestate');
        
        
       $post = array(
            'post_title'	=> $event_name,
            'post_content'	=> $comment,
            'post_status'	=> 'publish', 
            'post_type'         => 'wpestate_booking' ,
            'post_author'       => $userID
        );
        $post_id =  wp_insert_post($post );  
        
        $post = array(
            'ID'                => $post_id,
            'post_title'	=> $event_name.' '.$post_id
        );
        wp_update_post( $post );
        
        update_post_meta($post_id, 'booking_id', $property_id);
        update_post_meta($post_id, 'booking_from_date', $fromdate);
        update_post_meta($post_id, 'booking_to_date', $to_date);
        update_post_meta($post_id, 'booking_status', 'confirmed');
        update_post_meta($post_id, 'booking_invoice_no', 0);
        update_post_meta($post_id, 'booking_pay_ammount', 0);
        update_post_meta($post_id, 'booking_guests', $guests);
        
      
        $preview            =   wp_get_attachment_image_src(get_post_thumbnail_id($property_id), 'property_sidebar');

         // build the reservation array 
        $reservation_array = wpestate_get_booking_dates($property_id);
        
      
        update_post_meta($property_id, 'booking_dates', $reservation_array); 
        
        
        print '
        <div class="dasboard-prop-listing">
           <div class="blog_listing_image">
                   <img  src="'.$preview[0].'"  alt="slider-thumb" /></a>
           </div>

            <div class="prop-info">
                <h3 class="listing_title">
                    '.$event_name.'  
                </h3>



                <div class="user_dashboard_listed">
                    <strong>'.esc_html__( 'Request by ','wpestate').'</strong>'.get_the_author_meta( 'user_login', $userID ).'<strong>
                </div>

                <div class="user_dashboard_listed">
                    <strong>'.esc_html__( 'Period: ','wpestate').'</strong>  '.$fromdate.' <strong>'.esc_html__( 'to','wpestate').'</strong> '.$to_date.'
                </div>

                <div class="user_dashboard_listed">
                    <strong>'.esc_html__( 'Invoice No: ','wpestate').'</strong>    
                </div>

            
            </div>


            <div class="info-container_booking">
                <span class="tag-published">'.esc_html__( '쿠폰 결제 완료').'</span>
            </div>

         </div>';
        
        die();
    }
endif;







////////////////////////////////////////////////////////////////////////////////
/// Ajax  Register function
////////////////////////////////////////////////////////////////////////////////
add_action( 'wp_ajax_nopriv_wpestate_ajax_register_form_booking', 'wpestate_ajax_register_form_booking' );  
add_action( 'wp_ajax_ajax_wpestate_ajax_register_form_booking', 'wpestate_ajax_register_form_booking' );  
if( !function_exists('wpestate_ajax_register_form_booking') ):
    function wpestate_ajax_register_form_booking(){
            $allowed_html=array();
            check_ajax_referer( 'register_ajax_nonce','security-register');

            $user_email  =   trim(  wp_kses ( $_POST['user_email_register'],$allowed_html) ) ;
            $user_name   =   trim(  wp_kses ( $_POST['user_login_register'],$allowed_html) ) ;
            $group       =   trim(  wp_kses ( $_POST['group_register'],$allowed_html) ) ;
            if (preg_match("/^[0-9A-Za-z_]+$/", $user_name) == 0) {
                print esc_html__( 'Invalid username( *do not use special characters or spaces ) ','wpestate');
                die();
            }
       //  print '$user_email '.$user_email.' $user_name '.$user_name.' $group'.$group;

            if ($user_email=='' || $user_name==''){
              print esc_html__( 'Username and/or Email field is empty!','wpestate');
              exit();
            }

            if(filter_var($user_email,FILTER_VALIDATE_EMAIL) === false) {
                 print esc_html__( 'The email doesn\'t look right !','wpestate');
                exit();
            }

            $domain = substr(strrchr($user_email, "@"), 1);
            if( !checkdnsrr ($domain) ){
                print esc_html__( 'The email\'s domain doesn\'t look right.','wpestate');
                exit();
            }


            $user_id     =   username_exists( $user_name );
            if ($user_id){
                print esc_html__( 'Username already exists.  Please choose a new one.','wpestate');
                exit();
             }




            if ( !$user_id && email_exists($user_email) == false ) {
                $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );

                $user_id  = wp_create_user( $user_name, $random_password, $user_email );

                if ( is_wp_error($user_id) ){
                       
                }else{
                    print esc_html__( 'An email with the generated password was sent!','wpestate');
                    wpestate_update_profile_booking($user_id, $group);
                    wpestate_wp_new_user_notification( $user_id, $random_password ) ;
                    if('renter' ==  $group ){
                        wpestate_register_as_user($user_name,$user_id);
                    }
                    
                    rcapi_create_new_user($user_id,$user_name,$password,$user_email);
                }

            
                
            } else {
               print esc_html__( 'Email already exists.  Please choose a new one.','wpestate');
            }


            die();  
    }

endif;



if( !function_exists('wpestate_update_profile_booking') ):
    function wpestate_update_profile_booking($userID,$group){
        if(1==1){ // if membership is on

            if( get_option('wp_estate_free_mem_list_unl', '' ) ==1 ){
                $package_listings =-1;
                $featured_package_listings  = esc_html( get_option('wp_estate_free_feat_list','') );
            }else{
                $package_listings           = esc_html( get_option('wp_estate_free_mem_list','') );
                $featured_package_listings  = esc_html( get_option('wp_estate_free_feat_list','') );

                if($package_listings==''){
                    $package_listings=0;
                }
                if($featured_package_listings==''){
                    $featured_package_listings=0;
                }
            }
            update_user_meta($userID, 'user_group',$group);
            update_user_meta( $userID, 'package_listings', $package_listings) ;
            update_user_meta( $userID, 'package_featured_listings', $featured_package_listings) ;
            $time = time(); 
            $date = date('Y-m-d H:i:s',$time);
            update_user_meta( $userID, 'package_activation', $date);
            //package_id no id since the pack is free

        }

    }
endif;




////////////////////////////////////////////////////////////////////////////////
/// Ajax  Start Stripr
////////////////////////////////////////////////////////////////////////////////
add_action( 'wp_ajax_nopriv_wpestate_start_stripe', 'wpestate_start_stripe' );  
add_action( 'wp_ajax_wpestate_start_stripe', 'wpestate_start_stripe' );  
if( !function_exists('wpestate_start_stripe') ):
    function wpestate_start_stripe(){

        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }


            require_once get_template_directory().'/libs/stripe/lib/Stripe.php';
            $stripe_secret_key              =   esc_html( get_option('wp_estate_stripe_secret_key','') );
            $stripe_publishable_key         =   esc_html( get_option('wp_estate_stripe_publishable_key','') );


            $stripe = array(
                "secret_key"       => $stripe_secret_key,
                "publishable_key"  => $stripe_publishable_key 
            );

            Stripe::setApiKey($stripe['secret_key']);

            print '
            <div id="cover" style="display:block;"></div><div id="ajax_login_container">
                <h5>'.esc_html__( 'Proceed to payment.','wpestate').'</h5>
                <div id="closeadvancedlogin"></div>
                <div id="ajax_login_div">
                    <form action="charge.php" method="post">
                        <script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                        data-key="'. $stripe['publishable_key'].'"
                        data-label="'.esc_html__( 'Pay with Credit Card','wpestate').'"  
                        data-zip-code="true"
                        data-amount="5000" data-description="Reservation Payment"></script>
                    </form>
                </div>    
            </div>';


            die();  
    }
endif;




////////////////////////////////////////////////////////////////////////////////
/// Ajax  create invoice
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_show_confirmed_booking', 'wpestate_show_confirmed_booking');  
add_action('wp_ajax_wpestate_show_confirmed_booking', 'wpestate_show_confirmed_booking' );  
if( !function_exists('wpestate_show_confirmed_booking') ):
    function wpestate_show_confirmed_booking(){
        //check owner before delete 
        $current_user = wp_get_current_user();
        $userID                         =   $current_user->ID;


        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }


        $userID         =   $current_user->ID;
        $user_email     =   $current_user->user_email;
        $invoice_id     =   intval($_POST['invoice_id']);
        $bookid         =   intval($_POST['booking_id']);
        
        $the_post= get_post( $bookid);
        $book_author=$the_post->post_author;

        $the_post= get_post( $invoice_id);
        $inv_author=$the_post->post_author;
        
        
      
        if($userID!=$inv_author && $book_author!=$userID){
            exit('out pls');
        }
        
        
        wpestate_super_invoice_details($invoice_id);
       
        die();
    }
endif;



////////////////////////////////////////////////////////////////////////////////
/// Show  invoice dashboard
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_show_invoice_dashboard', 'wpestate_show_invoice_dashboard');  
add_action('wp_ajax_wpestate_show_invoice_dashboard', 'wpestate_show_invoice_dashboard' );  
if( !function_exists('wpestate_show_invoice_dashboard') ):
    function wpestate_show_invoice_dashboard(){
        //check owner before delete 
        $current_user = wp_get_current_user();
        $userID         =   $current_user->ID;
        
        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }

        
        $user_email     =   $current_user->user_email;
        $invoice_id     =   intval($_POST['invoice_id']);
        $bookid         =   intval($_POST['booking_id']);

        $the_post= get_post( $bookid);
        $book_author=$the_post->post_author;

        $the_post= get_post( $invoice_id);
        $inv_author=$the_post->post_author;
        
        if($userID!=$inv_author && $book_author!=$userID){
            exit('out pls');
        }
        
        
        $invoice_saved      =   esc_html(get_post_meta($invoice_id, 'invoice_type', true));
        wpestate_super_invoice_details($invoice_id);
       
        if($invoice_saved=='Listing'){
            $item_id        =   esc_html(get_post_meta($invoice_id, 'item_id', true));
            $item_price     =   esc_html(get_post_meta($invoice_id, 'item_price', true));
            $purchase_date  =   esc_html(get_post_meta($invoice_id, 'purchase_date', true));
            print  '<div class="create_invoice_form">
                        <h3>'.esc_html__( 'Invoice INV','wpestate').$invoice_id.'</h3>
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Type','wpestate').': </strong>'.$invoice_saved.'</div>   
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Listing Id','wpestate').': </strong>'.wpestate_show_product_type($item_id).'</div>  
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Price','wpestate').': </strong>'.$item_price.'</div> 
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Date','wpestate').': </strong>';
                    if(is_numeric($purchase_date)){
                        echo date('l jS \of F Y',$purchase_date);
                    }else{
                        echo $purchase_date;
                    }
            print  '</div> 
                   </div>';
        }
        
        if($invoice_saved=='Upgrade to Featured'){
            $item_id        =   esc_html(get_post_meta($invoice_id, 'item_id', true));
            $item_price     =   esc_html(get_post_meta($invoice_id, 'item_price', true));
            $purchase_date  =   esc_html(get_post_meta($invoice_id, 'purchase_date', true));
            print  '<div class="create_invoice_form">
                        <h3>'.esc_html__( 'Invoice INV','wpestate').$invoice_id.'</h3>
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Type','wpestate').': </strong>'.$invoice_saved.'</div>   
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Listing Id','wpestate').': </strong>'.wpestate_show_product_type($item_id).'</div>  
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Price','wpestate').': </strong>'.$item_price.'</div> 
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Date','wpestate').': </strong>';
                    if(is_numeric($purchase_date)){
                        echo date('l jS \of F Y',$purchase_date);
                    }else{
                        echo $purchase_date;
                    }
            print  '</div> 
                   </div>';
             
        }
        
        if($invoice_saved=='Publish Listing with Featured'){
            $item_id        =   esc_html(get_post_meta($invoice_id, 'item_id', true));
            $item_price     =   esc_html(get_post_meta($invoice_id, 'item_price', true));
            $purchase_date  =   esc_html(get_post_meta($invoice_id, 'purchase_date', true));
            print  '<div class="create_invoice_form">
                        <h3>'.esc_html__( 'Invoice INV','wpestate').$invoice_id.'</h3>
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Type','wpestate').': </strong>'.$invoice_saved.'</div>   
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Listing Id','wpestate').': </strong>'.wpestate_show_product_type($item_id).'</div>  
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Price','wpestate').': </strong>'.$item_price.'</div> 
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Date','wpestate').': </strong>';
                    if(is_numeric($purchase_date)){
                        echo date('l jS \of F Y',$purchase_date);
                    }else{
                        echo $purchase_date;
                    }
            print  '</div> 
                   </div>';
        }
        
        if($invoice_saved=='Package'){
            $invoice_period_saved      =  esc_html(get_post_meta($invoice_id, 'biling_type', true));
            $item_id        =   esc_html(get_post_meta($invoice_id, 'item_id', true));
            $item_price     =   esc_html(get_post_meta($invoice_id, 'item_price', true));
            $purchase_date  =   esc_html(get_post_meta($invoice_id, 'purchase_date', true));
            print  '<div class="create_invoice_form">
                        <h3>'.esc_html__( 'Invoice INV','wpestate').$invoice_id.'</h3>
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Type','wpestate').': </strong>'.$invoice_saved.'</div>   
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Package','wpestate').': </strong>'.wpestate_show_product_type($item_id).'</div>  
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Price','wpestate').': </strong>'.$item_price.'</div> 
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Period','wpestate').': </strong>'.$invoice_period_saved.'</div> 
                       
                        <div class="dashboard_invoice_details"><strong>'.esc_html__( 'Date','wpestate').': </strong>';
                    if(is_numeric($purchase_date)){
                        echo date('l jS \of F Y',$purchase_date);
                    }else{
                        echo $purchase_date;
                    }
            print  '</div> 
                   </div>';
            }
        die();
        
    }
endif;


////////////////////////////////////////////////////////////////////////////////
/// Ajax  create review form
////////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_nopriv_wpestate_show_review_form', 'wpestate_show_review_form');  
add_action('wp_ajax_wpestate_show_review_form', 'wpestate_show_review_form' );  
 
if (!function_exists('wpestate_show_review_form')):
    function wpestate_show_review_form(){
        //check owner before delete 
        $current_user = wp_get_current_user();
        $userID         =   $current_user->ID;
        
        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }

        
        $user_email     =   $current_user->user_email;
        $listing_id     =   intval($_POST['listing_id']);
        $bookid         =   intval ($_POST['bookid']);

        $the_post= get_post( $bookid); 
 
        if( $current_user->ID != $the_post->post_author ) {
            exit('you don\'t have the right to see this');
        }


        
        
        print '              
            <div class="create_invoice_form">
                    <h3>'.esc_html__( 'Post Review','wpestate').'</h3>

                    <div class="rating">
                        <span class="rating_legend">'.esc_html__( 'Your Rating','wpestate').'</span>
                        <span class="empty_star"></span>
                        <span class="empty_star"></span>
                        <span class="empty_star"></span>
                        <span class="empty_star"></span>
                        <span class="empty_star"></span>
                    </div>

                    <textarea id="review_content" name="review_content" class="form-control"></textarea>

                    <div class="action1_booking" id="post_review" data-bookid="'.$bookid.'" data-listing_id="'.$listing_id.'">'.esc_html__( 'Submit Review','wpestate').'</div>
            </div>';
        die();
    }
endif;  



add_action('wp_ajax_nopriv_wpestate_post_review', 'wpestate_post_review');  
add_action('wp_ajax_wpestate_post_review', 'wpestate_post_review' );  
 

if (!function_exists('wpestate_post_review')):
    function wpestate_post_review(){
        $current_user = wp_get_current_user();
        $allowed_html=array(); 
        
        $bookid     =   intval($_POST['bookid']);        
        $userID                         =   $current_user->ID;

        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }
       
        $the_post= get_post( $bookid); 
        if( $current_user->ID != $the_post->post_author ) {
            exit('you don\'t have the right to see this');
        }



        
        
        $userID         =   $current_user->ID;
        $user_login     =   $current_user->user_login;
        $user_email     =   $current_user->user_email;
        $listing_id     =   intval($_POST['listing_id']);
          
        $stars          =   intval($_POST['stars']);
        $content        =   wp_kses($_POST['content'],$allowed_html);
        $time           =   time();
        $time = current_time('mysql');
        $data = array(
            'comment_post_ID' => $listing_id,
            'comment_author' => $user_login,
            'comment_author_email' => $user_email,
            'comment_author_url' => '',
            'comment_content' => $content,
            'comment_type' => 'comment',
            'comment_parent' => 0,
            'user_id' => $userID,
            'comment_author_IP' => '127.0.0.1',
            'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
            'comment_date' => $time,
            'comment_approved' => 1,
        );

       
        $comment_id =    wp_insert_comment($data);
        add_comment_meta( $comment_id, 'review_stars',$stars  );
        update_post_meta($listing_id,'review_by_'.$userID,'has');
    }
endif;

?>