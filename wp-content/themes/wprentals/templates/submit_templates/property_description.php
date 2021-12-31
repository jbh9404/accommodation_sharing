<?php
global $edit_id;
global $submit_title;
global $submit_description;
global $property_price; 
global $property_label; 
global $prop_action_category;
global $prop_action_category_selected;
global $prop_category_selected;
global $property_city;
global $property_area;
global $guestnumber;
global $property_country;
global $property_admin_area;
global $edit_link_images;
global $instant_booking;

global $current_user;
get_currentuserinfo();

$user_name_id = $current_user->user_login;
?>



<div class="col-md-12" id="new_post2">
    <div class="user_dashboard_panel">
    <h4 class="user_dashboard_panel_title"><?php  esc_html_e('Description','wpestate');?></h4>
    
    


    <div class="col-md-12" id="profile_message"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-3 dashboard_chapter_label">
                <p>
                    <label for="title"><?php esc_html_e('여행자들에게 보여지는 제목'); ?> </label>
                </p>
            </div>

            <div class="col-md-6">
                <p>
                    <label for="title"><?php esc_html_e('여행자들에게 다음과 같이 보여집니다'); ?> </label>
                    <input type="text" id="title" class="form-control" value="<?php print $user_name_id."님의 공간"; ?>" readonly size="20" name="wpestate_title" />
                </p>
            </div>
        </div>




        <div class="col-md-12" >
            <div class="col-md-3 dashboard_chapter_label">
                <p>
                    <label for="prop_category"><?php esc_html_e('*Category and Listed In/Room Type','wpestate');?></label>
                </p>
            </div>

            <div class="col-md-6">
                <p>
                    <label for="prop_category"><?php esc_html_e('*Category (mandatory)','wpestate');?></label>
                    <?php
                    $args=array(
                        'class'       => 'select-submit2',
                        'hide_empty'  => false,
                        'selected'    => $prop_category_selected,
                        'name'        => 'prop_category',
                        'id'          => 'prop_category_submit',
                        'orderby'     => 'NAME',
                        'order'       => 'ASC',
                        'show_option_none'   => esc_html__( 'None','wpestate'),
                        'taxonomy'    => 'property_category',
                        'hierarchical'=> true
                    );
                    wp_dropdown_categories( $args ); ?>

                </p>
            </div>



            <div class="col-md-3" style="display: none;" >
                <p>
                    <label for="prop_action_category"> <?php esc_html_e('*Listed In/Room Type (mandatory)','wpestate'); $prop_action_category;?></label>
                    <?php
                    $args=array(
                        'class'       => 'select-submit2',
                        'hide_empty'  => true,
                        'selected'    => $prop_action_category_selected,
                        'name'        => 'prop_action_category',
                        'id'          => 'prop_action_category_submit',
                        'orderby'     => 'NAME',
                        'order'       => 'ASC',
                        'selected' => '74',
                        'show_option_none'   => esc_html__( 'None','wpestate'),
                        'taxonomy'    => 'property_action_category',
                        'hierarchical'=> true
                    );

                    wp_dropdown_categories( $args );  ?>
                </p>
            </div>

        </div>




        <div class="col-md-12">

            <div class="col-md-3 dashboard_chapter_label "> 
                <p>
                    <label for="guest_no"><?php esc_html_e('*Guest No','wpestate');?></label>  
                </p>
            </div>

            <div class="col-md-6">
                <p>
                    <label for="guest_no"><?php esc_html_e('*Guest No (mandatory)','wpestate');?></label>
                    <select id="guest_no" name="guest_no">
                        <?php 
                        for($i=0; $i<15; $i++) {
                            print '<option value="'.$i.'" ';
                                if ( $guestnumber==$i){
                                    print ' selected="selected" ';
                                }
                            print '>'.$i.'</option>';
                        } ?>
                    </select>    
                </p>
            </div>
        </div>

        <div class="col-md-12">
            <div class="col-md-3 dashboard_chapter_label">
                <p>
                    <label for="property_city_front"><?php esc_html_e('지역');?></label>
                </p>
            </div>

            <div class="col-md-6">
                <p>
                    <?php
                    $show_adv_search_general            =   get_option('wp_estate_wpestate_autocomplete','');
                    $wpestate_internal_search           =   '';
                    if($show_adv_search_general=='no'){
                        $wpestate_internal_search='_autointernal';
                    }
                    ?>
                    <label for="property_city_front"><?php esc_html_e('도시');?></label>
                    <input type="text"   id="property_city_front<?php echo $wpestate_internal_search;?>" name="property_city_front" placeholder="<?php esc_html_e('Type the city name','wpestate');?>" value="<?php echo $property_city;?>" class="advanced_select  form-control">
                    <?php  if($show_adv_search_general!='no'){ ?>
                        <input type="hidden" id="property_country" name="property_country" value="<?php echo $property_country;?>">
                    <?php } ?>
                    <input type="hidden" id="property_city" name="property_city"  value="<?php echo $property_city;?>" >
                    <input type="hidden" id="property_admin_area" name="property_admin_area" value="<?php echo $property_admin_area;?>">
                </p>
            </div>

            <div class="col-md-3" style="display:none;">
                <label for="property_city_front"><?php esc_html_e('Neighborhood','wpestate');?></label>
                <input type="text"   id="property_area_front" name="property_area_front" placeholder="<?php esc_html_e('Type the neighborhood name','wpestate');?>" value="<?php echo $property_area;?>" class="advanced_select  form-control">
            </div>
        </div>



        <?php  if($show_adv_search_general=='no'){ ?> 
            <div class="col-md-12" style="display:none;">
                 <div class="col-md-3 dashboard_chapter_label"> 
                    <label for="property_country"><?php esc_html_e('Country','wpestate');?></label>
                </div>

                <div class="col-md-3 property_country"> 
                    <label for="property_country"><?php esc_html_e('Country','wpestate');?></label>
                    <?php print wpestate_country_list(esc_html(get_post_meta($edit_id, 'property_country', true))); ?>
                </div>
            </div>    
        <?php } ?>




        <div class="col-md-12">
            <div class="col-md-3 dashboard_chapter_label">
                <label for="property_description"><?php esc_html_e('호스트 및 숙소 소개');?></label>
            </div>

            <div class="col-md-6">
                <label for="property_description"><?php esc_html_e('여행자들이 알기 쉽게 자세히 작성해주세요.');?></label>
                <textarea  rows="10" id="property_description" name="property_description"  class="advanced_select  form-control" placeholder="<?php esc_html_e('Describe your property','wpestate');?>"><?php echo $submit_description; ?></textarea>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12"> 
            <input style="float:left;" type="checkbox" class="form-control" value="1"  id="instant_booking" name="instant_booking" <?php print $instant_booking; ?> >
            <label style="display: inline;" for="instant_booking"><?php esc_html_e('Allow instant booking? If checked, you will not have the option to reject a booking request.','wpestate');?></label>
        </div>
    </div>
    <input type="hidden" name="" id="listing_edit" value="<?php echo $edit_id;?>">
    
    <div class="col-md-12" style="display: inline-block;"> 
        <input type="submit" class="wpb_btn-info wpb_btn-small wpestate_vc_button  vc_button" id="edit_prop_1" value="<?php esc_html_e('Save', 'wpestate') ?>" />
        <a href="<?php echo $edit_link_images;?>" class="next_submit_page"><?php esc_html_e('이미지 설정으로 이동합니다.(먼저 저장을 클릭해주세요.)');?></a>
    </div>

</div>
</div>