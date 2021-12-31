<div class="our-class wrap" >
	<form name="form" id="form" class="form-wrap">
		Receiver: <?php echo get_option( 'wp_ng_coupon_option_name' ); ?><br/>
		<label for="code">
			Code: <input type="text" name="code" id="code" />
		</label>
		<input type="hidden" name="action" id="action" value="request_code" />
		<?php wp_nonce_field( 'request_code' ); ?>
		<br />
		<input type="button" class="button-primary" id="send_code" value="Send code" />
	</form>
	<br />
	<h3><div id="output"></div></h3>
</div>

