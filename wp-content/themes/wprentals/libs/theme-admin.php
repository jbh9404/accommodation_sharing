<?php
if( !function_exists('wpestate_new_general_set') ):
function wpestate_new_general_set() {  
   if($_SERVER['REQUEST_METHOD'] === 'POST'){	
    
       
        $theme_options_api=array();
        $api_options = array(
   
            'wp_estate_measure_sys',
            'wp_estate_date_lang',
            'wp_estate_setup_weekend',    
            'wp_estate_separate_users',
            'wp_estate_publish_only',
            'wp_estate_prices_th_separator',
            'wp_estate_currency_symbol',
            'wp_estate_where_currency_symbol',
            'wp_estate_currency_label_main',
            'wp_estate_is_custom_cur',
            'wp_estate_auto_curency',
            'wp_estate_currency_name',
            'wp_estate_currency_label',
            'wp_estate_where_cur',
            'wp_estate_wp_estate_custom_fields',
            'wp_estate_feature_list',
            'wp_estate_status_list',        
            'wp_estate_company_name',
            'wp_estate_email_adr',
            'wp_estate_telephone_no',
            'wp_estate_mobile_no',
            'wp_estate_fax_ac',
            'wp_estate_skype_ac',
            'wp_estate_co_address',
            'wp_estate_facebook_link',
            'wp_estate_twitter_link',
            'wp_estate_google_link',
            'wp_estate_pinterest_link',
            'wp_estate_linkedin_link',
            'wp_estate_facebook_login',
            'wp_estate_google_login',
            'wp_estate_yahoo_login',
            'wp_estate_social_register_on',
            'wp_estate_readsys',
            'wp_estate_enable_paypal',
            'wp_estate_enable_stripe',
            'wp_estate_admin_submission',
            'wp_estate_price_submission',
            'wp_estate_price_featured_submission',
            'wp_estate_submission_curency',
            'wp_estate_prop_image_number',
            'wp_estate_enable_direct_pay',
            'wp_estate_submission_curency_custom',
            'wp_estate_free_mem_list',
            'wp_estate_free_feat_list',
            'wp_estate_book_down',
            'wp_estate_book_down_fixed_fee',
            'wp_estate_service_fee_fixed_fee',
            'wp_estate_service_fee'
        );

       
      
        
        if(isset($_POST['is_club_sms']) && intval($_POST['is_club_sms'])==1){
            $use_sms=array();
            if(isset($_POST['use_sms'])){
                $use_sms=$_POST['use_sms'];
            }
         
                    
            rcapi_save_sms($_POST['sms_content'],$use_sms,$_POST['twilio_api_key'],$_POST['twilio_auth_token'],$_POST['twilio_phone_no']);
        
        }
        
        if( isset($_POST['rcapi_api_key'])){
            update_option('wp_estate_curent_token','');
        }
      
        $allowed_html   =   array();
        if( isset( $_POST['add_field_name'] ) ){
            $new_custom=array();  
            foreach( $_POST['add_field_name'] as $key=>$value ){
                $temp_array=array();
                $temp_array[0]=$value;
                $temp_array[1]= wp_kses( $_POST['add_field_label'][sanitize_key($key)] ,$allowed_html);
                $temp_array[2]= wp_kses( $_POST['add_field_type'][sanitize_key($key)] ,$allowed_html);
                $temp_array[3]= wp_kses ( $_POST['add_field_order'][sanitize_key($key)],$allowed_html);
                $temp_array[4]=  ( $_POST['add_dropdown_order'][sanitize_key($key)]);
                $new_custom[]=$temp_array;
            }

          
            usort($new_custom,"wpestate_sorting_function");
            update_option( 'wp_estate_custom_fields', $new_custom );   
        }
          
        // multiple currencies
        if( isset( $_POST['add_curr_name'] ) ){
            foreach( $_POST['add_curr_name'] as $key=>$value ){
                $temp_array=array();
                $temp_array[0]=$value;
                $temp_array[1]= wp_kses( $_POST['add_curr_label'][sanitize_key($key)] ,$allowed_html);
                $temp_array[2]= wp_kses( $_POST['add_curr_value'][sanitize_key($key)] ,$allowed_html);
                $temp_array[3]= wp_kses( $_POST['add_curr_order'][sanitize_key($key)] ,$allowed_html);
                $new_custom_cur[]=$temp_array;
            }
            
            update_option( 'wp_estate_multi_curr', $new_custom_cur );   

       }else{
           
       }


        if( isset( $_POST['theme_slider'] ) ){
            update_option( 'wp_estate_theme_slider', true);  
        }
        
        
        $exclude_array=array(
            'is_club_sms',
            'use_sms',
            'add_field_name',
            'add_field_label',
            'add_field_type',
            'add_field_order',
            'add_dropdown_order',
            'adv_search_how',
            'adv_search_what',
            'adv_search_label',
        );
        
       
        foreach($_POST as $variable=>$value){	

            if ($variable!='submit'){
                if (!in_array($variable, $exclude_array) ){
                    
                    if( in_array('wp_estate_'.$variable, $api_options) ){
                        $theme_options_api[$variable]=$value;
                    }
                    
                    $variable   =   sanitize_key($variable);
                    if($variable=='co_address'){
                        $allowed_html_br=array(
                                'br' => array(),
                                'em' => array(),
                                'strong' => array()
                        );
                        $postmeta   =   wp_kses($value,$allowed_html_br);
                    }else{
                        $postmeta   =   wp_kses($value,$allowed_html);
                    
                    }
                    
               
                    update_option( wpestate_limit64('wp_estate_'.$variable), $postmeta );                
                }else{
                
                    update_option( 'wp_estate_'.$variable, $value );
                }	
            }	
        }
        
        
        if(isset($_POST['advanced_exteded']) && is_array($_POST['advanced_exteded']) ){
            
        }
        
        if( isset($_POST['is_custom']) && $_POST['is_custom']== 1 && !isset($_POST['add_field_name']) ){
                 update_option( 'wp_estate_custom_fields', '' ); 
        }
        
        if( isset($_POST['is_custom_cur']) && $_POST['is_custom_cur']== 1 && !isset($_POST['add_curr_name']) ){
            update_option( 'wp_estate_multi_curr', '' );
        }
        
      
        
    
        
        if ( isset($_POST['paid_submission']) ){
            if( $_POST['paid_submission']=='membership'){
                wp_estate_schedule_user_check();  
            }else{
                wp_clear_scheduled_hook('wpestate_check_for_users_event');
            }
        }
        
        if ( isset($_POST['delete_orphan']) ){
            if( $_POST['delete_orphan']=='yes'){
                setup_wp_estate_delete_orphan_lists();  
            }else{
                wp_clear_scheduled_hook('prefix_wp_estate_delete_orphan_lists');
            }
        }
        
           
        if( isset($_POST['wpestate_autocomplete'])  ){  
            if( $_POST['wpestate_autocomplete']=='no' ){
                wpestate_create_auto_data();
            }else{
                wp_clear_scheduled_hook('event_wp_estate_create_auto');
            }  
     
        }
    
        if ( isset($_POST['auto_curency']) ){
            if( $_POST['auto_curency']=='yes' ){
                wp_estate_enable_load_exchange();
            }else{
                wp_clear_scheduled_hook('wpestate_load_exchange_action');
            }
        }
        
        if( isset($_POST['on_child_theme']) && intval( $_POST['on_child_theme']==1) ){
          
          
            print '<script type="text/javascript">
                //<![CDATA[
                jQuery(document).ready(function(){
                   jQuery("#css_modal").show();
                });
                //]]>
                </script>';
        }
     
        if( !empty($theme_options_api) ){   
            rcapi_udate_theme_options($theme_options_api);
        }
      
        if( isset($_POST['book_down']) && floatval($_POST['book_down'])==100 ){
           update_option( 'wp_estate_include_expenses', 'yes' );   
        }
       
        if( isset($_POST['theme_slider_manual']) && $_POST['theme_slider_manual']!=''){
            $theme_slider           =  array();
            $new_ids= explode(',', $_POST['theme_slider_manual']);
            
            foreach($new_ids as $key=>$value){
                $theme_slider[]=$value;
            }
            update_option( 'wp_estate_theme_slider', $theme_slider); 
        }
        
}
    


    
$allowed_html   =   array();  
$active_tab = isset( $_GET[ 'tab' ] ) ? wp_kses( $_GET[ 'tab' ],$allowed_html ) : 'general_settings';  
require_once get_template_directory().'/libs/help_content.php';
print ' <div class="wrap">
      
        <div class="wpestate_admin_search_bar">
            <label class="wpestate_adv_search_label">'.__('Theme Help Search - there are over 170 articles to help you setup and use the theme. Please use this search and if your question is not here, please open a ticket in our client support system.','wpestate').'</label>
            <input type="text" id="wpestate_search_bar" placeholder="'.__('Search help documentation. For ex. type: Adv ','wpestate').'">
            <div id="wpestate_admin_results">
            </div>
        </div>';

      
        print '<div class="wrap-topbar">';
        
        $hidden_tab='none';
        if(isset($_POST['hidden_tab'])) {
            $hidden_tab= esc_attr( $_POST['hidden_tab'] );
        }
        
        $hidden_sidebar='none';
        if(isset($_POST['hidden_sidebar'])) {
            $hidden_sidebar= esc_attr( $_POST['hidden_sidebar'] );
        }
        
        print '<input type="hidden" id="hidden_tab" name="hidden_tab" value="'.$hidden_tab.'">';        
        print '<input type="hidden" id="hidden_sidebar"  name="hidden_sidebar" value="'.$hidden_sidebar.'">';
        
        print   '<div id="general_settings" data-menu="general_settings_sidebar" class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/general.png'.'" alt="general settings">'.__('General','wpestate').'
                </div>';
        
        print   '<div id="social_contact" data-menu="social_contact_sidebar" class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/contact.png'.'" alt="general settings">'.__('Social & Contact','wpestate').'
                </div>';
        
        print   '<div id="map_settings" data-menu="map_settings_sidebar" class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/map.png'.'" alt="general settings">'.__('Map','wpestate').'
                </div>';
         
        print   '<div id="design_settings" data-menu="design_settings_sidebar" class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/design.png'.'" alt="general settings">'.__('Design','wpestate').'
                </div>';
        
        print   '<div id="advanced_settings" data-menu="advanced_settings_sidebar" class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/advanced.png'.'" alt="general settings">'.__('Advanced','wpestate').'
                </div>';
            
        print   '<div id="membership_settings" data-menu="membership_settings_sidebar"  class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/membership.png'.'" alt="general settings">'.__('Membership','wpestate').'
                </div>';
        
        print   '<div id="advanced_search_settings" data-menu="advanced_search_settings_sidebar"  class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/search.png'.'" alt="general settings">'.__('Search','wpestate').'
                </div>';
        print   '<div id="help_custom" data-menu="rentals_club_sidebar"  class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/club.png'.'" alt="general settings">'.__('Rentals Club','wpestate').'
                </div>';
        print   '<div id="help_custom" data-menu="help_custom_sidebar"  class="admin_top_bar_button"> 
                    <img src="'.get_template_directory_uri().'/img/admin/help.png'.'" alt="general settings">'.__('Help & Custom','wpestate').'
                </div>';
        
        print '<div class="theme_details">'. wp_get_theme().'</div>';
        
    print '</div>';


    print '
    <div id="wpestate_sidebar_menu">
        <div id="general_settings_sidebar" class="theme_options_sidebar">
            <ul>
                <li data-optiontab="global_settings_tab" class="selected_option">'.__('Global Theme Settings','wpestate').'</li>
                <li data-optiontab="appearance_options_tab"   class="">'.__('Appearance','wpestate').'</li>
                <li data-optiontab="logos_favicon_tab"   class="">'.__('Logos & Favicon','wpestate').'</li>
                <li data-optiontab="header_settings_tab"   class="">'.__('Header','wpestate').'</li>
                <li data-optiontab="footer_settings_tab"   class="">'.__('Footer','wpestate').'</li>
                <li data-optiontab="price_curency_tab"   class="">'.__('Price & Currency','wpestate').'</li>
                <li data-optiontab="custom_fields_tab"   class="">'.__('Custom Fields','wpestate').'</li>
                <li data-optiontab="ammenities_features_tab"   class="">'.__('Features & Amenities','wpestate').'</li>
                <li data-optiontab="listing_labels_tab"   class="">'.__('Listings Labels','wpestate').'</li>   
                <li data-optiontab="theme_slider_tab"   class="">'.__('Theme Slider','wpestate').'</li>   
            </ul>
        </div>
        
        <div id="social_contact_sidebar" class="theme_options_sidebar" style="display:none;">
            <ul>
                <li data-optiontab="contact_details_tab" class="">'.__('Contact Page Details','wpestate').'</li>
                <li data-optiontab="social_accounts_tab" class="">'.__('Social Accounts','wpestate').'</li>
            </ul>
        </div>
        

        <div id="map_settings_sidebar" class="theme_options_sidebar" style="display:none;">
            <ul>
                <li data-optiontab="general_map_tab" class="">'.__('Map Settings','wpestate').'</li>
                <li data-optiontab="pin_management_tab" class="">'.__('Pins Management','wpestate').'</li>
                <li data-optiontab="generare_pins_tab" class="">'.__('Generate Pins','wpestate').'</li>
            </ul>
        </div>
        

        <div id="design_settings_sidebar" class="theme_options_sidebar" style="display:none;">
            <ul>
                <li data-optiontab="general_design_settings_tab" class="">'.__('General Design Settings','wpestate').'</li>
                <li data-optiontab="custom_colors_tab" class="">'.__('Custom Colors','wpestate').'</li>
                <li data-optiontab="custom_fonts_tab" class="">'.__('Fonts','wpestate').'</li>
                <li data-optiontab="mainmenu_design_elements_tab" class="">'.__('Main Menu Design','wpestate').'</li>
            </ul>
        </div>
        
        <div id="advanced_search_settings_sidebar" class="theme_options_sidebar" style="display:none;">
            <ul>
                <li data-optiontab="advanced_search_settings_tab" class="">'.__('Advanced Search Settings','wpestate').'</li>
              
            </ul>
        </div>
        
        <div id="membership_settings_sidebar" class="theme_options_sidebar" style="display:none;">
            <ul>
                <li data-optiontab="membership_settings_tab" class="">'.__('Membership Settings','wpestate').'</li>
                <li data-optiontab="paypal_settings_tab" class="">'.__('Paypal Settings','wpestate').'</li>
                <li data-optiontab="stripe_settings_tab" class="">'.__('Stripe Settings','wpestate').'</li>
            </ul>
        </div>

 
        <div id="advanced_settings_sidebar" class="theme_options_sidebar" style="display:none;">
             <ul>
                <li data-optiontab="email_management_tab" class="selected_option">'.__('Email Management','wpestate').'</li>
                <li data-optiontab="export_settings_tab" class="selected_option">'.__('Export Options','wpestate').'</li>
                <li data-optiontab="import_settings_tab" class="selected_option">'.__('Import Options','wpestate').'</li>
                <li data-optiontab="recaptcha_tab" class="selected_option">'.__('reCaptcha settings','wpestate').'</li>
               
            </ul>
        </div>
        
        
      
        <div id="help_custom_sidebar" class="theme_options_sidebar" style="display:none;">
             <ul>
                <li data-optiontab="help_custom_tab" class="selected_option">'.__('Help & Custom','wpestate').'</li>
            </ul>
        </div>
        
        
        <div id="rentals_club_sidebar" class="theme_options_sidebar" style="display:none;">
            <ul>
                <li data-optiontab="rentals_api_tab" class="selected_option">'.__('Rentals Club Api BETA','wpestate').'</li>
                <li data-optiontab="sms_notice_tab" class="selected_option">'.__('SMS','wpestate').'</li>
                <li data-optiontab="payments_management_tab" class="selected_option">'.__('Payments Management','wpestate').'</li>
            </ul>
        </div>
        
    </div>';
    

    print ' <div id="wpestate_wrapper_admin_menu">';
        print ' <div id="general_settings_sidebar_tab" class="theme_options_wrapper_tab">
                    <form method="post" action="" >
                        <div id="global_settings_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('General Settings','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';    
                            wpestate_theme_admin_general_settings();
                        print '        
                        </div>
                    </form>

                    <form method="post" action="">
                    <div id="appearance_options_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Appearance','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                        wpestate_theme_admin_apperance();
                    print '        
                    </div>
                    </form>

                    <form method="post" action="">
                    <div id="logos_favicon_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Logos & Favicon','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                        new_wpestate_theme_admin_logos_favicon();
                    print '        
                    </div>
                    </form>

                    <form method="post" action="">
                    <div id="header_settings_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Header Settings','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                        new_wpestate_header_settings();
                    print '        
                    </div>
                    </form>

                    <form method="post" action="">
                    <div id="footer_settings_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Footer Settings','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                        new_wpestate_footer_settings();
                    print '        
                    </div>
                    </form>

                    <form method="post" action="">
                    <div id="price_curency_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Price & Currency','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                        wpestate_price_set();
                    print '        
                    </div>
                    </form>

                    <form method="post" action="">
                    <div id="custom_fields_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Custom Fields','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                         wpestate_custom_fields();
                    print '        
                    </div>
                    </form>
                    
                    <form method="post" action="">
                    <div id="ammenities_features_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Features & Amenities','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                        wpestate_display_features();
                    print '        
                    </div>
                    </form>

                    <form method="post" action="">
                    <div id="listing_labels_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Listings Labels','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                        wpestate_display_labels();
                    print '        
                    </div>
                    </form>
                    
                    <form method="post" action="">
                    <div id="theme_slider_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Theme Slider ','wpestate').'</h1>
                        <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                        <div class="theme_option_separator"></div>';
                        wpestate_theme_slider();
                    print '        
                    </div>
                    </form>
 
                </div>';
                    
                
    print'   <div id="social_contact_sidebar_tab" class="theme_options_wrapper_tab" style="display:none">
                        <form method="post" action="">
                        <div id="contact_details_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Contact Page Details','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            wpestate_theme_admin_social();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="social_accounts_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Social Accounts ','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            new_wpestate_theme_social_accounts();
                        print '        
                        </div>
                        </form>

                       
                </div> 




                <div id="map_settings_sidebar_tab" class="theme_options_wrapper_tab" style="display:none">
                        <form method="post" action="">
                        <div id="general_map_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Map  Settings','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            wpestate_theme_admin_mapsettings();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="pin_management_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Pin Management','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            wpestate_show_pins();
                        print '        
                        </div>
                        </form>

                        <form method="post" action="">
                        <div id="generare_pins_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Generate Pins','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            wpestate_generate_file_pins();
                        print '        
                        </div>
                        </form>
                </div>'; 




                
    print'   <div id="design_settings_sidebar_tab" class="theme_options_wrapper_tab" style="display:none">
                    
                     
                        <form method="post" action="">
                        <div id="custom_colors_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Custom Colors Settings','wpestate').'</h1>
                            <span class="header_explanation">'.__('***Please understand that we cannot add here color controls for all theme elements & details. Doing that will result in a overcrowded and useless interface. These small details need to be addressed via custom css code','wpestate').'</span>    
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            new_wpestate_custom_colors();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="custom_fonts_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Custom Fonts','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                           new_wpestate_custom_fonts();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="mainmenu_design_elements_tab" class="theme_options_tab" style="display:none;" >
                            <h1>'.__('Main Menu Design Tab','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            new_wpestate_main_menu_design();
                        print '        
                        </div>
                        </form>
                        
                        
                        <form method="post" action="">
                        <div id="general_design_settings_tab" class="theme_options_tab" style="display:none;" >
                            <h1>' . __('General Design Settings', 'wpestate') . '</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="' . __('Save Changes', 'wpestate') . '" />
                            <div class="theme_option_separator"></div>';
                            wpestate_general_design_settings();
                        print '        
                        </div>
                        </form>


                </div> ';
                





    print'      <div id="advanced_search_settings_sidebar_tab" class="theme_options_wrapper_tab"  style="display:none">
                        <form method="post" action="">
                        <div id="advanced_search_settings_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Advanced Search Settings','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            wpestate_theme_admin_adv_search();
                        print '        
                        </div>
                        </form>
                        
               </div> 
                


                <div id="membership_settings_sidebar_tab" class="theme_options_wrapper_tab" style="display:none">
                        <form method="post" action="">
                        <div id="membership_settings_tab" class="theme_options_tab"  style="display:none;">
                            <h1>'.__('Membership Settings','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            wpestate_theme_admin_membershipsettings();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="paypal_settings_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('PaypPal Settings','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            new_wpestate_paypal_settings();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="stripe_settings_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Stripe Settings','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            new_wpestate_stripe_settings();
                        print '        
                        </div>
                        </form>

                </div> 
                
             

                <div id="advanced_settings_sidebar_tab" class="theme_options_wrapper_tab" style="display:none">
                
                        <form method="post" action="">
                        <div id="email_management_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Email Management','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                              wpestate_email_management();
                        print '        
                        </div>
                        </form>
                     
                        
                        <form method="post" action="">
                        <div id="export_settings_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Export Options','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                             new_wpestate_export_settings();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="import_settings_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Import Options','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            new_wpestate_import_options_tab();
                        print '        
                        </div>
                        </form>

                        
                        <form method="post" action="">
                        <div id="recaptcha_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('reCaptcha settings','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                            estate_recaptcha_settings();
                        print '        
                        </div>
                        </form>
                        
                        


     
                </div> 



                <div id="rentals_club_sidebar_tab" class="theme_options_wrapper_tab" style="display:none">
                
                        <form method="post" action="">
                        <div id="rentals_api_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Rentals Club Api','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                              rentals_api_managment();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="sms_notice_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('SMS Management','wpestate').'</h1>
                            <input type="submit" name="submit"  class="new_admin_submit new_admin_submit_right" value="'.__('Save Changes','wpestate').'" />
                            <div class="theme_option_separator"></div>';
                              wpestate_sms_notice_managment();
                        print '        
                        </div>
                        </form>
                        
                        <form method="post" action="">
                        <div id="payments_management_tab" class="theme_options_tab" style="display:none;">
                            <h1>'.__('Payments Management','wpestate').'</h1>
                           
                            <div class="payment_management_rentals_wrapper">
                            <p class=""><strong>'.esc_html__('The actual payments can be made only in RentalsClub.org interface ','wpestate').'</strong></p>';
                        
                                rcapi_payment_management_info();
                            print '</div>';

                        print '        
                        </div>
                        </form>



                </div>       

                <div id="help_custom_sidebar_tab" class="theme_options_wrapper_tab">
                    <form method="post" action="">
                    <div id="help_custom_tab" class="theme_options_tab" style="display:none;">
                        <h1>'.__('Help&Custom','wpestate').'</h1>
                        <div class="theme_option_separator"></div>';
                        wpestate_theme_admin_help();
                    print '        
                    </div>
                    </form>
                </div>


           </div>';

print '</div>';


       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
       
   
  print '<script type="text/javascript">
    //<![CDATA[
    
    function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
        return split( term ).pop();
    }
    function decodeHtml(html) {
  
        var txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }
                jQuery(document).ready(function(){
                    var autofill='.$help_content.';
                    jQuery("#wpestate_search_bar" ).autocomplete({
                   
                    source: function( request, response ) {
                   
                     response( jQuery.ui.autocomplete.filter(
                       autofill, extractLast( request.term ) ) );
                   },
                    focus: function( event, ui ) {
                        jQuery( "#wpestate_admin_results" ).val( decodeHtml( ui.item.label ) );
                        return false;
                    }, select: function( event, ui ) { 
                        window.open(ui.item.value,"_blank");
                    }
                    
                    
                    
                }).autocomplete( "instance" )._renderItem = function( ul, item ) {
                        return jQuery( "<li>" )
                        .append( "" + decodeHtml( item.label )+ "" )
                            .appendTo( ul );
                    };
                

           });
           //]]>
           </script>';
   
     
        
        print '<div class="clear"></div>';           
print '</div>';
print '<div class="clear"></div>';
}
endif; // end   wpestate_new_general_set  




if( !function_exists('wpestate_show_advanced_search_options') ):

function  wpestate_show_advanced_search_options($i,$adv_search_what){
    $return_string='';

    $curent_value='';
    if(isset($adv_search_what[$i])){
        $curent_value=$adv_search_what[$i];        
    }
    
   // $curent_value=$adv_search_what[$i];
    $admin_submission_array=array('types',
                                  'categories',
                                  'cities',
                                  'areas',
                                  'property price',
                                  'property size',
                                  'property lot size',
                                  'property rooms',
                                  'property bedrooms',
                                  'property bathrooms',
                                  'property address',
                                  'property county',
                                  'property state',
                                  'property zip',
                                  'property country',
                                  'property status'
                                );
    
    foreach($admin_submission_array as $value){

        $return_string.='<option value="'.$value.'" '; 
        if($curent_value==$value){
             $return_string.= ' selected="selected" ';
        }
        $return_string.= '>'.$value.'</option>';    
    }
    
    $i=0;
    $custom_fields = get_option( 'wp_estate_custom_fields', true); 
    if( !empty($custom_fields)){  
        while($i< count($custom_fields) ){          
            $name =   $custom_fields[$i][0];
            $type =   $custom_fields[$i][1];
            $slug =   str_replace(' ','-',$name);

            $return_string.='<option value="'.$slug.'" '; 
            if($curent_value==$slug){
               $return_string.= ' selected="selected" ';
            }
            $return_string.= '>'.$name.'</option>';    
            $i++;  
        }
    }  
    $slug='none';
    $name='none';
    $return_string.='<option value="'.$slug.'" '; 
    if($curent_value==$slug){
        $return_string.= ' selected="selected" ';
    }
    $return_string.= '>'.$name.'</option>';    

       
    return $return_string;
}
endif; // end   wpestate_show_advanced_search_options  



if( !function_exists('wpestate_show_advanced_search_how') ):
function  wpestate_show_advanced_search_how($i,$adv_search_how){
    $return_string='';
    $curent_value='';
    if (isset($adv_search_how[$i])){
         $curent_value=$adv_search_how[$i];
    }
   
    
    
    $admin_submission_how_array=array('equal',
                                      'greater',
                                      'smaller',
                                      'like',
                                      'date bigger',
                                      'date smaller');
    
    foreach($admin_submission_how_array as $value){
        $return_string.='<option value="'.$value.'" '; 
        if($curent_value==$value){
             $return_string.= ' selected="selected" ';
        }
        $return_string.= '>'.$value.'</option>';    
    }
    return $return_string;
}
endif; // end   wpestate_show_advanced_search_how  




/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Advanced Search Settings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('wpestate_theme_admin_adv_search') ):
function wpestate_theme_admin_adv_search(){
    $cache_array                    =   array('yes','no');  
    
    $custom_advanced_search= get_option('wp_estate_custom_advanced_search','');
    $adv_search_what    = get_option('wp_estate_adv_search_what','');
    $adv_search_how     = get_option('wp_estate_adv_search_how','');
    $adv_search_label   = get_option('wp_estate_adv_search_label','');
    
    
    $custom_advanced_search_select ='';
    $custom_advanced_status= esc_html ( get_option('wp_estate_custom_advanced_search','') );
    $value_array=array('no','yes');

    foreach($value_array as $value){
            $custom_advanced_search_select.='<option value="'.$value.'"';
            if ($custom_advanced_status==$value){
                $custom_advanced_search_select.='selected="selected"';
            }
            $custom_advanced_search_select.='>'.$value.'</option>';
    }
  
    
    $show_adv_search_general_select     =   '';
    $show_adv_search_general            =   get_option('wp_estate_show_adv_search_general','');

    foreach($cache_array as $value){
            $show_adv_search_general_select.='<option value="'.$value.'"';
            if ($show_adv_search_general    ==  $value){
                    $show_adv_search_general_select.=' selected="selected" ';
            }
            $show_adv_search_general_select.='> '.$value.'</option>';
    }
    
    
    $wpestate_autocomplete_select     =   '';
    $wpestate_autocomplete           =   get_option('wp_estate_wpestate_autocomplete','');

    foreach($cache_array as $value){
            $wpestate_autocomplete_select.='<option value="'.$value.'"';
            if ($wpestate_autocomplete    ==  $value){
                    $wpestate_autocomplete_select.=' selected="selected" ';
            }
            $wpestate_autocomplete_select.='> '.$value.'</option>';
    }
    
    
    
    
    
    
    
    $show_adv_search_slider_select     =   '';
    $show_adv_search_slider            =   get_option('wp_estate_show_adv_search_slider','');

    foreach($cache_array as $value){
            $show_adv_search_slider_select.='<option value="'.$value.'"';
            if ($show_adv_search_slider    ==  $value){
                    $show_adv_search_slider_select.=' selected="selected" ';
            }
            $show_adv_search_slider_select.='> '.$value.'</option>';
    }
    
    
    
    $show_adv_search_visible_select     =   '';
    $show_adv_search_visible            =   get_option('wp_estate_show_adv_search_visible','');

    foreach($cache_array as $value){
            $show_adv_search_visible_select.='<option value="'.$value.'"';
            if ($show_adv_search_visible    ==  $value){
                    $show_adv_search_visible_select.=' selected="selected" ';
            }
            $show_adv_search_visible_select.='> '.$value.'</option>';
    }
    
   
    $show_adv_search_slider_select     =   '';
    $show_adv_search_slider            =   get_option('wp_estate_show_adv_search_slider','');

    foreach($cache_array as $value){
            $show_adv_search_slider_select.='<option value="'.$value.'"';
            if ($show_adv_search_slider    ==  $value){
                    $show_adv_search_slider_select.=' selected="selected" ';
            }
            $show_adv_search_slider_select.='> '.$value.'</option>';
    }
    
    $search_array   =   array( 
                            "newtype" => esc_html__( 'Type 1','wpestate'),
                            "oldtype" => esc_html__( 'Type 2','wpestate')
                            );
    
    $search_type    =   get_option('wp_estate_adv_search_type','');
    $search_type_select  =   '';
    
    foreach( $search_array as $key=>$value){
        $search_type_select.='<option value="'.$key.'" ';
        if($key==$search_type){
            $search_type_select.=' selected="selected" ';
        }
        $search_type_select.='>'.$value.'</option>'; 
    }
    
   
    
    print '
        <div class="estate_option_row">
            <div class="label_option_row">'.esc_html__( 'Select search type ?','wpestate').'</div>
            <div class="option_row_explain">'.__('Type 1 - vertical design. Type 2 - horizontal design.','wpestate').'</div>   
           
            <select id="adv_search_type" name="adv_search_type">
                '.$search_type_select.'
            </select>
            
        </div>
      
        
        <div class="estate_option_row">
            <div class="label_option_row">'.esc_html__( 'Show Advanced Search?','wpestate').'</div>
            <div class="option_row_explain">'.esc_html__( ' Disables or enables the display of advanced search over header media (Google Maps, Revolution Slider, theme slider or image).','wpestate').'</div>
            <select id="show_adv_search_general" name="show_adv_search_general">
                '.$show_adv_search_general_select.'
            </select>
            
        </div>
        

        <div class="estate_option_row">
            <div class="label_option_row">'.esc_html__( 'Use Google Places autocomplete for Search?','wpestate').'</div>
            <div class="option_row_explain">'.esc_html__( 'If you select NO, the autocomplete will be done with data from properties already saved.','wpestate').'</div>
            <select id="wpestate_autocomplete" name="wpestate_autocomplete">
                '.$wpestate_autocomplete_select.'
            </select></br>
            '.esc_html__('Due to speed reasons the data for NON-Google autocomplete is generated 1 time per day. If you want to manually generate the data, click ','wpestate')
            .'<a href="themes.php?page=libs/theme-admin.php&tab=generate_pins"> '.esc_html__('this link','wpestate').'</a></td>
        </div>


       
        <div class="estate_option_row">
            <div class="label_option_row">'.esc_html__( 'Show Advanced Search over sliders or images?','wpestate').'</div>
            <div class="option_row_explain">'.esc_html__( 'Disables or enables the display of advanced search over header type: revolution slider, image and theme slider.','wpestate').'</div>
            <select id="show_adv_search_slider" name="show_adv_search_slider">
                '.$show_adv_search_slider_select.'
            </select>
         </div>
      
        
        
        <div class="estate_option_row">
            <div class="label_option_row">'.esc_html__( 'Minimum and Maximum value for Price Slider','wpestate').'</div>
            <div class="option_row_explain">'.esc_html__( 'Type only numbers!','wpestate').'</div>
                <input type="text" name="show_slider_min_price"  class="inptxt " value="'.floatval(get_option('wp_estate_show_slider_min_price','')).'"/>
                -   
                <input type="text" name="show_slider_max_price"  class="inptxt " value="'.floatval(get_option('wp_estate_show_slider_max_price','')).'"/>
        </div>

        </table>';
   
        print '<div class="estate_option_row"><h1 class="" style="padding-left:0px;">'.esc_html__( 'Amenities and Features for half map Advanced Search','wpestate').'</h1>'; 
        $feature_list       =   esc_html( get_option('wp_estate_feature_list') );
        $feature_list_array =   explode( ',',$feature_list);
     
  
        
       $advanced_exteded =  get_option('wp_estate_advanced_exteded');
       $advanced_exteded = wpestate_unstrip_array ($advanced_exteded);
        
        
        
        
        print ' <div >  '.esc_html__( '*Hold CTRL for multiple selection','wpestate').'</div>'
        . '<input type="hidden" name="advanced_exteded[]" value="none">'
        . '<div> <select name="advanced_exteded[]" multiple="multiple" style="height:400px;">';
        
        
        
        foreach($feature_list_array as $checker => $value){
            $post_var_name  =  html_entity_decode( str_replace(' ','_', trim(stripslashes($value))) , ENT_QUOTES, 'UTF-8');
            print '<option value="'.$post_var_name.'"' ;
            if(is_array($advanced_exteded)){
                if( in_array ($post_var_name,$advanced_exteded) ){
                    print ' selected="selected" ';
                } 
            }
            
            print '>'.stripslashes($value).' </option>';                
        }
        
        
        
        print '</select></div>';
        print '</div><div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
        </div>'; 
}
endif; // end   wpestate_theme_admin_adv_search  


function wpestate_unstrip_array($array){
    $stripped=array();
    foreach($array as $val){
      
            $stripped[] = stripslashes($val);
        
    }
    return $stripped;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Membership Settings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('wpestate_theme_admin_membershipsettings') ):
function wpestate_theme_admin_membershipsettings(){
    $price_submission               =   floatval( get_option('wp_estate_price_submission','') );
    $price_featured_submission      =   floatval( get_option('wp_estate_price_featured_submission','') );    
    
    $free_feat_list                 =   esc_html( get_option('wp_estate_free_feat_list','') );
    $free_mem_list                  =   esc_html( get_option('wp_estate_free_mem_list','') );
    $cache_array                    =   array('yes','no');  
    $book_down                      =   esc_html( get_option('wp_estate_book_down','') );
    $book_down_fixed_fee            =   esc_html( get_option('wp_estate_book_down_fixed_fee','') );
    $service_fee                    =   esc_html( get_option('wp_estate_service_fee','') );
    $service_fee_fixed_fee          =   esc_html( get_option('wp_estate_service_fee_fixed_fee','') );
    $extra_expense_list             =   esc_html( get_option('wp_estate_extra_expense_list','') );
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $free_mem_list_unl='';
    if ( intval( get_option('wp_estate_free_mem_list_unl', '' ) ) == 1){
      $free_mem_list_unl=' checked="checked" ';  
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $paypal_api_select='';
    $paypal_array   =   array( esc_html__( 'sandbox','wpestate'), esc_html__( 'live','wpestate') );
    $paypal_status  =   esc_html( get_option('wp_estate_paypal_api','') );
    
  
    foreach($paypal_array as $value){
	$paypal_api_select.='<option value="'.$value.'"';
	if ($paypal_status==$value){
            $paypal_api_select.=' selected="selected" ';
	}
	$paypal_api_select.='>'.$value.'</option>';
}



    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $submission_curency_array=array('USD','EUR','AUD','BRL','CAD','CZK','DKK','HKD','HUF','ILS','JPY','MYR','MXN','NOK','NZD','PHP','PLN','GBP','SGD','SEK','CHF','TWD','THB','TRY','RUB');
    $submission_curency_status = esc_html( get_option('wp_estate_submission_curency','') );
    $submission_curency_symbol='';

    foreach($submission_curency_array as $value){
            $submission_curency_symbol.='<option value="'.$value.'"';
            if ($submission_curency_status==$value){
                $submission_curency_symbol.=' selected="selected" ';
            }
            $submission_curency_symbol.='>'.$value.'</option>';
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $paypal_array=array('no','per listing','membership');
    $paid_submission_symbol='';
    $paid_submission_status= esc_html ( get_option('wp_estate_paid_submission','') );

    foreach($paypal_array as $value){
            $paid_submission_symbol.='<option value="'.$value.'"';
            if ($paid_submission_status==$value){
                    $paid_submission_symbol.=' selected="selected" ';
            }
            $paid_submission_symbol.='>'.$value.'</option>';
    }
    
    $merch_array=array('yes','no');
   
    
   
       

    $merch_array=array('yes','no');
    $enable_wire_symbol='';
    $enable_wire_status= esc_html ( get_option('wp_estate_enable_direct_pay','') );

    foreach($merch_array as $value){
            $enable_wire_symbol.='<option value="'.$value.'"';
            if ($enable_wire_status==$value){
                    $enable_wire_symbol.=' selected="selected" ';
            }
            $enable_wire_symbol.='>'.$value.'</option>';
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $admin_submission_symbol    =   '';
    $admin_submission_status    =   esc_html ( get_option('wp_estate_admin_submission','') );
    $submission_curency_custom  =   esc_html ( get_option('wp_estate_submission_curency_custom','') );
    
    foreach($cache_array as $value){
            $admin_submission_symbol.='<option value="'.$value.'"';
            if ($admin_submission_status==$value){
                    $admin_submission_symbol.=' selected="selected" ';
            }
            $admin_submission_symbol.='>'.$value.'</option>';
    }
    
    /////////////////////////////////////////////////////////////////////////////////////////////////
    
    $free_feat_list_expiration  =   intval ( get_option('wp_estate_free_feat_list_expiration','') );
    $direct_payment_details     = stripslashes (esc_html (get_option('wp_estate_direct_payment_details','')));
          
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Enable Paid Submission?', 'wpestate').'</div>
        <div class="option_row_explain">'.__('No = submission is free. Paid listing = submission requires user to pay a fee for each listing. Membership = submission is based on user membership package.', 'wpestate').'</div>    
            <select id="paid_submission" name="paid_submission">
                    '.$paid_submission_symbol.'
		 </select>
        </div>';

    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Paypal & Stripe Api ', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Sandbox = test API. LIVE = real payments API. Update PayPal and Stripe settings according to API type selection.', 'wpestate').'</div>    
            <select id="paypal_api" name="paypal_api">
                    '.$paypal_api_select.'
                </select>
        </div>';
    
    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Submited Listings should be approved by admin?','wpestate').'</div>
        <div class="option_row_explain">'.__('If yes, admin publishes each property submitted in front end manually.','wpestate').'</div>    
            <select id="admin_submission" name="admin_submission">
                    '.$admin_submission_symbol.'
		 </select>
        </div>';
    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Price Per Submission (for "per listing" mode)', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Use .00 format for decimals (ex: 5.50). Do not set price as 0!', 'wpestate').'</div>    
           <input  type="text" id="price_submission" name="price_submission"  value="'.$price_submission.'"/> 
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Price to make the listing featured (for "per listing" mode)', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Use .00 format for decimals (ex: 1.50). Do not set price as 0!', 'wpestate').'</div>    
           <input  type="text" id="price_featured_submission" name="price_featured_submission"  value="'.$price_featured_submission.'"/>
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Currency For Paid Submission', 'wpestate').'</div>
        <div class="option_row_explain">'.__('The currency in which payments are processed.', 'wpestate').'</div>    
            <select id="submission_curency" name="submission_curency">
                    '.$submission_curency_symbol.'
                </select>  
        </div>';
  
    
 

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Enable Direct Payment / Wire Payment?', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Enable or disable the wire payment option.', 'wpestate').'</div>    
            <select id="enable_direct_pay" name="enable_direct_pay">
                    '.$enable_wire_symbol.'
                </select>
        </div>';

        
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Wire instructions for direct payment', 'wpestate').'</div>
        <div class="option_row_explain">'.__('If wire payment is enabled, type the instructions below.', 'wpestate').'</div>    
            <textarea id="direct_payment_details" rows="5" style="width:700px;" name="direct_payment_details"   class="regular-text" >'.$direct_payment_details.'</textarea> 
        </div>';


    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Custom Currency Symbol - *select it from the list above after you add it.', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Add your own currency for Wire payments. ', 'wpestate').'</div>    
              <input  type="text" id="submission_curency_custom" name="submission_curency_custom" style="margin-right:20px;"    value="'.$submission_curency_custom.'"/>
        </div>';


    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Free Membership - no of listings (for "membership" mode)', 'wpestate').'</div>
        <div class="option_row_explain">'.__('If you change this value, the new value applies for new registered users. Old value applies for older registered accounts.', 'wpestate').'</div>    
                <input  type="text" id="free_mem_list" name="free_mem_list" style="margin-right:20px;"  value="'.$free_mem_list.'"/> 
                <input type="hidden" name="free_mem_list_unl" value="">
                <input type="checkbox"  id="free_mem_list_unl" name="free_mem_list_unl" value="1" '.$free_mem_list_unl.' />
                <label for="free_mem_list_unl">'.esc_html__( 'Unlimited listings ?','wpestate').'</label>
        </div>';

    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Free Membership - no of featured listings (for "membership" mode)', 'wpestate').'</div>
        <div class="option_row_explain">'.__('If you change this value, the new value applies for new registered users. Old value applies for older registered accounts.', 'wpestate').'</div>    
             <input  type="text" id="free_feat_list" name="free_feat_list" style="margin-right:20px;"    value="'.$free_feat_list.'"/>
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Free Membership Listings - no of days until a free listing will expire. *Starts from the moment the property is published on the website. (for "membership" mode) ', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Option applies for each free published listing.', 'wpestate').'</div>    
        <input  type="text" id="free_feat_list_expiration" name="free_feat_list_expiration" style="margin-right:20px;"    value="' . $free_feat_list_expiration . '"/> 
        </div>';
     
     
    $merch_array=array('no','yes');
    $include_expeses_symbol='';
    $include_expeses_status= esc_html ( get_option('wp_estate_include_expenses','') );

    foreach($merch_array as $value){
            $include_expeses_symbol.='<option value="'.$value.'"';
            if ($include_expeses_status==$value){
                    $include_expeses_symbol.=' selected="selected" ';
            }
            $include_expeses_symbol.='>'.$value.'</option>';
    }
    
    
    
     print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Include expenses when calculating deposit?', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Include expenses when calculating deposit. The expenses are city fee and cleaning fee.', 'wpestate').'</div>    
            <select id="include_expenses" name="include_expenses">
                    '.$include_expeses_symbol.'
                </select>
        </div>';
    
    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Deposit Fee - % booking fee', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Expenses are included or not in the deposit amount according to the above option. If the value is set to 100 (100%) the "Include expenses when calculating deposit?" option will be auto set to "yes"! ', 'wpestate').'</div>    
            <input  type="text" id="book_down" name="book_down" style="margin-right:20px;"    value="'.$book_down.'"/>  
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Deposit Fee - fixed value booking fee', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Add the fixed fee as a number. If you use this option, leave blank Deposit Fee - % booking fee', 'wpestate').'</div>    
          <input  type="text" id="book_down_fixed_fee" name="book_down_fixed_fee" style="margin-right:20px;"    value="'.$book_down_fixed_fee.'"/>
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Service Fee - % booking fee', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Service Fee.', 'wpestate').'</div>    
            <input  type="text" id="service_fee" name="service_fee" style="margin-right:20px;"    value="'.$service_fee.'"/>  
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Service Fee - fixed value service fee', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Service Fee - fixed value service fee. If you use this option, leave blank Service Fee - % booking fee', 'wpestate').'</div>    
          <input  type="text" id="service_fee_fixed_fee" name="service_fee_fixed_fee" style="margin-right:20px;"    value="'.$service_fee_fixed_fee.'"/>
        </div>';
    
   
    
    
    

    print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>'; 
}
endif; // end   wpestate_theme_admin_membershipsettings  



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Map Settings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('wpestate_theme_admin_mapsettings') ):
function wpestate_theme_admin_mapsettings(){
    $general_longitude              =   esc_html( get_option('wp_estate_general_longitude') );
    $general_latitude               =   esc_html( get_option('wp_estate_general_latitude') );
    $api_key                        =   esc_html( get_option('wp_estate_api_key') );
    $cache_array                    =   array('yes','no');
    $default_map_zoom               =   intval   ( get_option('wp_estate_default_map_zoom','') );
    $zoom_cluster                   =   esc_html ( get_option('wp_estate_zoom_cluster ','') );
    $hq_longitude                   =   esc_html ( get_option('wp_estate_hq_longitude') );
    $hq_latitude                    =   esc_html ( get_option('wp_estate_hq_latitude') );
    $min_height                     =   intval   ( get_option('wp_estate_min_height','') );
    $max_height                     =   intval   ( get_option('wp_estate_max_height','') );

    
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////    
    $readsys_symbol='';
    $readsys_array_status= esc_html ( get_option('wp_estate_readsys','') );

    foreach($cache_array as $value){
            $readsys_symbol.='<option value="'.$value.'"';
            if ($readsys_array_status==$value){
                    $readsys_symbol.=' selected="selected" ';
            }
            $readsys_symbol.='>'.$value.'</option>';
    }

    $ssl_map_symbol='';
    $ssl_map_status= esc_html ( get_option('wp_estate_ssl_map','') );

    foreach($cache_array as $value){
        $ssl_map_symbol.='<option value="'.$value.'"';
        if ($ssl_map_status==$value){
            $ssl_map_symbol.=' selected="selected" ';
        }
        $ssl_map_symbol.='>'.$value.'</option>';
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////    
    $cache_symbol='';
    $cache_array_status= esc_html ( get_option('wp_estate_cache','') );

    foreach($cache_array as $value){
            $cache_symbol.='<option value="'.$value.'"';
            if ($cache_array_status==$value){
                    $cache_symbol.=' selected="selected" ';
            }
            $cache_symbol.='>'.$value.'</option>';
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $show_filter_map_symbol='';
    $show_filter_map_status= esc_html ( get_option('wp_estate_show_filter_map','') );

    foreach($cache_array as $value){
            $show_filter_map_symbol.='<option value="'.$value.'"';
            if ($show_filter_map_status==$value){
                    $show_filter_map_symbol.=' selected="selected" ';
            }
            $show_filter_map_symbol.='>'.$value.'</option>';
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $home_small_map_symbol='';
    $home_small_map_status= esc_html ( get_option('wp_estate_home_small_map','') );

    foreach($cache_array as $value){
            $home_small_map_symbol.='<option value="'.$value.'"';
            if ($home_small_map_status==$value){
                    $home_small_map_symbol.=' selected="selected" ';
            }
            $home_small_map_symbol.='>'.$value.'</option>';
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $pin_cluster_symbol='';
    $pin_cluster_status= esc_html ( get_option('wp_estate_pin_cluster','') );

    foreach($cache_array as $value){
            $pin_cluster_symbol.='<option value="'.$value.'"';
            if ($pin_cluster_status==$value){
                    $pin_cluster_symbol.=' selected="selected" ';
            }
            $pin_cluster_symbol.='>'.$value.'</option>';
    }
    
    $geolocation_radius         =   esc_html ( get_option('wp_estate_geolocation_radius','') );
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
   /* $geolocation_symbol='';
    $geolocation_status= esc_html ( get_option('wp_estate_geolocation','') );

    foreach($cache_array as $value){
            $geolocation_symbol.='<option value="'.$value.'"';
            if ($geolocation_status==$value){
                    $geolocation_symbol.=' selected="selected" ';
            }
            $geolocation_symbol.='>'.$value.'</option>';
    }
*/
  
     ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $cache_array2=array('no','yes');
    $keep_min_symbol='';
    $keep_min_status= esc_html ( get_option('wp_estate_keep_min','') );
    
    foreach($cache_array2 as $value){
            $keep_min_symbol.='<option value="'.$value.'"';
            if ($keep_min_status==$value){
                    $keep_min_symbol.=' selected="selected" ';
            }
            $keep_min_symbol.='>'.$value.'</option>';
    }
    
    $show_adv_search_symbol_map_close='';
    $show_adv_search_map_close= esc_html ( get_option('wp_estate_show_adv_search_map_close','') );
    
    foreach($cache_array as $value){
            $show_adv_search_symbol_map_close.='<option value="'.$value.'"';
            if ($show_adv_search_map_close==$value){
                    $show_adv_search_symbol_map_close.=' selected="selected" ';
            }
            $show_adv_search_symbol_map_close.='>'.$value.'</option>';
    }
    
     ///////////////////////////////////////////////////////////////////////////////////////////////////////
 
    $on_demand_map_syumbol='';
    $on_demand_map_status= esc_html ( get_option('wp_estate_ondemandmap','') );
    
    
      foreach($cache_array as $value){
            $on_demand_map_syumbol.='<option value="'.$value.'"';
            if ($on_demand_map_status==$value){
                    $on_demand_map_syumbol.=' selected="selected" ';
            }
            $on_demand_map_syumbol.='>'.$value.'</option>';
    }
    
    $map_style  =   esc_html ( get_option('wp_estate_map_style','') );
    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Use file reading for pins? ','wpestate').'</div>
        <div class="option_row_explain">'.__('Use file reading for pins? (*recommended for over 200 listings. Read the manual for diffrences between file and mysql reading)','wpestate').'</div>    
            <select id="readsys" name="readsys">
                    '.$readsys_symbol.'
		 </select>
        </div>';
    
    $map_max_pins                 =   intval( get_option('wp_estate_map_max_pins') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Maximum number of pins to show on the map. ','wpestate').'</div>
    <div class="option_row_explain">'.__('A high number will increase the response time and server load. Use a number that works for your current hosting situation. Put -1 for all pins.','wpestate').'</div>    
        <input  type="text" id="map_max_pins" name="map_max_pins" class="regular-text" value="'.$map_max_pins.'"/>
  
    </div>';
        
     $ssl_map_symbol                 =   wpestate_dropdowns_theme_admin($cache_array,'ssl_map');
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Use Google maps with SSL ?','wpestate').'</div>
        <div class="option_row_explain">'.__('Set to Yes if you use SSL.','wpestate').'</div>    
            <select id="ssl_map" name="ssl_map">
                '.$ssl_map_symbol.'
                </select>
        </div>';     
        
    $api_key                        =   esc_html( get_option('wp_estate_api_key') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Google Maps API KEY','wpestate').'</div>
        <div class="option_row_explain">'.__('The Google Maps JavaScript API v3 REQUIRES an API key to function correctly. Get an APIs Console key and post the code in Theme Options. You can get it from <a href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key" target="_blank">here</a>','wpestate').'</div>    
            <input  type="text" id="api_key" name="api_key" class="regular-text" value="'.$api_key.'"/>
        </div>';
    
    $general_latitude               =   esc_html( get_option('wp_estate_general_latitude') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Starting Point Latitude','wpestate').'</div>
        <div class="option_row_explain">'.__('Applies for global header media with google maps. Add only numbers (ex: 40.577906).','wpestate').'</div>    
        <input  type="text" id="general_latitude"  name="general_latitude"   value="'.$general_latitude.'"/>
        </div>'; 
    
    $general_longitude              =   esc_html( get_option('wp_estate_general_longitude') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Starting Point Longitude','wpestate').'</div>
        <div class="option_row_explain">'.__('Applies for global header media with google maps. Add only numbers (ex: -74.155058).','wpestate').'</div>    
        <input  type="text" id="general_longitude" name="general_longitude"  value="'.$general_longitude.'"/>
        </div>'; 
       
    $default_map_zoom               =   intval   ( get_option('wp_estate_default_map_zoom','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Default Map zoom (1 to 20)','wpestate').'</div>
        <div class="option_row_explain">'.__('Applies for global header media with google maps, except advanced search results, properties list and taxonomies pages.','wpestate').'</div>    
        <input type="text" id="default_map_zoom" name="default_map_zoom" value="'.$default_map_zoom.'">   
        </div>'; 
    
    $pin_cluster_symbol = wpestate_dropdowns_theme_admin($cache_array, 'pin_cluster');
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Use on demand pins when moving the map, in Properties list half map and Advanced search results half map pages', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('See this ', 'wpestate') .'<a href="http://help.wprentals.org/2016/07/28/use-on-demand-pins-when-moving-the-map-in-properties-list-half-map-and-advanced-search-results-half-map-pages/" target="_blank">'.esc_html__('help article before','wpestate').'</a>.'. 
            '</div>    
            <select id="ondemandmap" name="ondemandmap">
                    '.$on_demand_map_syumbol.'
		 </select>
        </div>';
    
    
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Use Pin Cluster on map', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('If yes, it groups nearby pins in cluster.', 'wpestate') . '</div>    
            <select id="pin_cluster" name="pin_cluster">
                ' . $pin_cluster_symbol . '
            </select>
        </div>';

    $zoom_cluster = esc_html(get_option('wp_estate_zoom_cluster ', ''));
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Maximum zoom level for Cloud Cluster to appear', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Pin cluster disappears when map zoom is less than the value set in here. ', 'wpestate') . '</div>    
            <input id="zoom_cluster" type="text" size="36" name="zoom_cluster" value="' . $zoom_cluster . '" />
        </div>';

    $hq_latitude = esc_html(get_option('wp_estate_hq_latitude'));
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Contact Page - Company HQ Latitude', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Set company pin location for contact page template. Latitude must be a number (ex: 40.577906).', 'wpestate') . '</div>    
            <input  type="text" id="hq_latitude"  name="hq_latitude"   value="' . $hq_latitude . '"/>
        </div>';

    $hq_longitude = esc_html(get_option('wp_estate_hq_longitude'));
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Contact Page - Company HQ Longitude', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Set company pin location for contact page template. Longitude must be a number (ex: -74.155058).', 'wpestate') . '</div>    
            <input  type="text" id="hq_longitude" name="hq_longitude"  value="' . $hq_longitude . '"/>
        </div>';
    
      $geolocation_radius         =   esc_html ( get_option('wp_estate_geolocation_radius','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Geolocation Circle over map (in meters)','wpestate').'</div>
        <div class="option_row_explain">'.__('Controls circle radius value for user geolocation pin. Type only numbers (ex: 400).','wpestate').'</div>    
           <input id="geolocation_radius" type="text" size="36" name="geolocation_radius" value="'.$geolocation_radius.'" />
        </div>'; 
       
    $min_height                     =   intval   ( get_option('wp_estate_min_height','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Height of the Google Map when closed','wpestate').'</div>
        <div class="option_row_explain">'.__('Applies for header google maps when set as global header media type.','wpestate').'</div>    
           <input id="min_height" type="text" size="36" name="min_height" value="'.$min_height.'" />
        </div>';  
      
    $max_height                     =   intval   ( get_option('wp_estate_max_height','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Height of Google Map when open','wpestate').'</div>
        <div class="option_row_explain">'.__('Applies for header google maps when set as global header media type.','wpestate').'</div>    
           <input id="max_height" type="text" size="36" name="max_height" value="'.$max_height.'" />
        </div>'; 
      
    $keep_min_symbol                    =   wpestate_dropdowns_theme_admin($cache_array2,'keep_min');
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Force Google Map at the "closed" size ? ','wpestate').'</div>
        <div class="option_row_explain">'.__('Applies for header google maps when set as global header media type, except property page.','wpestate').'</div>    
            <select id="keep_min" name="keep_min">
                '.$keep_min_symbol.'
            </select>
        </div>';
    $map_style = esc_html(get_option('wp_estate_map_style', ''));
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Style for Google Map. Use https://snazzymaps.com/ to create styles', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Copy/paste below the custom map style code.', 'wpestate') . '</div>    
            <textarea id="map_style" style="width:100%;height:350px;" name="map_style">' . stripslashes($map_style) . '</textarea>
        </div>';

    print ' <div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="' . __('Save Changes', 'wpestate') . '" />
        </div>';


}
endif; // end   wpestate_theme_admin_mapsettings  



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  General Settings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if( !function_exists('wpestate_theme_admin_general_settings') ):
function wpestate_theme_admin_general_settings(){
    $cache_array                    =   array('yes','no');
    $social_array                   =   array('no','yes');
    $logo_image                     =   esc_html( get_option('wp_estate_logo_image','') );
    $transparent_logo_image         =   esc_html( get_option('wp_estate_transparent_logo_image','') );
    $mobile_logo_image              =   esc_html( get_option('wp_estate_mobile_logo_image','') );
    
    $logo_image_retina              =   esc_html( get_option('wp_estate_logo_image_retina','') );
    $mobile_logo_image_retina       =   esc_html( get_option('wp_estate_mobile_logo_image_retina','') );
    $transparent_logo_image_retina  =   esc_html( get_option('wp_estate_mobile_logo_image_retina','') );
    
    $footer_logo_image              =   esc_html( get_option('wp_estate_footer_logo_image','') );
    $favicon_image                  =   esc_html( get_option('wp_estate_favicon_image','') );
    $google_analytics_code          =   esc_html ( get_option('wp_estate_google_analytics_code','') );
  
    $general_country                =   esc_html( get_option('wp_estate_general_country') );

    $currency_symbol                =   esc_html( get_option('wp_estate_currency_symbol') );
    $front_end_register             =   esc_html( get_option('wp_estate_front_end_register','') );
    $front_end_login                =   esc_html( get_option('wp_estate_front_end_login','') );  
   

    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $measure_sys='';
    $measure_array=array( esc_html__( 'feet','wpestate')     =>esc_html__( 'ft','wpestate'),
                          esc_html__( 'meters','wpestate')   =>esc_html__( 'm','wpestate') 
                        );
    
    $measure_array_status= esc_html( get_option('wp_estate_measure_sys','') );

    foreach($measure_array as $key => $value){
            $measure_sys.='<option value="'.$value.'"';
            if ($measure_array_status==$value){
                $measure_sys.=' selected="selected" ';
            }
            $measure_sys.='>'.esc_html__( 'square','wpestate').' '.$key.' - '.$value.'<sup>2</sup></option>';
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $enable_top_bar_symbol='';
    $top_bar_status= esc_html ( get_option('wp_estate_enable_top_bar','') );

    foreach($cache_array as $value){
            $enable_top_bar_symbol.='<option value="'.$value.'"';
            if ($top_bar_status==$value){
                    $enable_top_bar_symbol.=' selected="selected" ';
            }
            $enable_top_bar_symbol.='>'.$value.'</option>';
    }

   
 ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $date_lang_symbol='';
    $date_lang_status= esc_html ( get_option('wp_estate_date_lang','') );
    $date_languages=array(  'xx'=> 'default',
                            'af'=>'Afrikaans',
                            'ar'=>'Arabic',
                            'ar-DZ' =>'Algerian',
                            'az'=>'Azerbaijani',
                            'be'=>'Belarusian',
                            'bg'=>'Bulgarian',
                            'bs'=>'Bosnian',
                            'ca'=>'Catalan',
                            'cs'=>'Czech',
                            'cy-GB'=>'Welsh/UK',
                            'da'=>'Danish',
                            'de'=>'German',
                            'el'=>'Greek',
                            'en-AU'=>'English/Australia',
                            'en-GB'=>'English/UK',
                            'en-NZ'=>'English/New Zealand',
                            'eo'=>'Esperanto',
                            'es'=>'Spanish',
                            'et'=>'Estonian',
                            'eu'=>'Karrikas-ek',
                            'fa'=>'Persian',
                            'fi'=>'Finnish',
                            'fo'=>'Faroese',
                            'fr'=>'French',
                            'fr-CA'=>'Canadian-French',
                            'fr-CH'=>'Swiss-French',
                            'gl'=>'Galician',
                            'he'=>'Hebrew',
                            'hi'=>'Hindi',
                            'hr'=>'Croatian',
                            'hu'=>'Hungarian',
                            'hy'=>'Armenian',
                            'id'=>'Indonesian',
                            'ic'=>'Icelandic',
                            'it'=>'Italian',
                            'it-CH'=>'Italian-CH',
                            'ja'=>'Japanese',
                            'ka'=>'Georgian',
                            'kk'=>'Kazakh',
                            'km'=>'Khmer',
                            'ko'=>'Korean',
                            'ky'=>'Kyrgyz',
                            'lb'=>'Luxembourgish',
                            'lt'=>'Lithuanian',
                            'lv'=>'Latvian',
                            'mk'=>'Macedonian',
                            'ml'=>'Malayalam',
                            'ms'=>'Malaysian',
                            'nb'=>'Norwegian',
                            'nl'=>'Dutch',
                            'nl-BE'=>'Dutch-Belgium',
                            'nn'=>'Norwegian-Nynorsk',
                            'no'=>'Norwegian',
                            'pl'=>'Polish',
                            'pt'=>'Portuguese',
                            'pt-BR'=>'Brazilian',
                            'rm'=>'Romansh',
                            'ro'=>'Romanian',
                            'ru'=>'Russian',
                            'sk'=>'Slovak',
                            'sl'=>'Slovenian',
                            'sq'=>'Albanian',
                            'sr'=>'Serbian',
                            'sr-SR'=>'Serbian-i18n',
                            'sv'=>'Swedish',
                            'ta'=>'Tamil',
                            'th'=>'Thai',
                            'tj'=>'Tajiki',
                            'tr'=>'Turkish',
                            'uk'=>'Ukrainian',
                            'vi'=>'Vietnamese',
                            'zh-CN'=>'Chinese',
                            'zh-HK'=>'Chinese-Hong-Kong',
                            'zh-TW'=>'Chinese Taiwan',
        );  

    foreach($date_languages as $key=>$value){
            $date_lang_symbol.='<option value="'.$key.'"';
            if ($date_lang_status==$key){
                    $date_lang_symbol.=' selected="selected" ';
            }
            $date_lang_symbol.='>'.$value.'</option>';
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $where_currency_symbol          =   '';
    $where_currency_symbol_array    =   array('before','after');
    $where_currency_symbol_status   =   esc_html( get_option('wp_estate_where_currency_symbol') );
    foreach($where_currency_symbol_array as $value){
            $where_currency_symbol.='<option value="'.$value.'"';
            if ($where_currency_symbol_status==$value){
                $where_currency_symbol.=' selected="selected" ';
            }
            $where_currency_symbol.='>'.$value.'</option>';
    }

    
     ///////////////////////////////////////////////////////////////////////////////////////////////////////    
    $orphan_symbol='';
    $orphan_array_status= esc_html ( get_option('wp_estate_delete_orphan','') );

    foreach($social_array as $value){
            $orphan_symbol.='<option value="'.$value.'"';
            if ($orphan_array_status==$value){
                    $orphan_symbol.=' selected="selected" ';
            }
            $orphan_symbol.='>'.$value.'</option>';
    }
    
    
    $separate_users_symbol='';
    $separate_users_status= esc_html ( get_option('wp_estate_separate_users','') );

    foreach($social_array as $value){
            $separate_users_symbol.='<option value="'.$value.'"';
            if ($separate_users_status==$value){
                    $separate_users_symbol.=' selected="selected" ';
            }
            $separate_users_symbol.='>'.$value.'</option>';
    }       
            
    $publish_only               =   esc_html( get_option('wp_estate_publish_only') );
    
    
    
    
    $show_submit_symbol='';
    $show_submit_status= esc_html ( get_option('wp_estate_show_submit','') );

    foreach($social_array as $value){
            $show_submit_symbol.='<option value="'.$value.'"';
            if ($show_submit_status==$value){
                    $show_submit_symbol.=' selected="selected" ';
            }
            $show_submit_symbol.='>'.$value.'</option>';
    }       
       
      
    $setup_weekend_symbol='';
    $setup_weekend_status= esc_html ( get_option('wp_estate_setup_weekend','') );
    $weekedn = array( 
            0 => __("Sunday and Saturday","wpestate"),
            1 => __("Friday and Saturday","wpestate"),
            2 => __("Friday, Saturday and Sunday","wpestate")
            );
    
    foreach($weekedn as $key=>$value){
            $setup_weekend_symbol.='<option value="'.$key.'"';
            if ($setup_weekend_status==$key){
                    $setup_weekend_symbol.=' selected="selected" ';
            }
            $setup_weekend_symbol.='>'.$value.'</option>';
    }       
       
    print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Country','wpestate').'</div>
            <div class="option_row_explain">'.__('Select default country','wpestate').'</div>    
                '.wpestate_general_country_list($general_country).'
        </div>';
    
      print'<div class="estate_option_row">
          <div class="label_option_row">' . esc_html__('Select Weekend days', 'wpestate') . '</div>
          <div class="option_row_explain">' . __('Users can set a difference price per day for weekend days', 'wpestate') . '</div>    
              <select id="setup_weekend" name="setup_weekend">
                  ' . $setup_weekend_symbol . '
              </select>            
      </div>';
      
    print'<div class="estate_option_row">
            <div class="label_option_row">' . __('Measurement Unit', 'wpestate') . '</div>
            <div class="option_row_explain">' . __('Select the measurement unit you will use on the website', 'wpestate') . '</div>    
                <select id="measure_sys" name="measure_sys">
                    ' . $measure_sys . '
                </select>
        </div>';
     $enable_user_pass_symbol    = wpestate_dropdowns_theme_admin($cache_array,'enable_user_pass');
      
    print'<div class="estate_option_row">
            <div class="label_option_row">' . esc_html__('Users can type the password on registration form', 'wpestate') . '</div>
            <div class="option_row_explain">' . __('If no, users will get the auto generated password via email', 'wpestate') . '</div>    
                <select id="enable_user_pass" name="enable_user_pass">
                    ' . $enable_user_pass_symbol . '
		 </select>
        </div>';
    
    print'<div class="estate_option_row">
       <div class="label_option_row">'.esc_html__( 'Separate users on registration','wpestate').'</div>
       <div class="option_row_explain">'.__('There will be 2 user types: who can only book and who can rent & book.','wpestate').'</div>    
           <select id="separate_users" name="separate_users">
               '.$separate_users_symbol.'
           </select>

        </div>';

    print'<div class="estate_option_row">
       <div class="label_option_row">'.esc_html__( 'Only these users can publish (separate SUBCRIBERS usernames with ,).','wpestate').'</div>
       <div class="option_row_explain">'.__('It must be used with the option "Separate users on registration" set on NO.','wpestate').'</div>    
       <textarea  name="publish_only" style="width:350px;" id="publish_only" >'.$publish_only.'</textarea>
        </div>';
    
    print'<div class="estate_option_row">
            <div class="label_option_row">'.esc_html__( 'Auto delete orphan listings','wpestate').'</div>
            <div class="option_row_explain">'.__('Listings that users started to submit but did not complete - cron will run 1 time per day','wpestate').'</div>    
                <select id="delete_orphan" name="delete_orphan">
                    '.$orphan_symbol.'
                </select>
        </div>';  
      
    print'<div class="estate_option_row">
            <div class="label_option_row">'.esc_html__( 'Language for datepicker','wpestate').'</div>
            <div class="option_row_explain">'.__('Select the language for booking form datepicker and search by date datepicker','wpestate').'</div>    
                <select id="date_lang" name="date_lang">
                    '.$date_lang_symbol.'
                </select>
            </div>';
    
     print'<div class="estate_option_row">
            <div class="label_option_row">' . __('Google Analytics Tracking id (ex UA-41924406-1', 'wpestate') . '</div>
            <div class="option_row_explain">' . __('Google Analytics Tracking id (ex UA-41924406-1)', 'wpestate') . '</div>    
                <input id="company_name" type="text" size="36"  id="google_analytics_code" name="google_analytics_code" value="'.$google_analytics_code.'" />
        </div>';


        $enable_user_pass_symbol='';
        $enable_user_pass_status= esc_html ( get_option('wp_estate_enable_user_pass','') );

        foreach($social_array as $value){
                $enable_user_pass_symbol.='<option value="'.$value.'"';
                if ($enable_user_pass_status==$value){
                        $enable_user_pass_symbol.=' selected="selected" ';
                }
                $enable_user_pass_symbol.='>'.$value.'</option>';
        }       

        $use_captcha_symbol='';
        $use_captcha_status= esc_html ( get_option('wp_estate_use_captcha','') );

        foreach($social_array as $value){
                $use_captcha_symbol.='<option value="'.$value.'"';
                if ($use_captcha_status==$value){
                        $use_captcha_symbol.=' selected="selected" ';
                }
                $use_captcha_symbol.='>'.$value.'</option>';
        }   
       print ' <div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
        </div>';

}
endif; // end   wpestate_theme_admin_general_settings  


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Contact Details
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if( !function_exists('wpestate_theme_admin_social') ):
function wpestate_theme_admin_social(){
    $fax_ac                     =   esc_html ( get_option('wp_estate_fax_ac','') );
    $skype_ac                   =   esc_html ( get_option('wp_estate_skype_ac','') );
    $telephone_no               =   esc_html ( get_option('wp_estate_telephone_no','') );
    $mobile_no                  =   esc_html ( get_option('wp_estate_mobile_no','') );
    $company_name               =   esc_html ( stripslashes( get_option('wp_estate_company_name','') ) );
    $email_adr                  =   esc_html ( get_option('wp_estate_email_adr','') );
  
    
    $co_address                 =   esc_html ( get_option('wp_estate_co_address','') );
    $facebook_link              =   esc_html ( get_option('wp_estate_facebook_link','') );
    $twitter_link               =   esc_html ( get_option('wp_estate_twitter_link','') );
    $google_link                =   esc_html ( get_option('wp_estate_google_link','') );
    $linkedin_link              =   esc_html ( get_option('wp_estate_linkedin_link','') );
    $pinterest_link             =   esc_html ( get_option('wp_estate_pinterest_link','') );
    
    $twitter_consumer_key       =   esc_html ( get_option('wp_estate_twitter_consumer_key','') );
    $twitter_consumer_secret    =   esc_html ( get_option('wp_estate_twitter_consumer_secret','') );
    $twitter_access_token       =   esc_html ( get_option('wp_estate_twitter_access_token','') );
    $twitter_access_secret      =   esc_html ( get_option('wp_estate_twitter_access_secret','') );
    $twitter_cache_time         =   intval   ( get_option('wp_estate_twitter_cache_time','') );
   
    $facebook_api               =   esc_html ( get_option('wp_estate_facebook_api','') );
    $facebook_secret            =   esc_html ( get_option('wp_estate_facebook_secret','') );
   
    
    $google_oauth_api           =   esc_html ( get_option('wp_estate_google_oauth_api','') );
    $google_oauth_client_secret =   esc_html ( get_option('wp_estate_google_oauth_client_secret','') );
    $google_api_key             =   esc_html ( get_option('wp_estate_google_api_key','') );
    
    
    $social_array               =   array('no','yes');
    $facebook_login_select='';
    $facebook_status  =   esc_html( get_option('wp_estate_facebook_login','') );

    foreach($social_array as $value){
            $facebook_login_select.='<option value="'.$value.'"';
            if ($facebook_status==$value){
                $facebook_login_select.=' selected="selected" ';
            }
            $facebook_login_select.='>'.$value.'</option>';
    }


    $google_login_select='';
    $google_status  =   esc_html( get_option('wp_estate_google_login','') );

    foreach($social_array as $value){
            $google_login_select.='<option value="'.$value.'"';
            if ($google_status==$value){
                $google_login_select.=' selected="selected" ';
            }
            $google_login_select.='>'.$value.'</option>';
    }


    $yahoo_login_select='';
    $yahoo_status  =   esc_html( get_option('wp_estate_yahoo_login','') );

    foreach($social_array as $value){
            $yahoo_login_select.='<option value="'.$value.'"';
            if ($yahoo_status==$value){
                $yahoo_login_select.=' selected="selected" ';
            }
            $yahoo_login_select.='>'.$value.'</option>';
    }

    
    $social_register_select='';
    $social_register_on  =   esc_html( get_option('wp_estate_social_register_on','') );

    foreach($social_array as $value){
            $social_register_select.='<option value="'.$value.'"';
            if ($social_register_on==$value){
                $social_register_select.=' selected="selected" ';
            }
            $social_register_select.='>'.$value.'</option>';
    }
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Company Name','wpestate').'</div>
        <div class="option_row_explain">'.__('Company name for contact page','wpestate').'</div>    
            <input id="company_name" type="text" size="36" name="company_name" value="'.$company_name.'" />
        </div>';
    
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Company Address', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Type company address', 'wpestate') . '</div>    
            <textarea cols="57" rows="2" name="co_address" id="co_address">' . $co_address . '</textarea>
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Email','wpestate').'</div>
        <div class="option_row_explain">'.__('Company email','wpestate').'</div>    
          <input id="email_adr" type="text" size="36" name="email_adr" value="'.$email_adr.'" />
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Telephone', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Company phone number.', 'wpestate') . '</div>    
        <input id="telephone_no" type="text" size="36" name="telephone_no" value="' . $telephone_no . '" />
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Mobile', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Company mobile', 'wpestate') . '</div>    
        <input id="mobile_no" type="text" size="36" name="mobile_no" value="' . $mobile_no . '" />
        </div>';

        
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Fax', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Company fax', 'wpestate') . '</div>    
        <input id="fax_ac" type="text" size="36" name="fax_ac" value="' . $fax_ac . '" />
        </div>';

        
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Skype', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Company skype', 'wpestate') . '</div>    
        <input id="skype_ac" type="text" size="36" name="skype_ac" value="' . $skype_ac . '" />
        </div>';

        

    print ' <div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="' . __('Save Changes', 'wpestate') . '" />
        </div>';


}
endif; // end   wpestate_theme_admin_social  



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Apperance
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('wpestate_theme_admin_apperance') ):
function wpestate_theme_admin_apperance(){
    $cache_array                =   array('yes','no');
    $prop_no                    =   intval   ( get_option('wp_estate_prop_no','') );
    $blog_sidebar_name          =   esc_html ( get_option('wp_estate_blog_sidebar_name','') );
    $copyright_message          =   stripslashes ( esc_html ( get_option('wp_estate_copyright_message','') ) );
    $logo_margin                =   intval( get_option('wp_estate_logo_margin','') ); 
    ///////////////////////////////////////////////////////////////////////////////////////////////////////    

    $show_empty_city_status_symbol='';
    $show_empty_city_status= esc_html ( get_option('wp_estate_show_empty_city','') );

    foreach($cache_array as $value){
            $show_empty_city_status_symbol.='<option value="'.$value.'"';
            if ($show_empty_city_status==$value){
                    $show_empty_city_status_symbol.=' selected="selected" ';
            }
            $show_empty_city_status_symbol.='>'.$value.'</option>';
    }


    $show_top_bar_user_menu_symbol='';
    $show_top_bar_user_menu_status= esc_html ( get_option('wp_estate_show_top_bar_user_menu','') );    
    
    foreach($cache_array as $value){
       $show_top_bar_user_menu_symbol.='<option value="'.$value.'"';
       if ($show_top_bar_user_menu_status==$value){
               $show_top_bar_user_menu_symbol.=' selected="selected" ';
       }
       $show_top_bar_user_menu_symbol.='>'.$value.'</option>';
    }
 
        
    $show_top_bar_user_login_symbol='';
    $show_top_bar_user_login_status= esc_html ( get_option('wp_estate_show_top_bar_user_login','') );    
    
    foreach($cache_array as $value){
       $show_top_bar_user_login_symbol.='<option value="'.$value.'"';
       if ($show_top_bar_user_login_status==$value){
               $show_top_bar_user_login_symbol.=' selected="selected" ';
       }
       $show_top_bar_user_login_symbol.='>'.$value.'</option>';
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    $prop_list_slider_symbol='';
    $prop_list_slider_status= esc_html ( get_option('wp_estate_prop_list_slider','') );    
    
    foreach($cache_array as $value){
       $prop_list_slider_symbol.='<option value="'.$value.'"';
       if ($prop_list_slider_status==$value){
               $prop_list_slider_symbol.=' selected="selected" ';
       }
       $prop_list_slider_symbol.='>'.$value.'</option>';
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $blog_sidebar_name_select='';
    foreach ( $GLOBALS['wp_registered_sidebars'] as $sidebar ) { 
        $blog_sidebar_name_select.='<option value="'.($sidebar['id'] ).'"';
            if($blog_sidebar_name==$sidebar['id']){ 
               $blog_sidebar_name_select.=' selected="selected"';
            }
        $blog_sidebar_name_select.=' >'.ucwords($sidebar['name']).'</option>';
    } 
    
  
            
    

    ///////////////////////////////////////////////////////////////////////////////////////////////////////    
    $blog_sidebar_select ='';
    $blog_sidebar= esc_html ( get_option('wp_estate_blog_sidebar','') );
    $blog_sidebar_array=array('no sidebar','right','left');

    foreach($blog_sidebar_array as $value){
            $blog_sidebar_select.='<option value="'.$value.'"';
            if ($blog_sidebar==$value){
                    $blog_sidebar_select.='selected="selected"';
            }
            $blog_sidebar_select.='>'.$value.'</option>';
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $general_font_select='';
    $general_font= esc_html ( get_option('wp_estate_general_font','') );
    if($general_font!='x'){
    $general_font_select='<option value="'.$general_font.'">'.$general_font.'</option>';
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////
  


//    $wide_array=array(
//               "1"  =>  esc_html__( "wide","wpestate"),
//               "2"  =>  esc_html__( "boxed","wpestate")
//            );
//    $wide_status_symbol     =   '';
//    $wide_status_status     =   esc_html(get_option('wp_estate_wide_status',''));
//    
//    
//    foreach($wide_array as $key => $value){
//        $wide_status_symbol.='<option value="'.$key.'"';
//        if ($wide_status_status == $key){
//                $wide_status_symbol.=' selected="selected" ';
//        }
//        $wide_status_symbol.='> '.$value.'</option>';
//    }
//  
    
    $prop_list_array=array(
               "1"  =>  esc_html__( "standard ","wpestate"),
               "2"  =>  esc_html__( "half map","wpestate")
            );
    $property_list_type_symbol =    '';
    $property_list_type_status =    esc_html(get_option('wp_estate_property_list_type',''));
    
    foreach($prop_list_array as $key => $value){
        $property_list_type_symbol.='<option value="'.$key.'"';
        if ($property_list_type_status == $key){
                $property_list_type_symbol.=' selected="selected" ';
        }
        $property_list_type_symbol.='> '.$value.'</option>';
    }
  
    
    $property_list_type_symbol_adv =    '';
    $property_list_type_status_adv =    esc_html(get_option('wp_estate_property_list_type_adv',''));
    
    foreach($prop_list_array as $key => $value){
        $property_list_type_symbol_adv.='<option value="'.$key.'"';
        if ($property_list_type_status_adv == $key){
                $property_list_type_symbol_adv.=' selected="selected" ';
        }
        $property_list_type_symbol_adv.='> '.$value.'</option>';
    }
  
    
    $use_upload_tax_page_symbol='';
    $use_upload_tax_page_status= esc_html ( get_option('wp_estate_use_upload_tax_page','') );

    foreach($cache_array as $value){
            $use_upload_tax_page_symbol.='<option value="'.$value.'"';
            if ($use_upload_tax_page_status==$value){
                    $use_upload_tax_page_symbol.=' selected="selected" ';
            }
            $use_upload_tax_page_symbol.='>'.$value.'</option>';
    }


    $headings_font_subset   =   esc_html ( get_option('wp_estate_headings_font_subset','') );
    $header_array   =   array(
                            'none',
                            'image',
                            'theme slider',
                            'revolution slider',
                            'google map'
                            );
    
    $header_type    =   get_option('wp_estate_header_type','');
    $header_select  =   '';
    
    foreach($header_array as $key=>$value){
       $header_select.='<option value="'.$key.'" ';
       if($key==$header_type){
           $header_select.=' selected="selected" ';
       }
       $header_select.='>'.$value.'</option>'; 
    }
    
    
    $user_header_type    =   get_option('wp_estate_user_header_type','');
    $user_header_select  =   '';
    
    foreach($header_array as $key=>$value){
        $user_header_select.='<option value="'.$key.'" ';
        if($key==$user_header_type){
            $user_header_select.=' selected="selected" ';
        }
        $user_header_select.='>'.$value.'</option>'; 
    }
    

    ///
    $listing_array   =   array( 
                            "1" => esc_html__( 'Type 1','wpestate'),
                            "2" => esc_html__( 'Type 2','wpestate')
                            );
    
    $listing_type    =   get_option('wp_estate_listing_unit_type','');
    $listing_select  =   '';
    
    foreach( $listing_array as $key=>$value){
       $listing_select.='<option value="'.$key.'" ';
       if($key==$listing_type){
           $listing_select.=' selected="selected" ';
       }
       $listing_select.='>'.$value.'</option>'; 
    }
    
   
    
    //
    
    $listing_array   =   array( 
                            "1" => esc_html__( 'Type 1','wpestate'),
                            "2" => esc_html__( 'Type 2','wpestate')
                            );
    
    $listing_page_type    =   get_option('wp_estate_listing_page_type','');
    $listing_page_select  =   '';
    
    foreach( $listing_array as $key=>$value){
        $listing_page_select.='<option value="'.$key.'" ';
        if($key==$listing_page_type){
            $listing_page_select.=' selected="selected" ';
        }
        $listing_page_select.='>'.$value.'</option>'; 
    }
    
    //
    
    $listing_array   =   array( 
                            "1" => esc_html__( 'List','wpestate'),
                            "2" => esc_html__( 'Grid','wpestate')
                            );
    
    $listing_unit_style_half    =   get_option('wp_estate_listing_unit_style_half','');
    $listing_select_half        =   '';
    
    foreach( $listing_array as $key=>$value){
        $listing_select_half.='<option value="'.$key.'" ';
        if($key==$listing_unit_style_half){
            $listing_select_half.=' selected="selected" ';
        }
        $listing_select_half.='>'.$value.'</option>'; 
    }
    
    
    
    $transparent_menu = get_option('wp_estate_transparent_menu','');
    $transparent_menu_select='';
    
     foreach($cache_array as $value){
            $transparent_menu_select.='<option value="'.$value.'"';
            if ($transparent_menu==$value){
                    $transparent_menu_select.=' selected="selected" ';
            }
            $transparent_menu_select.='>'.$value.'</option>';
    }
    
    
    $transparent_menu = get_option('wp_estate_transparent_menu_listing','');
    $transparent_menu_select_listing='';
    
     foreach($cache_array as $value){
            $transparent_menu_select_listing.='<option value="'.$value.'"';
            if ($transparent_menu==$value){
                    $transparent_menu_select_listing.=' selected="selected" ';
            }
            $transparent_menu_select_listing.='>'.$value.'</option>';
    }
    
   
    $global_revolution_slider   =  get_option('wp_estate_global_revolution_slider','');
    $global_header  =   get_option('wp_estate_global_header','');

    $footer_background    =   get_option('wp_estate_footer_background','');
    
    $repeat_array=array('repeat','repeat x','repeat y','no repeat');
    $repeat_footer_back_status  =   get_option('wp_estate_repeat_footer_back','');
    $repeat_footer_back_symbol  =   '';
    foreach($repeat_array as $value){
            $repeat_footer_back_symbol.='<option value="'.$value.'"';
            if ($repeat_footer_back_status==$value){
                    $repeat_footer_back_symbol.=' selected="selected" ';
            }
            $repeat_footer_back_symbol.='>'.$value.'</option>';
    }
    //
    $prop_list_slider = array(
            "0" => __("no ", "wpestate"),
            "1" => __("yes", "wpestate")
        );
    $prop_unit_slider_symbol = wpestate_dropdowns_theme_admin_with_key($prop_list_slider,'prop_list_slider');
    $wide_array=array(
               "1"  =>  esc_html__( "wide","wpestate"),
               "2"  =>  esc_html__( "boxed","wpestate")
            );
    $wide_status_symbol     =   '';
    $wide_status_status     =   esc_html(get_option('wp_estate_wide_status',''));
    foreach ($wide_array as $key => $value) {
            $wide_status_symbol.='<option value="' . $key . '"';
            if ($wide_status_status == $key) {
                $wide_status_symbol.=' selected="selected" ';
            }
            $wide_status_symbol.='> ' . $value . '</option>';
        }

        
    print '<div class = "estate_option_row">
        <div class = "label_option_row">'.__('Wide or Boxed?','wpestate').'</div>
        <div class = "option_row_explain">'.__('Choose the theme layout: wide or boxed.','wpestate').'</div>
             <select id="wide_status" name="wide_status">
            '. $wide_status_symbol .'
            </select>  
        </div >';
     
   
    
    print '<div class= "estate_option_row">
        <div class="label_option_row">' . __('Properties List - Properties number per page', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Set how many properties to show per page in lists.', 'wpestate') . '</div>    
                <input type="text" id="prop_no" name="prop_no" value="'.$prop_no.'"> 
        </div>';
        
    $wp_estate_prop_image_number    =   esc_html( get_option('wp_estate_prop_image_number','') );
    print '  <div class="estate_option_row">
    <div class="label_option_row">Maximum no of images per property (only front-end upload)</div>
    <div class="option_row_explain">The maximum no of images a user can upload in front end. Use 0 for unlimited</div>    
        <input type="text" id="prop_no" name="prop_image_number" value="'.$wp_estate_prop_image_number.'"> 
    </div>';
    
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Use Slider in Property Unit? (*doesn\'t apply for featured property unit and property shortcode list with no space between units)', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Enable / Disable the image slider in property unit (used in lists)', 'wpestate') . '</div>    
            <select id="prop_list_slider" name="prop_list_slider">
                ' . $prop_list_slider_symbol . '
            </select>  
        </div>';
    
    
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Show Cities and Areas with 0 properties in dropdowns?', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Enable or disable empty city or area categories in dropdowns', 'wpestate') . '</div>    
            <select id="show_empty_city" name="show_empty_city">
                    '.$show_empty_city_status_symbol.'
		 </select> 
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Property Page/Blog Category/Archive Sidebar Position', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Where to show the sidebar for blog category/archive list.', 'wpestate') . '</div>    
            <select id="blog_sidebar" name="blog_sidebar">
                    '.$blog_sidebar_select.'
                </select>
        </div>';

    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Blog Category/Archive Sidebar', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('What sidebar to show for blog category/archive list.', 'wpestate') . '</div>    
            <select id="blog_sidebar_name" name="blog_sidebar_name">
                    '.$blog_sidebar_name_select.'
                 </select>
        </div>';
    
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Property List Type for Taxonomy pages', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Select standard or half map style for property taxonomies pages.', 'wpestate') . '</div>    
            <select id="property_list_type" name="property_list_type">
                    '.$property_list_type_symbol.'
                 </select>
        </div>';
    
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Property List Type for Advanced Search', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Select standard or half map style for advanced search results page.', 'wpestate') . '</div>    
            <select id="property_list_type_adv" name="property_list_type_adv">
                    '.$property_list_type_symbol_adv.'
                 </select>
        </div>';
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Property Unit Type', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Select Property Unit Type', 'wpestate') . '</div>    
            <select id="listing_unit_type" name="listing_unit_type">
                    ' . $listing_select . '
                 </select>
        </div>';
    print'<div class="estate_option_row">
            <div class="label_option_row">' . __('Property Page Design Type', 'wpestate') . '</div>
            <div class="option_row_explain">' . __('Select design type for Property Page .', 'wpestate') . '</div>    
                <select id="listing_page_type" name="listing_page_type">
                    ' . $listing_page_select . '
                 </select>
        </div>';
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Property Unit Style for Half Map', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Select Property Unit Style for Half Map', 'wpestate') . '</div>    
            <select id="listing_unit_style_half" name="listing_unit_style_half">
                    '.$listing_select_half.'
                 </select>
        </div>';
    
        print ' <div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="' . __('Save Changes', 'wpestate') . '" />
        </div>';
}
endif; // end   wpestate_theme_admin_apperance  




/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  help and custom
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('wpestate_theme_admin_help') ):
function wpestate_theme_admin_help(){
    print '<div class="estate_option_row">';
 
    $support_link='http://support.wpestate.org/';
    print ' '.esc_html__( 'For support please go to ','wpestate').'<a href="'.$support_link.'" target="_blank">'.$support_link.'</a>,'.esc_html__('create an account and post a ticket. The registration is simple and as soon as you post we are notified. We usually answer in the next 24h (except weekends). Please use this system and not the email. It will help us answer much faster. Thank you!','wpestate').'
           </br></br> '.esc_html__( 'For custom work on this theme please go to','wpestate').' <a href="'.$support_link.'" target="_blank">'.$support_link.'</a>,'.esc_html__(' create a ticket with your request and we will offer a free quote.','wpestate').'
           </br></br> '.esc_html__( 'For help files please go to ','wpestate').'<a href="http://help.wprentals.org/">http://help.wprentals.org</a>
           </br></br> '.esc_html__( 'Subscribe to our mailing list in order to receive news about new features and theme upgrades.','wpestate').' <a href="http://eepurl.com/CP5U5">Subscribe Here!</a>
         
        
      ';
    print '</div>';
}
endif; // end   wpestate_theme_admin_help  



if( !function_exists('wpestate_general_country_list') ):
    function wpestate_general_country_list($selected){
        //$countries = array(esc_html__('Afghanistan','wpestate'),esc_html__('Albania','wpestate'),esc_html__('Algeria','wpestate'),esc_html__('American Samoa','wpestate'),esc_html__('Andorra','wpestate'),esc_html__('Angola','wpestate'),esc_html__('Anguilla','wpestate'),esc_html__('Antarctica','wpestate'),esc_html__('Antigua and Barbuda','wpestate'),esc_html__('Argentina','wpestate'),esc_html__('Armenia','wpestate'),esc_html__('Aruba','wpestate'),esc_html__('Australia','wpestate'),esc_html__('Austria','wpestate'),esc_html__('Azerbaijan','wpestate'),esc_html__('Bahamas','wpestate'),esc_html__('Bahrain','wpestate'),esc_html__('Bangladesh','wpestate'),esc_html__('Barbados','wpestate'),esc_html__('Belarus','wpestate'),esc_html__('Belgium','wpestate'),esc_html__('Belize','wpestate'),esc_html__('Benin','wpestate'),esc_html__('Bermuda','wpestate'),esc_html__('Bhutan','wpestate'),esc_html__('Bolivia','wpestate'),esc_html__('Bosnia and Herzegowina','wpestate'),esc_html__('Botswana','wpestate'),esc_html__('Bouvet Island','wpestate'),esc_html__('Brazil','wpestate'),esc_html__('British Indian Ocean Territory','wpestate'),esc_html__('Brunei Darussalam','wpestate'),esc_html__('Bulgaria','wpestate'),esc_html__('Burkina Faso','wpestate'),esc_html__('Burundi','wpestate'),esc_html__('Cambodia','wpestate'),esc_html__('Cameroon','wpestate'),esc_html__('Canada','wpestate'),esc_html__('Cape Verde','wpestate'),esc_html__('Cayman Islands','wpestate'),esc_html__('Central African Republic','wpestate'),esc_html__('Chad','wpestate'),esc_html__('Chile','wpestate'),esc_html__('China','wpestate'),esc_html__('Christmas Island','wpestate'),esc_html__('Cocos (Keeling) Islands','wpestate'),esc_html__('Colombia','wpestate'),esc_html__('Comoros','wpestate'),esc_html__('Congo','wpestate'),esc_html__('Congo, the Democratic Republic of the','wpestate'),esc_html__('Cook Islands','wpestate'),esc_html__('Costa Rica','wpestate'),esc_html__('Cote dIvoire','wpestate'),esc_html__('Croatia (Hrvatska)','wpestate'),esc_html__('Cuba','wpestate'),esc_html__('Curacao','wpestate'),esc_html__('Cyprus','wpestate'),esc_html__('Czech Republic','wpestate'),esc_html__('Denmark','wpestate'),esc_html__('Djibouti','wpestate'),esc_html__('Dominica','wpestate'),esc_html__('Dominican Republic','wpestate'),esc_html__('East Timor','wpestate'),esc_html__('Ecuador','wpestate'),esc_html__('Egypt','wpestate'),esc_html__('El Salvador','wpestate'),esc_html__('Equatorial Guinea','wpestate'),esc_html__('Eritrea','wpestate'),esc_html__('Estonia','wpestate'),esc_html__('Ethiopia','wpestate'),esc_html__('Falkland Islands (Malvinas)','wpestate'),esc_html__('Faroe Islands','wpestate'),esc_html__('Fiji','wpestate'),esc_html__('Finland','wpestate'),esc_html__('France','wpestate'),esc_html__('France Metropolitan','wpestate'),esc_html__('French Guiana','wpestate'),esc_html__('French Polynesia','wpestate'),esc_html__('French Southern Territories','wpestate'),esc_html__('Gabon','wpestate'),esc_html__('Gambia','wpestate'),esc_html__('Georgia','wpestate'),esc_html__('Germany','wpestate'),esc_html__('Ghana','wpestate'),esc_html__('Gibraltar','wpestate'),esc_html__('Greece','wpestate'),esc_html__('Greenland','wpestate'),esc_html__('Grenada','wpestate'),esc_html__('Guadeloupe','wpestate'),esc_html__('Guam','wpestate'),esc_html__('Guatemala','wpestate'),esc_html__('Guinea','wpestate'),esc_html__('Guinea-Bissau','wpestate'),esc_html__('Guyana','wpestate'),esc_html__('Haiti','wpestate'),esc_html__('Heard and Mc Donald Islands','wpestate'),esc_html__('Holy See (Vatican City State)','wpestate'),esc_html__('Honduras','wpestate'),esc_html__('Hong Kong','wpestate'),esc_html__('Hungary','wpestate'),esc_html__('Iceland','wpestate'),esc_html__('India','wpestate'),esc_html__('Indonesia','wpestate'),esc_html__('Iran (Islamic Republic of)','wpestate'),esc_html__('Iraq','wpestate'),esc_html__('Ireland','wpestate'),esc_html__('Israel','wpestate'),esc_html__('Italy','wpestate'),esc_html__('Jamaica','wpestate'),esc_html__('Japan','wpestate'),esc_html__('Jordan','wpestate'),esc_html__('Kazakhstan','wpestate'),esc_html__('Kenya','wpestate'),esc_html__('Kiribati','wpestate'),esc_html__('Korea, Democratic People Republic of','wpestate'),esc_html__('Korea, Republic of','wpestate'),esc_html__('Kuwait','wpestate'),esc_html__('Kyrgyzstan','wpestate'),esc_html__('Lao, People Democratic Republic','wpestate'),esc_html__('Latvia','wpestate'),esc_html__('Lebanon','wpestate'),esc_html__('Lesotho','wpestate'),esc_html__('Liberia','wpestate'),esc_html__('Libyan Arab Jamahiriya','wpestate'),esc_html__('Liechtenstein','wpestate'),esc_html__('Lithuania','wpestate'),esc_html__('Luxembourg','wpestate'),esc_html__('Macau','wpestate'),esc_html__('Macedonia, The Former Yugoslav Republic of','wpestate'),esc_html__('Madagascar','wpestate'),esc_html__('Malawi','wpestate'),esc_html__('Malaysia','wpestate'),esc_html__('Maldives','wpestate'),esc_html__('Mali','wpestate'),esc_html__('Malta','wpestate'),esc_html__('Marshall Islands','wpestate'),esc_html__('Martinique','wpestate'),esc_html__('Mauritania','wpestate'),esc_html__('Mauritius','wpestate'),esc_html__('Mayotte','wpestate'),esc_html__('Mexico','wpestate'),esc_html__('Micronesia, Federated States of','wpestate'),esc_html__('Moldova, Republic of','wpestate'),esc_html__('Monaco','wpestate'),esc_html__('Mongolia','wpestate'),esc_html__('Montserrat','wpestate'),esc_html__('Morocco','wpestate'),esc_html__('Mozambique','wpestate'),esc_html__('Montenegro','wpestate'),esc_html__('Myanmar','wpestate'),esc_html__('Namibia','wpestate'),esc_html__('Nauru','wpestate'),esc_html__('Nepal','wpestate'),esc_html__('Netherlands','wpestate'),esc_html__('Netherlands Antilles','wpestate'),esc_html__('New Caledonia','wpestate'),esc_html__('New Zealand','wpestate'),esc_html__('Nicaragua','wpestate'),esc_html__('Niger','wpestate'),esc_html__('Nigeria','wpestate'),esc_html__('Niue','wpestate'),esc_html__('Norfolk Island','wpestate'),esc_html__('Northern Mariana Islands','wpestate'),esc_html__('Norway','wpestate'),esc_html__('Oman','wpestate'),esc_html__('Pakistan','wpestate'),esc_html__('Palau','wpestate'),esc_html__('Panama','wpestate'),esc_html__('Papua New Guinea','wpestate'),esc_html__('Paraguay','wpestate'),esc_html__('Peru','wpestate'),esc_html__('Philippines','wpestate'),esc_html__('Pitcairn','wpestate'),esc_html__('Poland','wpestate'),esc_html__('Portugal','wpestate'),esc_html__('Puerto Rico','wpestate'),esc_html__('Qatar','wpestate'),esc_html__('Reunion','wpestate'),esc_html__('Romania','wpestate'),esc_html__('Russian Federation','wpestate'),esc_html__('Rwanda','wpestate'),esc_html__('Saint Kitts and Nevis','wpestate'),esc_html__('Saint Lucia','wpestate'),esc_html__('Saint Vincent and the Grenadines','wpestate'),esc_html__('Samoa','wpestate'),esc_html__('San Marino','wpestate'),esc_html__('Sao Tome and Principe','wpestate'),esc_html__('Saudi Arabia','wpestate'),esc_html__('Serbia','wpestate'),esc_html__('Senegal','wpestate'),esc_html__('Seychelles','wpestate'),esc_html__('Sierra Leone','wpestate'),esc_html__('Singapore','wpestate'),esc_html__('Slovakia (Slovak Republic)','wpestate'),esc_html__('Slovenia','wpestate'),esc_html__('Solomon Islands','wpestate'),esc_html__('Somalia','wpestate'),esc_html__('South Africa','wpestate'),esc_html__('South Georgia and the South Sandwich Islands','wpestate'),esc_html__('Spain','wpestate'),esc_html__('Sri Lanka','wpestate'),esc_html__('St. Helena','wpestate'),esc_html__('St. Pierre and Miquelon','wpestate'),esc_html__('Sudan','wpestate'),esc_html__('Suriname','wpestate'),esc_html__('Svalbard and Jan Mayen Islands','wpestate'),esc_html__('Swaziland','wpestate'),esc_html__('Sweden','wpestate'),esc_html__('Switzerland','wpestate'),esc_html__('Syrian Arab Republic','wpestate'),esc_html__('Taiwan, Province of China','wpestate'),esc_html__('Tajikistan','wpestate'),esc_html__('Tanzania, United Republic of','wpestate'),esc_html__('Thailand','wpestate'),esc_html__('Togo','wpestate'),esc_html__('Tokelau','wpestate'),esc_html__('Tonga','wpestate'),esc_html__('Trinidad and Tobago','wpestate'),esc_html__('Tunisia','wpestate'),esc_html__('Turkey','wpestate'),esc_html__('Turkmenistan','wpestate'),esc_html__('Turks and Caicos Islands','wpestate'),esc_html__('Tuvalu','wpestate'),esc_html__('Uganda','wpestate'),esc_html__('Ukraine','wpestate'),esc_html__('United Arab Emirates','wpestate'),esc_html__('United Kingdom','wpestate'),esc_html__('United States','wpestate'),esc_html__('United States Minor Outlying Islands','wpestate'),esc_html__('Uruguay','wpestate'),esc_html__('Uzbekistan','wpestate'),esc_html__('Vanuatu','wpestate'),esc_html__('Venezuela','wpestate'),esc_html__('Vietnam','wpestate'),esc_html__('Virgin Islands (British)','wpestate'),esc_html__('Virgin Islands (U.S.)','wpestate'),esc_html__('Wallis and Futuna Islands','wpestate'),esc_html__('Western Sahara','wpestate'),esc_html__('Yemen','wpestate'),esc_html__('Yugoslavia','wpestate'),esc_html__('Zambia','wpestate'),esc_html__('Zimbabwe','wpestate'));
        $countries = array(     'Afghanistan'           => esc_html__('Afghanistan','wpestate'),
                            'Albania'               => esc_html__('Albania','wpestate'),
                            'Algeria'               => esc_html__('Algeria','wpestate'),
                            'American Samoa'        => esc_html__('American Samoa','wpestate'),
                            'Andorra'               => esc_html__('Andorra','wpestate'),
                            'Angola'                => esc_html__('Angola','wpestate'),
                            'Anguilla'              => esc_html__('Anguilla','wpestate'),
                            'Antarctica'            => esc_html__('Antarctica','wpestate'),
                            'Antigua and Barbuda'   => esc_html__('Antigua and Barbuda','wpestate'),
                            'Argentina'             => esc_html__('Argentina','wpestate'),
                            'Armenia'               => esc_html__('Armenia','wpestate'),
                            'Aruba'                 => esc_html__('Aruba','wpestate'),
                            'Australia'             => esc_html__('Australia','wpestate'),
                            'Austria'               => esc_html__('Austria','wpestate'),
                            'Azerbaijan'            => esc_html__('Azerbaijan','wpestate'),
                            'Bahamas'               => esc_html__('Bahamas','wpestate'),
                            'Bahrain'               => esc_html__('Bahrain','wpestate'),
                            'Bangladesh'            => esc_html__('Bangladesh','wpestate'),
                            'Barbados'              => esc_html__('Barbados','wpestate'),
                            'Belarus'               => esc_html__('Belarus','wpestate'),
                            'Belgium'               => esc_html__('Belgium','wpestate'),
                            'Belize'                => esc_html__('Belize','wpestate'),
                            'Benin'                 => esc_html__('Benin','wpestate'),
                            'Bermuda'               => esc_html__('Bermuda','wpestate'),
                            'Bhutan'                => esc_html__('Bhutan','wpestate'),
                            'Bolivia'               => esc_html__('Bolivia','wpestate'),
                            'Bosnia and Herzegowina'=> esc_html__('Bosnia and Herzegowina','wpestate'),
                            'Botswana'              => esc_html__('Botswana','wpestate'),
                            'Bouvet Island'         => esc_html__('Bouvet Island','wpestate'),
                            'Brazil'                => esc_html__('Brazil','wpestate'),
                            'British Indian Ocean Territory'=> esc_html__('British Indian Ocean Territory','wpestate'),
                            'Brunei Darussalam'     => esc_html__('Brunei Darussalam','wpestate'),
                            'Bulgaria'              => esc_html__('Bulgaria','wpestate'),
                            'Burkina Faso'          => esc_html__('Burkina Faso','wpestate'),
                            'Burundi'               => esc_html__('Burundi','wpestate'),
                            'Cambodia'              => esc_html__('Cambodia','wpestate'),
                            'Cameroon'              => esc_html__('Cameroon','wpestate'),
                            'Canada'                => esc_html__('Canada','wpestate'),
                            'Cape Verde'            => esc_html__('Cape Verde','wpestate'),
                            'Cayman Islands'        => esc_html__('Cayman Islands','wpestate'),
                            'Central African Republic'  => esc_html__('Central African Republic','wpestate'),
                            'Chad'                  => esc_html__('Chad','wpestate'),
                            'Chile'                 => esc_html__('Chile','wpestate'),
                            'China'                 => esc_html__('China','wpestate'),
                            'Christmas Island'      => esc_html__('Christmas Island','wpestate'),
                            'Cocos (Keeling) Islands' => esc_html__('Cocos (Keeling) Islands','wpestate'),
                            'Colombia'              => esc_html__('Colombia','wpestate'),
                            'Comoros'               => esc_html__('Comoros','wpestate'),
                            'Congo'                 => esc_html__('Congo','wpestate'),
                            'Congo, the Democratic Republic of the' => esc_html__('Congo, the Democratic Republic of the','wpestate'),
                            'Cook Islands'          => esc_html__('Cook Islands','wpestate'),
                            'Costa Rica'            => esc_html__('Costa Rica','wpestate'),
                            'Cote dIvoire'          => esc_html__('Cote dIvoire','wpestate'),
                            'Croatia (Hrvatska)'    => esc_html__('Croatia (Hrvatska)','wpestate'),
                            'Cuba'                  => esc_html__('Cuba','wpestate'),
                            'Curacao'               => esc_html__('Curacao','wpestate'),
                            'Cyprus'                => esc_html__('Cyprus','wpestate'),
                            'Czech Republic'        => esc_html__('Czech Republic','wpestate'),
                            'Denmark'               => esc_html__('Denmark','wpestate'),
                            'Djibouti'              => esc_html__('Djibouti','wpestate'),
                            'Dominica'              => esc_html__('Dominica','wpestate'),
                            'Dominican Republic'    => esc_html__('Dominican Republic','wpestate'),
                            'East Timor'            => esc_html__('East Timor','wpestate'),
                            'Ecuador'               => esc_html__('Ecuador','wpestate'),
                            'Egypt'                 => esc_html__('Egypt','wpestate'),
                            'El Salvador'           => esc_html__('El Salvador','wpestate'),
                            'Equatorial Guinea'     => esc_html__('Equatorial Guinea','wpestate'),
                            'Eritrea'               => esc_html__('Eritrea','wpestate'),
                            'Estonia'               => esc_html__('Estonia','wpestate'),
                            'Ethiopia'              => esc_html__('Ethiopia','wpestate'),
                            'Falkland Islands (Malvinas)' => esc_html__('Falkland Islands (Malvinas)','wpestate'),
                            'Faroe Islands'         => esc_html__('Faroe Islands','wpestate'),
                            'Fiji'                  => esc_html__('Fiji','wpestate'),
                            'Finland'               => esc_html__('Finland','wpestate'),
                            'France'                => esc_html__('France','wpestate'),
                            'France Metropolitan'   => esc_html__('France Metropolitan','wpestate'),
                            'French Guiana'         => esc_html__('French Guiana','wpestate'),
                            'French Polynesia'      => esc_html__('French Polynesia','wpestate'),
                            'French Southern Territories' => esc_html__('French Southern Territories','wpestate'),
                            'Gabon'                 => esc_html__('Gabon','wpestate'),
                            'Gambia'                => esc_html__('Gambia','wpestate'),
                            'Georgia'               => esc_html__('Georgia','wpestate'),
                            'Germany'               => esc_html__('Germany','wpestate'),
                            'Ghana'                 => esc_html__('Ghana','wpestate'),
                            'Gibraltar'             => esc_html__('Gibraltar','wpestate'),
                            'Greece'                => esc_html__('Greece','wpestate'),
                            'Greenland'             => esc_html__('Greenland','wpestate'),
                            'Grenada'               => esc_html__('Grenada','wpestate'),
                            'Guadeloupe'            => esc_html__('Guadeloupe','wpestate'),
                            'Guam'                  => esc_html__('Guam','wpestate'),
                            'Guatemala'             => esc_html__('Guatemala','wpestate'),
                            'Guinea'                => esc_html__('Guinea','wpestate'),
                            'Guinea-Bissau'         => esc_html__('Guinea-Bissau','wpestate'),
                            'Guyana'                => esc_html__('Guyana','wpestate'),
                            'Haiti'                 => esc_html__('Haiti','wpestate'),
                            'Heard and Mc Donald Islands'  => esc_html__('Heard and Mc Donald Islands','wpestate'),
                            'Holy See (Vatican City State)'=> esc_html__('Holy See (Vatican City State)','wpestate'),
                            'Honduras'              => esc_html__('Honduras','wpestate'),
                            'Hong Kong'             => esc_html__('Hong Kong','wpestate'),
                            'Hungary'               => esc_html__('Hungary','wpestate'),
                            'Iceland'               => esc_html__('Iceland','wpestate'),
                            'India'                 => esc_html__('India','wpestate'),
                            'Indonesia'             => esc_html__('Indonesia','wpestate'),
                            'Iran (Islamic Republic of)'  => esc_html__('Iran (Islamic Republic of)','wpestate'),
                            'Iraq'                  => esc_html__('Iraq','wpestate'),
                            'Ireland'               => esc_html__('Ireland','wpestate'),
                            'Israel'                => esc_html__('Israel','wpestate'),
                            'Italy'                 => esc_html__('Italy','wpestate'),
                            'Jamaica'               => esc_html__('Jamaica','wpestate'),
                            'Japan'                 => esc_html__('Japan','wpestate'),
                            'Jordan'                => esc_html__('Jordan','wpestate'),
                            'Kazakhstan'            => esc_html__('Kazakhstan','wpestate'),
                            'Kenya'                 => esc_html__('Kenya','wpestate'),
                            'Kiribati'              => esc_html__('Kiribati','wpestate'),
                            'Korea, Democratic People Republic of'  => esc_html__('Korea, Democratic People Republic of','wpestate'),
                            'Korea, Republic of'    => esc_html__('Korea, Republic of','wpestate'),
                            'Kuwait'                => esc_html__('Kuwait','wpestate'),
                            'Kyrgyzstan'            => esc_html__('Kyrgyzstan','wpestate'),
                            'Lao, People Democratic Republic' => esc_html__('Lao, People Democratic Republic','wpestate'),
                            'Latvia'                => esc_html__('Latvia','wpestate'),
                            'Lebanon'               => esc_html__('Lebanon','wpestate'),
                            'Lesotho'               => esc_html__('Lesotho','wpestate'),
                            'Liberia'               => esc_html__('Liberia','wpestate'),
                            'Libyan Arab Jamahiriya'=> esc_html__('Libyan Arab Jamahiriya','wpestate'),
                            'Liechtenstein'         => esc_html__('Liechtenstein','wpestate'),
                            'Lithuania'             => esc_html__('Lithuania','wpestate'),
                            'Luxembourg'            => esc_html__('Luxembourg','wpestate'),
                            'Macau'                 => esc_html__('Macau','wpestate'),
                            'Macedonia, The Former Yugoslav Republic of'    => esc_html__('Macedonia, The Former Yugoslav Republic of','wpestate'),
                            'Madagascar'            => esc_html__('Madagascar','wpestate'),
                            'Malawi'                => esc_html__('Malawi','wpestate'),
                            'Malaysia'              => esc_html__('Malaysia','wpestate'),
                            'Maldives'              => esc_html__('Maldives','wpestate'),
                            'Mali'                  => esc_html__('Mali','wpestate'),
                            'Malta'                 => esc_html__('Malta','wpestate'),
                            'Marshall Islands'      => esc_html__('Marshall Islands','wpestate'),
                            'Martinique'            => esc_html__('Martinique','wpestate'),
                            'Mauritania'            => esc_html__('Mauritania','wpestate'),
                            'Mauritius'             => esc_html__('Mauritius','wpestate'),
                            'Mayotte'               => esc_html__('Mayotte','wpestate'),
                            'Mexico'                => esc_html__('Mexico','wpestate'),
                            'Micronesia, Federated States of'    => esc_html__('Micronesia, Federated States of','wpestate'),
                            'Moldova, Republic of'  => esc_html__('Moldova, Republic of','wpestate'),
                            'Monaco'                => esc_html__('Monaco','wpestate'),
                            'Mongolia'              => esc_html__('Mongolia','wpestate'),
                            'Montserrat'            => esc_html__('Montserrat','wpestate'),
                            'Morocco'               => esc_html__('Morocco','wpestate'),
                            'Mozambique'            => esc_html__('Mozambique','wpestate'),
                            'Montenegro'            => esc_html__('Montenegro','wpestate'),
                            'Myanmar'               => esc_html__('Myanmar','wpestate'),
                            'Namibia'               => esc_html__('Namibia','wpestate'),
                            'Nauru'                 => esc_html__('Nauru','wpestate'),
                            'Nepal'                 => esc_html__('Nepal','wpestate'),
                            'Netherlands'           => esc_html__('Netherlands','wpestate'),
                            'Netherlands Antilles'  => esc_html__('Netherlands Antilles','wpestate'),
                            'New Caledonia'         => esc_html__('New Caledonia','wpestate'),
                            'New Zealand'           => esc_html__('New Zealand','wpestate'),
                            'Nicaragua'             => esc_html__('Nicaragua','wpestate'),
                            'Niger'                 => esc_html__('Niger','wpestate'),
                            'Nigeria'               => esc_html__('Nigeria','wpestate'),
                            'Niue'                  => esc_html__('Niue','wpestate'),
                            'Norfolk Island'        => esc_html__('Norfolk Island','wpestate'),
                            'Northern Mariana Islands' => esc_html__('Northern Mariana Islands','wpestate'),
                            'Norway'                => esc_html__('Norway','wpestate'),
                            'Oman'                  => esc_html__('Oman','wpestate'),
                            'Pakistan'              => esc_html__('Pakistan','wpestate'),
                            'Palau'                 => esc_html__('Palau','wpestate'),
                            'Panama'                => esc_html__('Panama','wpestate'),
                            'Papua New Guinea'      => esc_html__('Papua New Guinea','wpestate'),
                            'Paraguay'              => esc_html__('Paraguay','wpestate'),
                            'Peru'                  => esc_html__('Peru','wpestate'),
                            'Philippines'           => esc_html__('Philippines','wpestate'),
                            'Pitcairn'              => esc_html__('Pitcairn','wpestate'),
                            'Poland'                => esc_html__('Poland','wpestate'),
                            'Portugal'              => esc_html__('Portugal','wpestate'),
                            'Puerto Rico'           => esc_html__('Puerto Rico','wpestate'),
                            'Qatar'                 => esc_html__('Qatar','wpestate'),
                            'Reunion'               => esc_html__('Reunion','wpestate'),
                            'Romania'               => esc_html__('Romania','wpestate'),
                            'Russian Federation'    => esc_html__('Russian Federation','wpestate'),
                            'Rwanda'                => esc_html__('Rwanda','wpestate'),
                            'Saint Kitts and Nevis' => esc_html__('Saint Kitts and Nevis','wpestate'),
                            'Saint Lucia'           => esc_html__('Saint Lucia','wpestate'),
                            'Saint Vincent and the Grenadines' => esc_html__('Saint Vincent and the Grenadines','wpestate'),
                            'Samoa'                 => esc_html__('Samoa','wpestate'),
                            'San Marino'            => esc_html__('San Marino','wpestate'),
                            'Sao Tome and Principe' => esc_html__('Sao Tome and Principe','wpestate'),
                            'Saudi Arabia'          => esc_html__('Saudi Arabia','wpestate'),
                            'Serbia'                => esc_html__('Serbia','wpestate'),
                            'Senegal'               => esc_html__('Senegal','wpestate'),
                            'Seychelles'            => esc_html__('Seychelles','wpestate'),
                            'Sierra Leone'          => esc_html__('Sierra Leone','wpestate'),
                            'Singapore'             => esc_html__('Singapore','wpestate'),
                            'Slovakia (Slovak Republic)'=> esc_html__('Slovakia (Slovak Republic)','wpestate'),
                            'Slovenia'              => esc_html__('Slovenia','wpestate'),
                            'Solomon Islands'       => esc_html__('Solomon Islands','wpestate'),
                            'Somalia'               => esc_html__('Somalia','wpestate'),
                            'South Africa'          => esc_html__('South Africa','wpestate'),
                            'South Georgia and the South Sandwich Islands' => esc_html__('South Georgia and the South Sandwich Islands','wpestate'),
                            'Spain'                 => esc_html__('Spain','wpestate'),
                            'Sri Lanka'             => esc_html__('Sri Lanka','wpestate'),
                            'St. Helena'            => esc_html__('St. Helena','wpestate'),
                            'St. Pierre and Miquelon'=> esc_html__('St. Pierre and Miquelon','wpestate'),
                            'Sudan'                 => esc_html__('Sudan','wpestate'),
                            'Suriname'              => esc_html__('Suriname','wpestate'),
                            'Svalbard and Jan Mayen Islands'    => esc_html__('Svalbard and Jan Mayen Islands','wpestate'),
                            'Swaziland'             => esc_html__('Swaziland','wpestate'),
                            'Sweden'                => esc_html__('Sweden','wpestate'),
                            'Switzerland'           => esc_html__('Switzerland','wpestate'),
                            'Syrian Arab Republic'  => esc_html__('Syrian Arab Republic','wpestate'),
                            'Taiwan, Province of China' => esc_html__('Taiwan, Province of China','wpestate'),
                            'Tajikistan'            => esc_html__('Tajikistan','wpestate'),
                            'Tanzania, United Republic of'=> esc_html__('Tanzania, United Republic of','wpestate'),
                            'Thailand'              => esc_html__('Thailand','wpestate'),
                            'Togo'                  => esc_html__('Togo','wpestate'),
                            'Tokelau'               => esc_html__('Tokelau','wpestate'),
                            'Tonga'                 => esc_html__('Tonga','wpestate'),
                            'Trinidad and Tobago'   => esc_html__('Trinidad and Tobago','wpestate'),
                            'Tunisia'               => esc_html__('Tunisia','wpestate'),
                            'Turkey'                => esc_html__('Turkey','wpestate'),
                            'Turkmenistan'          => esc_html__('Turkmenistan','wpestate'),
                            'Turks and Caicos Islands'  => esc_html__('Turks and Caicos Islands','wpestate'),
                            'Tuvalu'                => esc_html__('Tuvalu','wpestate'),
                            'Uganda'                => esc_html__('Uganda','wpestate'),
                            'Ukraine'               => esc_html__('Ukraine','wpestate'),
                            'United Arab Emirates'  => esc_html__('United Arab Emirates','wpestate'),
                            'United Kingdom'        => esc_html__('United Kingdom','wpestate'),
                            'United States'         => esc_html__('United States','wpestate'),
                            'United States Minor Outlying Islands'  => esc_html__('United States Minor Outlying Islands','wpestate'),
                            'Uruguay'               => esc_html__('Uruguay','wpestate'),
                            'Uzbekistan'            => esc_html__('Uzbekistan','wpestate'),
                            'Vanuatu'               => esc_html__('Vanuatu','wpestate'),
                            'Venezuela'             => esc_html__('Venezuela','wpestate'),
                            'Vietnam'               => esc_html__('Vietnam','wpestate'),
                            'Virgin Islands (British)'=> esc_html__('Virgin Islands (British)','wpestate'),
                            'Virgin Islands (U.S.)' => esc_html__('Virgin Islands (U.S.)','wpestate'),
                            'Wallis and Futuna Islands' => esc_html__('Wallis and Futuna Islands','wpestate'),
                            'Western Sahara'        => esc_html__('Western Sahara','wpestate'),
                            'Yemen'                 => esc_html__('Yemen','wpestate'),
                            'Yugoslavia'            => esc_html__('Yugoslavia','wpestate'),
                            'Zambia'                => esc_html__('Zambia','wpestate'),
                            'Zimbabwe'              => esc_html__('Zimbabwe','wpestate')
        );

        
        
        
        $country_select='<select id="general_country" style="width: 200px;" name="general_country">';

        foreach($countries as $key=>$country){
            $country_select.='<option value="'.$key.'"';  
            if($selected==$key){
                $country_select.='selected="selected"';
            }
            $country_select.='>'.$country.'</option>';
        }

        $country_select.='</select>';
        return $country_select;
    }
endif; // end   wpestate_general_country_list  


function wpestate_sorting_function($a, $b) {
    return $a[3] - $b[3];
};



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Wpestate Price settings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('wpestate_price_set') ):
function wpestate_price_set(){
    $custom_fields = get_option( 'wp_estate_multi_curr', true);     
    $current_fields='';
    
    $currency_symbol                =   esc_html( get_option('wp_estate_currency_symbol') );
    $where_currency_symbol          =   '';
    $where_currency_symbol_array    =   array('before','after');
    $where_currency_symbol_status   =   esc_html( get_option('wp_estate_where_currency_symbol') );
    foreach($where_currency_symbol_array as $value){
            $where_currency_symbol.='<option value="'.$value.'"';
            if ($where_currency_symbol_status==$value){
                $where_currency_symbol.=' selected="selected" ';
            }
            $where_currency_symbol.='>'.$value.'</option>';
    }
    $enable_auto_symbol             =   '';
    $enable_auto_symbol_array       =   array('yes','no');
    $where_enable_auto_status       =    esc_html( get_option('wp_estate_auto_curency') );
     foreach($enable_auto_symbol_array as $value){
            $enable_auto_symbol.='<option value="'.$value.'"';
            if ($where_enable_auto_status==$value){
                $enable_auto_symbol.=' selected="selected" ';
            }
            $enable_auto_symbol.='>'.$value.'</option>';
    }
    
    $i=0;
    if( !empty($custom_fields)){    
        while($i< count($custom_fields) ){
            $current_fields.='
                <div class=field_row>
                <div    class="field_item"><strong>'.esc_html__( 'Currency Code','wpestate').'</strong></br><input   type="text" name="add_curr_name[]"   value="'.$custom_fields[$i][0].'"  ></div>
                <div    class="field_item"><strong>'.esc_html__( 'Currency Label','wpestate').'</strong></br><input  type="text" name="add_curr_label[]"   value="'.$custom_fields[$i][1].'"  ></div>
                <div    class="field_item"><strong>'.esc_html__( 'Currency Value','wpestate').'</strong></br><input  type="text" name="add_curr_value[]"   value="'.$custom_fields[$i][2].'"  ></div>
                <div    class="field_item"><strong>'.esc_html__( 'Currency Position','wpestate').'</strong></br><input  type="text" name="add_curr_order[]"   value="'.$custom_fields[$i][3].'"  ></div>
                
                <a class="deletefieldlink" href="#">'.esc_html__( 'delete','wpestate').'</a>
            </div>';    
            $i++;
        }
    }
    
    
    
  
    
    print ' <div class="estate_option_row">
                <div class="label_option_row">'.__('Price - thousands separator','wpestate').'</div>
                <div class="option_row_explain">'.__('Set the thousand separator for price numbers.','wpestate').'</div>
                <input type="text" name="prices_th_separator" id="prices_th_separator" value="'.get_option('wp_estate_prices_th_separator','').'"> 
            </div>
            
            <div class="estate_option_row">
                <div class="label_option_row">'.__('Currency Symbol','wpestate').'</div>
                <div class="option_row_explain">'.esc_html__( 'This is used for default property price currency symbol and default currency symbol in multi currency dropdown','wpestate').'</div>
                <input  type="text" id="currency_label_main"  name="currency_label_main"   value="'. get_option('wp_estate_currency_label_main','').'" size="40"/>
            </div>
            
            <div class="estate_option_row">    
                <div class="label_option_row">'.__('Where to show the currency symbol?','wpestate').'</div>
                <div class="option_row_explain">'.esc_html__( 'Where to show the currency symbol?','wpestate').'</label></div>
                <select id="where_currency_symbol" name="where_currency_symbol">
                    '.$where_currency_symbol.'
                </select> 
            </div>
            
            <div class="estate_option_row">
                <div class="label_option_row">'.__('Currency code','wpestate').'</div>
                <div class="option_row_explain">'.__('Currency code is used for syncing the multi-currency options with Yahoo API, if enabled.','wpestate').'</div>
                <input  type="text" id="currency_symbol" name="currency_symbol"  value="'.$currency_symbol.'"/> </td>
            </div>        
       
          
          
        
            <div class="estate_option_row">
                <div class="label_option_row">'.__('Enable auto loading of exchange rates from Yahoo (1 time per day)?','wpestate').'</div>
                <div class="option_row_explain">'.__('Currency code must be set according to international standards. Complete list is here http://www.xe.com/iso4217.php .','wpestate').'</div>
                <select id="auto_curency" name="auto_curency">
                    '.$enable_auto_symbol.'
                </select> 
           </div>
           
    ';
    
    
    
 
     print'<div class="estate_option_row"><h3 style="margin-left:10px;width:100%;float:left;">'.esc_html__('Add Currencies for Multi Currency Widget.','wpestate').'</h3>
     
        <div id="custom_fields">
             '.$current_fields.'
            <input type="hidden" name="is_custom_cur" value="1">   
        </div>

        <div class="add_curency" id="add_curency_wrapper">
                      
            <div class="cur_explanations">'.esc_html__( 'Currency Code','wpestate').'</div>
            <input  type="text" id="currency_name"  name="currency_name"   value="" size="40"/>

            <div class="cur_explanations">'.esc_html__( 'Currency label - appears in front end in multi currency dropdown','wpestate').'</div>
            <input  type="text" id="currency_label"  name="currency_label"   value="" size="40"/>

            <div class="cur_explanations">'.esc_html__( 'Currency Value compared with the base currency','wpestate').'</div>
            <input  type="text" id="currency_value"  name="currency_value"   value="" size="40"/>

            <div class="cur_explanations">'.esc_html__( 'Show currency before or after price - in front pages','wpestate').'</div>
            <select id="where_cur" name="where_cur"  style="width:236px;">
                <option value="before"> before </option>
                <option value="after">  after </option>
            </select>
                    
        </div>                        
       <a href="#" id="add_curency">'.esc_html__( ' click to add currency','wpestate').'</a><br>

      

    </div>';
    print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>';;
}
endif;


if( !function_exists('wpestate_generate_file_pins') ):
function   wpestate_generate_file_pins(){
    print '<div class="estate_option_row">';
 
    if (isset($_POST['startgenerating']) == 1){
        $show_adv_search_general            =   get_option('wp_estate_wpestate_autocomplete','');
        if($show_adv_search_general=='no'){
            event_wp_estate_create_auto_function();
            esc_html_e('Autcomplete file was generated','wpestate').'</br>';
        }


        if ( get_option('wp_estate_readsys','') =='yes' ){

            $path= wpestate_get_pin_file_path_write();

            if ( file_exists ($path) && is_writable ($path) ){
                wpestate_listing_pins_for_file();
                esc_html_e('File was generated','wpestate');
            }else{
                print ' <div class="notice_file">'.esc_html__( 'the file Google map does NOT exist or is NOT writable','wpestate').'</div>';
            }

        }else{
            esc_html_e('Pin Generation works only if the file reading option in Google Map setting is set to yes','wpestate');
        }
    }else{
         print'
            <div class="label_option_row">'.__('Generate the pins','wpestate').'</div>
            <div class="option_row_explain">'.__('Generate the pins for the read from file map option','wpestate').'</div>    
            <input type="hidden" name="startgenerating" value="1" />
        ';
    }
       print '<input type="submit" name="submit"  class="new_admin_submit " value="'.__('Generate Pins','wpestate').'" />';

    print '</div>';   
}
endif;










if( !function_exists('wpestate_dropdowns_theme_admin') ):
    function wpestate_dropdowns_theme_admin($array_values,$option_name,$pre=''){
        
        $dropdown_return    =   '';
        $option_value       =   esc_html ( get_option('wp_estate_'.$option_name,'') );
        foreach($array_values as $value){
            $dropdown_return.='<option value="'.$value.'"';
              if ( $option_value == $value ){
                $dropdown_return.='selected="selected"';
            }
            $dropdown_return.='>'.$pre.$value.'</option>';
        }
        
        return $dropdown_return;
        
    }
endif;



if( !function_exists('new_wpestate_export_settings') ):
function  new_wpestate_export_settings(){
          
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Export Theme Options','wpestate').'</div>
        <div class="option_row_explain">'.__('Export Theme Options ','wpestate').'</div>    
            <textarea  rows="15" style="width:100%;" id="export_theme_options" onclick="this.focus();this.select()" name="export_theme_options">'.wpestate_export_theme_options().'</textarea>
       
    </div>';
   
}
endif;


if( !function_exists('wpestate_export_theme_options') ):
function wpestate_export_theme_options(){
    $export_options = array(
        'wp_estate_full_invoice_reminder',       
        'wp_estate_full_invoice_reminder',
        'wp_estate_map_max_pins',
        'wp_estate_logo_header_type',
        'wp_estate_logo_header_align',
        'wp_estate_wide_header',
        'wp_estate_wide_footer',
        'wp_estate_header_height',
        'wp_estate_sticky_header_height',
        'wp_estate_border_bottom_header',
        'wp_estate_sticky_border_bottom_header',
        'wp_estate_border_bottom_header_color',
        'wp_estate_border_bottom_header_sticky_color',
        'wp_estate_menu_font_color',
        'wp_estate_top_menu_hover_font_color',
        'wp_estate_transparent_menu_font_color',
        'wp_estate_transparent_menu_hover_font_color',
        'wp_estate_sticky_menu_font_color',
        'wp_estate_menu_items_color',
        'wp_estate_menu_item_back_color',
        'wp_estate_menu_hover_font_color',
        'wp_estate_top_menu_font_size',
        'wp_estate_menu_item_font_size',
        'wp_estate_top_menu_hover_back_font_color',
        'wp_estate_top_menu_hover_type',
        'wp_estate_logo_image',
        'wp_estate_transparent_logo_image',
        'wp_estate_mobile_logo_image',
        'wp_estate_favicon_image',
        'wp_estate_logo_image_retina',
        'wp_estate_transparent_logo_image_retina',
        'wp_estate_mobile_logo_image_retina',
        'wp_estate_google_analytics_code',
        'wp_estate_general_country',
        'wp_estate_measure_sys',
        'wp_estate_date_lang',
        'wp_estate_show_submit',
        'wp_estate_setup_weekend',
        'wp_estate_enable_user_pass',
        'wp_estate_use_captcha',
        'wp_estate_recaptha_sitekey',
        'wp_estate_recaptha_secretkey',
        'wp_estate_delete_orphan',
        'wp_estate_separate_users',
        'wp_estate_publish_only',
        'wp_estate_wide_status',
        'wp_estate_show_top_bar_user_menu',
        'wp_estate_show_top_bar_user_login',
        'wp_estate_header_type',
        'wp_estate_user_header_type',
        'wp_estate_transparent_menu',
        'wp_estate_transparent_menu_listing',
        'wp_estate_prop_list_slider',
        'wp_estate_logo_margin',
        'wp_estate_global_revolution_slider',
        'wp_estate_global_header',
        'wp_estate_footer_background',
        'wp_estate_repeat_footer_back',
        'wp_estate_prop_no',
        'wp_estate_show_empty_city',
        'wp_estate_blog_sidebar',
        'wp_estate_blog_sidebar_name',
        'wp_estate_property_list_type',
        'wp_estate_listing_unit_type',
        'wp_estate_listing_unit_style_half',
        'wp_estate_listing_page_type',
        'wp_estate_property_list_type_adv',
        'wp_estate_use_upload_tax_page',
        'wp_estate_general_font',
        'wp_estate_headings_font_subset',
        'wp_estate_copyright_message',
        'wp_estate_prices_th_separator',
        'wp_estate_currency_symbol',
        'wp_estate_where_currency_symbol',
        'wp_estate_currency_label_main',
        'wp_estate_is_custom_cur',
        'wp_estate_auto_curency',
        'wp_estate_currency_name',
        'wp_estate_currency_label',
        'wp_estate_where_cur',
        'wp_estate_wp_estate_custom_fields',
        'wp_estate_feature_list',
        'wp_estate_show_no_features',
        'wp_estate_property_adr_text',
        'wp_estate_property_features_text',
        'wp_estate_property_description_text',
        'wp_estate_property_details_text',
        'wp_estate_property_price_text',
        'wp_estate_property_pictures_text',
        'wp_estate_status_list',
        'wp_estate_theme_slider',
        'wp_estate_slider_cycle',
        'wp_estate_company_name',
        'wp_estate_email_adr',
        'wp_estate_telephone_no',
        'wp_estate_mobile_no',
        'wp_estate_fax_ac',
        'wp_estate_skype_ac',
        'wp_estate_co_address',
        'wp_estate_facebook_link',
        'wp_estate_twitter_link',
        'wp_estate_google_link',
        'wp_estate_pinterest_link',
        'wp_estate_linkedin_link',
        'wp_estate_facebook_login',
        'wp_estate_google_login',
        'wp_estate_yahoo_login',
        'wp_estate_social_register_on',
        'wp_estate_readsys',
        'wp_estate_ssl_map',
        'wp_estate_general_latitude',
        'wp_estate_general_longitude',
        'wp_estate_default_map_zoom',
        'wp_estate_ondemandmap',
        'wp_estate_pin_cluster',
        'wp_estate_zoom_cluster',
        'wp_estate_hq_latitude',
        'wp_estate_hq_longitude',
        'wp_estate_geolocation_radius',
        'wp_estate_min_height',
        'wp_estate_max_height',
        'wp_estate_keep_min',
        'wp_estate_map_style',
        'wp_estate_color_scheme',
        'wp_estate_on_child_theme',
        'wp_estate_main_color',
        'wp_estate_background_color',
        'wp_estate_content_back_color',
        'wp_estate_breadcrumbs_font_color',
        'wp_estate_font_color',
        'wp_estate_link_color',
        'wp_estate_headings_color',
        'wp_estate_footer_back_color',
        'wp_estate_footer_font_color',
        'wp_estate_footer_copy_color',
        'wp_estate_sidebar_widget_color',
        'wp_estate_sidebar_heading_boxed_color',
        'wp_estate_sidebar_heading_color',
        'wp_estate_sidebar2_font_color',
        'wp_estate_header_color',
        'wp_estate_menu_font_color',
        'wp_estate_menu_hover_back_color',
        'wp_estate_menu_hover_font_color',
        'wp_estate_top_bar_back',
        'wp_estate_top_bar_font',
        'wp_estate_box_content_back_color',
        'wp_estate_box_content_border_color',
        'wp_estate_hover_button_color',
        'wp_estate_custom_css',
        'wp_estate_adv_search_type',
        'wp_estate_show_adv_search_general',
        'wp_estate_wpestate_autocomplete',
        'wp_estate_show_adv_search_slider',
        'wp_estate_show_slider_price_values',
        'wp_estate_show_slider_min_price',
        'wp_estate_show_slider_max_price',
        'wp_estate_feature_list',
        'wp_estate_paid_submission',
        'wp_estate_enable_paypal',
        'wp_estate_enable_stripe',
        'wp_estate_admin_submission',
        'wp_estate_price_submission',
        'wp_estate_price_featured_submission',
        'wp_estate_submission_curency',
        'wp_estate_prop_image_number',
        'wp_estate_enable_direct_pay',
        'wp_estate_submission_curency_custom',
        'wp_estate_free_mem_list',
        'wp_estate_free_feat_list',
        'wp_estate_book_down',
        'wp_estate_book_down_fixed_fee',
        'wp_estate_free_feat_list_expiration',
        'wp_estate_new_user',                 
        'wp_estate_admin_new_user',         
        'wp_estate_password_reset_request',   
        'wp_estate_password_reseted',          
        'wp_estate_purchase_activated',       
        'wp_estate_approved_listing',       
        'wp_estate_admin_expired_listing',   
        'wp_estate_paid_submissions',         
        'wp_estate_featured_submission',      
        'wp_estate_account_downgraded',      
        'wp_estate_membership_cancelled',      
        'wp_estate_free_listing_expired',     
        'wp_estate_new_listing_submission',    
        'wp_estate_recurring_payment',       
        'wp_estate_membership_activated',    
        'wp_estate_agent_update_profile',    
        'wp_estate_bookingconfirmeduser',     
        'wp_estate_bookingconfirmed',         
        'wp_estate_bookingconfirmed_nodeposit',
        'wp_estate_inbox',                    
        'wp_estate_newbook',                 
        'wp_estate_mynewbook',                
        'wp_estate_newinvoice',              
        'wp_estate_deletebooking',            
        'wp_estate_deletebookinguser',       
        'wp_estate_deletebookingconfirmed',    
        'wp_estate_new_wire_transfer',        
        'wp_estate_admin_new_wire_transfer',  
        'wp_estate_subject_new_user',                 
        'wp_estate_subject_admin_new_user',         
        'wp_estate_subject_password_reset_request',   
        'wp_estate_subject_password_reseted',          
        'wp_estate_subject_purchase_activated',       
        'wp_estate_subject_approved_listing',       
        'wp_estate_subject_admin_expired_listing',   
        'wp_estate_subject_paid_submissions',         
        'wp_estate_subject_featured_submission',      
        'wp_estate_subject_account_downgraded',      
        'wp_estate_subject_membership_cancelled',      
        'wp_estate_subject_free_listing_expired',     
        'wp_estate_subject_new_listing_submission',    
        'wp_estate_subject_recurring_payment',       
        'wp_estate_subject_membership_activated',    
        'wp_estate_subject_agent_update_profile',    
        'wp_estate_subject_bookingconfirmeduser',     
        'wp_estate_subject_bookingconfirmed',         
        'wp_estate_subject_bookingconfirmed_nodeposit',
        'wp_estate_subject_inbox',                    
        'wp_estate_subject_newbook',                 
        'wp_estate_subject_mynewbook',                
        'wp_estate_subject_newinvoice',              
        'wp_estate_subject_deletebooking',            
        'wp_estate_subject_deletebookinguser',       
        'wp_estate_subject_deletebookingconfirmed',    
        'wp_estate_subject_new_wire_transfer',        
        'wp_estate_subject_admin_new_wire_transfer',  
        'wp_estate_advanced_exteded',
        'wp_estate_show_top_bar_user_menu',
        'wp_estate_service_fee_fixed_fee',
        'wp_estate_service_fee'
    );
    
    $return_exported_data=array();
    // esc_html( get_option('wp_estate_where_currency_symbol') );
    foreach($export_options as $option){
        $real_option=get_option($option);
        
        if(is_array($real_option)){
            $return_exported_data[$option]= get_option($option) ;
        }else{
            $return_exported_data[$option]=esc_html( get_option($option) );
        }
     
    }
    
    return base64_encode( serialize( $return_exported_data) );
    
}
endif;




if( !function_exists('new_wpestate_import_options_tab') ):
function new_wpestate_import_options_tab(){
    
    if(isset($_POST['import_theme_options']) && $_POST['import_theme_options']!=''){
        
        $data =@unserialize(base64_decode( trim($_POST['import_theme_options']) ) );
     
        if ($data !== false && !empty($data) && is_array($data)) {
            foreach($data as $key=>$value){
                update_option($key, $value);          
            }
        
            print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Import Completed','wpestate') .'</div>
            </div>';
            update_option('wp_estate_import_theme_options','') ;
   
        }else{
            print'<div class="estate_option_row">
            <div class="label_option_row">'.__('The inserted code is not a valid one','wpestate') .'</div>
            </div>';
            update_option('wp_estate_import_theme_options','') ;
        }

    }else{
        print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Import Theme Options','wpestate').'</div>
        <div class="option_row_explain">'.__('Import Theme Options ','wpestate').'</div>    
            <textarea  rows="15" style="width:100%;" id="import_theme_options" name="import_theme_options"></textarea>
        </div>';
        print ' <div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Import','wpestate').'" />
        </div>';
    } 
               
}
endif;



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// Logos & Favicon
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('new_wpestate_theme_admin_logos_favicon') ):
function new_wpestate_theme_admin_logos_favicon(){
    $cache_array                    =   array('yes','no');
    $social_array                   =   array('no','yes');
    $logo_image                     =   esc_html( get_option('wp_estate_logo_image','') );
    //$footer_logo_image              =   esc_html( get_option('wp_estate_footer_logo_image','') );
    $mobile_logo_image              =   esc_html( get_option('wp_estate_mobile_logo_image','') );
    $favicon_image                  =   esc_html( get_option('wp_estate_favicon_image','') );
    
    
  
    
    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Your Favicon','wpestate').'</div>
        <div class="option_row_explain">'.__('Upload site favicon in .ico, .png, .jpg or .gif format','wpestate').'</div>    
            <input id="favicon_image" type="text" size="36" name="favicon_image" value="'.$favicon_image.'" />
            <input id="favicon_image_button" type="button"  class="upload_button button" value="'.__('Upload Favicon','wpestate').'" />
       
    </div>'; 
    
     print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Your Logo','wpestate').'</div>
            <div class="option_row_explain">'.__('If you add images directly into the input fields (without Upload button) please use the full image path. For ex: http://yourdomain.org/ . If you use the "upload" button use also "Insert into Post" button from the pop up window.','wpestate').'</div>    
            <input id="logo_image" type="text" size="36" name="logo_image" value="'.$logo_image.'" />
            <input id="logo_image_button" type="button"  class="upload_button button" value="'.esc_html__( 'Upload Logo','wpestate').'" />
        </div>';
     
    
    $transparent_logo_image    =   esc_html( get_option('wp_estate_transparent_logo_image','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Your Transparent Header Logo','wpestate').'</div>
        <div class="option_row_explain">'.__('If you add images directly into the input fields (without Upload button) please use the full image path. For ex: http://yourdomain.org/ . If you use the "upload"  button use also "Insert into Post" button from the pop up window.','wpestate').'</div>    
            <input id="transparent_logo_image" type="text" size="36" name="transparent_logo_image" value="'.$transparent_logo_image.'" />
            <input id="transparent_logo_image_button" type="button"  class="upload_button button" value="'.__('Upload Logo','wpestate').'" /></br>
            '.'
    </div>';
     
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Mobile/Tablets Logo', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Upload mobile logo in jpg or png format.', 'wpestate') . '</div>    
            <input id="mobile_logo_image" type="text" size="36" name="mobile_logo_image" value="' . $mobile_logo_image . '" />
            <input id="mobile_logo_image_button" type="button"  class="upload_button button" value="' . __('Upload Logo', 'wpestate') . '" />
       
    </div>';
    
         
    $logo_image_retina              =   esc_html( get_option('wp_estate_logo_image_retina','') );
    print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Your Retina Logo','wpestate').'</div>
            <div class="option_row_explain">'.__('To create retina logo, add _2x at the end of name of the original file (for ex logo_2x.jpg)','wpestate').'</div>    
                <input id="logo_image_retina" type="text" size="36" name="logo_image_retina" value="'.$logo_image_retina.'" />
		<input id="logo_image_retina_button" type="button"  class="upload_button button" value="'.esc_html__( 'Upload Logo','wpestate').'" />
        </div>';
    
    $transparent_logo_image_retina  =   esc_html( get_option('wp_estate_mobile_logo_image_retina','') );
    print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Your Transparent Retina Logo','wpestate').'</div>
            <div class="option_row_explain">'.__('To create retina logo, add _2x at the end of name of the original file (for ex logo_2x.jpg)','wpestate').'</div>    
                <input id="transparent_logo_image_retina" type="text" size="36" name="transparent_logo_image_retina" value="'.$transparent_logo_image_retina.'" />
		<input id="transparent_logo_image_retina_button" type="button"  class="upload_button button" value="'.esc_html__( 'Upload Logo','wpestate').'" />
            </div>';
    $mobile_logo_image_retina       =   esc_html( get_option('wp_estate_mobile_logo_image_retina','') );
    print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Your Mobile Retina Logo','wpestate').'</div>
            <div class="option_row_explain">'.__('To create retina logo, add _2x at the end of name of the original file (for ex logo_2x.jpg)','wpestate').'</div>    
                <input id="mobile_logo_image_retina" type="text" size="36" name="mobile_logo_image_retina" value="'.$mobile_logo_image_retina.'" />
		<input id="mobile_logo_image_retina_button" type="button"  class="upload_button button" value="'.esc_html__( 'Upload Logo','wpestate').'" />
            </div>';
    
    
    

    $logo_margin                =   intval( get_option('wp_estate_logo_margin','') ); 
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Margin Top for logo','wpestate').'</div>
    <div class="option_row_explain">'.__('Add logo margin top (number only)','wpestate').'</div>    
        <input type="text" id="logo_margin" name="logo_margin" value="'.$logo_margin.'"> 
    </div>';

    
     /* 
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Retina Logo','wpestate').'</div>
        <div class="option_row_explain">'.__('Retina ready logo (add _2x after the name. For ex logo_2x.jpg) ','wpestate').'</div>    
            <input id="footer_logo_image" type="text" size="36" name="footer_logo_image" value="'.$footer_logo_image.'" />
            <input id="footer_logo_image_button" type="button"  class="upload_button button" value="'.__('Upload Logo','wpestate').'" />
       
    </div>';
     */

    print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>';
       
}
endif; // end new_wpestate_theme_admin_logos_favicon

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Header
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('new_wpestate_header_settings') ):
function new_wpestate_header_settings(){
    $cache_array                =   array('yes','no');
     
    
    $header_array = array(
            'none',
            'image',
            'theme slider',
            'revolution slider',
            'google map'
        );
    
    $header_type    =   get_option('wp_estate_header_type','');
    $header_select  =   '';
    
    foreach($header_array as $key=>$value){
        $header_select.='<option value="'.$key.'" ';
        if($key==$header_type){
            $header_select.=' selected="selected" ';
        }
        $header_select.='>'.$value.'</option>'; 
    }
    
    
    $user_header_type    =   get_option('wp_estate_user_header_type','');
    $user_header_select  =   '';
    
    foreach($header_array as $key=>$value){
        $user_header_select.='<option value="'.$key.'" ';
        if($key==$user_header_type){
            $user_header_select.=' selected="selected" ';
        }
        $user_header_select.='>'.$value.'</option>'; 
    }
    
    
    
    $transparent_menu           =   get_option('wp_estate_transparent_menu','');
    $transparent_menu_select    =   '';
    
    foreach($cache_array as $value){
            $transparent_menu_select.='<option value="'.$value.'"';
            if ($transparent_menu==$value){
                    $transparent_menu_select.=' selected="selected" ';
            }
            $transparent_menu_select.='>'.$value.'</option>';
    }
    
    
    $transparent_menu = get_option('wp_estate_transparent_menu_listing','');
    $transparent_menu_select_listing='';
    
     foreach($cache_array as $value){
            $transparent_menu_select_listing.='<option value="'.$value.'"';
            if ($transparent_menu==$value){
                    $transparent_menu_select_listing.=' selected="selected" ';
            }
            $transparent_menu_select_listing.='>'.$value.'</option>';
    }
    //
    
    $show_top_bar_user_menu_symbol      = wpestate_dropdowns_theme_admin($cache_array,'show_top_bar_user_menu');
    
    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Show top bar widget menu ?','wpestate').'</div>
        <div class="option_row_explain">'.__('Enable or disable top bar widget area.','wpestate').'</div>    
           <select id="show_top_bar_user_menu" name="show_top_bar_user_menu">
                '.$show_top_bar_user_menu_symbol.'
            </select>
        </div>';

    $social_array               =   array('no','yes');
    $show_submit_symbol = '';
    $show_submit_status = esc_html(get_option('wp_estate_show_submit', ''));

    foreach ($social_array as $value) {
            $show_submit_symbol.='<option value="' . $value . '"';
            if ($show_submit_status == $value) {
                $show_submit_symbol.=' selected="selected" ';
            }
            $show_submit_symbol.='>' . $value . '</option>';
        }

    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Show submit property button in header?','wpestate').'</div>
        <div class="option_row_explain">'.__('Submit property will only work with theme register/login.','wpestate').'</div>    
            <select id="show_submit" name="show_submit">
                '.$show_submit_symbol.'
            </select>
        </div>';
    
    
    
    $show_top_bar_user_login_symbol     = wpestate_dropdowns_theme_admin($cache_array,'show_top_bar_user_login');
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Show user login menu in header?','wpestate').'</div>
        <div class="option_row_explain">'.__('Enable or disable the user login / register in header. ','wpestate').'</div>    
                <select id="show_top_bar_user_login" name="show_top_bar_user_login">
                    '.$show_top_bar_user_login_symbol.'
                </select>
        </div>';
          

    $header_array   =   array(
                            'none',
                            'image',
                            'theme slider',
                            'revolution slider',
                            'google map'
                            );
    $header_select   = wpestate_dropdowns_theme_admin_with_key($header_array,'header_type');

    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Media Header Type?','wpestate').'</div>
        <div class="option_row_explain">'.__('Select what media header to use globally.','wpestate').'</div>    
            <select id="header_type" name="header_type">
                '.$header_select.'
            </select>
        </div>';
    
    $header_array_logo  =   array(
                            'type1',
                            'type2'
                        );
    $logo_header_select   = wpestate_dropdowns_theme_admin($header_array_logo,'logo_header_type');

    
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Header Type?','wpestate').'</div>
    <div class="option_row_explain">'.__('Select header type.','wpestate').'</div>    
        <select id="logo_header_type" name="logo_header_type">
            '.$logo_header_select.'
        </select>
    </div>';
    
     $header_array_logo_align  =   array(
                            'left',
                            'center',
                            'right',
                        );
    
    $logo_header_align_select   = wpestate_dropdowns_theme_admin($header_array_logo_align,'logo_header_align');
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Header Align(Logo Position)?','wpestate').'</div>
    <div class="option_row_explain">'.__('Select header alignment.','wpestate').'</div>    
        <select id="logo_header_align" name="logo_header_align">
            '.$logo_header_align_select.'
        </select>
    </div>';
    
    $cache_array                =   array('no','yes');
    $wide_header_select  =   wpestate_dropdowns_theme_admin($cache_array,'wide_header');
    
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Wide Header ? ','wpestate').'</div>
    <div class="option_row_explain">'.__('make the header 100%.','wpestate').'</div>    
        <select id="wide_header" name="wide_header">
            '.$wide_header_select.'
        </select>
    </div>';
    
    
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Transparent Menu over Header?', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Don\'t use this option with header media none or google maps', 'wpestate') . '</div>    
            <select id="transparent_menu" name="transparent_menu">
                '.$transparent_menu_select.'
            </select>
        </div>';
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('For Properties page: Use Transparent Menu over Header?', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Overwrites the option for Transparent Menu over Header', 'wpestate') . '</div>    
            <select id="transparent_menu_listing" name="transparent_menu_listing">
                '.$transparent_menu_select_listing.'
             </select>
        </div>';
    
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Header Type for Owners page?', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Overwrites the global header type option', 'wpestate') . '</div>    
            <select id="user_header_type" name="user_header_type">
                '.$user_header_select.'
            </select>
        </div>';




   
    $global_revolution_slider   =   get_option('wp_estate_global_revolution_slider','');
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Global Revolution Slider','wpestate').'</div>
    <div class="option_row_explain">'.__('If media header is set to Revolution Slider, type the slider name and save.','wpestate').'</div>    
        <input type="text" id="global_revolution_slider" name="global_revolution_slider" value="'.$global_revolution_slider.'">   
    </div>';
    
    
    $global_header              =   get_option('wp_estate_global_header','');
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Global Header Static Image','wpestate').'</div>
    <div class="option_row_explain">'.__('If media header is set to image, add the image below. ','wpestate').'</div>    
        <input id="global_header" type="text" size="36" name="global_header" value="'.$global_header.'" />
        <input id="global_header_button" type="button"  class="upload_button button" value="'.__('Upload Header Image','wpestate').'" />
    </div>';
    
    $use_upload_tax_page_symbol='';
    $use_upload_tax_page_status= esc_html ( get_option('wp_estate_use_upload_tax_page','') );

    
    
    
    foreach($cache_array as $value){
            $use_upload_tax_page_symbol.='<option value="'.$value.'"';
            if ($use_upload_tax_page_status==$value){
                    $use_upload_tax_page_symbol.=' selected="selected" ';
            }
            $use_upload_tax_page_symbol.='>'.$value.'</option>';
    }
    
    print'<div class="estate_option_row">
    <div class="label_option_row">' . __('Use uploaded Image for City and Area taxonomy page Header?', 'wpestate') . '</div>
    <div class="option_row_explain">' . __('Works with Taxonomy set to Standard type', 'wpestate') . '</div>    
        <select id="use_upload_tax_page" name="use_upload_tax_page">
            '.$use_upload_tax_page_symbol.'
        </select>
    </div>';
    
    print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>';
       
}
endif; // end new_wpestate_header_settings



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Footer
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('new_wpestate_footer_settings') ):
function new_wpestate_footer_settings(){
   
    $footer_background          =   get_option('wp_estate_footer_background','');
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Background for Footer', 'wpestate').'</div>
    <div class="option_row_explain">'.__('Insert background footer image below.', 'wpestate').'</div>    
        <input id="footer_background" type="text" size="36" name="footer_background" value="'.$footer_background.'" />
        <input id="footer_background_button" type="button"  class="upload_button button" value="'.__('Upload Background Image for Footer', 'wpestate').'" />
                 
    </div>';
    
    $repeat_array=array('repeat','repeat x','repeat y','no repeat');
    $repeat_footer_back_symbol  = wpestate_dropdowns_theme_admin($repeat_array,'repeat_footer_back');

    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Repeat Footer background ?','wpestate').'</div>
    <div class="option_row_explain">'.__('Set repeat options for background footer image.','wpestate').'</div>    
        <select id="repeat_footer_back" name="repeat_footer_back">
            '.$repeat_footer_back_symbol.'
        </select>     
    </div>';
    
    $cache_array                =   array('no','yes');
    $wide_footer_select  =   wpestate_dropdowns_theme_admin($cache_array,'wide_footer');
    
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Wide Footer ? ','wpestate').'</div>
    <div class="option_row_explain">'.__('make the footer 100%.','wpestate').'</div>    
        <select id="wide_footer" name="wide_footer">
            '.$wide_footer_select.'
        </select>
    </div>';
    
    $copyright_message = esc_html(stripslashes(get_option('wp_estate_copyright_message', '')));
     
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Copyright Message', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Type here the copyright message that will appear in footer. Add only text.', 'wpestate') . '</div>    
            <textarea cols="57" rows="2" id="copyright_message" name="copyright_message">' . $copyright_message . '</textarea></td>  
        </div>';

        print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>';
       
}
endif; // end new_wpestate_footer_settings





/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Social Accounts
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('new_wpestate_theme_social_accounts') ):
function new_wpestate_theme_social_accounts(){
    
    $facebook_link              =   esc_html ( get_option('wp_estate_facebook_link','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Facebook Link','wpestate').'</div>
        <div class="option_row_explain">'.__('Facebook page url, with https://','wpestate').'</div>    
            <input id="facebook_link" type="text" size="36" name="facebook_link" value="'.$facebook_link.'" />
        </div>';
    
      
    $twitter_link               =   esc_html ( get_option('wp_estate_twitter_link','') );
      print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Twitter page link','wpestate').'</div>
        <div class="option_row_explain">'.__('Twitter page link, with https://','wpestate').'</div>    
           <input id="twitter_link" type="text" size="36" name="twitter_link" value="'.$twitter_link.'" />
        </div>';
      
      
    $google_link                =   esc_html ( get_option('wp_estate_google_link','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Google+ Link','wpestate').'</div>
        <div class="option_row_explain">'.__('Google+ page link, with https://','wpestate').'</div>    
           <input id="google_link" type="text" size="36" name="google_link" value="'.$google_link.'" />
        </div>';
      
    $linkedin_link              =   esc_html ( get_option('wp_estate_linkedin_link','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Linkedin Link','wpestate').'</div>
        <div class="option_row_explain">'.__(' Linkedin page link, with https://','wpestate').'</div>    
            <input id="linkedin_link" type="text" size="36" name="linkedin_link" value="'.$linkedin_link.'" />
        </div>';
      
    $pinterest_link             =   esc_html ( get_option('wp_estate_pinterest_link','') );  
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Pinterest Link','wpestate').'</div>
        <div class="option_row_explain">'.__('Pinterest page link, with https://','wpestate').'</div>    
            <input id="pinterest_link" type="text" size="36" name="pinterest_link" value="'.$pinterest_link.'" />
        </div>';
      
  
      
    $twitter_consumer_key       =   esc_html ( get_option('wp_estate_twitter_consumer_key','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Twitter consumer_key','wpestate').'</div>
        <div class="option_row_explain">'.__('Twitter consumer_key is required for theme Twitter widget.','wpestate').'</div>    
            <input id="twitter_consumer_key" type="text" size="36" name="twitter_consumer_key" value="'.$twitter_consumer_key.'" />
        </div>';

    $twitter_consumer_secret    =   esc_html ( get_option('wp_estate_twitter_consumer_secret','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Twitter Consumer Secret','wpestate').'</div>
        <div class="option_row_explain">'.__('Twitter Consumer Secret is required for theme Twitter widget.','wpestate').'</div>    
            <input id="twitter_consumer_secret" type="text" size="36" name="twitter_consumer_secret" value="'.$twitter_consumer_secret.'" />
        </div>';
      
    $twitter_access_token       =   esc_html ( get_option('wp_estate_twitter_access_token','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Twitter Access Token','wpestate').'</div>
        <div class="option_row_explain">'.__('Twitter Access Token is required for theme Twitter widget.','wpestate').'</div>    
            <input id="twitter_account" type="text" size="36" name="twitter_access_token" value="'.$twitter_access_token.'" />
        </div>';
      
    $twitter_access_secret      =   esc_html ( get_option('wp_estate_twitter_access_secret','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Twitter Access Token Secret','wpestate').'</div>
        <div class="option_row_explain">'.__('Twitter Access Token Secret is required for theme Twitter widget.','wpestate').'</div>    
            <input id="twitter_access_secret" type="text" size="36" name="twitter_access_secret" value="'.$twitter_access_secret.'" />
        </div>';
      
    
    $twitter_cache_time         =   intval   ( get_option('wp_estate_twitter_cache_time','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Twitter Cache Time','wpestate').'</div>
        <div class="option_row_explain">'.__('Twitter Cache Time','wpestate').'</div>    
           <input id="twitter_cache_time" type="text" size="36" name="twitter_cache_time" value="'.$twitter_cache_time.'" />
        </div>';
      
    //      
    
    $facebook_api               =   esc_html ( get_option('wp_estate_facebook_api','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Facebook Api key','wpestate').'</div>
        <div class="option_row_explain">'.__('Facebook Api key is required for Facebook login.','wpestate').'</div>    
            <input id="facebook_api" type="text" size="36" name="facebook_api" value="'.$facebook_api.'" />
        </div>';
      
    
    $facebook_secret            =   esc_html ( get_option('wp_estate_facebook_secret','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Facebook Secret','wpestate').'</div>
        <div class="option_row_explain">'.__('Facebook Secret is required for Facebook login.','wpestate').'</div>    
            <input id="facebook_secret" type="text" size="36" name="facebook_secret" value="'.$facebook_secret.'" />
        </div>';
      
    
    $google_oauth_api           =   esc_html ( get_option('wp_estate_google_oauth_api','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Google Oauth Api','wpestate').'</div>
        <div class="option_row_explain">'.__('Google Oauth Api is required for Google Login','wpestate').'</div>    
            <input id="google_oauth_api" type="text" size="36" name="google_oauth_api" value="'.$google_oauth_api.'" />
        </div>';
      
    $google_oauth_client_secret =   esc_html ( get_option('wp_estate_google_oauth_client_secret','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Google Oauth Client Secret','wpestate').'</div>
        <div class="option_row_explain">'.__('Google Oauth Client Secret is required for Google Login.','wpestate').'</div>    
            <input id="google_oauth_client_secret" type="text" size="36" name="google_oauth_client_secret" value="'.$google_oauth_client_secret.'" />
        </div>';
      
    $google_api_key             =   esc_html ( get_option('wp_estate_google_api_key','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Google api key','wpestate').'</div>
        <div class="option_row_explain">'.__('Google api key is required for Google Login.','wpestate').'</div>    
            <input id="google_api_key" type="text" size="36" name="google_api_key" value="'.$google_api_key.'" />
        </div>';
      
    //
    $social_array               =   array('no','yes');
   
    $facebook_login_select      = wpestate_dropdowns_theme_admin($social_array,'facebook_login');
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Allow login via Facebook ? ','wpestate').'</div>
        <div class="option_row_explain">'.__('Enable or disable Facebook login. ','wpestate').'</div>    
            <select id="facebook_login" name="facebook_login">
                '.$facebook_login_select.'
            </select>
        </div>';
      
      
    $google_login_select        = wpestate_dropdowns_theme_admin($social_array,'google_login');
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Allow login via Google ?','wpestate').'</div>
        <div class="option_row_explain">'.__('Enable or disable Google login.','wpestate').'</div>    
            <select id="google_login" name="google_login">
                '.$google_login_select.'
            </select>
        </div>';
      
    $yahoo_login_select         = wpestate_dropdowns_theme_admin($social_array,'yahoo_login');
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Allow login via Yahoo ?','wpestate').'</div>
        <div class="option_row_explain">'.__('Enable or disable Yahoo login.','wpestate').'</div>    
            <select id="yahoo_login" name="yahoo_login">
                '.$yahoo_login_select.'
            </select>
        </div>';
    
    $social_register_select='';
    $social_register_on  =   esc_html( get_option('wp_estate_social_register_on','') );

    foreach($social_array as $value){
            $social_register_select.='<option value="'.$value.'"';
            if ($social_register_on==$value){
                $social_register_select.=' selected="selected" ';
            }
            $social_register_select.='>'.$value.'</option>';
    }


    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Display social login also on register modal window ?','wpestate').'</div>
        <div class="option_row_explain">'.__('Enable or disable social login also on register modal window','wpestate').'</div>    
            <select id="social_register_on" name="social_register_on">
                        '.$social_register_select.'
                    </select>
        </div>';
    
      
    
    print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>';
}
endif;//end new_wpestate_theme_social_accounts

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  Custom Colors Settings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( !function_exists('new_wpestate_custom_colors') ):      
function new_wpestate_custom_colors(){
    $main_color                     =  esc_html ( get_option('wp_estate_main_color','') );
    $background_color               =  esc_html ( get_option('wp_estate_background_color','') );
    $content_back_color             =  esc_html ( get_option('wp_estate_content_back_color','') );
    $header_color                   =  esc_html ( get_option('wp_estate_header_color','') );
  
    $breadcrumbs_font_color         =  esc_html ( get_option('wp_estate_breadcrumbs_font_color','') );
    $font_color                     =  esc_html ( get_option('wp_estate_font_color','') );
    $link_color                     =  esc_html ( get_option('wp_estate_link_color','') );
    $headings_color                 =  esc_html ( get_option('wp_estate_headings_color','') );
  
    $footer_back_color              =  esc_html ( get_option('wp_estate_footer_back_color','') );
    $footer_font_color              =  esc_html ( get_option('wp_estate_footer_font_color','') );
    $footer_copy_color              =  esc_html ( get_option('wp_estate_footer_copy_color','') );
    $sidebar_widget_color           =  esc_html ( get_option('wp_estate_sidebar_widget_color','') );
    $sidebar_heading_color          =  esc_html ( get_option('wp_estate_sidebar_heading_color','') );
    $sidebar_heading_boxed_color    =  esc_html ( get_option('wp_estate_sidebar_heading_boxed_color','') );
    $menu_font_color                =  esc_html ( get_option('wp_estate_menu_font_color','') );
    $menu_hover_back_color          =  esc_html ( get_option('wp_estate_menu_hover_back_color','') );
    $menu_hover_font_color          =  esc_html ( get_option('wp_estate_menu_hover_font_color','') );
    $agent_color                    =  esc_html ( get_option('wp_estate_agent_color','') );
    $sidebar2_font_color            =  esc_html ( get_option('wp_estate_sidebar2_font_color','') );
    $top_bar_back                   =  esc_html ( get_option('wp_estate_top_bar_back','') );
    $top_bar_font                   =  esc_html ( get_option('wp_estate_top_bar_font','') );
    $adv_search_back_color          =  esc_html ( get_option('wp_estate_adv_search_back_color ','') );
    $adv_search_font_color          =  esc_html ( get_option('wp_estate_adv_search_font_color','') );  
    $box_content_back_color         =  esc_html ( get_option('wp_estate_box_content_back_color','') );
    $box_content_border_color       =  esc_html ( get_option('wp_estate_box_content_border_color','') );
    
    $hover_button_color       =  esc_html ( get_option('wp_estate_hover_button_color ','') );
    
    $custom_css                     =  esc_html ( stripslashes( get_option('wp_estate_custom_css','') ) );
    
    $color_scheme_select ='';
    $color_scheme= esc_html ( get_option('wp_estate_color_scheme','') );
    $color_scheme_array=array('no','yes');

    foreach($color_scheme_array as $value){
            $color_scheme_select.='<option value="'.$value.'"';
            if ($color_scheme==$value){
                $color_scheme_select.='selected="selected"';
            }
            $color_scheme_select.='>'.$value.'</option>';
    }

    
    $on_child_theme= esc_html ( get_option('wp_estate_on_child_theme','') );
    
    $on_child_theme_symbol='';
    if($on_child_theme==1){
        $on_child_theme_symbol = " checked ";
    }
    		 
//    print'<div class="estate_option_row">
//        <div class="label_option_row">'.__('Use Custom Colors ?', 'wpestate').'</div>
//        <div class="option_row_explain">'.__('You must set YES and save for your custom colors to apply.', 'wpestate').'</div>    
//            <select id="color_scheme" name="color_scheme">
//                   '.$color_scheme_select.'
//                </select>  
//        </div>';
    

        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('On save, give me the css code to save in child theme style.css ?', 'wpestate').'</div>
            <div class="option_row_explain">'.__('*Recommended option', 'wpestate').'</div> 
                <input type="hidden"  name="on_child_theme" value="0" id="on_child_theme">
                <input type="checkbox" '.$on_child_theme_symbol.' name="on_child_theme" class="admin_checker" value="1" id="on_child_theme"></br>
                '.esc_html__('If you use this option, you will need to copy / paste the code that will apear when you click save, and use the code in child theme style.css. The colors will NOT change otherwise!','wpestate').'

            </div>';
      
       
                
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Main Color', 'wpestate').'</div>
            <div class="option_row_explain">'.__('Main Color', 'wpestate').'</div>    
                <input type="text" name="main_color" maxlength="7" class="inptxt " value="'.$main_color.'"/>
            	<div id="main_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$main_color.';"  ></div></div>
            </div>';


        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Background Color', 'wpestate').'</div>
            <div class="option_row_explain">'.__('Background Color', 'wpestate').'</div>    
                <input type="text" name="background_color" maxlength="7" class="inptxt " value="'.$background_color.'"/>
            	<div id="background_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$background_color.';"  ></div></div>
            </div>';
   
//         <!--
//        <tr valign="top">
//            <th scope="row"><label for="content_back_color">'.esc_html__( 'Content Background Color','wpestate').'</label></th>
//            <td>
//                <input type="text" name="content_back_color" value="'.$content_back_color.'" maxlength="7" class="inptxt" />
//            	<div id="content_back_color" class="colorpickerHolder" ><div class="sqcolor"  style="background-color:#'.$content_back_color.';" ></div></div>
//            </td>
//        </tr> -->
        
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Breadcrumbs, Meta and Property Info Font Color', 'wpestate').'</div>
            <div class="option_row_explain">'.__('Breadcrumbs, Meta and Property Info Font Color', 'wpestate').'</div>    
                <input type="text" name="breadcrumbs_font_color" value="'.$breadcrumbs_font_color.'" maxlength="7" class="inptxt" />
            	<div id="breadcrumbs_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$breadcrumbs_font_color.';" ></div></div>
            </div>';
        
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Font Color', 'wpestate').'</div>
            <div class="option_row_explain">'.__('Font Color', 'wpestate').'</div>    
                <input type="text" name="font_color" value="'.$font_color.'" maxlength="7" class="inptxt" />
            	<div id="font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$font_color.';" ></div></div>
            </div>';
                
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Link Color', 'wpestate').'</div>
            <div class="option_row_explain">'.__('Link Color', 'wpestate').'</div>    
                <input type="text" name="link_color" value="'.$link_color.'" maxlength="7" class="inptxt" />
            	<div id="link_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$link_color.';" ></div></div>
            </div>';
               
         print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Headings Color', 'wpestate').'</div>
            <div class="option_row_explain">'.__('Headings Color', 'wpestate').'</div>    
                <input type="text" name="headings_color" value="'.$headings_color.'" maxlength="7" class="inptxt" />
            	<div id="headings_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$headings_color.';" ></div></div>
             </div>';
         
        print'<div class="estate_option_row">
          <div class="label_option_row">'.__('Footer Background Color', 'wpestate').'</div>
          <div class="option_row_explain">'.__('Footer Background Color', 'wpestate').'</div>    
                <input type="text" name="footer_back_color" value="'.$footer_back_color.'" maxlength="7" class="inptxt" />
            	<div id="footer_back_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$footer_back_color.';" ></div></div>
            </div>';
          
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Footer Font Color', 'wpestate').'</div>
            <div class="option_row_explain">'.__('Footer Font Color', 'wpestate').'</div>    
                <input type="text" name="footer_font_color" value="'.$footer_font_color.'" maxlength="7" class="inptxt" />
            	<div id="footer_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$footer_font_color.';" ></div></div>
            </div>';

        print'<div class="estate_option_row">
           <div class = "label_option_row">'.__('Footer Copyright Font Color', 'wpestate').'</div>
           <div class = "option_row_explain">'.__('Footer Copyright Font Color', 'wpestate').'</div>
               <input type="text" name="footer_copy_color" value="'.$footer_copy_color.'" maxlength="7" class="inptxt" />
               <div id="footer_copy_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$footer_copy_color.';" ></div></div>
           </div > ';
        
        print'<div class="estate_option_row">
           <div class = "label_option_row">'.__('Sidebar Widget Background Color( for "boxed" widgets)', 'wpestate').'</div>
           <div class = "option_row_explain">'.__('Sidebar Widget Background Color( for "boxed" widgets)', 'wpestate').'</div>
               <input type="text" name="sidebar_widget_color" value="'.$sidebar_widget_color.'" maxlength="7" class="inptxt" />
               <div id="sidebar_widget_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$sidebar_widget_color.';" ></div></div>
            </div > ';

        print'<div class="estate_option_row">
            <div class = "label_option_row">'.__('Sidebar Heading Color (boxed widgets)', 'wpestate').'</div>
            <div class = "option_row_explain">'.__('Sidebar Heading Color (boxed widgets)', 'wpestate').'</div>
                <input type="text" name="sidebar_heading_boxed_color" value="'.$sidebar_heading_boxed_color.'" maxlength="7" class="inptxt" />
            	<div id="sidebar_heading_boxed_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$sidebar_heading_boxed_color.';"></div></div>
            </div > ';

        print'<div class="estate_option_row">
            <div class = "label_option_row">'.__('Sidebar Heading Color', 'wpestate').'</div>
            <div class = "option_row_explain">'.__('Sidebar Heading Color', 'wpestate').'</div>
                <input type="text" name="sidebar_heading_color" value="'.$sidebar_heading_color.'" maxlength="7" class="inptxt" />
            	<div id="sidebar_heading_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$sidebar_heading_color.';"></div></div>
            </div > ';

         
        print'<div class="estate_option_row">
            <div class = "label_option_row">'.__('Sidebar Font color', 'wpestate').'</div>
            <div class = "option_row_explain">'.__('Sidebar Font color', 'wpestate').'</div>
                <input type="text" name="sidebar2_font_color" value="'.$sidebar2_font_color.'" maxlength="7" class="inptxt" />
            	<div id="sidebar2_font_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$sidebar2_font_color.';"></div></div>
            </div > ';


        print'<div class="estate_option_row">
            <div class = "label_option_row">'.__('Header Background Color', 'wpestate').'</div>
            <div class = "option_row_explain">'.__('Header Background Color', 'wpestate').'</div>
                <input type="text" name="header_color" value="'.$header_color.'" maxlength="7" class="inptxt" />
            	<div id="header_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$header_color.';" ></div></div>
            </div > ';


//        print'<div class="estate_option_row">
//            <div class = "label_option_row">'.__('Top Menu Font Color', 'wpestate').'</div>
//            <div class = "option_row_explain">'.__('Top Menu Font Color', 'wpestate').'</div>
//                <input type="text" name="menu_font_color" value="'.$menu_font_color.'"  maxlength="7" class="inptxt" />
//            	<div id="menu_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$menu_font_color.';" ></div></div>
//            </div > ';
        
//        print'<div class="estate_option_row">
//            <div class = "label_option_row">'.__('Top Menu - submenu background color', 'wpestate').'</div>
//            <div class = "option_row_explain">'.__('Top Menu - submenu background color', 'wpestate').'</div>
//                <input type="text" name="menu_hover_back_color" value="'.$menu_hover_back_color.'"  maxlength="7" class="inptxt" />
//           	<div id="menu_hover_back_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$menu_hover_back_color.';"></div></div>
//            </div > ';
 
//        print'<div class="estate_option_row">
//            <div class = "label_option_row">'.__('Top Menu hover font color', 'wpestate').'</div>
//            <div class = "option_row_explain">'.__('Top Menu hover font color', 'wpestate').'</div>
//                <input type="text" name="menu_hover_font_color" value="'.$menu_hover_font_color.'" maxlength="7" class="inptxt" />
//            	<div id="menu_hover_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$menu_hover_font_color.';" ></div></div>
//            </div > ';

        print'<div class="estate_option_row">
            <div class = "label_option_row">'.__('Top Bar Background Color (Header Widget Menu)', 'wpestate').'</div>
            <div class = "option_row_explain">'.__('Top Bar Background Color (Header Widget Menu)', 'wpestate').'</div>
                <input type="text" name="top_bar_back" value="'.$top_bar_back.'" maxlength="7" class="inptxt" />
            	<div id="top_bar_back" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$top_bar_back.';"></div></div>
            </div > ';

        print'<div class="estate_option_row">
            <div class = "label_option_row">'.__('Top Bar Font Color (Header Widget Menu)', 'wpestate').'</div>
            <div class = "option_row_explain">'.__('Top Bar Font Color (Header Widget Menu)', 'wpestate').'</div>
                <input type="text" name="top_bar_font" value="'.$top_bar_font.'" maxlength="7" class="inptxt" />
            	<div id="top_bar_font" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$top_bar_font.';"></div></div>
            </div > ';

        print'<div class="estate_option_row">
            <div class = "label_option_row">'.__('Boxed Content Background Color', 'wpestate').'</div>
            <div class = "option_row_explain">'.__('Boxed Content Background Color', 'wpestate').'</div>
                 <input type="text" name="box_content_back_color" value="'.$box_content_back_color.'" maxlength="7" class="inptxt" />
            	<div id="box_content_back_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$box_content_back_color.';"></div></div>
            </div > ';
        
        print'<div class="estate_option_row">
           <div class = "label_option_row">'.__('Border Color', 'wpestate').'</div>
           <div class = "option_row_explain">'.__('Border Color', 'wpestate').'</div>
                <input type="text" name="box_content_border_color" value="'.$box_content_border_color.'" maxlength="7" class="inptxt" />
               <div id="box_content_border_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$box_content_border_color.';"></div></div>
           </div > ';
         
        print'<div class="estate_option_row">
            <div class = "label_option_row">'.__('Hover Button Color', 'wpestate').'</div>
            <div class = "option_row_explain">'.__('Hover Button Color', 'wpestate').'</div>
                <input type="text" name="hover_button_color" value="'.$hover_button_color.'" maxlength="7" class="inptxt" />
            	<div id="hover_button_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$hover_button_color.';"></div></div>
            </div > ';
    
        print'<div class="estate_option_row">
            <div class="label_option_row">'.__('Custom Css','wpestate').'</div>
            <div class="option_row_explain">'.__('Overwrite theme css using custom css.','wpestate').'</div>    
                <textarea cols="57" rows="5" name="custom_css" id="custom_css">'.$custom_css.'</textarea>
            </div>';
        
        print ' <div class="estate_option_row_submit">
            <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
            </div>';
         
    $on_child_theme= esc_html ( get_option('wp_estate_on_child_theme','') );
    
    print'<div class="" id="css_modal" tabindex="-1">
            <div class="css_modal_close">x</div>
            <textarea onclick="this.focus();this.select()" class="modal-content">';
      
            $general_font   = esc_html(get_option('wp_estate_general_font', ''));
            if ( $general_font != '' && $general_font != 'x'){
                require_once get_template_directory().'/libs/custom_general_font.php';
            }
            require_once get_template_directory().'/libs/customcss.php';
      
    print '</textarea><span style="margin-left:30px;">'.esc_html__('Copy the above code and add it into your child theme style.css','wpestate').'</span>'; 
    
    
    print '</div>';
    
    
    
}
endif;// end new_wpestate_custom_colors();



////////////////////////////////
//Fonts
////////////////////////////////////


if( !function_exists('new_wpestate_custom_fonts();') ):
function new_wpestate_custom_fonts(){
$google_fonts_array = array(                          
                                                            "Abel" => "Abel",
                                                            "Abril Fatface" => "Abril Fatface",
                                                            "Aclonica" => "Aclonica",
                                                            "Acme" => "Acme",
                                                            "Actor" => "Actor",
                                                            "Adamina" => "Adamina",
                                                            "Advent Pro" => "Advent Pro",
                                                            "Aguafina Script" => "Aguafina Script",
                                                            "Aladin" => "Aladin",
                                                            "Aldrich" => "Aldrich",
                                                            "Alegreya" => "Alegreya",
                                                            "Alegreya SC" => "Alegreya SC",
                                                            "Alex Brush" => "Alex Brush",
                                                            "Alfa Slab One" => "Alfa Slab One",
                                                            "Alice" => "Alice",
                                                            "Alike" => "Alike",
                                                            "Alike Angular" => "Alike Angular",
                                                            "Allan" => "Allan",
                                                            "Allerta" => "Allerta",
                                                            "Allerta Stencil" => "Allerta Stencil",
                                                            "Allura" => "Allura",
                                                            "Almendra" => "Almendra",
                                                            "Almendra SC" => "Almendra SC",
                                                            "Amaranth" => "Amaranth",
                                                            "Amatic SC" => "Amatic SC",
                                                            "Amethysta" => "Amethysta",
                                                            "Andada" => "Andada",
                                                            "Andika" => "Andika",
                                                            "Angkor" => "Angkor",
                                                            "Annie Use Your Telescope" => "Annie Use Your Telescope",
                                                            "Anonymous Pro" => "Anonymous Pro",
                                                            "Antic" => "Antic",
                                                            "Antic Didone" => "Antic Didone",
                                                            "Antic Slab" => "Antic Slab",
                                                            "Anton" => "Anton",
                                                            "Arapey" => "Arapey",
                                                            "Arbutus" => "Arbutus",
                                                            "Architects Daughter" => "Architects Daughter",
                                                            "Arimo" => "Arimo",
                                                            "Arizonia" => "Arizonia",
                                                            "Armata" => "Armata",
                                                            "Artifika" => "Artifika",
                                                            "Arvo" => "Arvo",
                                                            "Asap" => "Asap",
                                                            "Asset" => "Asset",
                                                            "Astloch" => "Astloch",
                                                            "Asul" => "Asul",
                                                            "Atomic Age" => "Atomic Age",
                                                            "Aubrey" => "Aubrey",
                                                            "Audiowide" => "Audiowide",
                                                            "Average" => "Average",
                                                            "Averia Gruesa Libre" => "Averia Gruesa Libre",
                                                            "Averia Libre" => "Averia Libre",
                                                            "Averia Sans Libre" => "Averia Sans Libre",
                                                            "Averia Serif Libre" => "Averia Serif Libre",
                                                            "Bad Script" => "Bad Script",
                                                            "Balthazar" => "Balthazar",
                                                            "Bangers" => "Bangers",
                                                            "Basic" => "Basic",
                                                            "Battambang" => "Battambang",
                                                            "Baumans" => "Baumans",
                                                            "Bayon" => "Bayon",
                                                            "Belgrano" => "Belgrano",
                                                            "Belleza" => "Belleza",
                                                            "Bentham" => "Bentham",
                                                            "Berkshire Swash" => "Berkshire Swash",
                                                            "Bevan" => "Bevan",
                                                            "Bigshot One" => "Bigshot One",
                                                            "Bilbo" => "Bilbo",
                                                            "Bilbo Swash Caps" => "Bilbo Swash Caps",
                                                            "Bitter" => "Bitter",
                                                            "Black Ops One" => "Black Ops One",
                                                            "Bokor" => "Bokor",
                                                            "Bonbon" => "Bonbon",
                                                            "Boogaloo" => "Boogaloo",
                                                            "Bowlby One" => "Bowlby One",
                                                            "Bowlby One SC" => "Bowlby One SC",
                                                            "Brawler" => "Brawler",
                                                            "Bree Serif" => "Bree Serif",
                                                            "Bubblegum Sans" => "Bubblegum Sans",
                                                            "Buda" => "Buda",
                                                            "Buenard" => "Buenard",
                                                            "Butcherman" => "Butcherman",
                                                            "Butterfly Kids" => "Butterfly Kids",
                                                            "Cabin" => "Cabin",
                                                            "Cabin Condensed" => "Cabin Condensed",
                                                            "Cabin Sketch" => "Cabin Sketch",
                                                            "Caesar Dressing" => "Caesar Dressing",
                                                            "Cagliostro" => "Cagliostro",
                                                            "Calligraffitti" => "Calligraffitti",
                                                            "Cambo" => "Cambo",
                                                            "Candal" => "Candal",
                                                            "Cantarell" => "Cantarell",
                                                            "Cantata One" => "Cantata One",
                                                            "Cardo" => "Cardo",
                                                            "Carme" => "Carme",
                                                            "Carter One" => "Carter One",
                                                            "Caudex" => "Caudex",
                                                            "Cedarville Cursive" => "Cedarville Cursive",
                                                            "Ceviche One" => "Ceviche One",
                                                            "Changa One" => "Changa One",
                                                            "Chango" => "Chango",
                                                            "Chau Philomene One" => "Chau Philomene One",
                                                            "Chelsea Market" => "Chelsea Market",
                                                            "Chenla" => "Chenla",
                                                            "Cherry Cream Soda" => "Cherry Cream Soda",
                                                            "Chewy" => "Chewy",
                                                            "Chicle" => "Chicle",
                                                            "Chivo" => "Chivo",
                                                            "Coda" => "Coda",
                                                            "Coda Caption" => "Coda Caption",
                                                            "Codystar" => "Codystar",
                                                            "Comfortaa" => "Comfortaa",
                                                            "Coming Soon" => "Coming Soon",
                                                            "Concert One" => "Concert One",
                                                            "Condiment" => "Condiment",
                                                            "Content" => "Content",
                                                            "Contrail One" => "Contrail One",
                                                            "Convergence" => "Convergence",
                                                            "Cookie" => "Cookie",
                                                            "Copse" => "Copse",
                                                            "Corben" => "Corben",
                                                            "Cousine" => "Cousine",
                                                            "Coustard" => "Coustard",
                                                            "Covered By Your Grace" => "Covered By Your Grace",
                                                            "Crafty Girls" => "Crafty Girls",
                                                            "Creepster" => "Creepster",
                                                            "Crete Round" => "Crete Round",
                                                            "Crimson Text" => "Crimson Text",
                                                            "Crushed" => "Crushed",
                                                            "Cuprum" => "Cuprum",
                                                            "Cutive" => "Cutive",
                                                            "Damion" => "Damion",
                                                            "Dancing Script" => "Dancing Script",
                                                            "Dangrek" => "Dangrek",
                                                            "Dawning of a New Day" => "Dawning of a New Day",
                                                            "Days One" => "Days One",
                                                            "Delius" => "Delius",
                                                            "Delius Swash Caps" => "Delius Swash Caps",
                                                            "Delius Unicase" => "Delius Unicase",
                                                            "Della Respira" => "Della Respira",
                                                            "Devonshire" => "Devonshire",
                                                            "Didact Gothic" => "Didact Gothic",
                                                            "Diplomata" => "Diplomata",
                                                            "Diplomata SC" => "Diplomata SC",
                                                            "Doppio One" => "Doppio One",
                                                            "Dorsa" => "Dorsa",
                                                            "Dosis" => "Dosis",
                                                            "Dr Sugiyama" => "Dr Sugiyama",
                                                            "Droid Sans" => "Droid Sans",
                                                            "Droid Sans Mono" => "Droid Sans Mono",
                                                            "Droid Serif" => "Droid Serif",
                                                            "Duru Sans" => "Duru Sans",
                                                            "Dynalight" => "Dynalight",
                                                            "EB Garamond" => "EB Garamond",
                                                            "Eater" => "Eater",
                                                            "Economica" => "Economica",
                                                            "Electrolize" => "Electrolize",
                                                            "Emblema One" => "Emblema One",
                                                            "Emilys Candy" => "Emilys Candy",
                                                            "Engagement" => "Engagement",
                                                            "Enriqueta" => "Enriqueta",
                                                            "Erica One" => "Erica One",
                                                            "Esteban" => "Esteban",
                                                            "Euphoria Script" => "Euphoria Script",
                                                            "Ewert" => "Ewert",
                                                            "Exo" => "Exo",
                                                            "Expletus Sans" => "Expletus Sans",
                                                            "Fanwood Text" => "Fanwood Text",
                                                            "Fascinate" => "Fascinate",
                                                            "Fascinate Inline" => "Fascinate Inline",
                                                            "Federant" => "Federant",
                                                            "Federo" => "Federo",
                                                            "Felipa" => "Felipa",
                                                            "Fjord One" => "Fjord One",
                                                            "Flamenco" => "Flamenco",
                                                            "Flavors" => "Flavors",
                                                            "Fondamento" => "Fondamento",
                                                            "Fontdiner Swanky" => "Fontdiner Swanky",
                                                            "Forum" => "Forum",
                                                            "Francois One" => "Francois One",
                                                            "Fredericka the Great" => "Fredericka the Great",
                                                            "Fredoka One" => "Fredoka One",
                                                            "Freehand" => "Freehand",
                                                            "Fresca" => "Fresca",
                                                            "Frijole" => "Frijole",
                                                            "Fugaz One" => "Fugaz One",
                                                            "GFS Didot" => "GFS Didot",
                                                            "GFS Neohellenic" => "GFS Neohellenic",
                                                            "Galdeano" => "Galdeano",
                                                            "Gentium Basic" => "Gentium Basic",
                                                            "Gentium Book Basic" => "Gentium Book Basic",
                                                            "Geo" => "Geo",
                                                            "Geostar" => "Geostar",
                                                            "Geostar Fill" => "Geostar Fill",
                                                            "Germania One" => "Germania One",
                                                            "Give You Glory" => "Give You Glory",
                                                            "Glass Antiqua" => "Glass Antiqua",
                                                            "Glegoo" => "Glegoo",
                                                            "Gloria Hallelujah" => "Gloria Hallelujah",
                                                            "Goblin One" => "Goblin One",
                                                            "Gochi Hand" => "Gochi Hand",
                                                            "Gorditas" => "Gorditas",
                                                            "Goudy Bookletter 1911" => "Goudy Bookletter 1911",
                                                            "Graduate" => "Graduate",
                                                            "Gravitas One" => "Gravitas One",
                                                            "Great Vibes" => "Great Vibes",
                                                            "Gruppo" => "Gruppo",
                                                            "Gudea" => "Gudea",
                                                            "Habibi" => "Habibi",
                                                            "Hammersmith One" => "Hammersmith One",
                                                            "Handlee" => "Handlee",
                                                            "Hanuman" => "Hanuman",
                                                            "Happy Monkey" => "Happy Monkey",
                                                            "Henny Penny" => "Henny Penny",
                                                            "Herr Von Muellerhoff" => "Herr Von Muellerhoff",
                                                            "Holtwood One SC" => "Holtwood One SC",
                                                            "Homemade Apple" => "Homemade Apple",
                                                            "Homenaje" => "Homenaje",
                                                            "IM Fell DW Pica" => "IM Fell DW Pica",
                                                            "IM Fell DW Pica SC" => "IM Fell DW Pica SC",
                                                            "IM Fell Double Pica" => "IM Fell Double Pica",
                                                            "IM Fell Double Pica SC" => "IM Fell Double Pica SC",
                                                            "IM Fell English" => "IM Fell English",
                                                            "IM Fell English SC" => "IM Fell English SC",
                                                            "IM Fell French Canon" => "IM Fell French Canon",
                                                            "IM Fell French Canon SC" => "IM Fell French Canon SC",
                                                            "IM Fell Great Primer" => "IM Fell Great Primer",
                                                            "IM Fell Great Primer SC" => "IM Fell Great Primer SC",
                                                            "Iceberg" => "Iceberg",
                                                            "Iceland" => "Iceland",
                                                            "Imprima" => "Imprima",
                                                            "Inconsolata" => "Inconsolata",
                                                            "Inder" => "Inder",
                                                            "Indie Flower" => "Indie Flower",
                                                            "Inika" => "Inika",
                                                            "Irish Grover" => "Irish Grover",
                                                            "Istok Web" => "Istok Web",
                                                            "Italiana" => "Italiana",
                                                            "Italianno" => "Italianno",
                                                            "Jim Nightshade" => "Jim Nightshade",
                                                            "Jockey One" => "Jockey One",
                                                            "Jolly Lodger" => "Jolly Lodger",
                                                            "Josefin Sans" => "Josefin Sans",
                                                            "Josefin Slab" => "Josefin Slab",
                                                            "Judson" => "Judson",
                                                            "Julee" => "Julee",
                                                            "Junge" => "Junge",
                                                            "Jura" => "Jura",
                                                            "Just Another Hand" => "Just Another Hand",
                                                            "Just Me Again Down Here" => "Just Me Again Down Here",
                                                            "Kameron" => "Kameron",
                                                            "Karla" => "Karla",
                                                            "Kaushan Script" => "Kaushan Script",
                                                            "Kelly Slab" => "Kelly Slab",
                                                            "Kenia" => "Kenia",
                                                            "Khmer" => "Khmer",
                                                            "Knewave" => "Knewave",
                                                            "Kotta One" => "Kotta One",
                                                            "Koulen" => "Koulen",
                                                            "Kranky" => "Kranky",
                                                            "Kreon" => "Kreon",
                                                            "Kristi" => "Kristi",
                                                            "Krona One" => "Krona One",
                                                            "La Belle Aurore" => "La Belle Aurore",
                                                            "Lancelot" => "Lancelot",
                                                            "Lato" => "Lato",
                                                            "League Script" => "League Script",
                                                            "Leckerli One" => "Leckerli One",
                                                            "Ledger" => "Ledger",
                                                            "Lekton" => "Lekton",
                                                            "Lemon" => "Lemon",
                                                            "Lilita One" => "Lilita One",
                                                            "Limelight" => "Limelight",
                                                            "Linden Hill" => "Linden Hill",
                                                            "Lobster" => "Lobster",
                                                            "Lobster Two" => "Lobster Two",
                                                            "Londrina Outline" => "Londrina Outline",
                                                            "Londrina Shadow" => "Londrina Shadow",
                                                            "Londrina Sketch" => "Londrina Sketch",
                                                            "Londrina Solid" => "Londrina Solid",
                                                            "Lora" => "Lora",
                                                            "Love Ya Like A Sister" => "Love Ya Like A Sister",
                                                            "Loved by the King" => "Loved by the King",
                                                            "Lovers Quarrel" => "Lovers Quarrel",
                                                            "Luckiest Guy" => "Luckiest Guy",
                                                            "Lusitana" => "Lusitana",
                                                            "Lustria" => "Lustria",
                                                            "Macondo" => "Macondo",
                                                            "Macondo Swash Caps" => "Macondo Swash Caps",
                                                            "Magra" => "Magra",
                                                            "Maiden Orange" => "Maiden Orange",
                                                            "Mako" => "Mako",
                                                            "Marck Script" => "Marck Script",
                                                            "Marko One" => "Marko One",
                                                            "Marmelad" => "Marmelad",
                                                            "Marvel" => "Marvel",
                                                            "Mate" => "Mate",
                                                            "Mate SC" => "Mate SC",
                                                            "Maven Pro" => "Maven Pro",
                                                            "Meddon" => "Meddon",
                                                            "MedievalSharp" => "MedievalSharp",
                                                            "Medula One" => "Medula One",
                                                            "Megrim" => "Megrim",
                                                            "Merienda One" => "Merienda One",
                                                            "Merriweather" => "Merriweather",
                                                            "Metal" => "Metal",
                                                            "Metamorphous" => "Metamorphous",
                                                            "Metrophobic" => "Metrophobic",
                                                            "Michroma" => "Michroma",
                                                            "Miltonian" => "Miltonian",
                                                            "Miltonian Tattoo" => "Miltonian Tattoo",
                                                            "Miniver" => "Miniver",
                                                            "Miss Fajardose" => "Miss Fajardose",
                                                            "Modern Antiqua" => "Modern Antiqua",
                                                            "Molengo" => "Molengo",
                                                            "Monofett" => "Monofett",
                                                            "Monoton" => "Monoton",
                                                            "Monsieur La Doulaise" => "Monsieur La Doulaise",
                                                            "Montaga" => "Montaga",
                                                            "Montez" => "Montez",
                                                            "Montserrat" => "Montserrat",
                                                            "Moul" => "Moul",
                                                            "Moulpali" => "Moulpali",
                                                            "Mountains of Christmas" => "Mountains of Christmas",
                                                            "Mr Bedfort" => "Mr Bedfort",
                                                            "Mr Dafoe" => "Mr Dafoe",
                                                            "Mr De Haviland" => "Mr De Haviland",
                                                            "Mrs Saint Delafield" => "Mrs Saint Delafield",
                                                            "Mrs Sheppards" => "Mrs Sheppards",
                                                            "Muli" => "Muli",
                                                            "Mystery Quest" => "Mystery Quest",
                                                            "Neucha" => "Neucha",
                                                            "Neuton" => "Neuton",
                                                            "News Cycle" => "News Cycle",
                                                            "Niconne" => "Niconne",
                                                            "Nixie One" => "Nixie One",
                                                            "Nobile" => "Nobile",
                                                            "Nokora" => "Nokora",
                                                            "Norican" => "Norican",
                                                            "Nosifer" => "Nosifer",
                                                            "Nothing You Could Do" => "Nothing You Could Do",
                                                            "Noticia Text" => "Noticia Text",
                                                            "Nova Cut" => "Nova Cut",
                                                            "Nova Flat" => "Nova Flat",
                                                            "Nova Mono" => "Nova Mono",
                                                            "Nova Oval" => "Nova Oval",
                                                            "Nova Round" => "Nova Round",
                                                            "Nova Script" => "Nova Script",
                                                            "Nova Slim" => "Nova Slim",
                                                            "Nova Square" => "Nova Square",
                                                            "Numans" => "Numans",
                                                            "Nunito" => "Nunito",
                                                            "Odor Mean Chey" => "Odor Mean Chey",
                                                            "Old Standard TT" => "Old Standard TT",
                                                            "Oldenburg" => "Oldenburg",
                                                            "Oleo Script" => "Oleo Script",
                                                            "Open Sans" => "Open Sans",
                                                            "Open Sans Condensed" => "Open Sans Condensed",
                                                            "Orbitron" => "Orbitron",
                                                            "Original Surfer" => "Original Surfer",
                                                            "Oswald" => "Oswald",
                                                            "Over the Rainbow" => "Over the Rainbow",
                                                            "Overlock" => "Overlock",
                                                            "Overlock SC" => "Overlock SC",
                                                            "Ovo" => "Ovo",
                                                            "Oxygen" => "Oxygen",
                                                            "PT Mono" => "PT Mono",
                                                            "PT Sans" => "PT Sans",
                                                            "PT Sans Caption" => "PT Sans Caption",
                                                            "PT Sans Narrow" => "PT Sans Narrow",
                                                            "PT Serif" => "PT Serif",
                                                            "PT Serif Caption" => "PT Serif Caption",
                                                            "Pacifico" => "Pacifico",
                                                            "Parisienne" => "Parisienne",
                                                            "Passero One" => "Passero One",
                                                            "Passion One" => "Passion One",
                                                            "Patrick Hand" => "Patrick Hand",
                                                            "Patua One" => "Patua One",
                                                            "Paytone One" => "Paytone One",
                                                            "Permanent Marker" => "Permanent Marker",
                                                            "Petrona" => "Petrona",
                                                            "Philosopher" => "Philosopher",
                                                            "Piedra" => "Piedra",
                                                            "Pinyon Script" => "Pinyon Script",
                                                            "Plaster" => "Plaster",
                                                            "Play" => "Play",
                                                            "Playball" => "Playball",
                                                            "Playfair Display" => "Playfair Display",
                                                            "Podkova" => "Podkova",
                                                            "Poiret One" => "Poiret One",
                                                            "Poller One" => "Poller One",
                                                            "Poly" => "Poly",
                                                            "Pompiere" => "Pompiere",
                                                            "Pontano Sans" => "Pontano Sans",
                                                            "Port Lligat Sans" => "Port Lligat Sans",
                                                            "Port Lligat Slab" => "Port Lligat Slab",
                                                            "Prata" => "Prata",
                                                            "Preahvihear" => "Preahvihear",
                                                            "Press Start 2P" => "Press Start 2P",
                                                            "Princess Sofia" => "Princess Sofia",
                                                            "Prociono" => "Prociono",
                                                            "Prosto One" => "Prosto One",
                                                            "Puritan" => "Puritan",
                                                            "Quantico" => "Quantico",
                                                            "Quattrocento" => "Quattrocento",
                                                            "Quattrocento Sans" => "Quattrocento Sans",
                                                            "Questrial" => "Questrial",
                                                            "Quicksand" => "Quicksand",
                                                            "Qwigley" => "Qwigley",
                                                            "Radley" => "Radley",
                                                            "Raleway" => "Raleway",
                                                            "Rammetto One" => "Rammetto One",
                                                            "Rancho" => "Rancho",
                                                            "Rationale" => "Rationale",
                                                            "Redressed" => "Redressed",
                                                            "Reenie Beanie" => "Reenie Beanie",
                                                            "Revalia" => "Revalia",
                                                            "Ribeye" => "Ribeye",
                                                            "Ribeye Marrow" => "Ribeye Marrow",
                                                            "Righteous" => "Righteous",
                                                            "Rochester" => "Rochester",
                                                            "Rock Salt" => "Rock Salt",
                                                            "Rokkitt" => "Rokkitt",
                                                            "Ropa Sans" => "Ropa Sans",
                                                            "Rosario" => "Rosario",
                                                            "Rosarivo" => "Rosarivo",
                                                            "Rouge Script" => "Rouge Script",
                                                            "Ruda" => "Ruda",
                                                            "Ruge Boogie" => "Ruge Boogie",
                                                            "Ruluko" => "Ruluko",
                                                            "Ruslan Display" => "Ruslan Display",
                                                            "Russo One" => "Russo One",
                                                            "Ruthie" => "Ruthie",
                                                            "Sail" => "Sail",
                                                            "Salsa" => "Salsa",
                                                            "Sancreek" => "Sancreek",
                                                            "Sansita One" => "Sansita One",
                                                            "Sarina" => "Sarina",
                                                            "Satisfy" => "Satisfy",
                                                            "Schoolbell" => "Schoolbell",
                                                            "Seaweed Script" => "Seaweed Script",
                                                            "Sevillana" => "Sevillana",
                                                            "Shadows Into Light" => "Shadows Into Light",
                                                            "Shadows Into Light Two" => "Shadows Into Light Two",
                                                            "Shanti" => "Shanti",
                                                            "Share" => "Share",
                                                            "Shojumaru" => "Shojumaru",
                                                            "Short Stack" => "Short Stack",
                                                            "Siemreap" => "Siemreap",
                                                            "Sigmar One" => "Sigmar One",
                                                            "Signika" => "Signika",
                                                            "Signika Negative" => "Signika Negative",
                                                            "Simonetta" => "Simonetta",
                                                            "Sirin Stencil" => "Sirin Stencil",
                                                            "Six Caps" => "Six Caps",
                                                            "Slackey" => "Slackey",
                                                            "Smokum" => "Smokum",
                                                            "Smythe" => "Smythe",
                                                            "Sniglet" => "Sniglet",
                                                            "Snippet" => "Snippet",
                                                            "Sofia" => "Sofia",
                                                            "Sonsie One" => "Sonsie One",
                                                            "Sorts Mill Goudy" => "Sorts Mill Goudy",
                                                            "Special Elite" => "Special Elite",
                                                            "Spicy Rice" => "Spicy Rice",
                                                            "Spinnaker" => "Spinnaker",
                                                            "Spirax" => "Spirax",
                                                            "Squada One" => "Squada One",
                                                            "Stardos Stencil" => "Stardos Stencil",
                                                            "Stint Ultra Condensed" => "Stint Ultra Condensed",
                                                            "Stint Ultra Expanded" => "Stint Ultra Expanded",
                                                            "Stoke" => "Stoke",
                                                            "Sue Ellen Francisco" => "Sue Ellen Francisco",
                                                            "Sunshiney" => "Sunshiney",
                                                            "Supermercado One" => "Supermercado One",
                                                            "Suwannaphum" => "Suwannaphum",
                                                            "Swanky and Moo Moo" => "Swanky and Moo Moo",
                                                            "Syncopate" => "Syncopate",
                                                            "Tangerine" => "Tangerine",
                                                            "Taprom" => "Taprom",
                                                            "Telex" => "Telex",
                                                            "Tenor Sans" => "Tenor Sans",
                                                            "The Girl Next Door" => "The Girl Next Door",
                                                            "Tienne" => "Tienne",
                                                            "Tinos" => "Tinos",
                                                            "Titan One" => "Titan One",
                                                            "Trade Winds" => "Trade Winds",
                                                            "Trocchi" => "Trocchi",
                                                            "Trochut" => "Trochut",
                                                            "Trykker" => "Trykker",
                                                            "Tulpen One" => "Tulpen One",
                                                            "Ubuntu" => "Ubuntu",
                                                            "Ubuntu Condensed" => "Ubuntu Condensed",
                                                            "Ubuntu Mono" => "Ubuntu Mono",
                                                            "Ultra" => "Ultra",
                                                            "Uncial Antiqua" => "Uncial Antiqua",
                                                            "UnifrakturCook" => "UnifrakturCook",
                                                            "UnifrakturMaguntia" => "UnifrakturMaguntia",
                                                            "Unkempt" => "Unkempt",
                                                            "Unlock" => "Unlock",
                                                            "Unna" => "Unna",
                                                            "VT323" => "VT323",
                                                            "Varela" => "Varela",
                                                            "Varela Round" => "Varela Round",
                                                            "Vast Shadow" => "Vast Shadow",
                                                            "Vibur" => "Vibur",
                                                            "Vidaloka" => "Vidaloka",
                                                            "Viga" => "Viga",
                                                            "Voces" => "Voces",
                                                            "Volkhov" => "Volkhov",
                                                            "Vollkorn" => "Vollkorn",
                                                            "Voltaire" => "Voltaire",
                                                            "Waiting for the Sunrise" => "Waiting for the Sunrise",
                                                            "Wallpoet" => "Wallpoet",
                                                            "Walter Turncoat" => "Walter Turncoat",
                                                            "Wellfleet" => "Wellfleet",
                                                            "Wire One" => "Wire One",
                                                            "Yanone Kaffeesatz" => "Yanone Kaffeesatz",
                                                            "Yellowtail" => "Yellowtail",
                                                            "Yeseva One" => "Yeseva One",
                                                            "Yesteryear" => "Yesteryear",
                                                            "Zeyada" => "Zeyada",
                                                    );

    $font_select='';
    foreach($google_fonts_array as $key=>$value){
        $font_select.='<option value="'.$key.'">'.$value.'</option>';
    }

    $headings_font_subset   =   esc_html ( get_option('wp_estate_headings_font_subset','') );

    //   
     ///////////////////////////////////////////////////////////////////////////////////////////////////////
    $general_font_select='';
    $general_font= esc_html ( get_option('wp_estate_general_font','') );
    if($general_font!='x'){
    $general_font_select='<option value="'.$general_font.'">'.$general_font.'</option>';
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Main Font','wpestate').'</div>
        <div class="option_row_explain">'.__('Select main font','wpestate').'</div>    
            <select id="general_font" name="general_font">
                    '.$general_font_select.'
                    <option value="">- original font -</option>
                    '.$font_select.'                   
            </select> 
        </div>';

    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Main Font subset', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Select Main Font subset( like greek,cyrillic, etc..)', 'wpestate').'</div>    
                <input type="text" id="headings_font_subset" name="headings_font_subset" value="'.$headings_font_subset.'">    
        </div>';
    
    print ' <div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="' . __('Save Changes', 'wpestate') . '" />
        </div>';
    }
endif;// end new_wpestate_custom_fonts();



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  reCaptcha settings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('estate_recaptcha_settings') ):
function estate_recaptcha_settings(){
        $use_captcha_symbol='';
        $use_captcha_status= esc_html ( get_option('wp_estate_use_captcha','') );
        $social_array=   array('no','yes');
        foreach($social_array as $value){
                $use_captcha_symbol.='<option value="'.$value.'"';
                if ($use_captcha_status==$value){
                        $use_captcha_symbol.=' selected="selected" ';
                }
                $use_captcha_symbol.='>'.$value.'</option>';
        }   
        
        
        $recaptha_sitekey               =   esc_html( get_option('wp_estate_recaptha_sitekey') );
        $recaptha_secretkey               =   esc_html( get_option('wp_estate_recaptha_secretkey') );    
       
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Use reCaptcha on register ?','wpestate').'</div>
        <div class="option_row_explain">'.__('This helps preventing registration spam.','wpestate').'</div>    
           <select id="use_captcha" name="use_captcha">
                    '.$use_captcha_symbol.'
            </select>
       </div>';
    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('reCaptha site key','wpestate').'</div>
        <div class="option_row_explain">'.__('Get this detail after you signup here ','wpestate').'<a target="_blank" href="https://www.google.com/recaptcha/intro/index.html">https://www.google.com/recaptcha/intro/index.html</a></div>    
            <input  type="text" id="recaptha_sitekey" name="recaptha_sitekey"  value="'.$recaptha_sitekey.'"/> 
        </div>';
    
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('reCaptha secret key','wpestate').'</div>
        <div class="option_row_explain">'.__('Get this detail after you signup here ','wpestate').'<a target="_blank" href="https://www.google.com/recaptcha/intro/index.html">https://www.google.com/recaptcha/intro/index.html</a></div>    
            <input  type="text" id="recaptha_secretkey" name="recaptha_secretkey"  value="'.$recaptha_secretkey.'"/> 
        </div>';
     
    print ' <div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
        </div>';
   
}
endif; //estate_recaptcha_settings
//



if( !function_exists('wpestate_dropdowns_theme_admin_with_key') ):
    function wpestate_dropdowns_theme_admin_with_key($array_values,$option_name){
        
        $dropdown_return    =   '';
        $option_value       =   esc_html ( get_option('wp_estate_'.$option_name,'') );
        foreach($array_values as $key=>$value){
            $dropdown_return.='<option value="'.$key.'"';
              if ( $option_value == $key ){
                $dropdown_return.='selected="selected"';
            }
            $dropdown_return.='>'.$value.'</option>';
        }
        
        return $dropdown_return;
        
    }
endif;


if( !function_exists('new_wpestate_paypal_settings') ):
    function new_wpestate_paypal_settings(){
        $paypal_client_id               =   esc_html( get_option('wp_estate_paypal_client_id','') );
        $paypal_client_secret           =   esc_html( get_option('wp_estate_paypal_client_secret','') );
        $paypal_api_username            =   esc_html( get_option('wp_estate_paypal_api_username','') );
        $paypal_api_password            =   esc_html( get_option('wp_estate_paypal_api_password','') );
        $paypal_api_signature           =   esc_html( get_option('wp_estate_paypal_api_signature','') );
        $paypal_rec_email               =   esc_html( get_option('wp_estate_paypal_rec_email','') );
    
    
        $merch_array=array('yes','no');
        $enable_paypal_symbol='';
        $enable_paypal_status= esc_html ( get_option('wp_estate_enable_paypal','') );

        foreach($merch_array as $value){
                $enable_paypal_symbol.='<option value="'.$value.'"';
                if ($enable_paypal_status==$value){
                        $enable_paypal_symbol.=' selected="selected" ';
                }
                $enable_paypal_symbol.='>'.$value.'</option>';
        }
    
        print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Enable Paypal', 'wpestate').'</div>
        <div class="option_row_explain">'.__('You can enable or disable PayPal buttons.', 'wpestate').'</div>    
            <select id="enable_paypal" name="enable_paypal">
                '.$enable_paypal_symbol.'
            </select>
        </div>';
        
        print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Paypal Client id', 'wpestate').'</div>
        <div class="option_row_explain">'.__('PayPal business account is required. Info is taken from https://developer.paypal.com/. See help.', 'wpestate').'</div>    
            <input  type="text" id="paypal_client_id" name="paypal_client_id" class="regular-text"  value="'.$paypal_client_id.'"/>
        </div>';
    
        print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Paypal Client Secret Key', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Info is taken from https://developer.paypal.com/ See help.', 'wpestate').'</div>    
            <input  type="text" id="paypal_client_secret" name="paypal_client_secret"  class="regular-text" value="'.$paypal_client_secret.'"/>
        </div>';
    
       

        print'<div class="estate_option_row">
        <div class = "label_option_row">'.__('Paypal Api User Name','wpestate').'</div>
        <div class = "option_row_explain">'.__('Info is taken from https://www.paypal.com/ or http://sandbox.paypal.com/ See help.','wpestate').'</div>    
        <input  type="text" id="paypal_api_username" name="paypal_api_username"  class="regular-text" value="'.$paypal_api_username.'"/>
        </div > '; 

        
        print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Paypal API Password', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Info is taken from https://www.paypal.com/ or http://sandbox.paypal.com/ See help.', 'wpestate').'</div>    
           <input  type="text" id="paypal_api_password" name="paypal_api_password"  class="regular-text" value="'.$paypal_api_password.'"/>
        </div>';


        print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Paypal API Signature', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Info is taken from https://www.paypal.com/ or http://sandbox.paypal.com/ See help.', 'wpestate').'</div>    
           <input  type="text" id="paypal_api_signature" name="paypal_api_signature"  class="regular-text" value="'.$paypal_api_signature.'"/>
        </div>';


 
        print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Paypal receiving email','wpestate').'</div>
        <div class="option_row_explain">'.__('Info is taken from https://www.paypal.com/ or http://sandbox.paypal.com/ See help.','wpestate').'</div>    
           <input  type="text" id="paypal_rec_email" name="paypal_rec_email"  class="regular-text" value="'.$paypal_rec_email.'"/>
        </div>';
        
        print ' <div class="estate_option_row_submit">
        <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
        </div>'; 
    }
endif;

if( !function_exists('new_wpestate_stripe_settings') ):
function new_wpestate_stripe_settings(){
    $merch_array=array('yes','no');
   
    
    $enable_stripe_symbol='';
    $enable_stripe_status= esc_html ( get_option('wp_estate_enable_stripe','') );

    foreach($merch_array as $value){
            $enable_stripe_symbol.='<option value="'.$value.'"';
            if ($enable_stripe_status==$value){
                    $enable_stripe_symbol.=' selected="selected" ';
            }
            $enable_stripe_symbol.='>'.$value.'</option>';
    }
       
    
    $stripe_secret_key              =   esc_html( get_option('wp_estate_stripe_secret_key','') );
    $stripe_publishable_key         =   esc_html( get_option('wp_estate_stripe_publishable_key','') );
  
    
    
    print'<div class="estate_option_row">
       <div class="label_option_row">'.__('Enable Stripe', 'wpestate').'</div>
       <div class="option_row_explain">'.__('You can enable or disable Stripe buttons.', 'wpestate').'</div>    
           <select id="enable_stripe" name="enable_stripe">
                    '.$enable_stripe_symbol.'
		 </select>
       </div>';
    
       print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Stripe Secret Key', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Info is taken from your account at https://dashboard.stripe.com/login', 'wpestate') . '</div>    
           <input  type="text" id="stripe_secret_key" name="stripe_secret_key"  class="regular-text" value="'.$stripe_secret_key.'"/> 
        </div>';
 
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Stripe Publishable Key', 'wpestate').'</div>
        <div class="option_row_explain">'.__('Info is taken from your account at https://dashboard.stripe.com/login', 'wpestate').'</div>    
           <input  type="text" id="stripe_publishable_key" name="stripe_publishable_key"  class="regular-text" value="'.$stripe_publishable_key.'"/>
        </div>';


    print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>'; 
}
endif;


/////////////////////////////////////////////////////////////////
//Main menu design
//////////////////////////////////////////////////////

if( !function_exists('new_wpestate_main_menu_design')):
function new_wpestate_main_menu_design(){
    $menu_font_color                =  esc_html ( get_option('wp_estate_menu_font_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Top Menu Font Color','wpestate').'</div>
    <div class="option_row_explain">'.__('Top Menu Font Color','wpestate').'</div>    
        <input type="text" name="menu_font_color" value="'.$menu_font_color.'"  maxlength="7" class="inptxt" />
        <div id="menu_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$menu_font_color.';" ></div></div>
    </div>';
    
    $top_menu_hover_font_color                =  esc_html ( get_option('wp_estate_top_menu_hover_font_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Top Menu Hover Font Color','wpestate').'</div>
    <div class="option_row_explain">'.__('Top Menu Hover Font Color','wpestate').'</div>    
        <input type="text" name="top_menu_hover_font_color" value="'.$top_menu_hover_font_color.'"  maxlength="7" class="inptxt" />
        <div id="top_menu_hover_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$top_menu_hover_font_color.';" ></div></div>
    </div>';
    
    $transparent_menu_font_color                =  esc_html ( get_option('wp_estate_transparent_menu_font_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Transparent Header - Top Menu Font Color','wpestate').'</div>
    <div class="option_row_explain">'.__('Transparent Header - Top Menu Font Color','wpestate').'</div>    
        <input type="text" name="transparent_menu_font_color" value="'.$transparent_menu_font_color.'"  maxlength="7" class="inptxt" />
        <div id="transparent_menu_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$transparent_menu_font_color.';" ></div></div>
    </div>';
    
    $transparent_menu_hover_font_color               =  esc_html ( get_option('wp_estate_transparent_menu_hover_font_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Transparent Header - Top Menu Hover Font Color','wpestate').'</div>
    <div class="option_row_explain">'.__('Transparent Header - Top Menu Hover Font Color','wpestate').'</div>    
        <input type="text" name="transparent_menu_hover_font_color" value="'.$transparent_menu_hover_font_color.'"  maxlength="7" class="inptxt" />
        <div id="transparent_menu_hover_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$transparent_menu_hover_font_color.';" ></div></div>
    </div>';
    
    $sticky_menu_font_color                =  esc_html ( get_option('wp_estate_sticky_menu_font_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Sticky Menu Font Color','wpestate').'</div>
    <div class="option_row_explain">'.__('Sticky Menu Font Color','wpestate').'</div>    
        <input type="text" name="sticky_menu_font_color" value="'.$sticky_menu_font_color.'"  maxlength="7" class="inptxt" />
        <div id="sticky_menu_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$sticky_menu_font_color.';" ></div></div>
    </div>';
    
    $menu_items_color        =  esc_html ( get_option('wp_estate_menu_items_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Menu Item Color','wpestate').'</div>
    <div class="option_row_explain">'.__('Menu Item Color','wpestate').'</div>    
        <input type="text" name="menu_items_color" value="'.$menu_items_color.'"  maxlength="7" class="inptxt" />
        <div id="menu_items_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$menu_items_color.';" ></div></div>
    </div>';
    
     $menu_item_back_color         =  esc_html ( get_option('wp_estate_menu_item_back_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Menu Item Back Color','wpestate').'</div>
    <div class="option_row_explain">'.__('Menu Item  Back Color','wpestate').'</div>    
       <input type="text" name="menu_item_back_color" value="'.$menu_item_back_color.'"  maxlength="7" class="inptxt" />
        <div id="menu_item_back_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$menu_item_back_color.';"></div></div>
    </div>';
    

    
    $menu_hover_font_color          =  esc_html ( get_option('wp_estate_menu_hover_font_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Menu Item hover font color','wpestate').'</div>
    <div class="option_row_explain">'.__('Menu Item hover font color','wpestate').'</div>    
        <input type="text" name="menu_hover_font_color" value="'.$menu_hover_font_color.'" maxlength="7" class="inptxt" />
        <div id="menu_hover_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$menu_hover_font_color.';" ></div></div>
    </div>';
    
    
    
    $wp_estate_top_menu_font_size     = get_option('wp_estate_top_menu_font_size','');
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Top Menu Font Size','wpestate').'</div>
    <div class="option_row_explain">'.__('Top Menu Font Size','wpestate').'</div>    
        <input  type="text" id="top_menu_font_size" name="top_menu_font_size"  value="'.$wp_estate_top_menu_font_size.'"/> 
    </div>';
    
    $wp_estate_menu_item_font_size     = get_option('wp_estate_menu_item_font_size','');
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Menu Item Font Size','wpestate').'</div>
    <div class="option_row_explain">'.__('Menu Item Font Size','wpestate').'</div>    
        <input  type="text" id="menu_item_font_size" name="menu_item_font_size"  value="'.$wp_estate_menu_item_font_size.'"/> 
    </div>';
    
 
      
     $top_menu_hover_back_font_color                =  esc_html ( get_option('wp_estate_top_menu_hover_back_font_color','') );
    print'<div class="estate_option_row">
    <div class="label_option_row">'.__('Top Menu Hover Background Color','wpestate').'</div>
    <div class="option_row_explain">'.__('Top Menu Hover Background Color (*applies on some hover types)','wpestate').'</div>    
        <input type="text" name="top_menu_hover_back_font_color" value="'.$top_menu_hover_back_font_color.'"  maxlength="7" class="inptxt" />
        <div id="top_menu_hover_back_font_color" class="colorpickerHolder" ><div class="sqcolor" style="background-color:#'.$top_menu_hover_back_font_color.';" ></div></div>
    </div>';
    
    $cache_array=array(1,2,3,4,5,6);
    $top_menu_hover_type=  wpestate_dropdowns_theme_admin($cache_array,'top_menu_hover_type');
    
    print'<div class="estate_option_row">
    <img  style="border:1px solid #FFE7E7;margin-bottom:10px;" src="'. get_template_directory_uri().'/img/menu_types.png" alt="logo"/>
                      
    <div class="label_option_row">'.__('Top Menu Hover Type','wpestate').'</div>
    <div class="option_row_explain">'.__('Top Menu Hover Type','wpestate').'</div>    
        <select id="top_menu_hover_type" name="top_menu_hover_type">
            '.$top_menu_hover_type.'
        </select> 
    </div>';
    
    print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>';
}
endif; // end Main menu design


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///  General Design Settings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !function_exists('wpestate_general_design_settings') ):
function wpestate_general_design_settings(){
   
    $header_height = esc_html(get_option('wp_estate_header_height', ''));
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Header Height','wpestate').'</div>
        <div class="option_row_explain">'.__('Header Height in px','wpestate').'</div>    
            <input type="text" name="header_height" id="header_height" value="'.$header_height.'"> 
        </div>';

    
    $sticky_header_height = esc_html(get_option('wp_estate_sticky_header_height', ''));
    print'<div class="estate_option_row">
        <div class="label_option_row">' . __('Sticky Header Height', 'wpestate') . '</div>
        <div class="option_row_explain">' . __('Sticky Header Height in px', 'wpestate') . '</div>    
            <input type="text" name="sticky_header_height" id="sticky_header_height" value="' . $sticky_header_height . '"> 
        </div>';
        
    $border_bottom_header                                              =   esc_html ( get_option('wp_estate_border_bottom_header','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Border Bottom Header Height','wpestate').'</div>
        <div class="option_row_explain">'.__('Border Bottom Header Height in px','wpestate').'</div>    
            <input type="text" name="border_bottom_header" id="border_bottom_header" value="'.$border_bottom_header.'"> 
        </div>';

        
    $border_bottom_header_color             =  esc_html ( get_option('wp_estate_border_bottom_header_color','') );
    print'<div class="estate_option_row">
        <div class="label_option_row">'.__('Header Border Bottom Color','wpestate').'</div>
        <div class="option_row_explain">'.__('Header Border Bottom Color','wpestate').'</div>    
            <input type="text" name="border_bottom_header_color" value="'.$border_bottom_header_color.'" maxlength="7" class="inptxt" />
            <div id="border_bottom_header_color" class="colorpickerHolder"><div class="sqcolor" style="background-color:#'.$border_bottom_header_color.';"></div></div>
        </div>';
     
    $cache_array=array('no','yes');
    $show_menu_dashboard_symbol    = wpestate_dropdowns_theme_admin($cache_array,'show_menu_dashboard');
      
    print'<div class="estate_option_row">
            <div class="label_option_row">' . esc_html__('Show the header menu in dashboard?', 'wpestate') . '</div>
            <div class="option_row_explain">' . __('Show the header menu in dashboard?', 'wpestate') . '</div>    
                <select id="show_menu_dashboard" name="show_menu_dashboard">
                    ' . $show_menu_dashboard_symbol . '
		 </select>
        </div>';
   
    print ' <div class="estate_option_row_submit">
    <input type="submit" name="submit"  class="new_admin_submit " value="'.__('Save Changes','wpestate').'" />
    </div>';
}
endif;//wpestate_general_design_settings



?>