/*global $, jQuery, ajaxcalls_vars, document, control_vars, window, tb_show, tb_remove*/
wpestate_set_theme_tab_visible();
jQuery(document).ready(function ($) {
    "use strict";
    estate_activate_template_action();
    wpestate_theme_options_sidebar();
    
     $('.admin_top_bar_button').click(function(event){
        event.preventDefault();
        var selected = $(this).attr('data-menu');
        var autoselect='';
        
        $('.admin_top_bar_button').removeClass('tobpbar_selected_option');
        $(this).addClass('tobpbar_selected_option');
        
        $('.theme_options_sidebar, .theme_options_wrapper_tab,.theme_options_tab').hide();
        $('#'+selected).show();
        $('#'+selected+'_tab').show();
        $('#'+selected+'_tab .theme_options_tab:eq(0)').show();
      
      
        localStorage.setItem('hidden_tab',selected);
        
        
        $('#'+selected+' li:eq(0)').addClass('selected_option');
        autoselect =  $('#'+selected+' li:eq(0)').attr('data-optiontab');
     
        localStorage.setItem('hidden_sidebar',autoselect);
        wpestate_theme_options_sidebar();
    });
     
     
   
    
     
    $('#wpestate_sidebar_menu li').click(function(event){
        event.preventDefault();
        $('#wpestate_sidebar_menu li').removeClass('selected_option');
        $(this).addClass('selected_option');
        
        var selected = $(this).attr('data-optiontab');
      
        $('.theme_options_tab').hide();
        $('#'+selected).show();
        $('#hidden_sidebar').val(selected);
        
       
        localStorage.setItem('hidden_sidebar',selected);
        wpestate_theme_options_sidebar();
                
    });
     
    
    var my_colors, k;
    ///////////////////////////////////////////////////////////////////////////////
    /// add new membership
    ///////////////////////////////////////////////////////////////////////////////
    $('#new_membership').click(function () {
        var new_row;
        new_row = $('#sample_member_row').html();
        new_row = '<div class="memebership_row">' + new_row + '</div>';
        $('#new_membership').before(new_row);
    });

    $('.remove_pack').click(function () {
        $(this).parent().remove();
    });

    ///////////////////////////////////////////////////////////////////////////////
    /// pin upload
    ///////////////////////////////////////////////////////////////////////////////
    $('.pin-upload').click(function () {
        var formfield, imgurl;
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        formfield = $(this).prev();
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            formfield.val(imgurl);
            tb_remove();
        };
        return false;
    });

    ///////////////////////////////////////////////////////////////////////////////
    /// upload header
    ///////////////////////////////////////////////////////////////////////////////
    $('#global_header_button').click(function () {
        var formfield, imgurl;
        formfield = $('#global_header').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#global_header').val(imgurl);
            tb_remove();
        };
        return false;
    });

    /////////////////////////////////////////////////////////////////////////////////
    /// upload footer
    ///////////////////////////////////////////////////////////////////////////////
    $('#footer_background_button').click(function () {
        var formfield, imgurl;
        formfield = $('#footer_background').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#footer_background').val(imgurl);
            tb_remove();
        };
        return false;
    });

    ///////////////////////////////////////////////////////////////////////////////
    /// upload logo
    ///////////////////////////////////////////////////////////////////////////////
    $('#logo_image_button').click(function () {
        var formfield, imgurl;
        formfield = $('#logo_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#logo_image').val(imgurl);
            tb_remove();
        };
        return false;
    });
    
    ///////////////////////////////////////////////////////////////////////////////
    /// upload logo
    ///////////////////////////////////////////////////////////////////////////////
    $('#transparent_logo_image_button').click(function () {
        var formfield, imgurl;
        formfield = $('#logo_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#transparent_logo_image').val(imgurl);
            tb_remove();
        };
        return false;
    });
    
     ///////////////////////////////////////////////////////////////////////////////
    /// upload logo
    ///////////////////////////////////////////////////////////////////////////////
    $('#mobile_logo_image_button').click(function () {
        var formfield, imgurl;
        formfield = $('#logo_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#mobile_logo_image').val(imgurl);
            tb_remove();
        };
        return false;
    });

    ///////////////////////////////////////////////////////////////////////////////
    /// upload fotoer logo
    ///////////////////////////////////////////////////////////////////////////////
    $('#footer_logo_image_button').click(function () {
        var formfield, imgurl;
        formfield = $('#footer_logo_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#footer_logo_image').val(imgurl);
            tb_remove();
        };
        return false;
    });

    $('#favicon_image_button').click(function () {
        var formfield, imgurl;
        formfield = $('#favicon_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#favicon_image').val(imgurl);
            tb_remove();
        };
        return false;
    });


    $('#logo_image_retina_button').click(function () {
        var formfield, imgurl;
        formfield = $('#logo_image_retina').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#logo_image_retina').val(imgurl);
            tb_remove();
        };
        return false;
    });
    $('#transparent_logo_image_retina_button').click(function () {
        var formfield, imgurl;
        formfield = $('#transparent_logo_image_retina').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#transparent_logo_image_retina').val(imgurl);
            tb_remove();
        };
        return false;
    });
    
    $('#mobile_logo_image_retina_button').click(function () {
        var formfield, imgurl;
        formfield = $('#mobile_logo_image_retina').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#mobile_logo_image_retina').val(imgurl);
            tb_remove();
        };
        return false;
    });


  
    $('#background_image_button').click(function () {
        var formfield, imgurl;
        formfield = $('#background_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#background_image').val(imgurl);
            tb_remove();
        };
        return false;
    });


    function getUrlVars() {
        var vars = [], hash, hashes, i;
        hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

    if ($(".admin_menu_list")[0]) {//if is our custom admin page
        // admin tab controls.
        var fullUrl, tab, pick;
        fullUrl = getUrlVars()["page"];
        tab = (fullUrl.split("#"));
        pick = tab[1];

        if (typeof tab[1] === 'undefined') {
            pick = "tab1";
        }

        $(".tabadmin").each(function () {
            if ($(this).attr("data-tab") === pick) {
                $(this).addClass("active");
            } else {
                $(this).removeClass("active");
            }
        });

        $(".admin_menu_list li").each(function () {
            if ($(this).attr("rel") === pick) {
                $(this).addClass("active");
            } else {
                $(this).removeClass("active");
            }
        });

    }



    //admin color changes

    my_colors = ['main_color',
        'background_color',
        'content_back_color',
        'header_color',
        'breadcrumbs_back_color',
        'breadcrumbs_font_color',
        'font_color','link_color',
        'headings_color',
        'comments_font_color',
        'coment_back_color',
        'footer_back_color',
        'footer_font_color',
        'footer_copy_color',
        'sidebar_widget_color',
        'sidebar_heading_boxed_color',
        'sidebar_heading_color',
        'sidebar2_font_color',
        'menu_font_color',
        'menu_hover_back_color',
        'menu_hover_font_color',
        'agent_color',
        'listings_color',
        'blog_color',
        'dotted_line',
        'footer_top_band',
        'top_bar_back',
        'top_bar_font',
        'adv_search_back_color',
        'adv_search_font_color',
        'box_content_back_color',
        'box_content_border_color',
        'hover_button_color',
        'top_menu_hover_font_color',
        'transparent_menu_font_color',
        'transparent_menu_hover_font_color',
        'sticky_menu_font_color ',
        'menu_items_color ',
        'menu_item_back_color',
        'top_menu_hover_back_font_color',
        'border_bottom_header_color',
        'border_bottom_header_sticky_color'];
    for (k in my_colors ) {
        eval("$('#" + my_colors[k] + "' ).ColorPicker({ onChange: function (hsb, hex, rgb) {$('#" + my_colors[k] + " .sqcolor').css('background-color', '#' + hex);$('[name=" + my_colors[k] + "]' ).val( hex );}});");			
    }

    function clearimg() {
	$('#tabpat img').each(
            function () {
                $(this).css('border','none');
            });
    };



    $('input[id^="item-custom"]').click(function () {
	var formfieldx, imgurl;
        formfieldx = "edit-menu-"+$(this).attr("id");
	tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = jQuery('img',html).attr('src');
            jQuery("#"+formfieldx).val(imgurl);
            tb_remove();
        }
        return false;
	});
});

function wpestate_set_theme_tab_visible(){
    //   var show_tab = jQuery('#hidden_tab').val();
   // var show_sidebar = jQuery('#hidden_sidebar').val();
   
    var show_tab        =   localStorage.getItem('hidden_tab');
    var show_sidebar    =   localStorage.getItem('hidden_sidebar');
   
   
    if(show_tab===null || show_tab===''){
        show_tab = 'general_settings_sidebar';
    }
    
    if(show_sidebar=== null || show_sidebar==''){
        show_sidebar = 'global_settings_tab';
    }
    

  
    if(show_tab!=='none'){
     
        jQuery('.theme_options_sidebar, .theme_options_wrapper_tab').hide();
        jQuery('#'+show_tab).show();
        jQuery('#'+show_tab+'_tab').show();
        jQuery('.wrap-topbar div').removeClass('tobpbar_selected_option');
        jQuery('.wrap-topbar div[data-menu="'+show_tab+'"]').addClass('tobpbar_selected_option');
    }


    if(show_sidebar!=='none'){
       
        jQuery('.theme_options_tab').hide();
        jQuery('#'+show_sidebar).show();
        jQuery('#wpestate_sidebar_menu li').removeClass('selected_option');
        jQuery('#wpestate_sidebar_menu li[data-optiontab="'+show_sidebar+'"]').addClass('selected_option');
        
    }
 
}


function    estate_activate_template_action(){ 
   
    jQuery('.activate_template').click(function(){
    
        var ajaxurl, base_template, parent;
        base_template   =   jQuery(this).attr('data-baseid');
        ajaxurl         =   ajax_upload_demo_vars.admin_url + 'admin-ajax.php';
        parent          =   jQuery(this).parent();
       
        jQuery(this).parent().find('.importing_mess').empty().text(ajax_upload_demo_vars.importing);
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action'             :   'wpestate_start_demo_import',
                'base_template'      :   base_template,
                
            },
            success: function (data) { 
                console.log(data);  
                parent.find('.importing_mess').empty().text(ajax_upload_demo_vars.complete);
            },
            error: function (errorThrown) {
              
            }
        });//end ajax     
        
    });
}


function wpestate_theme_options_sidebar(){
    var new_height;
    new_height = jQuery ('#wpestate_wrapper_admin_menu').height();
    jQuery ('#wpestate_sidebar_menu').height(new_height)
    
}
