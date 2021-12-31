<?php
/**
 * Plugin Name: 나그네 쿠폰 플러그인
 * Plugin URI: http://didimstory.com
 * Description: 나그네 쿠폰 시스템 적용을 위한 플러그인
 * Version: 1.0
 * Author: Sangkyeol Kim
 * Author URI: mailto://skkim@didimstory.com
 * Text Domain: wp_ng_coupon
 * License: Private
 *
 * @link http://codex.wordpress.org/Writing_a_Plugin#File_Headers
 */

$main_file = __FILE__;

/**
 * 플러그인이 활성화 될 때의 액션입니다.
 * @link http://codex.wordpress.org/Function_Reference/register_activation_hook
 */
register_activation_hook($main_file, 'wp_ng_coupon_on_activated');

/**
 * register_activation_hook callback
 */
function wp_ng_coupon_on_activated()
{

    // 플러그인 활성화 때 필요한 rewrite 규칙을 설정해둡니다.
    wp_ng_coupon_rewrite_rule();
    flush_rewrite_rules();

    /**
     * @link http://codex.wordpress.org/Function_Reference/update_option
     * @link http://codex.wordpress.org/Function_Reference/get_currentuserinfo
     */
    update_option('wp_ng_coupon_option_name', wp_get_current_user()->display_name);
}

/**
 * 플러그인이 비활성화 될 때의 액션입니다.
 * @link http://codex.wordpress.org/Function_Reference/register_deactivation_hook
 */
register_deactivation_hook($main_file, 'wp_ng_coupon_on_deactivated');

/**
 * register_deactivation_hook callback
 */
function wp_ng_coupon_on_deactivated()
{

    // 플러그인 비활성화 때 더 이상 사용하지 않을 우리의 rewrite 규칙은 삭제합니다.
    flush_rewrite_rules();

    /**
     * @link http://codex.wordpress.org/Function_Reference/get_option
     */
    if (FALSE !== get_option('wp_ng_coupon_option_name', FALSE)) {
        /**
         * @link http://codex.wordpress.org/Function_Reference/delete_option
         */
        delete_option('wp_ng_coupon_option_name');
    }
}

/**
 * 플러그인이 완전히 삭제될 때의 액션입니다.
 * @link http://codex.wordpress.org/Function_Reference/register_uninstall_hook
 */
register_uninstall_hook($main_file, 'wp_ng_coupon_on_uninstall');

/**
 * register_uninstall_hook callback
 */
function wp_plugin_tutorial_on_uninstall()
{
    // uninstall code ...
}

/**
 * 플러그인의 메뉴를 삽입합니다. 옵션 페이지를 삽입합니다.
 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
 */
//add_action('admin_menu', 'wp_ng_coupon_add_admin_menu');

/**
 * admin_menu action callback
 * 워드프레스 대시보드 관리 메뉴에 이 플러그인의 메뉴를 출력.
 *
 * 구조
 * ====
 * 플러그인 메인 메뉴
 *   > 플러그인 메인 메뉴
 *   > 하위 메뉴 #1
 *   > 하위 메뉴 #2
 * 설정 메뉴 (기존의 워드프레스 기본 메뉴)
 *   > 우리 플러그인의 설정 메뉴
 */
function wp_ng_coupon_add_admin_menu()
{

    /**
     * 메인 메뉴 페이지를 삽입
     * @link http://codex.wordpress.org/Function_Reference/add_menu_page
     */
    add_menu_page(
        __('나그네 쿠폰 관리'),     // 웹브라우저 상단에 보일 문자 (<title> 태그)
        __('나그네 쿠폰 관리'),     // 메뉴 페이지에 보일 문자
        'manage_options',                                           // 플러그인 접근 권한
        'wp_ng_coupon_main_menu',                             // 메뉴 슬러그
        'wp_ng_coupon_main_menu_callback',                    // 콜백 함수
        '',                                                         // (옵션) 아이콘 URL
        NULL                                                        // (옵션) 메뉴 출력 위치
    );

    /**
     * 두 개의 하위 메뉴를 삽입
     * @link http://codex.wordpress.org/Function_Reference/add_submenu_page
     */
    add_submenu_page(
        'wp_ng_coupon_main_menu',                             // 부모 페이지의 슬러그
        __('나그네 쿠폰 관리_1'),                     // 웹브라우저 상단에 보일 문자 (<title> 태그)
        __('나그네 쿠폰 관리_1'),                     // 메뉴 페이지에 보일 문자
        'manage_options',                                           // 접근 권한
        'wp_ng_coupon_submenu_1',                             // 이 하위 메뉴의 슬러그
        'wp_ng_coupon_submenu_1_callback'                     // 콜백 함수
    );

    add_submenu_page(
        'wp_ng_coupon_main_menu',
        __('나그네 쿠폰 관리_2'),
        __('나그네 쿠폰 관리_2'),
        'manage_options',
        'wp_ng_coupon_submenu_2',
        'wp_ng_coupon_submenu_2_callback'
    );

    /**
     * 메인 메뉴 밑에 바로 하위 메뉴로 반복되는 첫 번째 항목을 삭제합니다.
     * @link http://codex.wordpress.org/Function_Reference/remove_submenu_page
     */
    // remove_submenu_page( 'wp_plugin_tutorial_main_menu', 'wp_plugin_tutorial_main_menu' );

    /**
     * 설정 페이지를 삽입
     * @link http://codex.wordpress.org/Function_Reference/add_options_page
     */
    add_options_page(
        __('나그네 쿠폰 관리_3'),              // 웹브라우저 상단에 보일 문자 (<title> 태그)
        __('나그네 쿠폰 관리_3'),              // 메뉴 페이지에 보일 문자
        'manage_options',                                           // 접근 권한
        'wp_ng_coupon_option',                                // 슬러그
        'wp_ng_coupon_option_callback'                        // 콜백 함수
    );
}

/**
 * 옵션 메뉴를 구축합니다.
 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
 */
//add_action('admin_init', 'wp_ng_coupon_settings');
function wp_ng_coupon_settings()
{

    /**
     * 옵션을 등록
     * @link http://codex.wordpress.org/Function_Reference/register_setting
     * @link http://codex.wordpress.org/Data_Validation
     */
    register_setting(
        'wp_ng_coupon_option_group',         // 옵션 그룹
        'wp_ng_coupon_option_name',          // 옵션 이름. DB 테이블의 옵션 이름이 된다.
        'sanitize_text_field'                      // 값 세정을 위한 콜백
    );

    /**
     * 섹션을 등록
     * @link http://codex.wordpress.org/Function_Reference/add_settings_section
     */
    add_settings_section(
        'wp_ng_coupon_section',                                           // id
        __('나그네 쿠폰 관리 4'),                  // 섹션 제목
        'wp_ng_coupon_section_callback',                                  // 콜백
        'wp_ng_coupon_option'                                             // 이 섹션이 보여질 페이지 슬러그
    );

    /**
     * 필드를 등록
     * @link http://codex.wordpress.org/Function_Reference/add_settings_field
     */
    add_settings_field(
        'wp_ng_coupon_field',                                             // id
        __('나그네 쿠폰 관리 5'),                    // 필드 제목
        'wp_ng_coupon_field_callback',                                    // 콜백
        'wp_ng_coupon_option',                                            // 이 필드가 속한 페이지 슬러그
        'wp_ng_coupon_section',                                           // 이 필드가 속한 섹션 id
        array(
            'id' => 'wp_ng_coupon_field',        // 이 id는 첫번째 함수 인자와 동일해야 한다.
            'name' => 'wp_ng_coupon_option_name',  // 이 name DB 테이블 옵션 이름과 동일해야 한다.
            'value' => esc_attr(get_option('wp_ng_coupon_option_name')),
            'description' => '데이터베이스에 입력할 값.'
        )                                                                 // 콜백 함수로 들어가는 인자 목록을 키/값으로
    );
}

/**
 * 플러그인 로컬라이즈를 준비합니다.
 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/plugins_loaded
 */
add_action('plugins_loaded', 'wp_ng_coupon_localize');
function wp_ng_coupon_localize()
{
    // @link https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
    load_plugin_textdomain(
        'wp_ng_coupon',                              // textdomain
        FALSE,                                             // deprecated
        dirname(plugin_basename(__FILE__)) . '/lang'   // plugin_rel_path
    );
}

/**
 * 하위 메뉴 #1에서 전송하는 POST 전송에 대한 대응을 준비합니다.
 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_post_%28action%29
 */
//add_action('admin_post_greet_repeat', 'wp_ng_coupon_greet_repeat_callback');

/**
 * 하위 메뉴 #2에서 AJAX 방식 호출에 대한 대응을 준비합니다.
 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_%28action%29
 */
//add_action('wp_ajax_request_code', 'wp_ng_coupon_request_code_callback');


/**
 * 워드프레스에서 rewrite 하도록 규칙을 설정합니다.
 * 서버에서 rewrite가 허용되어야 합니다.
 */
add_action('init', 'wp_ng_coupon_rewrite_rule', 10, 0);
function wp_ng_coupon_rewrite_rule()
{

    /**
     * @link http://codex.wordpress.org/Rewrite_API/add_rewrite_rule
     */
    add_rewrite_rule('^tutorial/([^&]+)/?$', 'index.php?tutorial=$matches[1]', 'top');
}

/**
 * 특정 쿼리 변수를 허용하도록 필터를 걸어 줍니다.
 * wp_ng_coupon 이라는 쿼리 변수를 허용하도록 추가합니다.
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/query_vars
 */
add_filter('query_vars', 'wp_ng_coupon_custom_var');
function wp_ng_coupon_custom_var($vars)
{
    $vars[] = 'tutorial';
    return $vars;
}

/**
 * 쿼리 파싱할 때 원하는 동작이 일어나도록 액션을 겁니다.
 * query_vars 필터를 통해 wp_ng_coupon 쿼리 변수를 받도록 허용했으므로, 쿼리 변수에 숫자 값이 받아질 것입니다.
 */
add_action('parse_request', 'wp_ng_coupon_parse_request');
function wp_ng_coupon_parse_request()
{

    global $wp;

    $tutorial = isset($wp->query_vars['tutorial']) ? $wp->query_vars['tutorial'] : '';
    if (!empty($tutorial)) {
        add_action('template_redirect', 'wp_ng_coupon_template_redirect_callback');
    }
}

/**
 * 커스텀 포스트를 등록합니다.
 */
add_action('init', 'wp_ng_coupon_custom_post');
function wp_ng_coupon_custom_post()
{

    /**
     * @link http://codex.wordpress.org/Function_Reference/register_post_type
     */
    $labels = array(
        'name' => _x('나그네 쿠폰 관리'),  // 보통 복수의 이름. 전체 목록 화면 가장 상단에 출력.
        'singular_name' => _x('나그네 쿠폰 관리'),     // 단수 이름
        'menu_name' => _x('나그네 쿠폰 관리'),            // 메뉴에 나타날 텍스트
        //'name_admin_bar' => _x('Tutorial', 'add new on admin bar', 'wp_ng_coupon'),     // Add New 드롭다운 어드민 바에 나오는 내용
        //'add_new' => _x('[Add New]', 'tutorial', 'wp_ng_coupon'),                // 새 아이템 추가 텍스트
        //'add_new_item' => __('/Add New/', 'wp_ng_coupon'),                            // 새 아이템 추가 텍스트 (작성 화면에서 출력)
        // 'new_item' => __('New Tutorial', 'wp_ng_coupon'),                         // 새 튜토리얼 추가
        'all_items' => __('발행된 쿠폰'),                        // 모든 아이템
        //'edit_item' => __('Edit Tutorial', 'wp_ng_coupon'),                        // 아이템 수정 (수정 화면에서 화면 상단에 출력)
        'view_item' => __('View Tutorial', 'wp_ng_coupon'),                        // 아이템 조회 텍스트
        'search_items' => __('쿠폰 검색'),                     // 아이템 검색 텍스트
        'not_found' => __('발행된 쿠폰이 없습니다.'),                   // 찾지 못함 텍스트
        'not_found_in_trash' => __('No tutorial found in Trash', 'wp_ng_coupon'),           // 휴지통에서 찾지 못함
        'parent_item_colon' => __('Parent Tutorial:', 'wp_ng_coupon'),                     // 부모 페이지를 의미. 있을 경우에만.
    );

    $args = array(
        'labels' => $labels,
        'description' => '나그네 발행된 쿠폰 리스트',         // 설명
        'public' => TRUE,                                // 외부로 보이는 타입인지
        'show_ui' => TRUE,                                // 이 타입을 UI에서 볼 수 있는지 결정
        'hierarchical' => FALSE,
        'supports' => array('title', 'excerpt'),
        //'register_meta_box_cb' => 'wp_ng_coupon_meta_box_cb_callback',
    );

    $obj = register_post_type('ng_coupon_type', $args);
    if (is_wp_error($obj)) {
        echo $obj->get_error_message();
    }
}

add_filter("manage_edit-ng_couponlist_columns", "ng_couponlist_edit_columns");
add_action("manage_posts_custom_column", "ng_couponlist_custom_columns");

function ng_couponlist_edit_columns($columns)
{

    $columns = array(
        "cb" => "<input type=\"checkbox\" />",
        "user" => "User",
        "code" => "Coupon Code",
    );
    return $columns;
}

function ng_couponlist_custom_columns($column)
{
    global $post;
    $custom = get_post_custom();
    switch ($column) {
        case "user":
            // - show taxonomy terms -
            $userdata = $custom["user"];
            if ($userdata) {
               echo $userdata;
            } else {
                echo '사용자 없음';
            }
            break;
        case "code":
            $codedata = $custom["code"];
            if ($codedata) {
                echo $codedata;
            } else {
                echo '코드 없음';
            }
            break;
    }

}


/**
 * 메타 값을 저장하기 위해 액션을 추가합니다.
 * 자세한 사항은 코덱스를 참조하기 바랍니다.
 */
add_action('save_post', 'wp_ng_coupon_save_post');
function wp_ng_coupon_save_post($post_id)
{

    // save code see http://codex.wordpress.org/Function_Reference/add_meta_box#Examples
}

// 나머지 콜백 함수는 이 쪽에서 구현합니다.
include_once('callbacks.php');
