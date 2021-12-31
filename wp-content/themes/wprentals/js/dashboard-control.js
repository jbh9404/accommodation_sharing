/*global $, jQuery, ajaxcalls_vars, document, control_vars, window, map, setTimeout, Modernizr, create_review_action, create_payment_action, invoice_create_js, wpestate_booking_cretupm_paypal, location, google, dashboard_vars, options, create_delete_invoice_action, create_generate_invoice_action*/
jQuery(document).ready(function ($) {
    "use strict";
    create_delete_invoice_action();
    create_generate_invoice_action();


    jQuery('#add_extra_feed').click(function (event) {
        var to_add, feed_label, feed_url;

        feed_label = $('#property_icalendar_import_name_new').val();
        feed_url = $('#property_icalendar_import_feed_new').val();

        if (feed_url !== '') {
            to_add = '<div class="icalfeed"><input type="text" class="form-control property_icalendar_import_name_new" size="40" width="200"  name="property_icalendar_import_name[]" value="' + feed_label + '"><input type="text"  class="form-control property_icalendar_import_feed_new" size="40" width="200" name="property_icalendar_import_feed[]" value="' + feed_url + '"></div>';

            $('#property_icalendar_import_name_new').val('');
            $('#property_icalendar_import_feed_new').val('');
            $('#icalfeed_wrapper').append(to_add);
        }
    });


    jQuery('.delete_extra_option').click(function (event) {
        jQuery(this).parent().remove();
    });


    jQuery('#add_extra_option').click(function (event) {

        var option_name, option_value, option_type, to_add, select_name;
        $('.no_extra_pay_option').remove();

        option_name = $('#add_option_name').val();
        option_value = $('#add_option_value').val();
        option_type = $('#add_option_type').val();
        select_name = option_name + "_select";

        to_add = '<div class="extra_pay_option"><input type="text" class="add_option_input extra_option_name form-control" value="' + option_name + '">\n\
<input type="text" style="width:100px;" class="add_option_input extra_option_value form-control" value="' + option_value + '">\n\
' + wpestate_generate_type_dropdown(option_type) + '</div>';


        $('#add_option_name').val('');
        $('#add_option_value').val('');
        //        $('#add_option_type').val('');

        $('.extra_pay_option_wrapper').append(to_add);

    });


    function wpestate_generate_type_dropdown(type) {
        var type_free, type_per_night, type_per_guest, type_per_night_guest;
        type_free = '';
        type_per_night = '';
        type_per_guest = '';
        type_per_night_guest = '';
        type = parseInt(type);
        if (type === 0) {
            type_free = ' selected ';
        } else if (type === 1) {
            type_per_night = ' selected ';
        } else if (type === 2) {
            type_per_guest = ' selected ';
        } else if (type === 3) {
            type_per_night_guest = ' selected ';
        }


        return '<select class="select_submit_price"><option value="0" ' + type_free + '>Single Fee</option><option value="1" ' + type_per_night + ' >Per Night</option><option value="2" ' + type_per_guest + '>Per Guest</option><option value="3" ' + type_per_night_guest + '>Per Night per Guest</option></select>'
    }


    jQuery('.activate_payments').click(function (event) {
        jQuery(this).parent().parent().find('.listing_submit').show();
    });

    jQuery('.close_payments').click(function (event) {
        jQuery(this).parent().hide();
    });

    jQuery("#invoice_start_date").datepicker({
        dateFormat: "yy-mm-dd",

    }, jQuery.datepicker.regional[control_vars.datepick_lang]).datepicker('widget').wrap('<div class="ll-skin-melon"/>');


    jQuery("#invoice_end_date").datepicker({
        dateFormat: "yy-mm-dd",

    }, jQuery.datepicker.regional[control_vars.datepick_lang]).datepicker('widget').wrap('<div class="ll-skin-melon"/>');


    $('#invoice_start_date, #invoice_end_date, #invoice_type ,#invoice_status ').change(function () {
        filter_invoices();
    });


    ///////////////////////////////////////////////////////////////////////////////////////
    /// messages read and reply
    /////////////////////////////////////////////////////////////////////////////////////// 
    $('.mess_send_reply_button').click(function () {
        var messid, ajaxurl, acesta, parent, title, content, container, mesage_container;
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        parent = $(this).parent().parent();
        mesage_container = parent.find('.mess_content');
        container = $(this).parent();
        messid = parent.attr('data-messid');
        acesta = $(this);
        title = parent.find('.subject_reply').val();
        content = parent.find('.message_reply_content').val();
        parent.find('.mess_unread').remove();
        acesta.text(dashboard_vars.sending);
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_message_reply',
                'messid': messid,
                'title': title,
                'content': content
            },
            success: function (data) {

                mesage_container.hide();
                container.hide();
            },
            error: function (errorThrown) {
            }
        });
    });

    ///////////////////////////////////////////////////////////////////////////////////////
    /// messages read and reply
    ///////////////////////////////////////////////////////////////////////////////////////
    $('.message_header').click(function () {
        var messid, ajaxurl, acesta, parent;
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        parent = $(this).parent();
        messid = parent.attr('data-messid');
        acesta = $(this);
        $('.mess_content, .mess_reply_form').hide();
        $(this).parent().find('.mess_content').show();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_booking_mark_as_read',
                'messid': messid
            },
            success: function (data) {
                parent.find('.mess_unread').remove();
            },
            error: function (errorThrown) {
            }
        });
    });

    ///////////////////////////////////////////////////////////////////////////////////////
    /// messages reply
    ///////////////////////////////////////////////////////////////////////////////////////
    $('.mess_reply').click(function (event) {
        var messid, ajaxurl, acesta, parent;
        event.stopPropagation();
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        parent = $(this).parent().parent().parent();
        messid = parent.attr('data-messid');
        acesta = $(this);
        $('.mess_content, .mess_reply_form').hide();
        parent.find('.mess_content').show();
        parent.find('.mess_reply_form').show();

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_booking_mark_as_read',
                'messid': messid
            },
            success: function (data) {
                parent.find('.mess_unread').remove();
            },
            error: function (errorThrown) {
            }
        });
    });

    ///////////////////////////////////////////////////////////////////////////////////////
    /// messages delete 
    ///////////////////////////////////////////////////////////////////////////////////////
    $('.mess_delete').click(function (event) {
        var messid, ajaxurl, acesta, parent;
        event.stopPropagation();
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        parent = $(this).parent().parent().parent().parent();
        messid = parent.attr('data-messid');
        acesta = $(this);
        //$(this).empty().removeClass('mess_delete').html(dashboard_vars.deleting);

        $(this).parent().parent().empty().addClass('delete_inaction').html(dashboard_vars.deleting);

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_booking_delete_mess',
                'messid': messid
            },
            success: function (data) {
                parent.parent().remove();
                $('.mess_content, .mess_reply_form').hide();
            },
            error: function (errorThrown) {

            }
        });
    });
    //////////////////////////////////////////////////////////////////////////////////////
    /// post review for reservation
    ///////////////////////////////////////////////////////////////////////////////////////
    $('.post_review').click(function () {
        var listing_id, ajaxurl, acesta, parent, bookid;
        listing_id = $(this).attr('data-listing-review');
        bookid = $(this).attr('data-bookid');
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        acesta = $(this);
        parent = $(this).parent().parent();

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_show_review_form',
                'listing_id': listing_id,
                'bookid': bookid
            },
            success: function (data) {
                jQuery('.create_invoice_form').remove();
                parent.after(data);
                create_review_action();
            },
            error: function (errorThrown) {
            }
        });
    });

    function enable_star_action() {
        jQuery('.empty_star').hover(
            function () {
                var loop, index;
                index = jQuery('.empty_star').index(this);
                jQuery('.empty_star').each(function () {
                    loop = jQuery('.empty_star').index(this);
                    if (loop <= index) {
                        jQuery(this).addClass('starselected');
                    } else {
                        jQuery(this).removeClass('starselected');
                    }
                });
            },
            function () {
            }
        );
    }

    function create_review_action() {
        enable_star_action();
        jQuery('#post_review').click(function () {
            var listing_id, ajaxurl, acesta, stars, content, parent, bookid;
            listing_id = jQuery(this).attr('data-listing_id');
            bookid = jQuery(this).attr('data-bookid');
            content = jQuery(this).parent().find('#review_content').val();
            stars = jQuery(this).parent().find('.starselected').length;
            ajaxurl = control_vars.admin_url + 'admin-ajax.php';
            acesta = jQuery(this);
            parent = jQuery(this).parent().parent();
            jQuery(this).text(dashboard_vars.sending);


            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'wpestate_post_review',
                    'listing_id': listing_id,
                    'bookid': bookid,
                    'stars': stars,
                    'content': content
                },
                success: function (data) {
                    jQuery('.create_invoice_form').remove();
                    jQuery('.post_review').remove();
                },
                error: function (errorThrown) {
                }
            });
        });
    }

    //////////////////////////////////////////////////////////////////////////////////////
    /// remind email
    ///////////////////////////////////////////////////////////////////////////////////////

    $('.full_invoice_reminder').click(function () {
        var invoice_id, booking_id, ajaxurl, acesta, parent;
        booking_id = $(this).attr('data-bookid');
        invoice_id = $(this).attr('data-invoiceid');
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        acesta = $(this);
        parent = $(this).parent().parent();
        $(this).text(dashboard_vars.sending);
        $(this).unbind('click');
        acesta = $(this);

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_send_full_pay_reminder',
                'invoice_id': invoice_id,
                'booking_id': booking_id
            },
            success: function (data) {

                acesta.text(dashboard_vars.sent);
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    });


    //////////////////////////////////////////////////////////////////////////////////////
    /// confimed booking invoice
    ///////////////////////////////////////////////////////////////////////////////////////
    $('.confirmed_booking').click(function () {
        var invoice_id, booking_id, ajaxurl, acesta, parent;
        booking_id = $(this).attr('data-booking-confirmed');
        invoice_id = $(this).attr('data-invoice-confirmed');
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        acesta = $(this);
        parent = $(this).parent().parent();

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_show_confirmed_booking',
                'invoice_id': invoice_id,
                'booking_id': booking_id
            },
            success: function (data) {

                jQuery('.create_invoice_form').remove();
                parent.after(data);
                create_payment_action();
            },
            error: function (errorThrown) {
            }
        });
    });

    enable_invoice_actions();

    ///////////////////////////////////////////////////////////////////////////////////////
    /// proceed to payment
    ///////////////////////////////////////////////////////////////////////////////////////
    $('.proceed-payment,.proceed-payment_full').click(function () {
        var is_full, invoice_id, booking_id, ajaxurl, acesta, parent;
        invoice_id = $(this).attr('data-invoiceid');
        booking_id = $(this).attr('data-bookid');
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        acesta = $(this);
        parent = $(this).parent().parent();
        is_full = 0;
        if ($(this).hasClass('proceed-payment_full')) {
            is_full = 1;
        }

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_create_pay_user_invoice_form',
                'booking_id': booking_id,
                'invoice_id': invoice_id,
                'is_full': is_full
            },
            success: function (data) {
                jQuery('.create_invoice_form').remove();
                parent.after(data);
                create_payment_action();
            },
            error: function (errorThrown) {
                console.log(errorThrown);
            }
        });
    });


    ///////////////////////////////////////////////////////////////////////////////////////
    /// delete booking request
    ///////////////////////////////////////////////////////////////////////////////////////
    $('.delete_booking').click(function () {
        var booking_id, ajaxurl, acesta, isuser;
        booking_id = $(this).attr('data-bookid');
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        acesta = $(this);
        isuser = 0;
        if ($(this).hasClass('usercancel')) {
            isuser = 1;
        }
        $(this).empty().html(dashboard_vars.deleting);
        $('.create_invoice_form').remove();
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_delete_booking_request',
                'booking_id': booking_id,
                'isuser': isuser
            },
            success: function (data) {

                acesta.parent().parent().remove();
            },
            error: function (errorThrown) {
            }
        });
    });

    ///////////////////////////////////////////////////////////////////////////////////////
    /// cancel bookings by user or admin
    ///////////////////////////////////////////////////////////////////////////////////////
    $('.cancel_own_booking, .cancel_user_booking').click(function () {
        var booking_id, ajaxurl, acesta, listing_id;
        booking_id = $(this).attr('data-booking-confirmed');
        listing_id = $(this).attr('data-listing-id');
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        acesta = $(this);

        $(this).empty().html(dashboard_vars.deleting);
        $(".create_invoice_form").hide();
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_cancel_own_booking',
                'booking_id': booking_id,
                'listing_id': listing_id
            },
            success: function (data) {

                acesta.parent().parent().remove();
            },
            error: function (errorThrown) {
            }
        });
    });


});

///////////////////////////////////////////////////////////////////////////////////////
/// generate invoice form
///////////////////////////////////////////////////////////////////////////////////////
function create_generate_invoice_action() {
    "use strict";
    jQuery('.generate_invoice').click(function () {
        var parent, ajaxurl, bookid, acesta;
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        parent = jQuery(this).parent().parent();
        bookid = jQuery(this).attr('data-bookid');
        acesta = jQuery(this);
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_create_invoice_form',
                'bookid': bookid
            },
            success: function (data) {
                jQuery('.create_invoice_form').remove();
                parent.after(data);
                invoice_create_js();
            },
            error: function (errorThrown) {
            }
        });
    });
}

///////////////////////////////////////////////////////////////////////////////////////
/// delete invoice
///////////////////////////////////////////////////////////////////////////////////////
function create_delete_invoice_action() {
    "use strict";
    jQuery('.delete_invoice').click(function () {
        var invoice_id, ajaxurl, acesta, booking_id;
        booking_id = jQuery(this).attr('data-bookid');
        invoice_id = jQuery(this).attr('data-invoiceid');
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        acesta = jQuery(this);
        jQuery(this).empty().html('deleting...');
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_delete_invoice',
                'invoice_id': invoice_id,
                'booking_id': booking_id
            },
            success: function (data) {
                var book_id = acesta.parent().find('.delete_booking').attr('data-bookid');
                acesta.parent().find('.waiting_payment').after('<span class="generate_invoice" data-bookid="' + book_id + '">' + dashboard_vars.issue_inv1 + '</span>');
                acesta.parent().find('.waiting_payment').remove();
                acesta.remove();
                create_generate_invoice_action();
            },
            error: function (errorThrown) {
            }
        });
    });
}


function wpestate_calculate_deposit_js(book_down, book_down_fixed_fee, total_amm_compute) {
    var deposit;

    if (book_down_fixed_fee === 0) {
        deposit = (total_amm_compute * book_down / 100);
        deposit = Math.round(deposit * 100) / 100;
    } else {
        deposit = book_down_fixed_fee;
    }
    return deposit;
}


function invoice_create_js() {
    "use strict";
    jQuery('#add_inv_expenses,#add_inv_discount').click(function () {
        var acesta = jQuery(this);
        wpestate_recreate_invoice_manual_expenses(acesta);
    });

    function delete_expense_js() {
        "use strict";
        jQuery(".delete_exp").unbind("click");
        jQuery('.delete_exp').click(function (event) {
            var acesta = jQuery(this);
            wpestate_recreate_invoice_manual_expenses(acesta);
        });
    };

    function wpestate_recreate_invoice_manual_expenses(butonul) {
        var inv_service_fee_fixed, extra_guests, is_remove, taxes_value, security_dep, early_bird_percent,
            invoice_manual_extra, invoice_default_extra, inter_price, early_bird, inv_depozit, inv_balance, youearned,
            inv_service_fee, inv_taxes, book_down_fixed_fee, ex_name, ex_value, ex_value_show, new_row, total_amm,
            deposit, balance, book_down, cleaning_fee, city_fee, total_amm_compute, include_expenses;
        is_remove = 0;
        ex_name = jQuery('#inv_expense_name').val();
        ex_value = parseFloat(jQuery('#inv_expense_value').val());

        if (butonul.is('#add_inv_discount')) {

            ex_name = dashboard_vars.discount;
            ex_value = parseFloat(jQuery('#inv_expense_discount').val(), 10) * (-1);
        }

        if (butonul.hasClass('delete_exp')) {
            is_remove = 1;
            ex_name = 'nothng';
            ex_value = parseFloat(butonul.attr('data-delvalue')) * -1;
        }


        include_expenses = butonul.attr('data-include_ex');

        if (dashboard_vars.where_currency_symbol === 'before') {
            ex_value_show = dashboard_vars.currency_symbol + ' ' + '<span class="inv_data_value" data-clearprice="' + ex_value + '">' + ex_value + '</span>';
        } else {
            ex_value_show = '<span class="inv_data_value"  data-clearprice="' + ex_value + '" >' + ex_value + '</span>' + ' ' + dashboard_vars.currency_symbol;
        }

        total_amm = parseFloat(jQuery('#total_amm').attr('data-total'));
        cleaning_fee = parseFloat(jQuery('#cleaning-fee').attr('data-cleaning-fee'));
        city_fee = parseFloat(jQuery('#city-fee').attr('data-city-fee'));
        early_bird = parseFloat(jQuery('#erarly_bird_row').attr('data-val'));
        inv_depozit = parseFloat(jQuery('#inv_depozit').attr('data-val'));
        inv_balance = parseFloat(jQuery('#inv_balance').attr('data-val'));
        youearned = parseFloat(jQuery('#youearned').attr('data-youearned'));
        inv_service_fee = parseFloat(jQuery('#inv_service_fee').attr('data-value'));


        inv_taxes = parseFloat(jQuery('#inv_taxes').attr('data-value'));
        inter_price = parseFloat(jQuery('#inter_price').attr('data-value'));
        security_dep = parseFloat(jQuery('#security_depozit_row').attr('data-val'));
        early_bird_percent = parseFloat(jQuery('#property_details_invoice').attr('data-earlyb'));
        taxes_value = parseFloat(jQuery('#property_details_invoice').attr('data-taxes_value'));
        extra_guests = parseFloat(jQuery('#extra-guests').attr('data-extra-guests'));

        invoice_default_extra = 0;
        jQuery('.invoice_default_extra').each(function () {
            invoice_default_extra = invoice_default_extra + parseFloat(jQuery(this).attr('data-value'));
        });

        invoice_manual_extra = ex_value;
        jQuery('.invoice_manual_extra').each(function () {
            invoice_manual_extra = invoice_manual_extra + parseFloat(jQuery(this).attr('data-value'));
        });


        if (isNaN(cleaning_fee)) {
            cleaning_fee = 0;
        }
        if (isNaN(city_fee)) {
            city_fee = 0;
        }
        if (isNaN(extra_guests)) {
            extra_guests = 0;
        }
        if (isNaN(early_bird)) {
            early_bird = 0;
        }
        if (isNaN(inv_taxes)) {
            inv_taxes = 0;
        }

        if (isNaN(inv_service_fee_fixed)) {
            inv_service_fee_fixed = 0;
        }


        if (isNaN(youearned)) {
            youearned = 0;
        }
        if (isNaN(inter_price)) {
            inter_price = 0;
        }
        if (isNaN(security_dep)) {
            security_dep = 0;
        }


        if (include_expenses === 'yes') {
            total_amm_compute = total_amm;
        } else {
            total_amm_compute = total_amm - city_fee - cleaning_fee;
        }


        //total_amm_compute       =   total_amm  ;
        if (ex_name !== '' && ex_value !== '' && ex_name !== 0 && ex_value !== 0 && !isNaN(ex_value)) {

            if (is_remove == 1) {
                butonul.parent().remove();
            } else {
                new_row = '<div class="invoice_row invoice_content manual_ex"><span class="inv_legend">' + ex_name + '</span><span class="inv_data invoice_manual_extra" data-value="' + ex_value + '">' + ex_value_show + '</span><span class="inv_exp"></span><span class="delete_exp" data-include_ex="' + include_expenses + '" data-delvalue="' + ex_value + '"><i class="fa fa-times"></i></span></div>';
                jQuery('.invoice_total').before(new_row);
                jQuery('#inv_expense_name').val('');
                jQuery('#inv_expense_value').val('');
                jQuery('#inv_expense_discount').val('');
            }


            if (early_bird > 0) {
                early_bird = (inter_price + invoice_default_extra + invoice_manual_extra + extra_guests) * early_bird_percent / 100;
            }


            var service_fee = parseFloat(dashboard_vars.service_fee);
            inv_service_fee_fixed = parseFloat(dashboard_vars.service_fee_fixed_fee);


            total_amm = (inter_price + invoice_default_extra + invoice_manual_extra + extra_guests) - early_bird + city_fee + cleaning_fee + security_dep;


            if (parseFloat(inv_service_fee_fixed, 10) > 0) {

                inv_service_fee = parseFloat(inv_service_fee_fixed);
            } else {

                inv_service_fee = (total_amm - security_dep - city_fee - cleaning_fee) * service_fee / 100;
            }


            youearned = total_amm - security_dep - city_fee - cleaning_fee - inv_service_fee;
            youearned = Math.round(youearned * 100) / 100;


            inv_taxes = youearned * taxes_value / 100;
            inv_taxes = Math.round(inv_taxes * 100) / 100;


            book_down = parseFloat(dashboard_vars.book_down);
            book_down_fixed_fee = parseFloat(dashboard_vars.book_down_fixed_fee);

            deposit = wpestate_calculate_deposit_js(book_down, book_down_fixed_fee, total_amm);
            deposit = Math.round(deposit * 100) / 100;

            balance = total_amm - deposit;
            balance = Math.round(balance * 100) / 100;

            delete_expense_js();
            jQuery('#total_amm').attr('data-total', total_amm);
            if (dashboard_vars.where_currency_symbol === 'before') {
                jQuery('#inv_depozit').empty().html(dashboard_vars.currency_symbol + ' ' + deposit);
                jQuery('#inv_depozit').attr('data-value', deposit);
                jQuery('#inv_balance').empty().html(dashboard_vars.currency_symbol + ' ' + balance);
                jQuery('#total_amm').empty().append(dashboard_vars.currency_symbol + ' ' + total_amm);

                jQuery("#youearned").attr('data-value', youearned);
                jQuery("#youearned").empty().html(dashboard_vars.currency_symbol + ' ' + youearned);

                jQuery("#inv_service_fee").attr('data-value', inv_service_fee);
                jQuery("#inv_service_fee").empty().html(dashboard_vars.currency_symbol + ' ' + inv_service_fee);

                jQuery("#inv_taxes").attr('data-value', inv_taxes);
                jQuery("#inv_taxes").empty().html(dashboard_vars.currency_symbol + ' ' + inv_taxes);

                jQuery("#erarly_bird_row").attr('data-value', early_bird);
                jQuery("#erarly_bird_row inv_data_value").attr('data-clearprice', early_bird);
                jQuery("#erarly_bird_row").empty().html(dashboard_vars.currency_symbol + '<span class="inv_data_value" data-clearprice="' + early_bird + '"> ' + early_bird + '</span> ');


            } else {
                jQuery('#inv_depozit').empty().html(deposit + ' ' + dashboard_vars.currency_symbol);
                jQuery('#inv_depozit').attr('data-value', deposit);
                jQuery('#inv_balance').empty().html(balance + ' ' + dashboard_vars.currency_symbol);
                jQuery('#total_amm').empty().append(total_amm + ' ' + dashboard_vars.currency_symbol);

                jQuery("#youearned").attr('data-value', youearned);
                jQuery("#youearned").empty().html(youearned + ' ' + dashboard_vars.currency_symbol);

                jQuery("#inv_service_fee").attr('data-value', inv_service_fee);
                jQuery("#inv_service_fee").empty().html(inv_service_fee + ' ' + dashboard_vars.currency_symbol);

                jQuery("#inv_taxes").attr('data-value', inv_taxes);
                jQuery("#inv_taxes").empty().html(inv_taxes + ' ' + dashboard_vars.currency_symbol);

                jQuery("#erarly_bird_row").attr('data-value', early_bird);
                jQuery("#erarly_bird_row inv_data_value").attr('data-clearprice', early_bird);
                jQuery("#erarly_bird_row").empty().html('<span class="inv_data_value" data-clearprice="' + early_bird + '"> ' + early_bird + '</span> ' + dashboard_vars.currency_symbol);


            }
        }
    }


    ///////////////////////////////////////////////////////////////////////////////////////
    /// send invoice
    ///////////////////////////////////////////////////////////////////////////////////////
    jQuery('#invoice_submit').click(function () {
        var is_available, is_confirmed, parent, nonce, ajaxurl, bookid, price, details, details_item,
            manual_expenses_item, manual_expenses, acesta, to_be_paid, youearned;
        details = new Array();
        manual_expenses = new Array();
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        bookid = jQuery(this).attr('data-bookid');
        is_confirmed = jQuery(this).attr('data-is_confirmed');
        price = parseFloat(jQuery('#total_amm').attr('data-total'));
        youearned = parseFloat(jQuery('#youearned').attr('data-youearned'));
        parent = jQuery(this).parent().parent().prev();
        acesta = jQuery(this);
        nonce = jQuery('#security-create_invoice_ajax_nonce').val();
        to_be_paid = parseFloat(jQuery('#inv_depozit').attr('data-value'));

        jQuery(this).text(control_vars.pls_wait);


        jQuery('.invoice_content').each(function () {
            details_item = new Array();
            details_item[0] = jQuery(this).find('.inv_legend').text();
            details_item[1] = jQuery(this).find('.inv_data_value').attr('data-clearprice');
            details_item[2] = jQuery(this).find('.inv_data_exp').text();

            details.push(details_item);
        });


        jQuery('.manual_ex').each(function () {
            manual_expenses_item = new Array();
            manual_expenses_item[0] = jQuery(this).find('.inv_legend').text();
            manual_expenses_item[1] = jQuery(this).find('.inv_data_value').attr('data-clearprice');
            manual_expenses_item[2] = jQuery(this).find('.inv_data_exp').text();
            manual_expenses.push(manual_expenses_item);
        });


        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_add_booking_invoice',
                'bookid': bookid,
                'price': price,
                'details': details,
                'manual_expenses': manual_expenses,
                'to_be_paid': to_be_paid,
                'youearned': youearned,
                'is_confirmed': is_confirmed,
                'security': nonce
            },
            success: function (data) {


                if (data === 'stop') {
                    jQuery('.alert_error').remove();
                    acesta.before('<span class="alert_error"> ' + dashboard_vars.doublebook + '</span>');
                } else {
                    parent.find('.invoice_list_id').html(data);
                    parent.find('.generate_invoice').after('<span class="delete_invoice" data-invoiceid="' + data + '" data-bookid="' + bookid + '">' + dashboard_vars.delete_inv + '</span>');
                    parent.find('.generate_invoice').after('<span class="waiting_payment">' + dashboard_vars.issue_inv + '</span>');
                    parent.find('.generate_invoice').remove();
                    parent.find('#inv_new_price').empty().append(price);
                    jQuery('.create_invoice_form').remove();
                    create_delete_invoice_action();
                }
            },
            error: function (errorThrown) {
                console.log(errorThrown)
            }
        });
    });


    function check_booking_valability_on_invoice_(bookid) {
        "use strict";
        var bookid, ajaxurl;
        exit();

        ajaxurl = control_vars.admin_url + 'admin-ajax.php';

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_ajax_check_booking_valability_on_invoice',
                'bookid': bookid,

            },
            success: function (data) {

                if (data === 'run') {
                    return 1;
                } else {
                    return 0;
                }
            },
            error: function (errorThrown) {
            }
        });
    }


    ///////////////////////////////////////////////////////////////////////////////////////
    /// direct confirmation for booking invoice
    ///////////////////////////////////////////////////////////////////////////////////////
    jQuery('#direct_confirmation').click(function () {
        var parent, nonce, ajaxurl, bookid, price, details, acesta, details_item;
        details = new Array();
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        bookid = jQuery(this).attr('data-bookid');
        price = jQuery('#total_amm').attr('data-total');
        parent = jQuery(this).parent().parent().prev();
        acesta = jQuery(this);
        nonce = jQuery('#security-create_invoice_ajax_nonce').val();
        jQuery('.invoice_content').each(function () {
            details_item = new Array();
            details_item[0] = jQuery(this).find('.inv_legend').text();
            details_item[1] = jQuery(this).find('.inv_data_value').attr('data-clearprice');
            details_item[2] = jQuery(this).find('.inv_data_exp').text();
            details.push(details_item);
        });
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_add_booking_invoice',
                'bookid': bookid,
                'price': price,
                'details': details,
                'security': nonce
            },
            success: function (data) {


                if (data === "doublebook") {

                    acesta.after('<div class="delete_booking" style="float:left;">' + dashboard_vars.doublebook + '</div>');
                    acesta.remove();
                } else {
                    parent.find('.generate_invoice').after('<span class="tag-published">' + dashboard_vars.confirmed + '</span>');
                    parent.find('.generate_invoice').remove();
                    parent.find('.delete_booking').remove();
                    jQuery('.create_invoice_form').remove();
                }
            },
            error: function (errorThrown) {
            }
        });
    });
} // end function 


function create_payment_action() {
    "use strict";


    jQuery('#confirm_zero_instant_booking').click(function () {
        var ajaxurl, pay_paypal, prop_id, book_id, invoice_id, is_featured, is_upgrade, depozit;
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';

        prop_id = jQuery(this).attr('data-propid');
        book_id = jQuery(this).attr('data-bookid');
        invoice_id = jQuery(this).attr('data-invoiceid');
        depozit = jQuery(this).attr('data-deposit');

        jQuery(this).text(control_vars.pls_wait);

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_admin_activate_reservation_fee',
                'book_id': book_id,
                'invoice_id': invoice_id,
            },
            success: function (data) {
                window.open(control_vars.my_reservations_url, '_self', false);
            },
            error: function (errorThrown) {
            }
        });

    });


    jQuery('#paypal_booking').click(function () {
        var pay_paypal, prop_id, book_id, invoice_id, is_featured, is_upgrade, depozit;
        prop_id = jQuery(this).attr('data-propid');
        book_id = jQuery(this).attr('data-bookid');
        invoice_id = jQuery(this).attr('data-invoiceid');
        depozit = jQuery(this).attr('data-deposit');

        is_featured = 0;
        is_upgrade = 0;
        pay_paypal = '<div class="modal fade" id="paypal_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body listing-submit">' + ajaxcalls_vars.paypal + '</div></div></div></div></div>';
        jQuery('body').append(pay_paypal);
        jQuery('#paypal_modal').modal();
        wpestate_booking_cretupm_paypal(prop_id, book_id, invoice_id, depozit);
    });

    jQuery('#direct_pay_booking').click(function () {
        var pay_paypal, prop_id, book_id, invoice_id, is_featured, is_upgrade;
        prop_id = jQuery(this).attr('data-propid');
        book_id = jQuery(this).attr('data-bookid');
        invoice_id = jQuery(this).attr('data-invoiceid');
        is_featured = 0;
        is_upgrade = 0;
        enable_booking_direct_pay(prop_id, book_id, invoice_id);
    });
}


function enable_booking_direct_pay(prop_id, book_id, invoice_id) {
    var is_full_pay, price_pack, direct_pay_modal, selected_pack, selected_prop, include_feat, attr, price_pack;


    if (jQuery("#is_full_pay").length == 0) {
        price_pack = parseFloat(jQuery('.depozit_show').attr('data-value'), 10);
    } else {
        price_pack = parseFloat(jQuery('#is_full_pay').val());
    }


    var float_price_pack = price_pack;

    if (control_vars.where_curency === 'after') {
        price_pack = price_pack + ' ' + control_vars.submission_curency;
    } else {
        price_pack = control_vars.submission_curency + ' ' + price_pack;
    }

    price_pack = control_vars.direct_price + ': ' + price_pack;

    if (selected_pack !== '') {
        window.scrollTo(0, 0);
        direct_pay_modal = '<div class="modal fade" id="direct_pay_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h2 class="modal-title_big">쿠폰 결제</h2></div><div class="modal-body listing-submit"><span class="to_be_paid">쿠폰 1개가 소진됩니다.</span><div id="send_direct_bill_booking" data-invoiceid="' + invoice_id + '"  data-propid="' + prop_id + '" data-bookid="' + book_id + '">쿠폰 결제하기</div></div></div></div></div></div>';
        jQuery('body').append(direct_pay_modal);
        jQuery('#direct_pay_modal').modal();
        enable_booking_direct_pay_button(float_price_pack);
    }

    jQuery('#direct_pay_modal').on('hidden.bs.modal', function (e) {
        jQuery('#direct_pay_modal').remove();
    })
}

function enable_booking_direct_pay_button(price_pack) {
    jQuery('#send_direct_bill_booking').unbind('click');
    jQuery('#send_direct_bill_booking').click(function () {
        jQuery('#send_direct_bill_booking').unbind('click');
        var invoiceid, ajaxurl, propid, book_id;

        invoiceid = jQuery(this).attr('data-invoiceid');
        propid = jQuery(this).attr('data-propid')
        book_id = jQuery(this).attr('data-bookid')
        ajaxurl = ajaxcalls_vars.admin_url + 'admin-ajax.php';


        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_direct_pay_booking',
                'invoiceid': invoiceid,
                'propid': propid,
                'book_id': book_id,
                'price_pack': price_pack
            },
            success: function (data) {
                if (data.includes("not_enough")) {
                    jQuery('#send_direct_bill_booking').hide();
                    jQuery('#direct_pay_modal .listing-submit span:nth-child(1)').empty().html("쿠폰이 부족하여 결제에 실패하였습니다.<br><a href='http://nagne.kr/shop/'>쿠폰 구매하기</a>");
                } else if (data.includes("already_finished")) {
                    jQuery('#send_direct_bill_booking').hide();
                    jQuery('#direct_pay_modal .listing-submit span:nth-child(1)').empty().html("이미 결제된 항목입니다.<br> 3초 후 새로고침됩니다.");
                    setTimeout(function () {
                        location.reload(true);
                    }, 3000);
                } else if (data.includes("success")) {
                    jQuery('#send_direct_bill_booking').hide();
                    jQuery('#direct_pay_modal .listing-submit span:nth-child(1)').empty().html("결제에 성공하였습니다.<br> 3초 후 새로고침됩니다.");
                    setTimeout(function () {
                        location.reload(true);
                    }, 3000);
                }
                //jQuery('#send_direct_bill_booking').hide();
                //jQuery('#direct_pay_modal .listing-submit span:nth-child(2)').empty().html(control_vars.direct_thx);
            },
            error: function (errorThrown) {
            }
        });//end ajax

    });
}


function wpestate_booking_cretupm_paypal(prop_id, book_id, invoice_id, depozit) {
    "use strict";
    var ajaxurl = control_vars.admin_url + 'admin-ajax.php';
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action': 'wpestate_ajax_booking_pay',
            'propid': prop_id,
            'bookid': book_id,
            'invoice_id': invoice_id,
            'depozit': depozit
        },
        success: function (data) {

            window.location.href = data;
        },
        error: function (errorThrown) {
        }
    });//end ajax
}


function filter_invoices() {
    "use strict";
    var ajaxurl, start_date, end_date, type, status;
    start_date = jQuery('#invoice_start_date').val();
    end_date = jQuery('#invoice_end_date').val();
    type = jQuery('#invoice_type').val();
    status = jQuery('#invoice_status').val();


    ajaxurl = control_vars.admin_url + 'admin-ajax.php';
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            'action': 'wpestate_ajax_filter_invoices',
            'start_date': start_date,
            'end_date': end_date,
            'type': type,
            'status': status
        },
        success: function (data) {

            jQuery('#container-invoices').empty().append(data.results);
            jQuery('#invoice_issued').empty().append(data.invoice_issued);
            jQuery('#invoice_confirmed').empty().append(data.invoice_confirmed);
            enable_invoice_actions();

        },

        error: function (errorThrown) {
        }
    });//end ajax
}

function enable_invoice_actions() {
    jQuery('.invoice_unit').click(function () {
        var invoice_id, booking_id, ajaxurl, acesta, parent;
        booking_id = jQuery(this).attr('data-booking-confirmed');
        invoice_id = jQuery(this).attr('data-invoice-confirmed');
        ajaxurl = control_vars.admin_url + 'admin-ajax.php';
        acesta = jQuery(this);
        parent = jQuery(this);

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpestate_show_invoice_dashboard',
                'invoice_id': invoice_id,
                'booking_id': booking_id
            },
            success: function (data) {

                jQuery('.create_invoice_form').remove();
                parent.append(data);
                create_payment_action();
            },
            error: function (errorThrown) {
            }
        });
    });

}