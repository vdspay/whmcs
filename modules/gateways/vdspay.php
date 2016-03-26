<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function vdspay_MetaData() {
  return array(
        'DisplayName' => 'VdsPay',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function vdspay_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'VdsPay',
        ),
        'accountNo' => array(
            'FriendlyName' => 'Account Number',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'VdsPay Account Number',
        ),
        'username' => array(
            'FriendlyName' => 'Username',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Merchant Username',
        ),
        'api_key' => array(
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Merchant API Key',
        ),
        'api_pass' => array(
            'FriendlyName' => 'API Password',
            'Type' => 'text',
            'Size' => '75',
            'Default' => '',
            'Description' => 'Merchant API Password',
        ),
    );
}

function vdspay_link($params) {
  
  $accountNo = $params["accountNo"];
  $api_key = $params["api_key"];
  $invoiceId = $params['invoiceid'];
  $description = $params["description"];
  $amount = $params['amount'];
  $currency = $params['currency'];
  $systemUrl = $params['systemurl'];
  $moduleName = $params['paymentmethod'];
  $firstname = $params['clientdetails']['firstname'];
  $lastname = $params['clientdetails']['lastname'];
  $email = $params['clientdetails']['email'];
  
  //Prepare Params
  $post = array();
  $post["transaction"]["accountNo"] = $accountNo;
  $post["transaction"]["memo"] = $description;
  $post["transaction"]["reference"] = $invoiceId;
  $post["transaction"]["amount"] = $amount;
  $post["transaction"]["currency"] = $currency;
  $post["transaction"]["type"] = "Sale";
  $post["transaction"]["return_url"] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
  $post["transaction"]["notify_url"] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
  $post["transaction"]["customer"]["name"] = "$firstname $lastname";
  $post["transaction"]["customer"]["email"] = $email;
  $post["transaction"]["customer"]["phone"] = $params['clientdetails']['phonenumber'];
  $post_data = json_encode($post, true);
  
  //Calculate Hash
  $hash = hash("sha512", $accountNo.$invoiceId.$amount.$api_key);
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://acs.vdspay.net/transaction/auth");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($post_data),
	  'Authorization: Merchant '.$hash.'')                                                                     
    ); 
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  $c = curl_exec($ch);
  $res = json_decode($c, true);
  if($res["message"] == "Authorization URL created") {
    $url = $res["data"]["authorization_url"];
    $OutPut = '<a href="'.$url.'" class="btn btn-primary" target="_blank">Click To Pay</a><br />';
  } else {
		$OutPut = $res["message"];
	}
	
	return $OutPut;
  
}

?>
