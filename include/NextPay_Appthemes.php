<?php
ob_start();
if (!defined( 'ABSPATH' )) 
	exit;
add_action( 'init', 'nextpay_app_gateway_load', 1);
function nextpay_app_gateway_load() {
	
	if (!class_exists('APP_Gateway') || !current_theme_supports('app-payments')) 
		return;
		
	
	if ( !function_exists('NextPay_Appthemes_Iran_Currencies') )
	{
	
		function NextPay_Appthemes_Iran_Currencies() {
			$Iran_Currency = array('IRR' => 'ریال', 'IRT' => 'تومان');
			foreach ($Iran_Currency as $k => $v)
			{
				$details = array('symbol' => $v, 'name' => $v.' ایران' );
				APP_Currencies::add_currency( $k, $details );
			}
			return true;
		}
		NextPay_Appthemes_Iran_Currencies();
	
	}
	
	define( 'NEXTPAYAPPT', 'Appthemes_NextPay');
	
	//NextPay_Gateway Class ....
	class NextPay_Gateway extends APP_Gateway {
		
		protected $options;
		
		public function __construct() {
			
			$title = trim(get_option(NEXTPAYAPPT."_title"));
			if (!$title) 
				$title = __( 'درگاه پرداخت نکست پی', NEXTPAYAPPT );
			
			parent::__construct( 'nextpay', array(
					'admin' 	=> __( 'نکست پی', NEXTPAYAPPT ),
					'dropdown' 	=> $title
				) 
			);
		
		}
		
		public function create_form( $order, $options ){ 
			//not required ...
		}
		
		public function form() {
			$title   	  = __( 'درگاه پرداخت نکست پی', NEXTPAYAPPT );
			$description  = "<a target='_blank' href='http://webforest.ir/'><img border='0' style='float:right; margin-left:15px' src='".plugin_dir_url( __FILE__ )."assets/NextPay.png'></a><br/>";
			$description .= sprintf(__( '<br/><a target="_blank" style="text-decoration:none;" href="%s">وب سایت پشتیبانی درگاه پرداخت &#187;</a>', NEXTPAYAPPT ), "http://webforest.ir") . "<br/><br/><br/><br/>";
			$description .= '<strong>'.__( 'تنظیمات درگاه نکست پی', NEXTPAYAPPT ) . '</strong>';
			$fields = array(
					array(
							'name'			=> 'title',
							'title'       	=> __( 'نام نمایشی درگاه', NEXTPAYAPPT ),
							'type'        	=> 'text',
							'default'     	=> __( 'درگاه پرداخت نکست پی', NEXTPAYAPPT ),
							'desc' 			=> '<p>'.__( 'عنوان نمایشی درگاه پرداخت', NEXTPAYAPPT ).'</p>'
					),
					array(
							'name'			=> 'api_key',
							'title' 		=> '<br/>'.__('کلید مجوزدهی', NEXTPAYAPPT ),
							'type' 			=> 'text',
							'default' 		=> '',
							'desc' 			=> '<p>'.__("کلید مجوزدهی نکست پی", NEXTPAYAPPT).'</p>'
					),
					array(
							'name'			=> 'query',
							'title' 		=> '<br/>'.__('نام لاتین درگاه', NEXTPAYAPPT ),
							'type' 			=> 'text',
							'default' 		=> 'NextPay',
							'desc' 			=> '<p>'.__("این نام در هنگام بازگشت از بانک در آدرس بازگشت از بانک نمایان خواهد شد . از به کاربردن حروف زائد و فاصله جدا خودداری نمایید .", NEXTPAYAPPT).'</p>'
					),
					array(
							'name'			=> 'success_massage',
							'title'       => __( 'پیام پرداخت موفق', NEXTPAYAPPT ),
							'type'        => 'textarea',
							'desc' => __( '<br/>متن پیامی که میخواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {transaction_id} برای نمایش کد رهگیری ( کد مرجع تراکنش ) و از شرت کد {order_id} برای شماره درخواست تراکنش نکست پی استفاده نمایید .', NEXTPAYAPPT ),
							'default'     => __( 'با تشکر از شما . سفارش شما با موفقیت پرداخت شد .', NEXTPAYAPPT ),
							'extra' => array(
								'style' => 'width:500px;height:100px'
							),
					),
					array(
							'name'			=> 'failed_massage',
							'title'       => __( 'پیام پرداخت ناموفق', NEXTPAYAPPT ),
							'type'        => 'textarea',
							'desc' => __( '<br/>متن پیامی که میخواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {fault} برای نمایش دلیل خطای رخ داده استفاده نمایید . این دلیل خطا از سایت نکست پی ارسال میگردد .', NEXTPAYAPPT ),
							'default'     => __( 'پرداخت شما ناموفق بوده است . لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید .', NEXTPAYAPPT ),
							'extra' => array(
								'style' => 'width:500px;height:100px'
							),
					),
					array(
							'name'			=> 'cancelled_massage',
							'title'       => __( 'پیام انصراف از پرداخت', NEXTPAYAPPT ),
							'type'        => 'textarea',
							'desc' => __( '<br/>متن پیامی که میخواهید بعد از انصراف کاربر از پرداخت نمایش دهید را وارد نمایید . این پیام بعد از بازگشت از بانک نمایش داده خواهد شد .', NEXTPAYAPPT ),
							'default'     => __( 'پرداخت به دلیل انصراف شما ناتمام باقی ماند .', NEXTPAYAPPT ),
							'extra' => array(
								'style' => 'width:500px;height:100px'
							),
					)
			);
			
			if (isset($_POST["gateways"]["nextpay"]["title"]) && strip_tags($_POST["gateways"]["nextpay"]["title"])) 
				update_option(NEXTPAYAPPT."_title", strip_tags(sanitize_text_field($_POST["gateways"]["nextpay"]["title"])));
			
			
			if (isset($_POST["gateways"]["nextpay"]["success_massage"])) 
				update_option(NEXTPAYAPPT."_success_massage", sanitize_text_field($_POST["gateways"]["nextpay"]["success_massage"]));

			
			if (isset($_POST["gateways"]["nextpay"]["failed_massage"])) 
				update_option(NEXTPAYAPPT."_failed_massage", sanitize_text_field($_POST["gateways"]["nextpay"]["failed_massage"]));

			
			if (isset($_POST["gateways"]["nextpay"]["cancelled_massage"])) 
				update_option(NEXTPAYAPPT."_cancelled_massage", sanitize_text_field($_POST["gateways"]["nextpay"]["cancelled_massage"]));
			
			
			$config = array( array(
							'title' => $title."<br/><br/><div style='font-size:13px;font-weight:normal;'>".$description."</div><br/>",
							'fields' => $fields
				)
			);
			return apply_filters( 'appthemes_nextpay_settings_form', $config );
		}
	
		public function process( $order, $options ){
			Proccess_NextPay_By_NextPay ($order, $options, false);
			return true;
		}
	}
	appthemes_register_gateway( 'NextPay_Gateway' );
	//NextPay_Gateway Class ....
	
	
		
	class APP_Escrow_NextPay extends NextPay_Gateway implements APP_Escrow_Payment_Processor {
		
		public function supports( $service = 'instant' ){
			switch ( $service ) {
				case 'escrow':
					return true;
				break;
				default:
					return parent::supports( $service );
				break;
			}
		}
		
		public function form() {
			$fields = parent::form();
			return apply_filters( 'appthemes_nextpay_escrow_settings_form', $fields );
		}
		
		public function user_form() {
			$fields = array(
				'title' => __( 'اطلاعات پرداخت کاربر', NEXTPAYAPPT ),
				'fields' => array(
					array(
						'title' => __( 'نام بانک', NEXTPAYAPPT ),
						'type' => 'text',
						'name' => 'BANK',
						'extra' => array(
							'class' => 'text regular-text',
						),
						'desc' => __( 'نام بانک صادر کننده عابر بانک', NEXTPAYAPPT ),
					),
					array(
						'title' => __( 'شماره 16 رقمی کارت', NEXTPAYAPPT ),
						'type' => 'text',
						'name' => 'card-Number',
						'extra' => array(
							'class' => 'text regular-text',
						),
						'desc' => __( 'شماره 16 رقمی کارت بانکی خود را وارد کنید .', NEXTPAYAPPT ),
					),
				),
			);
			return apply_filters( 'appthemes_nextpay_escrow_user_settings_form', $fields );
		}
		
		public function get_details( APP_Escrow_Order $order, array $options ) {
			//not required ...
		}	
		
		public function process_escrow( APP_Escrow_Order $order, array $options ) {
			Proccess_NextPay_By_NextPay ($order, $options, true);
			return true;
		}


		public function complete_escrow( APP_Escrow_Order $order, array $options ) {

		}

		public function fail_escrow( APP_Escrow_Order $order, array $options ) {

		}

	}
	
	add_action( 'init', 'NextPay_Gateway_Appthemes_Init_Escrow', 15 );
	function NextPay_Gateway_Appthemes_Init_Escrow() {

		if (function_exists('appthemes_is_escrow_enabled') && appthemes_is_escrow_enabled()) {
	
			appthemes_register_gateway( 'APP_Escrow_NextPay' );	
		
			add_action('parse_request', 'NextPay_Gateway_escrow_parse_request');
			function NextPay_Gateway_escrow_parse_request() {
				if (stripos($_SERVER["REQUEST_URI"], "/transfer-funds/") === 0 && isset($_GET['oid']) && intval($_GET['oid'])){
					$order = appthemes_get_order( intval($_GET['oid']) );
					if ($order && is_object($order) && $order->get_gateway() == "nextpay" && $order->is_escrow())
					{
						$url = $order->get_return_url();
						if ($url && !stripos($url, $_SERVER["REQUEST_URI"])) { wp_redirect($url); die; }
					}
				}
				return true;
			}
			
			add_action('hrb_before_workspace_project_details', 'NextPay_Gateway_escrow_pay');
			function NextPay_Gateway_escrow_pay(){
				$order = (get_the_ID()) ? appthemes_get_order_connected_to( get_the_ID() ) : "";
				
			if ($order && is_object($order) && $order->is_escrow() && $order->get_author() == get_current_user_id() && $order->get_status() != APPTHEMES_ORDER_PAID )
				echo '<a href="'.($order->get_gateway()=="nextpay" ? $order->get_return_url() : site_url("transfer-funds/?oid=".$order->get_id())).'"><span class="label right project-status">'.__( 'Transfer Funds Now &#187;', NEXTPAYAPPT ).'</span></a>';
				return true;
			}
		
		}
	
	}
		
	function Proccess_NextPay_By_NextPay ( $order, $options, $escrow = false )	{
		
		$options["title"] 	= $options["title"] ? $options["title"] : __('درگاه پرداخت نکست پی', NEXTPAYAPPT);

		$query = $options["query"] ? $options["query"] : 'nextpay';
		$user			= $order->get_author();
		$recipient = get_user_by( 'id', $order->get_author() );
		$order_id 		= $order->get_id();
		$order_currency = $order->get_currency();
		$amount	= $order->get_total();
		$status = appthemes_get_order($order_id)->get_status();
		
		if (!$order || !$order_id || !$amount || ($escrow && !$order->is_escrow()))
			throw new Exception('متاسفانه برخی متغیر های مورد نیاز برای شماره سفارش ' . $order_id . ' وجود ندارد و امکان پرداخت وجود ندارد .');
		
		if ( $status == APPTHEMES_ORDER_COMPLETED || $status == APPTHEMES_ORDER_PAID  || $status == APPTHEMES_ORDER_ACTIVATED) {
			echo '<h2>' . __( 'اخطار !', NEXTPAYAPPT ) . '</h2>' . PHP_EOL;
			echo "<div align='center'>";
			echo "تراکنش قبلا انجام شده است .<br/><br/>";
			echo "</div>";
		}
		else {
			if (!$user && !$escrow) 
				$user = __( 'مهمان', NEXTPAYAPPT );
			if (!$user){
				echo '<h2>' . __( 'اخطار !', NEXTPAYAPPT ) . '</h2>' . PHP_EOL;
				echo "<div align='center'><a href='".wp_login_url(get_permalink())."'>".__( 'شما باید وارد سایت شوید . برای ورود به سایت کلیک نمایید .', NEXTPAYAPPT )."</a></div>";
			}
			elseif ($amount < 0){
				echo '<h2>' . __( 'خطا !', NEXTPAYAPPT ) . '</h2>' . PHP_EOL;
				echo "<div class='notice error alert-box'>".__( 'مبلغ پرداخت کوچک تر از صفر است و امکان پرداخت وجود ندارد . این موضوع را با مدیر سایت در میان بگذارید .', NEXTPAYAPPT )."</div>";
			}
			elseif ( $amount == 0 ) {
				do_action( 'Appthemes_NextPay_Return_from_Gateway_Success_before', $order_id, $options, $escrow );
				do_action( 'Appthemes_Return_from_Gateway_Success_before', $order_id, $options, $escrow );
			
				if ($escrow)
				{
					$item = $order->get_item();
				//	echo $item["post"]->post_title;
					$order->paid(); 
				}
				else
				{
					$order->complete(); 
				}
				
				do_action( 'Appthemes_NextPay_Return_from_Gateway_Success_after', $order_id, $options, $escrow );
				do_action( 'Appthemes_Return_from_Gateway_Success_after', $order_id, $options, $escrow );
			
				wp_redirect( $order->get_return_url() );
				exit;
			}
			else if ( !isset($_GET['checkout_gateway']) ) {
				Send_to_NextPay_By_NextPay ( $order, $options, $escrow );
			}
			else if ( isset($_GET['checkout_gateway']) and $_GET['checkout_gateway'] == $query ) {
				Return_from_NextPay_By_NextPay ( $order, $options, $escrow );
			}
		}
		return true;
	}
	
	
	function Send_to_NextPay_By_NextPay ( $order, $options, $escrow = false ) {
		if ( isset($_GET['checkout_gateway']))
			return;
		ob_start();
		
		
		//include NextPay Class Object
		include_once("nextpay_payment.php");
		//----
		
		
		$query = $options["query"] ? $options["query"] : 'nextpay';
		$user			= $order->get_author();
		$recipient = get_user_by( 'id', $order->get_author() );
		$order_id 		= $order->get_id();
		$order_currency = $order->get_currency();
		$amount	= $order->get_total();
		
		update_post_meta( $order_id, '_checked', 'no' );
		
		$Amount = intval($amount);
		if (strtolower($order_currency) == strtolower('IRT') || 
		    strtolower($order_currency) == strtolower('TOMAN') || 
		    strtolower($order_currency) == strtolower('Iran TOMAN') || 
		    strtolower($order_currency) == strtolower('Iranian TOMAN') || 
		    strtolower($order_currency) == strtolower('Iran-TOMAN') || 
		    strtolower($order_currency) == strtolower('Iranian-TOMAN') || 
		    strtolower($order_currency) == strtolower('Iran_TOMAN') || 
		    strtolower($order_currency) == strtolower('Iranian_TOMAN') || 
		    strtolower($order_currency) == strtolower('تومان') || 
		    strtolower($order_currency) == strtolower('تومان ایران')
                )
                    $Amount = $Amount * 1;
                else if (strtolower($order_currency) == strtolower('IRHT'))
                    $Amount = $Amount * 1000;
                else if (strtolower($order_currency) == strtolower('IRHR'))
                    $Amount = $Amount * 100;
                else if (strtolower($order_currency) == strtolower('IRR') ||
                         strtolower($order_currency) == strtolower('ریال') ||
                         strtolower($order_currency) == strtolower('rial') ||
                         strtolower($order_currency) == strtolower('rials') ||
                         strtolower($order_currency) == strtolower('ریال ایران') ||
                         strtolower($order_currency) == strtolower('iran rial')
                )
                    $Amount = $Amount / 10;
		
			
			
				
		//Hooks for iranian developer
		
		do_action( 'Appthemes_NextPay_Gateway_Payment', $order_id, $options, $escrow );
		do_action( 'Appthemes_Gateway_Payment', $order_id, $options, $escrow );
		
		
		
		
		$is_error = 'no';
		$code_err = 0;
		
		$api_key = $options["api_key"];
		
		$CallbackUrl = add_query_arg( 'checkout_gateway' , $query , $order->get_return_url() );
		$parameters = array
		(
		    "api_key"=>$api_key,
		    "order_id"=> $order_id,
		    "amount"=>$Amount,
		    "callback_uri"=>$CallbackUrl
		);


		$nextpay = new Nextpay_Payment($parameters);
		$nextpay->setDefaultVerify(0);
		$result = (object)$nextpay->token();
		$code_err = intval($result->code);

		if($code_err == -1){
		    $nextpay->send($result->trans_id);
		    exit;
		} else {
		    $is_error = 'yes';
		}
		
		
		if ($is_error == 'yes') {
				
			$fault = $code_err;
			
			do_action( 'Appthemes_NextPay_Send_to_Gateway_Failed_before', $order_id, $options, $escrow, $fault );
			do_action( 'Appthemes_Send_to_Gateway_Failed_before', $order_id, $options, $escrow, $fault );
			
			echo '<h1 class="single dotted">'.__('وضعیت پرداخت', NEXTPAYAPPT).'</h1><br/>';
			$Note = sprintf( __( 'خطا در هنگام ارسال به بانک : %s', NEXTPAYAPPT), Appthemes_Fault_NextPay($fault) );
			$Note = apply_filters( 'Appthemes_NextPay_Send_to_Gateway_Failed_Note', $Note, $order_id, $fault );
			echo "<div class='notice error alert-box'>".$Note."</div>";
				
			$order->log( sprintf( __( 'خطا در هنگام ارسال به بانک : %s', NEXTPAYAPPT), Appthemes_Fault_NextPay($fault) ) ,'failed');
			
			do_action( 'Appthemes_NextPay_Send_to_Gateway_Failed_after', $order_id, $options, $escrow, $fault );
			do_action( 'Appthemes_Send_to_Gateway_Failed_after', $order_id, $options, $escrow, $fault );
		}
		
	}
	
	
	function Return_from_NextPay_By_NextPay ( $order, $options, $escrow = false ) {
		
		$query = $options["query"] ? $options["query"] : 'nextpay';
		if ( !isset($_GET['checkout_gateway']) || ( isset($_GET['checkout_gateway']) and $_GET['checkout_gateway'] != $query ) )
			return;
		ob_start();
		
		//include NextPay Class Object
		include_once("nextpay_payment.php");
		//----
		
		$user_id			= $order->get_author();
		$recipient = get_user_by( 'id', $order->get_author() );
		$order_id 		= $order->get_id();
		$order = appthemes_get_order( $order_id );
		$order_currency = $order->get_currency();
		$amount	= $order->get_total();
		$checked = get_post_meta( $order_id, '_checked', true );
		echo '<h1 class="single dotted">'.__('وضعیت پرداخت', NEXTPAYAPPT).'</h1><br/>';
	
		$Amount = intval($amount);
		if (strtolower($order_currency) == strtolower('IRT') || 
		    strtolower($order_currency) == strtolower('TOMAN') || 
		    strtolower($order_currency) == strtolower('Iran TOMAN') || 
		    strtolower($order_currency) == strtolower('Iranian TOMAN') || 
		    strtolower($order_currency) == strtolower('Iran-TOMAN') || 
		    strtolower($order_currency) == strtolower('Iranian-TOMAN') || 
		    strtolower($order_currency) == strtolower('Iran_TOMAN') || 
		    strtolower($order_currency) == strtolower('Iranian_TOMAN') || 
		    strtolower($order_currency) == strtolower('تومان') || 
		    strtolower($order_currency) == strtolower('تومان ایران')
                )
                    $Amount = $Amount * 1;
                else if (strtolower($order_currency) == strtolower('IRHT'))
                    $Amount = $Amount * 1000;
                else if (strtolower($order_currency) == strtolower('IRHR'))
                    $Amount = $Amount * 100;
                else if (strtolower($order_currency) == strtolower('IRR') ||
                         strtolower($order_currency) == strtolower('ریال') ||
                         strtolower($order_currency) == strtolower('rial') ||
                         strtolower($order_currency) == strtolower('rials') ||
                         strtolower($order_currency) == strtolower('ریال ایران') ||
                         strtolower($order_currency) == strtolower('iran rial')
                )
                    $Amount = $Amount / 10;

		$pay_trans_id = isset($_POST['trans_id']) ? sanitize_text_field($_POST['trans_id']) : '';
		$pay_order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';
		
		if ( !isset($pay_trans_id) || !isset($pay_order_id))
			return;
		
		$api_key = $options["api_key"]; 
		$order_id = $pay_order_id;
		$trans_id = $pay_trans_id;
		$status = '';
		$fault = 0;
		
		$parameters = array
		(
		    'api_key'	=> $api_key,
		    'order_id'	=> $order_id,
		    'trans_id' 	=> $trans_id,
		    'amount'	=> $Amount,
		);

		$nextpay = new Nextpay_Payment();
		$nextpay->setDefaultVerify(0);
		$result = $nextpay->verify_request($parameters);

		if (intval($result) == 0) {
		    $status = 'completed';
		    $transaction_id = $trans_id;
		    $fault = 0;
		    $Message = '';
		} else {
		    $status = 'failed';
		    $transaction_id = $trans_id;
		    $fault = $result;
		    $Message = 'تراکنش ناموفق بود';
		}
	
		
		update_post_meta( $order_id, '_checked', 'yes' );
		
		if ( isset($transaction_id) and $transaction_id!=0 and $checked != 'yes' ) {
			update_post_meta( $order_id, 'transaction_id', $transaction_id );
			$order->log( sprintf( __( 'کد رهگیری (کد مرجع تراکنش) : %s', NEXTPAYAPPT), $transaction_id ), 'major' );
		}
		
		if ( isset($order_id) and $order_id!=0 and $checked != 'yes' ) {
			$order->log( sprintf( __( 'شماره درخواست تراکنش : %s', NEXTPAYAPPT), $order_id ), 'major' );
		}
		
		
		if ($status == "completed") {
			
			if ( $checked != 'yes' ) {
				do_action( 'Appthemes_NextPay_Return_from_Gateway_Success_before', $order_id, $options, $escrow );
				do_action( 'Appthemes_Return_from_Gateway_Success_before', $order_id, $options, $escrow );
			
				if ($escrow)
				{
					$item = $order->get_item();
				//	echo $item["post"]->post_title;
					$order->paid(); 
				}
				else
				{
					$order->complete(); 
				}
				
				do_action( 'Appthemes_NextPay_Return_from_Gateway_Success_after', $order_id, $options, $escrow );
				do_action( 'Appthemes_Return_from_Gateway_Success_after', $order_id, $options, $escrow );
				ob_end_flush();
				ob_end_clean();
				if (!headers_sent()) {
					wp_redirect( $order->get_return_url() );
					exit;
				}
				else {
					$redirect_page = $order->get_return_url();
					echo "<script type='text/javascript'>window.onload = function () { top.location.href = '" . $redirect_page . "'; };</script>";
					exit;
				}
			}	
		}
		elseif ( $status == 'cancelled') {
			
			if ( $checked != 'yes' ) {
				do_action( 'Appthemes_NextPay_Return_from_Gateway_Cancelled_before', $order_id, $options, $escrow );
				do_action( 'Appthemes_Return_from_Gateway_Cancelled_before', $order_id, $options, $escrow );
			}
			
			
			$cancelled_massage = wpautop( wptexturize(get_option(NEXTPAYAPPT."_cancelled_massage")) );
			$cancelled_massage = str_replace("{transaction_id}",$transaction_id, $cancelled_massage);
			$cancelled_massage = str_replace("{order_id}",$order_id,$cancelled_massage);
							
			if (!$cancelled_massage)
				$cancelled_massage = __( '', NEXTPAYAPPT );
			$cancelled_massage = apply_filters( 'Appthemes_NextPay_Return_from_Gateway_Cancelled_Message', $cancelled_massage, $order_id, $options, $escrow, $transaction_id,$order_id );
			echo "<div class='notice error alert-box'>".$cancelled_massage."</div>";
			echo '<br/><a class="re-pay button mbtn" href="'.$order->get_return_url().'">'.__('پرداخت', NEXTPAYAPPT).'</a>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="cancel-gateway button mbtn secondary previous-step" href="'.$order->get_cancel_url().'">'.__('تغییر روش پرداخت', NEXTPAYAPPT).'</a>';
			echo '<br/>';
			
			if ( $checked != 'yes' ) {
				$order->log( __('تراکنش به دلیل انصراف کاربر ناتمام باقی ماند . روش پرداخت : درگاه نکست پی', NEXTPAYAPPT) ,'failed' );
				do_action( 'Appthemes_NextPay_Return_from_Gateway_Cancelled_after', $order_id, $options, $escrow );
				do_action( 'Appthemes_Return_from_Gateway_Cancelled_after', $order_id, $options, $escrow );
			}
		}
		else {
			
			$fault_error = Appthemes_Fault_NextPay($fault);
			
			if ( $checked != 'yes' ) {
				do_action( 'Appthemes_NextPay_Return_from_Gateway_Failed_before', $order_id, $options, $escrow, $fault );
				do_action( 'Appthemes_Return_from_Gateway_Failed_before', $order_id, $options, $escrow, $fault );
				$order->failed();
			}
			
			 
			$failed_massage = wpautop( wptexturize(get_option(NEXTPAYAPPT."_failed_massage")) );
			$failed_massage = str_replace("{transaction_id}",$transaction_id, $failed_massage);
			$failed_massage = str_replace("{order_id}",$order_id,$failed_massage);
			$failed_massage = str_replace("{fault}", $fault_error, $failed_massage);
			if (!$failed_massage)
				$failed_massage = __( '', NEXTPAYAPPT );
			$failed_massage = apply_filters( 'Appthemes_NextPay_Return_from_Gateway_Failed_Message', $failed_massage, $order_id, $options, $escrow, $transaction_id,$order_id, $fault );
			echo "<div class='notice error alert-box'>".$failed_massage."</div><br/><br/>";
		
		
			if ( $checked != 'yes' ) {
				$order->log( sprintf( __( 'خطا در هنگام بازگشت از بانک : %s روش پرداخت : درگاه نکست پی', NEXTPAYAPPT), $fault_error  ) , 'failed'  );
				do_action( 'Appthemes_NextPay_Return_from_Gateway_Failed_after', $order_id, $options, $escrow, $fault );
				do_action( 'Appthemes_Return_from_Gateway_Failed_after', $order_id, $options, $escrow, $fault );
			}
			
		}
		
		return true;
	}
	
	
	add_action( 'appthemes_before_order_summary', 'Appthemes_NextPay_Order_Summary' );
	function Appthemes_NextPay_Order_Summary( $order ){
		
		if ( $order->get_gateway() != 'nextpay')
			return;
		
		$order_id 		= $order->get_id();
		$order = appthemes_get_order( $order_id );
		$status = appthemes_get_order($order_id)->get_status();
		$transaction_id = get_post_meta( $order_id, 'transaction_id', true ) ? get_post_meta( $order_id, 'transaction_id', true ) : '-';
		$Order_Id = get_post_meta( $order_id, '_pay_order_id', true ) ? get_post_meta( $order_id, '_pay_order_id', true ) : '-';
		
		$success_massage = wpautop( wptexturize(get_option(NEXTPAYAPPT."_success_massage")) );
		$success_massage = str_replace("{transaction_id}",$transaction_id, $success_massage);
		$success_massage = str_replace("{order_id}",$Order_Id, $success_massage);
		
		if (!$success_massage)
			$success_massage = __( '', NEXTPAYAPPT );
		$success_massage = apply_filters( 'Appthemes_NextPay_Return_from_Gateway_Success_Message', $success_massage, $order_id, $transaction_id, $Order_Id );
				
		do_action( 'Appthemes_NextPay_Order_Summary_before', $order_id );
		do_action( 'Appthemes_Order_Summary_before', $order_id );
		
		if (  $status == APPTHEMES_ORDER_COMPLETED || $status == APPTHEMES_ORDER_PAID || $status == APPTHEMES_ORDER_ACTIVATED  ) {
			echo $success_massage;
		}
		
		do_action( 'Appthemes_NextPay_Order_Summary_after', $order_id );
		do_action( 'Appthemes_Order_Summary_after', $order_id );
		
	}
	
	
	function Appthemes_Fault_NextPay($err_code){
		
		$message = __('در حین پرداخت خطای سیستمی رخ داده است .', NEXTPAYAPPT );
		
		$error_code = intval($err_code);
		$error_array = array(
		    0 => "Complete Transaction",
		    -1 => "Default State",
		    -2 => "Bank Failed or Canceled",
		    -3 => "Bank Payment Pendding",
		    -4 => "Bank Canceled",
		    -20 => "api key is not send",
		    -21 => "empty trans_id param send",
		    -22 => "amount in not send",
		    -23 => "callback in not send",
		    -24 => "amount incorrect",
		    -25 => "trans_id resend and not allow to payment",
		    -26 => "Token not send",
		    -30 => "amount less of limite payment",
		    -32 => "callback error",
		    -33 => "api_key incorrect",
		    -34 => "trans_id incorrect",
		    -35 => "type of api_key incorrect",
		    -36 => "order_id not send",
		    -37 => "transaction not found",
		    -38 => "token not found",
		    -39 => "api_key not found",
		    -40 => "api_key is blocked",
		    -41 => "params from bank invalid",
		    -42 => "payment system problem",
		    -43 => "gateway not found",
		    -44 => "response bank invalid",
		    -45 => "payment system deactived",
		    -46 => "request incorrect",
		    -48 => "commission rate not detect",
		    -49 => "trans repeated",
		    -50 => "account not found",
		    -51 => "user not found"
		);
		$message =  __($error_array[$error_code], NEXTPAYAPPT );
		return $message;
	}
}