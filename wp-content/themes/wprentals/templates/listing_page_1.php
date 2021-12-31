<?php
global $post;
global $current_user;
global $feature_list_array;
global $propid ;
global $post_attachments;
global $options;
global $where_currency;
global $property_description_text;     
global $property_details_text;
global $property_features_text;
global $property_adr_text;  
global $property_price_text;   
global $property_pictures_text;    
global $propid;
global $gmap_lat;  
global $gmap_long;
global $unit;
global $currency;
global $use_floor_plans;
global $favorite_text;
global $favorite_class;
global $property_action_terms_icon;
global $property_action;
global $property_category_terms_icon;
global $property_category;
global $guests;
global $bedrooms;
global $bathrooms;
global $show_sim_two;

$price              =   intval   ( get_post_meta($post->ID, 'property_price', true) );
$price_label        =   esc_html ( get_post_meta($post->ID, 'property_label', true) );  
$property_city      =   get_the_term_list($post->ID, 'property_city', '', ', ', '') ;
$property_area      =   get_the_term_list($post->ID, 'property_area', '', ', ', '');

$post_id=$post->ID; 
$guest_no_prop ='';
if(isset($_GET['guest_no_prop'])){
    $guest_no_prop = intval($_GET['guest_no_prop']);
}
$guest_list= wpestate_get_guest_dropdown('noany');
?>


   

<div class="row content-fixed-listing listing_type_1">
    <?php //get_template_part('templates/breadcrumbs'); ?>
    <div class=" <?php 
    if ( $options['content_class']=='col-md-12' || $options['content_class']=='none'){
        print 'col-md-8';
    }else{
       print  $options['content_class']; 
    }?> ">
    
        <?php get_template_part('templates/ajax_container'); ?>
        <?php
        while (have_posts()) : the_post();
            $image_id       =   get_post_thumbnail_id();
            $image_url      =   wp_get_attachment_image_src($image_id, 'wpestate_property_full_map');
            $full_img       =   wp_get_attachment_image_src($image_id, 'full');
            $image_url      =   $image_url[0];
            $full_img       =   $full_img [0];     
        ?>
        
     
    <div class="single-content listing-content">
        <h1 class="entry-title entry-prop"><?php the_title(); ?>        
            <span class="property_ratings">
                <?php 
                $args = array(
                    'number' => '15',
                    'post_id' => $post->ID, // use post_id, not post_ID
                );
                $comments   =   get_comments($args);
                $coments_no =   0;
                $stars_total=   0;

                foreach($comments as $comment) :
                    $coments_no++;
                    $rating= get_comment_meta( $comment->comment_ID , 'review_stars', true );
                    $stars_total+=$rating;
                endforeach;

                if($stars_total!= 0 && $coments_no!=0){
                    $list_rating= ceil($stars_total/$coments_no);
                    $counter=0; 
                    while($counter<5){
                        $counter++;
                        if( $counter<=$list_rating ){
                            print '<i class="fa fa-star"></i>';
                        }else{
                            print '<i class="fa fa-star-o"></i>'; 
                        }

                    }
                    print '<span class="rating_no">('.$coments_no.')</span>';
                }  
                ?>         
            </span> 
        </h1>
       
        <div class="listing_main_image_location">
            <?php print  $property_city; ?>
        </div>   


        <div class="panel-wrapper imagebody_wrapper">
            <div class="panel-body imagebody imagebody_new">
                <?php  
                get_template_part('templates/property_pictures3');
                ?>
            </div> 
        </div>



        <div class="category_wrapper ">
            <div class="category_details_wrapper">


                <?php  if( $property_category!='') {
                    echo $property_category;?> <span class="property_header_separator">|</span>
                <?php } ?>

                <?php print '<span class="no_link_details">'.$guests.' '. esc_html__( '명까지 가능').'</span>';?>
            </div>

            <a href="#listing_calendar" class="check_avalability"><?php esc_html_e('요청 확인');?></a>
        </div>

        
        
        <div id="listing_description">
        <?php
            $content = get_the_content();
            $content = apply_filters('the_content', $content);
            $content = str_replace(']]>', ']]&gt;', $content);
            if($content!=''){   
                   
                $property_description_text =  get_option('wp_estate_property_description_text');
                if (function_exists('icl_translate') ){
                    $property_description_text     =   icl_translate('wpestate','wp_estate_property_description_text', esc_html( get_option('wp_estate_property_description_text') ) );
                }

                    
                print '<h4 class="panel-title-description">호스트 및 숙소 소개</h4>';
                print '<div class="panel-body">'.$content.'</div>';       
            }
        ?>
        </div>
        <div id="view_more_desc"><?php esc_html_e('View more','wpestate');?></div>

          
        <!-- property price   -->   
        <div class="panel-wrapper" id="listing_price">
            <a class="panel-title" data-toggle="collapse" data-parent="#accordion_prop_addr" href="#collapseOne"> <span class="panel-title-arrow"></span>
                <?php if($property_price_text!=''){
                    echo $property_price_text;
                } else{
                    esc_html_e('Property Price','wpestate');
                }  ?>
            </a>
            <div id="collapseOne" class="panel-collapse collapse in">
                <div class="panel-body panel-body-border">
                    <?php print estate_listing_price($post->ID); ?>
                    <?php  wpestate_show_custom_details($post->ID); ?>
                </div>
            </div>
        </div>
        
        
        
        <div class="panel-wrapper">
            <!-- property address   -->             
            <a class="panel-title" data-toggle="collapse" data-parent="#accordion_prop_addr" href="#collapseTwo">  <span class="panel-title-arrow"></span>
               위치
            </a>    
            <div id="collapseTwo" class="panel-collapse collapse in">
                <div class="panel-body panel-body-border">
                    <?php print estate_listing_address($post->ID); ?>
                </div>
            </div>
        </div>

        <div class="property_page_container">
            <h3 class="panel-title" id="on_the_map"><?php esc_html_e('자세한 위치');?></h3>
            <div class="google_map_on_list_wrapper">
                <div id="gmapzoomplus"></div>
                <div id="gmapzoomminus"></div>
                <div id="gmapstreet"></div>

                <div id="google_map_on_list"
                     data-cur_lat="<?php   echo $gmap_lat;?>"
                     data-cur_long="<?php echo $gmap_long ?>"
                     data-post_id="<?php echo $post->ID; ?>">
                </div>
            </div>
        </div>


        <div class="property_page_container ">
            <?php
            get_template_part ('/templates/show_avalability');
            wp_reset_query();
            ?>  
        </div>    
         
        <?php
        endwhile; // end of the loop
        $show_compare=1;
        ?>
        
        
        
      
        <?php     get_template_part ('/templates/listing_reviews'); ?>

        
       
        
    
        
        </div><!-- end single content -->

        <?php   
        $show_sim_two=1;
        get_template_part ('/templates/similar_listings');
        ?> 
    </div><!-- end 8col container-->
    
    
    
  
    
    <div class="clearfix visible-xs"></div>
    <div class=" 
        <?php
        if($options['sidebar_class']=='' || $options['sidebar_class']=='none' ){
            print ' col-md-4 '; 
        }else{
            print $options['sidebar_class'];
        }
        ?> 
        widget-area-sidebar listingsidebar2 listing_type_1" id="primary" >
        
            <div class="listing_main_image_price">
                티켓을 이용해 예약하세요
            </div>

            <div class="booking_form_request" id="booking_form_request">
            <div id="booking_form_request_mess"></div>
            <h3 ><?php esc_html_e('방문 희망일');?></h3>
             
                <div class="has_calendar calendar_icon">
                    <input type="text" id="start_date" placeholder="<?php esc_html_e('Check in','wpestate'); ?>"  class="form-control calendar_icon" size="40" name="start_date" 
                            value="<?php if( isset($_GET['check_in_prop']) ){
                               echo sanitize_text_field ( $_GET['check_in_prop'] );
                            }
                            ?>">
                </div>

                <div class=" has_calendar calendar_icon">
                    <input type="text" id="end_date" disabled placeholder="<?php esc_html_e('Check Out','wpestate'); ?>" class="form-control calendar_icon" size="40" name="end_date" 
                           value="<?php if( isset($_GET['check_out_prop']) ){
                               echo sanitize_text_field ( $_GET['check_out_prop'] );
                            }
                            ?>">
                </div>

                <div class=" has_calendar guest_icon ">
                    <?php 
                    $max_guest = get_post_meta($post_id,'guest_no',true);
                    print '
                    <div class="dropdown form-control">
                        <div data-toggle="dropdown" id="booking_guest_no_wrapper" class="filter_menu_trigger" data-value="';
                        if(isset($_GET['guest_no_prop']) && $_GET['guest_no_prop']!=''){
                            echo esc_html( $_GET['guest_no_prop'] );
                        }else{
                          echo 'all';
                        }
                
                       
                        print '">';
                         
                        if(isset($_GET['guest_no_prop']) && $_GET['guest_no_prop']!=''){
                            echo esc_html( $_GET['guest_no_prop'] ).' '.esc_html__( 'guests','wpestate');
                        }else{
                            esc_html_e('Guests','wpestate');
                        }
                
                        print'<span class="caret caret_filter"></span>
                        </div>           
                        <input type="hidden" name="booking_guest_no"  value="">
                        <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="booking_guest_no_wrapper" id="booking_guest_no_wrapper_list">
                            '.$guest_list.'
                        </ul>        
                    </div>';
                    ?> 
                </div>
                
            
                <?php
                // shw extra options
                wpestate_show_extra_options_booking($post_id)
                ?>
            

                <p class="full_form " id="add_costs_here"></p>            

                <input type="hidden" id="listing_edit" name="listing_edit" value="<?php echo $post_id;?>" />

                <div class="submit_booking_front_wrapper">
                    <?php   
                    $overload_guest                 =   floatval   ( get_post_meta($post_id, 'overload_guest', true) );
                    $price_per_guest_from_one       =   floatval   ( get_post_meta($post_id, 'price_per_guest_from_one', true) );
                    ?>
                    
                    <?php  $instant_booking                 =   floatval   ( get_post_meta($post_id, 'instant_booking', true) ); 
                    if($instant_booking ==1){ ?>
                        <div id="submit_booking_front_instant_wrap"><input type="submit" id="submit_booking_front_instant" data-maxguest="<?php echo $max_guest; ?>" data-overload="<?php echo $overload_guest;?>" data-guestfromone="<?php echo $price_per_guest_from_one; ?>"  class="wpb_btn-info wpb_btn-small wpestate_vc_button  vc_button" value=" <?php esc_html_e('Instant Booking','wpestate');?>" /></div>
                    <?php }else{?>   
                        <input type="submit" id="submit_booking_front" data-maxguest="<?php echo $max_guest; ?>" data-overload="<?php echo $overload_guest;?>" data-guestfromone="<?php echo $price_per_guest_from_one; ?>"  class="wpb_btn-info wpb_btn-small wpestate_vc_button  vc_button" value="<?php esc_html_e('요청하기');?>" />
                    <?php }?>


                    <?php wp_nonce_field( 'booking_ajax_nonce', 'security-register-booking_front' );?>
                </div>

                <div class="third-form-wrapper">
                    <div class="col-md-6 reservation_buttons">
                        <div id="add_favorites" class=" <?php print $favorite_class;?>" data-postid="<?php the_ID();?>">
                            <?php echo $favorite_text;?>
                        </div>                 
                    </div>

                    <div class="col-md-6 reservation_buttons">
                        <div id="contact_host" class="col-md-6"  data-postid="<?php the_ID();?>">
                            <?php esc_html_e('Contact Owner','wpestate');?>
                        </div>  
                    </div>
                </div>
                
                <?php 
                if (has_post_thumbnail()){
                    $pinterest = wp_get_attachment_image_src(get_post_thumbnail_id(),'wpestate_property_full_map');
                }
                ?>

                <div class="prop_social">
                    <span class="prop_social_share"><?php esc_html_e('Share','wpestate');?></span>
                    <a href="http://www.facebook.com/sharer.php?u=<?php echo esc_url(get_permalink()); ?>&amp;t=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="share_facebook"><i class="fa fa-facebook fa-2"></i></a>
                    <a href="http://twitter.com/home?status=<?php echo urlencode(get_the_title() .' '.esc_url( get_permalink()) ); ?>" class="share_tweet" target="_blank"><i class="fa fa-twitter fa-2"></i></a>
                    <a href="https://plus.google.com/share?url=<?php echo esc_url(get_permalink()); ?>" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;" target="_blank" class="share_google"><i class="fa fa-google-plus fa-2"></i></a> 
                    <?php if (isset($pinterest[0])){ ?>
                        <a href="http://pinterest.com/pin/create/button/?url=<?php echo esc_url(get_permalink()); ?>&amp;media=<?php echo $pinterest[0];?>&amp;description=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="share_pinterest"> <i class="fa fa-pinterest fa-2"></i> </a>      
                    <?php } ?>           
                </div>             

        </div>
        
        <div class="owner_area_wrapper_sidebar" id="listing_owner">
            <?php get_template_part ('/templates/owner_area2'); ?>
        </div>
        
        <?php  include(locate_template('sidebar-listing.php')); ?>
    </div>
</div>   

<?php get_footer(); ?>