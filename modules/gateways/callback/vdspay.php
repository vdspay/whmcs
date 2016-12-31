<?php 
#Require Libs
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

#Require VdsPay SDK
require_once __DIR__ . '/../../../vdspay_sdk/Service/emp.class.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

//Define Credentials
Vdspay::Username($gatewayParams["username"]); //VdsPay Username
Vdspay::AccountNo($gatewayParams["accountNo"]); //VdsPay Account Number
Vdspay::ApiKey($gatewayParams["api_key"]); //VdsPay API Key
Vdspay::ApiPassword($gatewayParams["api_pass"]); //VdsPay API Password

//Retrieve Transaction ID From Gateway Callback
$transactionId = $_POST["transid"];

try {
$transaction = Vdspay_Transaction::query(array("transid" => $transactionId));
if($transaction["status"] == 'Approved') {
  $invoiceId = $transaction["ref_code"];
  $paymentAmount = $transaction["amount"];
  $paymentFee = 0.00;
  $add = addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );
  header("Location: ../../../viewinvoice.php?id=$invoiceId&status=Success&paymentsuccess=true");
	exit();
} elseif($transaction["status"] == 'Failed')  {
  $invoiceId = $transaction["ref_code"];
  $msg = $transaction["response"];
	header("Location: ../../../viewinvoice.php?id=$invoiceId&msg=$msg&paymentfailed=true");
	exit();
}} catch(Service_Error $e) {
  $error = json_decode($e, true);
  $msg = $error["Error_Message"];
  header("Location: ../../../viewinvoice.php?id=$invoiceId&msg=$msg");
	exit();
}
