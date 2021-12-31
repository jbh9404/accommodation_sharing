<?php
global $post;
global $userID;
global $where_currency;
global $current_user;
?>

<div class="col-md-12 invoice_unit " data-booking-confirmed="<?php echo esc_html(get_post_meta($post->ID, 'item_id', true));?>" data-invoice-confirmed="<?php echo $post->ID; ?>">
    <div class="col-md-2">
         <?php echo get_the_title(); ?> 
    </div>
    
    <div class="col-md-2">
        <?php echo get_the_date(); ?> 
    </div>
    
    <div class="col-md-2">
        <?php 
//        echo esc_html(get_post_meta($post->ID, 'invoice_status', true));
//        echo ' | '.esc_html(get_post_meta($post->ID, 'invoice_status_full', true));
//            
        $booking_status         =  esc_html(get_post_meta($post->ID, 'invoice_status', true));
        $booking_status_full    = esc_html(get_post_meta($post->ID, 'invoice_status_full', true));

        if($booking_status == 'canceled' && $booking_status_full== 'canceled'){
            esc_html_e('취소됨');
        }else if($booking_status == 'confirmed' && $booking_status_full== 'confirmed'){
            echo    esc_html__('승인됨').' | ' .esc_html__('결제 완료');
        }else if($booking_status == 'confirmed' && $booking_status_full== 'waiting'){
            echo    esc_html__('deposit paid','wpestate').' | ' .esc_html__('waiting for full payment','wpestate');
        }else if($booking_status == 'refunded' ){
            esc_html_e('refunded','wpestate');
        }else if($booking_status == 'pending' ){
            esc_html_e('pending','wpestate');
        }else if($booking_status == 'waiting' ){
            esc_html_e('결제 대기중');
        }else if($booking_status == 'issued' ){
            esc_html_e('issued','wpestate');
        }else if($booking_status == 'confirmed' ){
            esc_html_e('confirmed','wpestate');
        }
        
        ?>
        
        
    </div>
</div>
