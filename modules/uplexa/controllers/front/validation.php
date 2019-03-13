<?php
include(dirname(__FILE__). '/../../library.php');
class uplexaValidationModuleFrontController extends ModuleFrontController
{
	private $uplexa_daemon;

	public function postProcess()
	{
		global $currency;
        $cart = $this->context->cart;
      	$c = $currency->iso_code;
		$total = $cart->getOrderTotal();
		$amount = $this->changeto($total, $c);
		$actual = $this->retriveprice($c);
		$payment_id  = $this->get_paymentid_cookie();
		$daemon_address = Configuration::get('UPLEXA_WALLET');
		$status = "We are waiting for your payment to be confirmed by the uPlexa network.";

		$this->uplexa_daemon = new uPlexa_Library('http://'. $daemon_address .'/json_rpc');
		if($this->verify_payment($payment_id, $amount))
		{
			$status = "Thank you. Your order has been received.";
			//Tools::redirectLink(PS_BASE_URI . 'order-confirmation.php?id_cart=' . $id_cart . '&id;_module=' . intval($this->module->id) . '&id;_order=' . intval($tpg_deposit->currentOrder) . '&key=' . $this->context->customer->secure_key);
		}

		$this->context->smarty->assign(array(
            'this_path_ssl'   => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
				'status' => $status ));
		$this->setTemplate('payment_box.tpl');
	}

	private function get_paymentid_cookie()
	{
		$payment_id = $_COOKIE['payment_id'];
		return $payment_id;
	}

	public function retriveprice($c)
	{
		$xmr_price = Tools::file_get_contents('https://uplexa.com/data?currencies=BTC,USD,EUR,CAD,INR,GBP&extraParams=uplexa_prestashop');
		$price         = json_decode($xmr_price, TRUE);

		if ($c== 'USD') {
			return $price['USD'];
		}
		if ($c== 'EUR') {
			return $price['EUR'];
		}
		if ($c== 'CAD'){
			return $price['CAD'];
		}
		if ($c== 'GBP'){
			return $price['GBP'];
		}
		if ($c== 'INR'){
			return $price['INR'];
		}
		else{
			//return $price['USD'];
		}
	}

	public function changeto($amount, $currency)
	{
		$xmr_live_price = $this->retriveprice($currency);
		$new_amount     = $amount / $xmr_live_price;
		$rounded_amount = round($new_amount, 2); //the uPlexa wallet can't handle decimals smaller than 0.01
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
