<?php

class CJAHANPAY extends PaymentModule
{
	function _initVars()
	{
		$this->title = CJAHANPAY_TTL;
		$this->description = CJAHANPAY_DSCR;
		$this->sort_order = 1;		
		$this->Settings = array("CONF_PAYMENTMODULE_JAHANPAY_API", "CONF_PAYMENTMODULE_JAHANPAY_RLS_CURRENCY");
	}

	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );
		if ( $this->_getSettingValue('CONF_PAYMENTMODULE_JAHANPAY_RLS_CURRENCY') > 0 )
		{
			$SAcurr = currGetCurrencyByID ( $this->_getSettingValue('CONF_PAYMENTMODULE_JAHANPAY_RLS_CURRENCY') );
			$SAcurr_rate = $SAcurr["currency_value"];
		}
		if (!isset($SAcurr) || !$SAcurr)
		{
			$SAcurr_rate = 1;
		}
		$modID =  $this->get_id();
		$order_amount = round(100*$order["order_amount"] * $SAcurr_rate)/100;
		$api = $this->_getSettingValue("CONF_PAYMENTMODULE_JAHANPAY_API");
		$callBackUrl = CONF_FULL_SHOP_URL."/?JAHANPAY&modID=$modID";
		try
		{
			date_default_timezone_set("Asia/Tehran");
			$client = new SoapClient("http://www.jpws.me/directservice?wsdl");
            $res = $client->requestpayment($api , $order_amount , $callBackUrl , $orderID );
			if ($res['result'] AND $res['result']==1)
			{
				@session_start();
				$_SESSION['jResNum'] = $orderID;
				$_SESSION['jPrice'] = $order_amount;
				$_SESSION['jAU'] = $res['au'];
			echo ('<div style="display:none;">'.$res['form'].'</div><script>document.forms["jahanpay"].submit();</script>');
			}
			else
			{
				echo "Error $res['result']";
			}
		}
		catch (SoapFault $ex)
		{
			echo  'Error: '.$ex->getMessage();
		}
		exit;
	}

	function _initSettingFields()
	{
		$this->SettingsFields['CONF_PAYMENTMODULE_JAHANPAY_API'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CJAHANPAY_CFG_API_TTL, 
			'settings_description' 	=> CJAHANPAY_CFG_API_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_JAHANPAY_RLS_CURRENCY'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> CJAHANPAY_CFG_RIAL_CURRENCY_TTL, 
			'settings_description' 	=> CJAHANPAY_CFG_RIAL_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
	}
}
?>