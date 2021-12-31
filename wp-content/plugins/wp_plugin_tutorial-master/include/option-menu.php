<form action="options.php" method="post" id="form">
	<?php

	/**
	 * nonce 필드 및 옵션 폼을 구성하기 위한 기본적인 필드를 출력하기 위해 호출됩니다.
	 * wp_plugin_tutorial_settings() 함수에서
	 * register_setting() 함수를 호출할 때 입력한 그룹 이름을 입력합니다.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/settings_fields
	 * @see wp_plugin_tutorial_settings
	 */
	settings_fields( 'wp_ng_coupon_option_group' );

	/**
	 * 페이지 이름을 인자로 넣습니다.
	 * wp_plugin_tutorial_add_admin_menu() 함수에서
	 * add_options_page() 함수를 호출할 때 입력한 페이지 슬러그를 입력합니다.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/do_settings_sections
	 * @see wp_plugin_tutorial_add_admin_menu
	 */
	do_settings_sections( 'wp_ng_coupon_option' );

	/**
	 * Submit 버튼을 출력하기 위한 함수입니다.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/submit_button
	 */
	submit_button();
	?>
</form>