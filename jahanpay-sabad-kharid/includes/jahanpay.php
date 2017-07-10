<?php
if (isset($_GET['jahanpay']))
{
	if (isset($_GET['modID']))
	{
		$modID = $_GET['modID'];
		$comStatID = _getSettingOptionValue('CONF_COMPLETED_ORDER_STATUS');
		$api = '';
		$q = db_query("SELECT * FROM ".SETTINGS_TABLE." WHERE settings_constant_name='CONF_PAYMENTMODULE_jahanpay_API_$modID'");
		$res = db_fetch_row($q);
		if(!empty($res['settings_value']))
		{
			$api = $res['settings_value'];
		}
		else
		{
			Redirect( "index.php" );
		}
		@session_start();
		$time = $_SESSION['jResNum'];
		$cost = $_SESSION['jPrice'];
		$au = $_SESSION['jAU'];
		date_default_timezone_set("Asia/Tehran");
        $client = new SoapClient("http://www.jpws.me/directservice?wsdl");
        $res = $client->verification($api , $cost , $au , $time, $_POST + $_GET );
		if (!empty($res['result']) AND $res['result']==1)
		{
			$pininfo = ostSetOrderStatusToOrder($time, $comStatID, 'Your Online Payment with jahanpay gateway accepted', 1);
			$body =  STR_SHETAB_THANKS.'<br>';
			$body .= STR_SHETAB_REFNUM.': '.$au.'<br>';
			$body .= $pininfo;
			updateRefNum($time, $au);
		}
		else
		{
			ostSetOrderStatusToOrder($time, 1);
			$body .= STR_SHETAB_REFNUM.': '.$au.'<br>';
			$body = ERROR_SHETAB_PAYMENTFAILD.'<br> '.STR_SHETAB_RESON.' ' ;
			$body .= 'ErrorCode : '.$res['result'];
		}
		define('تایید پرداخت توسط جهان پی', 'تایید پرداخت توسط جهان پی');
		$smarty->assign("page_body", $body );
		$smarty->assign("main_content_template", "jahanpay.tpl.html" );
	}
	else
	{
		$smarty->assign("main_content_template", "page_not_found.tpl.html" );
	}
}

?>