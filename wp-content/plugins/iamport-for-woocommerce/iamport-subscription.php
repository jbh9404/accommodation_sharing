<?php
class WC_Gateway_Iamport_Subscription extends WC_Payment_Gateway {

	public function __construct() {
		//settings
		$this->id = 'iamport_subscription'; //id가 먼저 세팅되어야 init_setting가 제대로 동작
		$this->method_title = __( '아임포트(KEY-IN결제/정기결제)', 'iamport-for-woocommerce' );
		$this->method_description = __( '아임포트를 통해 KEY-IN결제 또는 정기결제를 사용하실 수 있습니다. (정기결제 기능을 위해서는 Woocommerce-Subscription 플러그인 설치가 필요합니다. KEY-IN결제는 이용가능)', 'iamport-for-woocommerce' );
		$this->has_fields = true;
		$this->supports = array( 'subscriptions', 'subscription_reactivation'/*이것이 있어야 subscription 후 active상태로 들어갈 수 있음*/, 'subscription_suspension', 'subscription_cancellation' , 'refunds', 'subscription_date_changes', 'subscription_amount_changes' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->settings['title'];

		$this->imp_rest_key = $this->settings['imp_rest_key'];
		$this->imp_rest_secret = $this->settings['imp_rest_secret'];

		//woocommerce action
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// if ( class_exists( 'WC_Subscriptions_Order' ) ) {
			add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );
			add_action( 'woocommerce_subscription_cancelled_' . $this->id, array( $this, 'cancelled_subscription' ), 10, 1 );

			add_filter( 'woocommerce_credit_card_form_fields', array( $this, 'iamport_credit_card_form_fields' ), 10, 2);

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_iamport_script') );
		// }
	}

	public function enqueue_iamport_script() {
		wp_register_script( 'iamport_rsa', plugins_url( '/assets/js/rsa.bundle.js',plugin_basename(__FILE__) ));
		wp_register_script( 'iamport_script_for_woocommerce_rsa', plugins_url( '/assets/js/iamport.woocommerce.rsa.js',plugin_basename(__FILE__) ), array('jquery'), '20170314');
		wp_register_style( 'iamport_woocommerce_css', plugins_url( '/assets/css/iamport.woocommerce.css',plugin_basename(__FILE__)) );

		wp_enqueue_script('iamport_rsa');
		wp_enqueue_script('iamport_script_for_woocommerce_rsa');
		wp_enqueue_style( 'iamport_woocommerce_css' );
	}

	public function init_form_fields() {
		//iamport기본 플러그인에 해당 정보가 세팅되어있는지 먼저 확인
		$default_api_key = get_option('iamport_rest_key');
		$default_api_secret = get_option('iamport_rest_secret');

		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'woocommerce' ),
				'type' => 'checkbox',
				'label' => __( '아임포트(KEY-IN결제/정기결제) 결제 사용', 'iamport-for-woocommerce' ),
				'default' => 'no'
			),
			'title' => array(
				'title' => __( 'Title', 'woocommerce' ),
				'type' => 'text',
				'description' => __( '구매자에게 표시될 구매수단명', 'iamport-for-woocommerce' ),
				'default' => __( 'KEY-IN결제/정기결제', 'iamport-for-woocommerce' ),
				'desc_tip'      => true,
			),
			'imp_rest_key' => array(
				'title' => __( '[아임포트] REST API 키', 'iamport-for-woocommerce' ),
				'type' => 'text',
				'description' => __( 'https://admin.iamport.kr에서 회원가입 후, "시스템설정" > "내정보"에서 확인하실 수 있습니다.', 'iamport-for-woocommerce' ),
				'label' => __( '[아임포트] REST API 키', 'iamport-for-woocommerce' ),
				'default' => $default_api_key
			),
			'imp_rest_secret' => array(
				'title' => __( '[아임포트] REST API Secret', 'iamport-for-woocommerce' ),
				'type' => 'text',
				'description' => __( 'https://admin.iamport.kr에서 회원가입 후, "시스템설정" > "내정보"에서 확인하실 수 있습니다.', 'iamport-for-woocommerce' ),
				'label' => __( '[아임포트] REST API Secret', 'iamport-for-woocommerce' ),
				'default' => $default_api_secret
			)
		);
	}

	public function iamport_credit_card_form_fields($default_fields, $id) {
		if ( $id !== $this->id ) 	return $default_fields;

		$args = array('fields_have_names'=>true);
		$iamoprt_fields = array(
			'card-number-field' => '<p class="form-row form-row-first">
			<label for="' . esc_attr( $id ) . '-card-number">' . __( 'Card Number', 'woocommerce' ) . ' <span class="required">*</span></label>
			<input id="' . esc_attr( $id ) . '-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" name="' . ( $args['fields_have_names'] ? $this->id . '-card-number' : '' ) . '" />
			</p>',
			'card-expiry-field' => '<p class="form-row form-row-last">
			<label for="' . esc_attr( $id ) . '-card-expiry">' . __( 'Expiry (MM/YY)', 'woocommerce' ) . ' <span class="required">*</span></label>
			<input id="' . esc_attr( $id ) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="' . esc_attr__( 'MM / YY', 'woocommerce' ) . '" name="' . ( $args['fields_have_names'] ? $this->id . '-card-expiry' : '' ) . '" />
			</p>',
			'card-birth-field' => '<p class="form-row form-row-first">
			<label for="' . esc_attr( $id ) . '-card-birth">' . __( '소지자확인(생년월일6자리 또는 사업자등록번호10자리)', 'iamport-for-woocommerce' ) . ' <span class="required">*</span></label>
			<input id="' . esc_attr( $id ) . '-card-birth" class="input-text wc-credit-card-form-card-birth" type="password" autocomplete="off" placeholder="' . esc_attr__( '카드소지자확인(생년월일6자리 또는 사업자등록번호10자리)', 'iamport-for-woocommerce' ) . '" name="' . ( $args['fields_have_names'] ? $this->id . '-card-birth' : '' ) . '" maxlength="10"/>
			</p>',
			'card-pwd-field' => '<p class="form-row form-row-last">
			<label for="' . esc_attr( $id ) . '-card-pwd">' . __( '카드비밀번호 앞2자리', 'iamport-for-woocommerce' ) . ' <span class="required">*</span></label>
			<input id="' . esc_attr( $id ) . '-card-pwd" class="input-text wc-credit-card-form-card-pwd" type="password" autocomplete="off" placeholder="' . esc_attr__( '카드비밀번호 앞2자리', 'iamport-for-woocommerce' ) . '" name="' . ( $args['fields_have_names'] ? $this->id . '-card-pwd' : '' ) . '" maxlength="2"/>
			</p>'
		);

		return $iamoprt_fields;
	}

	public function payment_fields() {
		ob_start();

		$private_key = $this->get_private_key();
		$public_key = $this->get_public_key($private_key, $this->keyphrase());
		?>
		<div id="iamport-subscription-card-holder" data-module="<?=$public_key['module']?>" data-exponent="<?=$public_key['exponent']?>">
			<input type="hidden" name="enc_iamport_subscription-card-number" value="">
			<input type="hidden" name="enc_iamport_subscription-card-expiry" value="">
			<input type="hidden" name="enc_iamport_subscription-card-birth" value="">
			<input type="hidden" name="enc_iamport_subscription-card-pwd" value="">
			<?php $this->credit_card_form( array( 'fields_have_names' => false ) ); ?>
		</div>
		<?php
		ob_end_flush();
	}

	public function credit_card_form( $args = array(), $fields = array() ) {
		//woocommerce 2.6부터는 WC_Payment_Gateway_CC->form을 사용해야 함
		if ( class_exists('WC_Payment_Gateway_CC') ) {
			$cc_form = new WC_Payment_Gateway_CC;
			$cc_form->id       = $this->id;
			$cc_form->supports = $this->supports;
			$cc_form->form();
		} else {
			parent::credit_card_form( $args, $fields );
		}
	}

	public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {
		error_log('######## SCHEDULED ########');
		$response = $this->process_subscription_payment( $renewal_order, $amount_to_charge );

		if ( is_wp_error( $response ) ) {
			$old_status = $renewal_order->get_status();
			$renewal_order->update_status( 'failed', sprintf( __( '정기결제승인에 실패하였습니다. (상세 : %s)', 'iamport-for-woocommerce' ), $response->get_error_message() ) );

			//fire hook
			do_action('iamport_order_status_changed', $old_status, $renewal_order->get_status());
		}
	}

	public function process_payment( $order_id ) {
		// Processing subscription
		if ( function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_is_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) ) ) {
			return $this->process_subscription( $order_id, true );
		} else {
			//KEY-IN결제
			return $this->process_subscription( $order_id, false );
		}
	}

	public function process_subscription( $order_id, $subscribe_able = true ) {
		//payment_form에서 온 것은 wp_redirect만 하므로 ajax response를 직접해줘야 함
		$from_payment_form = isset( $_GET['pay_for_order'] ) && isset( $_GET['key'] );
		$order = wc_get_order( $order_id );

		if ( $order->get_total() > 0 ) {
			$iamport_response = $this->process_subscription_payment( $order, $order->get_total(), true, $subscribe_able );

			if ( is_wp_error($iamport_response) ) {
				if ( $subscribe_able ) {
					$errorMessage = sprintf( __( '정기결제 최초 과금(signup fee)에 실패하였습니다. (상세사유 : %s)', 'iamport-for-woocommerce' ) , $iamport_response->get_error_message() );

					return $this->jsonOrException( $errorMessage , $from_payment_form );
				} else {
					$errorMessage = sprintf( __( 'KEY-IN결제에 실패하였습니다. (상세사유 : %s)', 'iamport-for-woocommerce' ) , $iamport_response->get_error_message() );

					return $this->jsonOrException( $errorMessage , $from_payment_form );
				}
			}
		} else {
			if ( $subscribe_able ) {
				//free trial로 signup-fee가 없더라도 빌링키는 등록해야 함
				$checking_amount = $this->checkingAmount();
				$iamport_response = $this->process_register_billing( $order, $checking_amount );
				if ( is_wp_error($iamport_response) ) {
					$errorMessage = sprintf( __( '빌링키 등록에 실패하였습니다. (상세사유 : %s)', 'iamport-for-woocommerce' ) , $iamport_response->get_error_message() );

					return $this->jsonOrException( $errorMessage , $from_payment_form );
				}
			}

			$old_status = $order->get_status();
			$order->payment_complete();
			$order->set_payment_method( $this );

			//fire hook
			do_action('iamport_order_status_changed', $old_status, $order->get_status());
		}

		WC()->cart->empty_cart();

		// Return thank you page redirect
		$response = array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);

		if ( $from_payment_form ) {
			wp_send_json( $response );
		} else {
			return $response;
		}
	}

	/**
	 * process_subscription_payment function.
	 *
	 * @access public
	 * @param mixed $order
	 * @param int $amount (default: 0)
	 * @param  bool initial_payment
	 */
	public function process_subscription_payment( $order = '', $amount = 0, $initial_payment = false, $subscribe_able = true ) {
		try {
			require_once(dirname(__FILE__).'/lib/iamport.php');
			
			$user_id         = $order->customer_user;
			$customer_uid 	 = null;
			if ( !$initial_payment || $subscribe_able ) $customer_uid 	 = $this->get_customer_uid($order);

			if ( $initial_payment ) {
				$cardInfo = $this->getDecryptedCard();
				if ( is_wp_error($cardInfo) )	return $cardInfo; //return WP_Error

				$iamport = new WooIamport($this->imp_rest_key, $this->imp_rest_secret);
				$pay_data = array(
					'amount' => $amount,
					// 'vat' => 0,
					'merchant_uid' => $order->order_key,
					// 'customer_uid' => $customer_uid,
					'card_number' => $cardInfo['dec_card_number'], 
					'expiry' => $this->format_expiry($cardInfo['dec_expiry']),
					'birth' => $cardInfo['dec_birth'],
					'pwd_2digit' => $cardInfo['dec_pwd'],
					'name' => $this->get_order_name($order, $initial_payment),
					'buyer_name' => $order->billing_last_name . $order->billing_first_name,
					'buyer_email' => $order->billing_email,
					'buyer_tel' => $order->billing_phone
				);

				if ( $subscribe_able )	$pay_data['customer_uid'] = $customer_uid;

				$result = $iamport->sbcr_onetime($pay_data);

				$payment_data = $result->data;
				if ( $result->success ) {
					if ( $payment_data->status == 'paid' ) {
						$order_id = $order->id;

						$this->_iamport_post_meta($order_id, '_iamport_rest_key', $this->imp_rest_key);
						$this->_iamport_post_meta($order_id, '_iamport_rest_secret', $this->imp_rest_secret);
						$this->_iamport_post_meta($order_id, '_iamport_provider', $payment_data->pg_provider);
						$this->_iamport_post_meta($order_id, '_iamport_paymethod', $payment_data->pay_method);
						$this->_iamport_post_meta($order_id, '_iamport_pg_tid', $payment_data->pg_tid);
						$this->_iamport_post_meta($order_id, '_iamport_receipt_url', $payment_data->receipt_url);

						if ( $subscribe_able ) {
							$this->_iamport_post_meta($order_id, '_iamport_customer_uid', $customer_uid);

							$order->add_order_note( sprintf( __( '정기결제 최초 과금(signup fee)에 성공하였습니다. (imp_uid : %s)', 'iamport-for-woocommerce' ) , $payment_data->imp_uid ) );
						} else { 
							$order->add_order_note( sprintf( __( 'KEY-IN방식 결제에 성공하였습니다. (imp_uid : %s)', 'iamport-for-woocommerce' ) , $payment_data->imp_uid ) );
						}

						$old_status = $order->get_status();
						$order->payment_complete( $payment_data->imp_uid );
						$order->set_payment_method( $this );

						//fire hook
						do_action('iamport_order_status_changed', $old_status, $order->get_status());
					} else {
						if ( $subscribe_able ) {
							$message = sprintf( __( '정기결제 최초 과금(signup fee)에 실패하였습니다. (status : %s)', 'iamport-for-woocommerce' ) , $payment_data->status );
						} else {
							$message = sprintf( __( 'KEY-IN방식 결제에 실패하였습니다. (status : %s)', 'iamport-for-woocommerce' ) , $payment_data->status );
						}
						$order->add_order_note( $message );

						return new WP_Error( 'iamport_error', $message );
					}
				} else {
					if ( $subscribe_able ) {
						$message = sprintf( __( '정기결제 최초 과금(signup fee)에 실패하였습니다. (사유 : %s)', 'iamport-for-woocommerce' ) , $result->error['message'] );
					} else {
						$message = sprintf( __( 'KEY-IN방식 결제에 실패하였습니다. (사유 : %s)', 'iamport-for-woocommerce' ) , $result->error['message'] );
					}

					$order->add_order_note( $message );

					return new WP_Error( 'iamport_error', $message );
				}
			} else {
				error_log('#### AGAIN ####');
				error_log($order->order_key);

				$iamport = new WooIamport($this->imp_rest_key, $this->imp_rest_secret);
				$recurring_md_suffix = date('md');

				$result = $iamport->sbcr_again(array(
					'amount' => $amount,
					// 'vat' => 0,
					'merchant_uid' => $order->order_key.$recurring_md_suffix,
					'customer_uid' => $customer_uid,
					'name' => $this->get_order_name($order, $initial_payment),
					'buyer_name' => $order->billing_last_name . $order->billing_first_name,
					'buyer_email' => $order->billing_email,
					'buyer_tel' => $order->billing_phone
				));

				$payment_data = $result->data;
				if ( $result->success ) {
					if ( $payment_data->status == 'paid' ) {
						$order_id = $order->id;

						$this->_iamport_post_meta($order_id, '_iamport_rest_key', $this->imp_rest_key);
						$this->_iamport_post_meta($order_id, '_iamport_rest_secret', $this->imp_rest_secret);
						$this->_iamport_post_meta($order_id, '_iamport_provider', $payment_data->pg_provider);
						$this->_iamport_post_meta($order_id, '_iamport_paymethod', $payment_data->pay_method);
						$this->_iamport_post_meta($order_id, '_iamport_pg_tid', $payment_data->pg_tid);
						$this->_iamport_post_meta($order_id, '_iamport_receipt_url', $payment_data->receipt_url);
						$this->_iamport_post_meta($order_id, '_iamport_customer_uid', $customer_uid);
						$this->_iamport_post_meta($order_id, '_iamport_recurring_md', $recurring_md_suffix);

						$order->add_order_note( sprintf( __( '정기결제 회차 과금(%s차결제)에 성공하였습니다. (imp_uid : %s)', 'iamport-for-woocommerce' ) , $order->suspension_count, $payment_data->imp_uid ) );

						$old_status = $order->get_status();
						$order->payment_complete( $payment_data->imp_uid );
						$order->set_payment_method( $this );

						//fire hook
						do_action('iamport_order_status_changed', $old_status, $order->get_status());
					} else {
						$message = sprintf( __( '정기결제 회차 과금(%s차결제)에 실패하였습니다. (사유 : %s, status : %s)', 'iamport-for-woocommerce' ) , $order->suspension_count, $payment_data->fail_reason, $payment_data->status );
						$order->add_order_note( $message );

						return new WP_Error( 'iamport_error', $message );
					}
				} else {
					$message = sprintf( __( '정기결제 회차 과금(%s차결제)에 실패하였습니다. (사유 : %s)', 'iamport-for-woocommerce' ) , $order->suspension_count, $result->error['message'] );
					$order->add_order_note( $message );

					return new WP_Error( 'iamport_error', $message );
				}
			}

			return $result;
		} catch(Exception $e) {
			return new WP_Error( 'iamport_error', $e->getMessage() );
		}
	}

	public function process_register_billing($order = '', $checking_amount = 0) {
		require_once(dirname(__FILE__).'/lib/iamport.php');

		$order_id = $order->id;
		$customer_uid = $this->get_customer_uid($order);

		$cardInfo = $this->getDecryptedCard();
		if ( is_wp_error($cardInfo) )	return $cardInfo; //return WP_Error

		$iamport = new WooIamport($this->imp_rest_key, $this->imp_rest_secret);
		$customer_data = array(
			'card_number' => $cardInfo['dec_card_number'],
			'expiry' => $this->format_expiry($cardInfo['dec_expiry']),
			'birth' => $cardInfo['dec_birth'],
			'pwd_2digit' => $cardInfo['dec_pwd'],
			'customer_name' => $order->billing_last_name . $order->billing_first_name,
			'customer_email' => $order->billing_email,
			'customer_tel' => $order->billing_phone
		);

		$result = $iamport->customer_save($customer_uid, $customer_data);
		if ( $result->success ) {
			if ( $checking_amount > 0 ) {
				$checking_data = array(
					'amount' => $checking_amount,
					'merchant_uid' => date('YmdHis') . 't' . rand(0, 999),
					'card_number' => $cardInfo['dec_card_number'], 
					'expiry' => $this->format_expiry($cardInfo['dec_expiry']),
					'birth' => $cardInfo['dec_birth'],
					'pwd_2digit' => $cardInfo['dec_pwd'],
					'name' => '카드등록테스트결제-자동취소예정',
					'buyer_name' => $order->billing_last_name . $order->billing_first_name,
					'buyer_email' => $order->billing_email,
					'buyer_tel' => $order->billing_phone
				);

				$checking_result = $iamport->sbcr_onetime($checking_data);

				if ( $checking_result->success && $checking_result->data->status == 'paid' ) {
					$this->_iamport_post_meta($order_id, '_iamport_checking_uid', $checking_result->data->imp_uid);
					$order->add_order_note( sprintf( __('카드등록 중 테스트 결제 %s원 승인이 성공되었습니다. 곧 자동취소됩니다.(%s)', 'iamport-for-woocommerce'), number_format($checking_amount) ), $checking_result->data->imp_uid );

					sleep(1); //1초후 결제취소

					$cancel_result = $iamport->cancel(array(
						'imp_uid' => $checking_result->data->imp_uid,
						'reason' => '카드등록테스트결제-자동취소'
					));

					if ( $cancel_result->success ) {
						$this->_iamport_post_meta($order_id, '_iamport_checking_cancel', 'Y');
						$order->add_order_note( sprintf( __('카드등록 중 테스트한 결제 %s원에 대한 취소/환불이 정상적으로 처리되었습니다.', 'iamport-for-woocommerce'), number_format($checking_amount) ) );
					} else {
						$this->_iamport_post_meta($order_id, '_iamport_checking_cancel', 'N');
						$order->add_order_note( sprintf( __('카드등록 중 테스트한 결제 %s원에 대한 취소/환불에 실패하였습니다.(%s)', 'iamport-for-woocommerce'), number_format($checking_amount), $cancel_result->error['message'] ) );
					}
				} else {
					if ( !$checking_result->success )						$err = $checking_result->error['message'];
					else if ( $checking_result->data->status != 'paid' )	$err = $checking_result->data->fail_reason;

					$message = sprintf( __('카드등록 중 테스트 결제 %s원에 대한 결제가 실패하였습니다.(%s)', 'iamport-for-woocommerce'), number_format($checking_amount), $err );
					$order->add_order_note( $message );

					return new WP_Error( 'iamport_error', $message );
				}
			}

			$customer = $result->data;

			$this->_iamport_post_meta($order_id, '_iamport_customer_uid', $customer_uid);
			$this->_iamport_post_meta($order_id, '_iamport_rest_key', $this->imp_rest_key);
			$this->_iamport_post_meta($order_id, '_iamport_rest_secret', $this->imp_rest_secret);

			$order->add_order_note( __( '정기결제 최초 빌링키 등록에 성공하였습니다. (signup-fee : 0원)', 'iamport-for-woocommerce' ) );
		} else {
			$message = sprintf( __( '정기결제 최초 빌링키 등록에 실패하였습니다. (사유 : %s)', 'iamport-for-woocommerce' ) , $result->error['message'] );
			$order->add_order_note( $message );

			return new WP_Error( 'iamport_error', $message );
		}
	}

	//common for refund(나중에 합치자...)
	public function process_refund($order_id, $amount = null, $reason = '') {
		require_once(dirname(__FILE__).'/lib/iamport.php');

		global $woocommerce;
		$order = new WC_Order( $order_id );

		$imp_uid = $order->get_transaction_id();
		$iamport = new WooIamport($this->imp_rest_key, $this->imp_rest_secret);

		// 만약 데이터 동기화에 실패하는 상황이 되어 imp_uid가 없더라도 order_key가 있으면 취소를 시도해볼 수 있다.
		if ( empty($imp_uid) ) {
			//회차결제를 위해 md가 뒤에 붙도록 설계되어있음
			$merchant_uid = $order->order_key;
			$recurring_md_suffix = get_post_meta($order->id, '_iamport_recurring_md', true);
			if ( !empty($recurring_md_suffix) )	$merchant_uid = $merchant_uid . $recurring_md_suffix;

			$cancel_data = array(
				'merchant_uid'=>$merchant_uid,
				'reason'=>$reason,
				'amount'=>$amount
			);
		} else {
			$cancel_data = array(
				'imp_uid'=>$imp_uid,
				'reason'=>$reason,
				'amount'=>$amount
			);
		}

		$result = $iamport->cancel($cancel_data);

		if ( $result->success ) {
			$payment_data = $result->data;
			$order->add_order_note( sprintf(__( '%s 원 환불완료', 'iamport-for-woocommerce'), number_format($amount)) );
			if ( $payment_data->amount == $payment_data->cancel_amount ) {
				$old_status = $order->get_status();
				$order->update_status('refunded');

				//fire hook
				do_action('iamport_order_status_changed', $old_status, $order->get_status());
			}
			return true;
		} else {
			$order->add_order_note($result->error['message']);
			return false;
		}

		return false;
	}

	public function cancelled_subscription($subscription) {
		/* 동일한 사람이 여러 상품의 subscription도 할 수 있으므로 삭제하지 않는다.
		require_once(dirname(__FILE__).'/lib/iamport.php');

		$iamport = new WooIamport($this->imp_rest_key, $this->imp_rest_secret);
		$customer_uid 	 = $this->get_customer_uid($subscription);

		$result = $iamport->customer_delete($customer_uid);
		if ( $result->success ) {
			$subscription->add_order_note( '정기결제 등록정보를 삭제하였습니다.' );
		} else {
			$subscription->add_order_note( '정기결제 등록정보 삭제에 실패하였습니다.' );
		}
		*/
	}

	protected function getDecryptedCard() {
		$card_number 	= $_POST['enc_iamport_subscription-card-number'];
		$expiry 		= $_POST['enc_iamport_subscription-card-expiry'];
		$birth 			= $_POST['enc_iamport_subscription-card-birth'];
		$pwd_2digit 	= $_POST['enc_iamport_subscription-card-pwd'];

		$private_key = $this->get_private_key();

		$dec_card_number 	= $this->decrypt( $card_number, $private_key );
		$dec_expiry			= $this->decrypt( $expiry, $private_key );
		$dec_birth			= $this->decrypt( $birth, $private_key );
		$dec_pwd			= $this->decrypt( $pwd_2digit, $private_key );

		if ( empty($dec_card_number) || empty($dec_expiry) || empty($dec_birth) || empty($dec_pwd) ) {
			return new WP_Error( 'iamport_error', __( '암호화되어 전송된 카드정보를 복호화하는데 실패하였습니다. 관리자에게 문의해주세요.', 'iamport-for-woocommerce' ) );
		}

		return array(
			'dec_card_number' => $dec_card_number,
			'dec_expiry' => $dec_expiry,
			'dec_birth' => $dec_birth,
			'dec_pwd' => $dec_pwd
		);
	}

	protected function get_order_name($order, $initial_payment) {
		if ( $initial_payment ) {
			$order_name = "#" . $order->get_order_number() . "번 주문 정기결제(최초과금)";
		} else {
			$order_name = "#" . $order->get_order_number() . sprintf("번 주문 정기결제(%s회차)", $order->suspension_count);
		}

		$order_name = apply_filters('iamport_recurring_order_name', $order_name, $order, $initial_payment);

		return $order_name;
	}

	private function get_customer_uid($order) {
		$prefix = get_option('_iamport_customer_prefix');
		if ( empty($prefix) ) {
			require_once( ABSPATH . 'wp-includes/class-phpass.php');
			$hasher = new PasswordHash( 8, false );
			$prefix = md5( $hasher->get_random_bytes( 32 ) );

			if ( !add_option( '_iamport_customer_prefix', $prefix ) )	throw new Exception( __( "정기결제 구매자정보 생성에 실패하였습니다.", 'iamport-for-woocommerce' ), 1);
		}

		$user_id = $order->user_id; // wp_cron에서는 get_current_user_id()가 없다.
		if ( empty($user_id) )		throw new Exception( __( "정기결제기능은 로그인된 사용자만 사용하실 수 있습니다.", 'iamport-for-woocommerce' ), 1);

		return $prefix . 'c' . $user_id;
	}

	protected function _iamport_post_meta($order_id, $meta_key, $meta_value) {
		if ( !add_post_meta($order_id, $meta_key, $meta_value, true) ) {
			update_post_meta($order_id, $meta_key, $meta_value);
		}

		do_action('iamport_order_meta_saved', $order_id, $meta_key, $meta_value);
	}

	private function format_expiry($expiry) {
		$arr = explode('/', $expiry);
		if ( $arr && count($arr) == 2 ) {
			$month = trim($arr[0]);
			$year = trim($arr[1]);

			if ( strlen($year) == 2 ) {
				$year = '20'.$year;
			}

			return $year . '-' . $month;
		}

		return false;
	}

	//rsa

	private function keyphrase() {
		$keyphrase = get_option('_iamport_rsa_keyphrase');
		if ( $keyphrase )		return $keyphrase;

		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		$hasher = new PasswordHash( 8, false );
		$keyphrase = md5( $hasher->get_random_bytes( 16 ) );

		if ( add_option('_iamport_rsa_keyphrase', $keyphrase) )		return $keyphrase;

		return false;
	}

	private function get_private_key() {
		$private_key = get_option('_iamport_rsa_private_key');

		if ( $private_key )		return $private_key; //있으면 기존 것을 반환

		$config = array(
			"digest_alg" => "sha256",
			"private_key_bits" => 4096,
			"private_key_type" => OPENSSL_KEYTYPE_RSA
		);

		// Create the private key
		$res = openssl_pkey_new($config);
		$success = openssl_pkey_export($res, $private_key, $this->keyphrase()); //-------BEGIN RSA PRIVATE KEY...로 시작되는 문자열을 $private_key에 저장

		if ( $success && add_option('_iamport_rsa_private_key', $private_key) )		return $private_key;

		return false;
	}

	private function get_public_key($private_key, $keyphrase) {
		$res = openssl_pkey_get_private($private_key, $keyphrase);
		$details = openssl_pkey_get_details($res);

		return array('module'=>$this->to_hex($details['rsa']['n']), 'exponent'=>$this->to_hex($details['rsa']['e']));
	}

	private function to_hex($data) {
		return strtoupper(bin2hex($data));
	}

	private function decrypt($encrypted, $private_key) {
		$payload = pack('H*', $encrypted);
		$pk_info = openssl_pkey_get_private($private_key, $this->keyphrase());
		if ( $pk_info && openssl_private_decrypt($payload, $decrypted, $pk_info) ) {
			return $decrypted;
		}

		return false;
	}

	private function jsonOrException($messages, $from_payment_form) {
		if ( $from_payment_form ) {
			$response = array(
				'result'	=> 'failure',
				'messages' 	=> isset( $messages ) ? $messages : '',
				'refresh' 	=> isset( WC()->session->refresh_totals ) ? 'true' : 'false',
				'reload'    => isset( WC()->session->reload_checkout ) ? 'true' : 'false'
			);

			wp_send_json( $response );
		} else {
			throw new Exception( $messages );
		}
	}

	private function checkingAmount() {
		return intval( get_option('woocommerce_iamport_subscription_checking_amount') );
	}

}