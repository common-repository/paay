<?php

class Paay_Gateway extends WC_Payment_Gateway
{
    private $order;
    public function __construct()
    {
        $this->id                 = 'paay_gateway'; // Unique ID for your gateway. e.g. ‘your_gateway’
        // $this->icon – If you want to show an image next to the gateway’s name on the frontend, enter a URL to an image.
        $this->has_fields         = false; // Bool. Can be set to true if you want payment fields to show on the checkout (if doing a direct integration).
        $this->method_title       = 'Credit Card powered by PAAY'; // Title of the payment method shown on the admin page.
        $this->method_description = '<div id="wc_get_started" class="paay"><span class="main"><img class="logo" src="'. paayPluginPath() .'images/paay/paay.jpg" alt="PAAY logo" style="width:25px; height:25px"/ alt="PAAY"></span><span>Safe and simple online checkout.</span></div>'; // Description for the payment method shown on the admin page.
        $this->title              = 'Credit Card powered by PAAY';
        $this->shop_title         = 'Credit / Debit card';
        $this->description        = 'Safe and simple online checkout.';
        $this->icon               = paayPluginPath() ."images/paay/paay-secure.png";

        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));

        $order = $this->get_order_from_url();
        /**
         * Thank you page overhaul
         */
        // Override "Order reveived" text
        add_filter('the_title', function ($title, $page_id) use ($order) {
            if ($page_id !== wc_get_page_id('checkout')) {
                return $title;
            }

            if (null === $order) {
                return $title;
            }

            if ('on-hold' !== $order->get_status()) {
                return $title;
            }

            return 'PAAY for Order';
        }, 10, 2);
        // Hide "Thank you, order received" text
        add_filter('woocommerce_thankyou_order_received_text', function ($text) use ($order) {
            if ('on-hold' === $order->get_status()) {
                $text = '';
            }

            return $text;
        });
        // Add PAAY buttonform
        add_action('woocommerce_thankyou_'.$this->id, array($this, 'payment_form'));
        // Hide order details: billing, shipping etc.
        add_action('woocommerce_thankyou', function ($order_id) use ($order) {
            if ('on-hold' === $order->get_status()) {
                remove_all_actions('woocommerce_thankyou');
            }
        }, 1);

        //Do empty cart only if user's redirected to paayonhold page - otherwise, keep stuff in!
        add_action('get_header', function() {
            if (!isset($_GET['paayonhold'])) {
                remove_action('get_header', 'wc_clear_cart_after_payment');
            }
        }, 1);

        //Set up callback url
        $this->status_url = add_query_arg('wc-api', 'PAAY_Gateway_Standalone', home_url('/'));
        add_action('woocommerce_api_paay_gateway_standalone', array($this, 'verifyCallback'));

    }

    public function get_title()
    {
        return $this->shop_title;
    }

    private function sig()
    {
        if (null === $this->sig) {
            $api_key    = $this->settings['paay_key'];
            $api_secret = $this->settings['paay_secret'];
            $this->sig = new PAAY_Auth_Signature($api_key, $api_secret);
        }

        return $this->sig;
    }

    private function get_order_from_url()
    {
        static $order = null;
        $order_key = (isset($_GET['key'])) ? $_GET['key'] : false;

        if(!$order_key){
            return $order;
        }

        if (null === $order) {
            $order_id = wc_get_order_id_by_order_key($order_key);

            $order_id  = apply_filters('woocommerce_thankyou_order_id', $order_id);
            $order_key = apply_filters('woocommerce_thankyou_order_key', $order_key);

            if ($order_id > 0) {
                $order = wc_get_order($order_id);
                if ($order->order_key != $order_key) {
                    unset($order);
                }
            }
        }

        return $order;
    }

    public function init_form_fields()
    {
        $strategies = array(
            'never'    => 'Never show',
            'detected' => 'Show if needed',
        );
        $standalone_strategies = array(
            'modal_auto'          => 'Load PAAY in a modal automatically',
            'modal_manual'        => 'Load PAAY in a modal, show summary page first',
            'landing_page_auto'   => 'Redirect to PAAY automatically',
            'landing_page_manual' => 'Redirect to PAAY, show summary page first',
        );

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommercepaay'),
                'type' => 'checkbox',
                'label' => __('Enable PAAY Standard Checkout', 'woocommercepaay'),
                'default' => 'yes'
            ),
            'PAAYButton' => array(
                'type' => 'checkbox',
                'label' => __('Enable PAAY Mobile Wallet Checkout', 'woocommercepaay'),
                'default' => 'yes'
            ),
            'paay_key' => array(
                'title' => __('Merchant "API KEY"', 'woocommercepaay'),
                'type' => 'text',
                'label' => __('Merchant "API KEY"', 'woocommercepaay'),
            ),
            'paay_secret' => array(
                'title' => __('Merchant "API SECRET"', 'woocommercepaay'),
                'type' => 'text',
                'label' => __('Merchant "API SECRET"', 'woocommercepaay'),
            ),
            'paay_3ds_strategy' => array(
                'title' => __('3D Secure Prompt', 'woocommercepaay'),
                'type' => 'select',
                'options' => $strategies,
                'description' => 'When the transaction is considered to have a high probability of being a fraudulent transaction, 3D Secure prompts the consumer with an extra authentication step. Choose “never show” if you want to avoid the extra authentication step when it’s required, and send the transaction without 3D Secure. <br><br> NOTE: If you choose “never show”, you will not get chargeback protection for transactions that require consumer authe',
                'desc_tip'    => true,
            ),
            'paay_standalone_strategy' => array(
                'title' => __('Standalone strategy', 'woocommercepaay'),
                'type' => 'select',
                'options' => $standalone_strategies,
            ),
            'paay_host' => array(
                'title' => __('PAAY host', 'woocommercepaay'),
                'type' => 'text',
                'default' => 'https://api.paay.co'
            ),
            'paay_return_url' => array(
	            'title' => __('PAAY Return Url', 'woocommercepaay'),
	            'description'=>'Url address after payment process. <br> Add only page name e.g. if page URL is http://example.com/<b>success_transaction</b> add only "success_transaction"',
	            'desc_tip'=>true,
	            'type' => 'text',

            ),
            'paay_cancel_url' => array(
	            'title' => __('PAAY Cancel Url', 'woocommercepaay'),
	            'description'=>'Url address after failed/cancel payment process. <br> Add only page name e.g. if page URL is http://example.com/<b>transaction_faild</b> add only "transaction_failed"',
	            'desc_tip'=>true,
	            'type' => 'text',

            ),
            'paay_standalone_host' => array(
                'title' => __('PAAY Standalone host', 'woocommercepaay'),
                'type' => 'text',
                'default' => 'https://api2.paay.co'
            ),
        );
    }

	public function returnUrl(){
    	if (!empty($this->settings['paay_return_url'])){
    		return trim(get_site_url(),'/').'/'.$this->settings['paay_return_url'];
	    }
		return get_site_url();
	}
	public function cancelUrl(){
		if (!empty($this->settings['paay_cancel_url'])){
			return trim(get_site_url(),'/').'/'.$this->settings['paay_cancel_url'];
		}
		return get_site_url();
	}
    public function payment_form($order_id)
    {
        global $woocommerce;
        $order    = new WC_Order($order_id);
        $items    = $order->get_items();
        $returnUrl = $this->returnUrl();
        $cancelUrl  = $this->cancelUrl();
        $products = array();
        foreach ($items as $item) {
            $products[] = array(
                'name'   => $item['name'],
                'amount' => $item['line_total'],
            );
        }

        $transaction = array(
            //Customer
            'email'             => $order->billing_email,
            //Order
            'amount'            => $order->get_total(),
            'details'           => json_encode($products, true),
            'orderId'           => $order->get_order_number(),
            'returnUrl'         => $returnUrl,
            'cancelUrl'         => $cancelUrl,
            'statusUrl'         => $this->status_url,
            //Billing
            'billingFirstName'  => $order->billing_first_name,
            'billingLastName'   => $order->billing_last_name,
            'billingCompany'    => $order->billing_company,
            'billingEmail'      => $order->billing_email,
            'billingAddress1'   => $order->billing_address_1,
            'billingAddress2'   => $order->billing_address_2,
            'billingCity'       => $order->billing_city,
            'billingPostcode'   => $order->billing_postcode,
            'billingState'      => $order->billing_state,
            'billingCountry'    => $order->billing_country,
            'billingPhone'      => $order->billing_phone,
            //Shipping
            'shippingFirstName' => $order->shipping_first_name,
            'shippingLastName'  => $order->shipping_last_name,
            'shippingCompany'   => $order->shipping_company,
            'shippingAddress1'  => $order->shipping_address_1,
            'shippingAddress2'  => $order->shipping_address_2,
            'shippingCity'      => $order->shipping_city,
            'shippingPostcode'  => $order->shipping_postcode,
            'shippingState'     => $order->shipping_state,
            'shippingCountry'   => $order->shipping_country,
            'shippingPhone'     => '',
            'api_key'           => '',
            'signature'         => '',
        );
        $standalone_strategy               = (isset($this->settings['paay_standalone_strategy'])) ? $this->settings['paay_standalone_strategy'] : 'modal';
        $threeds_visibility                = $this->settings['paay_3ds_strategy'];
        $transaction['threeds_visibility'] = (empty($threeds_visibility)) ? 'visible' : $threeds_visibility;

        $api_host           = $this->settings['paay_standalone_host'];
        $api_key            = $this->settings['paay_key'];
        $signature          = $this->sig()->get($transaction);
        $paayonhold         = isset($_GET['paayonhold']);
        $paaydeclined       = isset($_GET['paaydeclined']);
        $template = dirname(__FILE__).'/../../templates/payment_form.php';
        require_once $template;
    }

    private function callbackResponse($message)
    {
        header('Content-type: text/html');
        echo $message;
        exit;
    }

    public function verifyCallback()
    {
        $http_method = (isset($_SERVER['REQUEST_METHOD'])) ? strtolower($_SERVER['REQUEST_METHOD']) : 'unknown';
        if ('post' !== $http_method) {
            return $this->callbackResponse('INVALID REQUEST METHOD: '.$http_method);
        }

        $data = $_POST;

        //Get the order
        $order = new WC_Order($data['order_id']);

        //No order, no fun
        if (!$order) {
            return $this->callbackResponse('ORDER NOT FOUND');
        }

        //Order not in "waiting state" - return proper error message
        if ('on-hold' !== $order->get_status()) {
            return $this->callbackResponse('ORDER NOT IN PENDING STATE');
        }

        //Verify signature
        $signature = $data['signature'];
        $expected  = $this->sig()->get($data);
        if ($signature !== $expected) {
            return $this->callbackResponse('WRONG SIGNATURE PROVIDED');
        }

        // Reduce stock levels
        $order->reduce_order_stock();

        //We should be fine now - let's update the order
        $order->payment_complete();

        $notification = new WC_Email_Customer_Processing_Order();
        $notification->trigger($data['order_id']);

        return $this->callbackResponse('OK');
    }

    public function process_payment($order_id)
    {
        global $woocommerce;
        $order = new WC_Order($order_id);

        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status('on-hold', __('Awaiting PAAY payment', 'woocommerce'));

        // Return thankyou redirect
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }
}
