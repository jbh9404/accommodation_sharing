<?php

add_action('wp_ajax_wpestate_delete_file',             'wpestate_delete_file');
add_action('wp_ajax_noprivwpestate_delete_file',        'wpestate_delete_file');




if( !function_exists('wpestate_delete_file') ):
    function wpestate_delete_file(){
       
        $current_user = wp_get_current_user();
        $userID =   $current_user->ID;
      
        if ( !is_user_logged_in() ) {   
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }
     
        
        $attach_id = intval($_POST['attach_id']);
        
        $the_post= get_post( $attach_id); 

        if( $userID != $the_post->post_author ) {
            exit('you don\'t have the right to delete this');;
        }
        
        wp_delete_attachment($attach_id, true);
        exit;
    }
endif;



add_action('wp_ajax_wpestate_me_upload',             'wpestate_me_upload');
add_action('wp_ajax_aaiu_delete',           'wpestate_me_delete_file');
add_action('wp_ajax_nopriv_wpestate_me_upload',      'wpestate_me_upload');
add_action('wp_ajax_nopriv_aaiu_delete',    'wpestate_me_delete_file');


add_action('wp_ajax_me_upload_demo',             'me_upload_demo');
add_action('wp_ajax_nopriv_me_upload_demo',             'me_upload_demo');


function wpestate_me_delete_file(){
    $current_user = wp_get_current_user();
    $userID =   $current_user->ID;

    if ( !is_user_logged_in() ) {   
        exit('ko');
    }
    if($userID === 0 ){
        exit('out pls');
    }



    $attach_id = intval($_POST['attach_id']);
    $the_post= get_post( $attach_id); 

    if( $current_user->ID != $the_post->post_author ) {
        exit('you don\'t have the right to delete this');;
    }

 
    wp_delete_attachment($attach_id, true);
    exit;
}

function wpestate_me_upload(){
    $current_user = wp_get_current_user();
    $userID                         =   $current_user->ID;


    if ( !is_user_logged_in() ) {   
        exit('ko');
    }
    if($userID === 0 ){
        exit('out pls');
    }


    $file = array(
        'name'      => $_FILES['aaiu_upload_file']['name'],
        'type'      => $_FILES['aaiu_upload_file']['type'],
        'tmp_name'  => $_FILES['aaiu_upload_file']['tmp_name'],
        'error'     => $_FILES['aaiu_upload_file']['error'],
        'size'      => $_FILES['aaiu_upload_file']['size']
    );
    $file = fileupload_process($file);
}  
    
    
    
function fileupload_process($file){

    $attachment = handle_file($file);

    if (is_array($attachment)) {
        $html = getHTML($attachment);

        $response = array(
            'success'   => true,
            'html'      => $html,
            'attach'    => $attachment['id']
        );

        echo json_encode($response);
        exit;
    }

    $response = array('success' => false);
    echo json_encode($response);
    exit;
}
    
    
function handle_file($upload_data){

    $return = false;
    $uploaded_file = wp_handle_upload($upload_data, array('test_form' => false));

    if (isset($uploaded_file['file'])) {
        $file_loc   =   $uploaded_file['file'];
        $file_name  =   basename($upload_data['name']);
        $file_type  =   wp_check_filetype($file_name);

        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_name)),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id      =   wp_insert_attachment($attachment, $file_loc);
        $attach_data    =   wp_generate_attachment_metadata($attach_id, $file_loc);
        wp_update_attachment_metadata($attach_id, $attach_data);
        $return = array('data' => $attach_data, 'id' => $attach_id);
        
        
        return $return;
    }

    return $return;
}
    
    
function getHTML($attachment){
    $attach_id  =   $attachment['id'];
    $file='';
    $html='';

    if( isset($attachment['data']['file'])){
        $file       =   explode('/', $attachment['data']['file']);
        $file       =   array_slice($file, 0, count($file) - 1);
        $path       =   implode('/', $file);

        if(is_page_template('user_dashboard_add_step1.php') ){
            $image      =   $attachment['data']['sizes']['thumbnail']['file'];
        }else{
            $image      =   $attachment['data']['sizes']['wpestate_property_listings']['file'];
            if ($image==''){
                $get_name=explode('/',$attachment['data']['file']);
                $image      = $get_name[2]  ;
            }
        }
        $post       =   get_post($attach_id);
        $dir        =   wp_upload_dir();
        $path       =   $dir['baseurl'] . '/' . $path;
        $html       =   '';
        
        $current_user = wp_get_current_user();
          
        //if( is_page_template('user_dashboard_profile.php') ){
          //  update_user_meta($userID, 'custom_picture', $path.'/'.$image);
       // }
        $userID  =   $current_user->ID;
        $html   .=   $path.'/'.$image; 

    }
    
   
    
    return $html;
}






if( !function_exists('me_upload_demo') ):
    function me_upload_demo(){
    

 
        $filename = convertAccentsAndSpecialToNormal($_FILES['aaiu_upload_file']['tmp_name']);
        $base='';
        $allowed_html =array();
    
        
        $file = array(
            'name'      => convertAccentsAndSpecialToNormal($_FILES['aaiu_upload_file']['name']),
            'type'      => $_FILES['aaiu_upload_file']['type'],
            'tmp_name'  => $_FILES['aaiu_upload_file']['tmp_name'],
            'error'     => $_FILES['aaiu_upload_file']['error'],
            'size'      => $_FILES['aaiu_upload_file']['size'],
           
        );
        $file = fileupload_process_demo_import($file);
    }  
endif;    



function fileupload_process_demo_import($file){

    
        $attachment = handle_file_demo_import($file);

        if($attachment!='not'){
            $response = array('success' => true,'template'=>$attachment);
            echo json_encode($response);
        }
        exit;
}

    
function handle_file_demo_import($upload_data){
        $return = false;
        $name = $_FILES['aaiu_upload_file']['name'];
        $uploadedfile = $upload_data;
        $upload_overrides = array(
            'test_form' => false
        );
        
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            
        } 
        else {
            echo $movefile['error'];
        }

        
        $upload_dir = wp_upload_dir();
        $file_path = $movefile['file'];
        
    
        WP_Filesystem();
        global $wp_filesystem;
        
        $upload_dir = wp_upload_dir();
        $destination = $upload_dir['basedir'];
        $destination_path = $destination . '/estate_templates';
       
        if (is_dir($destination_path)){
            //nothing
        }else{
            mkdir($destination_path);
        }
       
       
        
        
        



        $unzipfile = unzip_file( $file_path, $destination_path);
      
       if ( $unzipfile ) {
        $return=$name;
       } else {
         $return='not';
       }

        //wp_die();
        return $return;
    }    
?>