<?php
get_header();
global $term;
global $taxonmy;
global $options;
$options        =   wpestate_page_details('');
$filtred        =   0;
$show_compare   =   1;
$compare_submit =   wpestate_get_compare_link();


// get curency , currency position and no of items per page
$current_user       =   wp_get_current_user();
$currency           =   esc_html( get_option('wp_estate_currency_label_main','') );
$where_currency     =   esc_html( get_option('wp_estate_where_currency_symbol','') );
$prop_no            =   intval( get_option('wp_estate_prop_no','') );
$userID             =   $current_user->ID;
$user_option        =   'favorites'.$userID;
$curent_fav         =   get_option($user_option);


$taxonmy    = get_query_var('taxonomy');
$term       = get_query_var( 'term' );
$tax_array  = array(
                'taxonomy'  => $taxonmy,
                'field'     => 'slug',
                'terms'     => $term
            );
 

$paged= (get_query_var('paged')) ? get_query_var('paged') : 1;
    
$args = array(
    'post_type'         => 'estate_property',
    'post_status'       => 'publish',
    'paged'             => $paged,
    'posts_per_page'    => $prop_no,
    'meta_key'          => 'prop_featured',
    'orderby'           => 'meta_value',
    'order'             => 'DESC',
    'tax_query'         => array(
                            'relation' => 'AND',
                            $tax_array
                        )
);	



add_filter( 'posts_orderby', 'wpestate_my_order' );
$prop_selection = new WP_Query($args);
remove_filter( 'posts_orderby', 'wpestate_my_order' );

$property_list_type_status =    esc_html(get_option('wp_estate_property_list_type',''));

if ( $property_list_type_status == 2 ){
    get_template_part('templates/half_map_core');
}else{
    get_template_part('templates/normal_map_core');
}


if (wp_script_is( 'wpestate_googlecode_regular', 'enqueued' )) {
    $mapargs                    =   $args;
    $max_pins                   =   intval( get_option('wp_estate_map_max_pins') );
    $mapargs['posts_per_page']  =   $max_pins;
    $mapargs['offset']          =   ($paged-1)*$prop_no;
   // $mapargs['paged']           =   1;
//  print_r($args);    
//  print_r($mapargs);
    $selected_pins  =   wpestate_listing_pins($mapargs,1);//call the new pins   
    wp_localize_script('wpestate_googlecode_regular', 'googlecode_regular_vars2', 
                array('markers2'          =>  $selected_pins,
                      'taxonomy'          =>  $taxonmy,
                      'term'              =>  $term));

}



get_footer(); 
?>