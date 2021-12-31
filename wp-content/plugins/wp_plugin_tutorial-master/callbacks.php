<?php

/**
 * 메인 메뉴를 선택하면 호출되는 콜백 함수입니다.
 * @see wp_plugin_tutorial_add_admin_menu
 */
function wp_ng_coupon_main_menu_callback() {
	include_once( 'include/main-menu.php' );
}

/**
 * 하위 메뉴 #1을 선택했을 때 호출되는 콜백 함수입니다.
 * @see wp_plugin_tutorial_add_admin_menu
 */
function wp_ng_coupon_submenu_1_callback() {
	include_once( 'include/sub-menu-1.php' );
}

/**
 * 하위 메뉴 #2를 선택했을 때 호출되는 콜백 함수입니다.
 * @see wp_plugin_tutorial_add_admin_menu
 */
function wp_ng_coupon_submenu_2_callback() {

	/**
	 * @link http://codex.wordpress.org/Function_Reference/wp_register_script
	 */
	wp_register_script(
		'submenu_2_script_handle',                                                  // 핸들 이름
		plugin_dir_url( __FILE__ ) . 'include/js/sub-menu-2.js',                    // 소스 경로
		array( 'jquery' ),                                                          // 선택: 의존성 지정.
		NULL                                                                        // 선택: 스크립트의 버전
		// 선택: 푸터 쪽으로 출력할 지 여부 (true/false)
	);

	/**
	 * @link http://codex.wordpress.org/Function_Reference/wp_localize_script
	 */
	wp_localize_script(
		'submenu_2_script_handle',                                                  // 핸들 이름
		'ajax_object',                                                              // 데이터를 가질 변수명
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )                        // 삽입할 데이터
	);

	/**
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_script
	 */
	wp_enqueue_script(
		'submenu_2_script_handle'
		// 선택: 소스 경로
		// 선택: 의존성 지정.
		// 선택: 스크립트의 버전
		// 선택: 푸터 쪽으로 출력할 지 여부 (true/false)
	);

	include_once( 'include/sub-menu-2.php' );
}

/**
 * 설정 메뉴 - Tutorial Option을 선택했을 때 호출되는 콜백 함수입니다.
 * @see wp_plugin_tutorial_add_admin_menu
 */
function wp_ng_coupon_option_callback() {
	include_once( 'include/option-menu.php' );
}

/**
 * 하위 메뉴 #1 POST 방식 폼 전송의 콜백 함수입니다.
 */
function wp_ng_coupon_greet_repeat_callback() {

	/**
	 * @link http://codex.wordpress.org/Function_Reference/wp_verify_nonce
	 */
	if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'greet_repeat' ) ) {
		die( 'nonce verification failed' );
	}

	echo '<!DOCTYPE html><html><head><meta charset="utf-8" /><title>Submenu #1 Callback</title></head><body>';

	$greet = esc_attr( $_POST['greet'] );
	$repeat = filter_var( $_POST['repeat'], FILTER_VALIDATE_INT );

	if( FALSE !== $repeat ) {
		for( $i = 0; $i < $repeat; ++$i) {
			echo "<p>$greet</p>";
		}
	} else {
		echo "invalid greet!";
	}

	echo '</body></html>';
	die();
}


/**
 * 하위 메뉴 #2 AJAX 방식 폼 전송의 콜백 함수입니다.
 * @see wp_plugin_tutorial_settings
 */
function wp_ng_coupon_request_code_callback() {

	if( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'request_code' )) {
		die( 'nonce verification failed' );
	}

	$code = filter_var( $_GET['code'], FILTER_VALIDATE_INT );
	if( FALSE === $code ){
		die( "invalid code! Only integer numbers are accepted." );
	}

	printf( 'request code %04s delivered.', $code );
	die();
}

/**
 * 튜토리얼 플러그인 설정 섹션 부분에서 콜백되는 함수입니다.
 * @see wp_plugin_tutorial_settings
 */
function wp_ng_coupon_section_callback( $args ) {
	printf( '<p id="%s">%s</p>', $args['id'], '섹션의 콜백에 의해 출력된 텍스트입니다. 여기 p 태그의 id 속성을 체크하세요.' );
}

/**
 * 튜토리얼 플러그인 설정 필드 부분에서 콜백되는 함수입니다.
 * @see wp_plugin_tutorial_parse_request
 */
function wp_ng_coupon_field_callback( $args ) {
	printf(
		'<input type="text" id="%s" name="%s" value="%s" /> <span class="description">%s</span>',
		$args['id'],
		$args['name'],
		$args['value'],
		$args['description']
	);
}

/**
 * template_redirect 액션의 콜백입니다. 특정 주소를 입력할 때 특정 페이지를 로드하도록 합니다.
 */
function wp_ng_coupon_template_redirect_callback() {
	include_once( 'include/custom-page.php' );
	exit();
}

/**
 * 커스텀 포스트를 작성할 때 메타 박스가 나타나도록 합니다.
 * @see wp_plugin_tutorial_custom_post
 */
function wp_ng_coupon_meta_box_cb_callback() {
	add_meta_box(
		'tutorial_meta_1',
		__( 'Tutorial Meta #1', 'wp_ng_coupon' ),
		'wp_plugin_tutorial_meta_1_callback',
		'wp_tutorial_type',
		'normal',
		'default',
		array( 'description' => 'meta value description' )
	);
}

/**
 * 메타 박스에 대한 콜백입니다.
 * @see wp_plugin_tutorial_meta_box_cb_callback
 */
function wp_ng_coupon_meta_1_callback( $post, $metabox ) {

	$fmt  = '<label for="wp_plugin_tutorial_meta_1">%s';
	$fmt .= '<input type="text" id="wp_plugin_tutorial_meta_1" name="wp_plugin_tutorial_meta_1" value="%s" />';
	$fmt .= '</label><span class="description">%s</span>';
	wp_nonce_field( 'wp_plugin_tutorial_meta_1', 'wp_plugin_tutorial_meta_1_nonce' );
	printf( $fmt, "Meta Value #1", esc_attr( get_post_meta( $post->ID, 'wp_plugin_tutorial_meta_1', TRUE ) ), $metabox['args']['description'] );
}
