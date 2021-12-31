<?php
/**
 * POST 방식을 통해 서버로 값을 전송합니다.
 */
$greeter = get_option( 'wp_ng_coupon_option_name' );

?>
<div class="wrap">
  <?php 
  /**
   * @link http://codex.wordpress.org/Function_Reference/admin_url
   */
  ?>
	<form class="form-wrap" id="form" name="form" method="POST" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		Greeter: <?php echo $greeter; ?><br/>
		<label for="greet">Greet: <input type="text" id="greet" name="greet" value="hello" /></label>
		<label for="greet">Repeat: <input type="number" id="repeat" name="repeat" min="1" value="1" /></label>
		<br />
		<input type="hidden" id="action" name="action" value="greet_repeat" />
		<input type="submit" class="button button-primary" />
		<?php
		/**
		 * @link http://codex.wordpress.org/Function_Reference/wp_nonce_field
		 */
		wp_nonce_field( 'greet_repeat' );
		?>
	</form>
</div>
