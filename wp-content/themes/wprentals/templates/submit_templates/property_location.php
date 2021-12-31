<?php
global $property_latitude;
global $property_longitude;
global $property_address;
global $edit_id;
global $edit_link_amenities;
?>


<div class="col-md-12">
    <div class="user_dashboard_panel">
        <h4 class="user_dashboard_panel_title"><?php esc_html_e('Listing Location', 'wpestate'); ?></h4>

        <div class="col-md-12" id="profile_message"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-3 dashboard_chapter_label"> <?php esc_html_e('Listing location details', 'wpestate'); ?></div>
                <div class="col-md-9">
                    <p>
                        <label for="property_address"><?php esc_html_e('주소 지정'); ?>&nbsp;</label>
                        <button id="find_loc"
                                class="wpb_btn-info wpestate_vc_button vc_button"><?php esc_html_e('주소 검색'); ?></button>
                        <input type="text" id="property_address" placeholder="상단의 주소 검색 버튼을 눌러 입력해주세요."
                               class="form-control postcodify_address" size="40" name="property_address"
                               value="<?php print $property_address; ?>">
                        <input type="hidden" id="property_latitude" value="<?php print $property_latitude; ?>">
                        <input type="hidden" id="property_longitude" value="<?php print $property_longitude; ?>">
                    </p>
                </div>
            </div>


            <div class="col-md-12">
                <div class="col-md-3"></div>

                <div class="col-md-9">
                    <div id="google_capture"
                         class="wpb_btn-small wpestate_vc_button  vc_button"><?php esc_html_e('지도에 반영'); ?></div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-3 dashboard_chapter_label"> <?php esc_html_e('지도에서 보기'); ?></div>
                <div class="col-md-9">
                    <div id="googleMapsubmit"></div>
                </div>
            </div>
        </div>
        <input type="hidden" id="property_city_submit" value="<?php echo $property_city ?>">
        <input id="property_country" type="hidden" value="<?php echo $property_country; ?>">

        <div class="col-md-12" style="display: inline-block;">
            <input type="hidden" name="" id="listing_edit" value="<?php echo $edit_id; ?>">
            <input type="submit" class="wpb_btn-info wpb_btn-small wpestate_vc_button  vc_button"
                   id="edit_prop_locations" value="<?php esc_html_e('Save', 'wpestate') ?>"/>
            <a href="<?php echo $edit_link_amenities; ?>"
               class="next_submit_page"><?php esc_html_e('Go to Amenities settings (*make sure you click save first).', 'wpestate'); ?></a>

        </div>
    </div>
