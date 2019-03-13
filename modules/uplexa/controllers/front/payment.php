<?php
include(dirname(__FILE__). '/../../library.php');
class uPlexaPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = false;


    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;



        $currency = $this->context->currency;
        $currency_iso = $currency->iso_code;
        $total = $cart->getOrderTotal(true, Cart::BOTH);

		$amount = $this->changeto($total, $currency_iso);
		$actual = $this->retriveprice($currency_iso);

		$payment_id  = $this->set_paymentid_cookie();
		$address = Configuration::get('UPLEXA_ADDRESS');
		$uri = "uplexa:$address?amount=$amount?payment_id=$payment_id";
		$status = "Awaiting Confirmation...";


		$daemon_address = Configuration::get('UPLEXA_WALLET');

		$this->uplexa_daemon = new uPlexa_Library('http://'. $daemon_address .'/json_rpc'); // example $daemon address 127.0.0.1:21061

		$integrated_address_method = $this->uplexa_daemon->make_integrated_address($payment_id);
		$integrated_address = $integrated_address_method["integrated_address"];

        if($this->verify_payment($payment_id, $amount))
		{
			$status = "Your Payment has been confirmed! Yay!";
			// Confirm Cart!

		}


    	$this->context->smarty->assign(
    	array(
    		'address' => $address,
    		'currency' => $currency_iso,
    		'amount' => $amount,
    		'actual' => $actual,
    		'payment_id' => $payment_id,
    		'uri' => $uri,
    		'integrated_address' => $integrated_address,
    		'status' => $status
    	));

        $this->setTemplate('module:uplexa/views/templates/front/payment_execution.tpl');

    }

    private function set_paymentid_cookie()
				{
					if(!isset($_COOKIE['payment_id']))
					{
						$payment_id  = bin2hex(openssl_random_pseudo_bytes(8));
						setcookie('payment_id', $payment_id, time()+2700);
					}
					else
						$payment_id = $_COOKIE['payment_id'];
					return $payment_id;
				}

	public function retriveprice($c)
				{
								$upx_price = Tools::file_get_contents('https://uplexa.com/data?currencies=BTC,USD,EUR,CAD,INR,GBP');
								$price         = json_decode($upx_price, TRUE);

								if ($c == 'USD') {
												return $price['USD'];
								}
								if ($c == 'EUR') {
												return $price['EUR'];
								}
								if ($c == 'CAD'){
												return $price['CAD'];
								}
								if ($c == 'GBP'){
												return $price['GBP'];
								}
								if ($c == 'INR'){
												return $price['INR'];
								}
								else{
												return $price['USD'];
								}
				}

	public function changeto($amount, $currency)
	{
		$upx_live_price = $this->retriveprice($currency);
		echo $upx_live_price;
		$new_amount     = $amount / $upx_live_price;
		$rounded_amount = round($new_amount, 2); //the moneo wallet can't handle decimals smaller than 0.000000000001
		return $rounded_amount;
	}

	public function verify_payment($payment_id, $amount)
	{
      /*
       * function for verifying payments
       * Check if a payment has been made with this payment id then notify the merchant
       */

      $amount_atomic_units = $amount * 100;
      $get_payments_method = $this->uplexa_daemon->get_payments($payment_id);
      if(isset($get_payments_method["payments"][0]["amount"]))
      {
		if($get_payments_method["payments"][0]["amount"] >= $amount_atomic_units)
		{
			$confirmed = true;
		}
	  }
	  else
	  {
		  $confirmed = false;
	  }
	  return $confirmed;
  }




}
