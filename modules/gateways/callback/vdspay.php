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

#Define VdsPay Merchant Credentials
define("username", $gatewayParams["username"]);
define("acct_number", $gatewayParams["accountNo"]);
define("api_key", $gatewayParams["api_key"]);
define("api_pass", $gatewayParams["api_pass"]);

//Load The Service Class
$service = new emp_service();

//Retrieve Transaction ID From Gateway Callback
$transactionId = $_POST["transid"];

try {
$transaction = $service->query_transaction($transactionId);
if($transaction["status"] == 'Approved') {
  $invoiceId = $transaction["reference"];
  $paymentAmount = $transaction["amount"];
  $paymentFee = 0.00;
  $add = addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );
  header("Location: ../../viewinvoice.php?id=$invoiceId&status=Success");
	exit();
} else {
  $invoiceId = $transaction["reference"];
  $msg = $transaction["response"];
	header("Location: ../../viewinvoice.php?id=$invoiceId&msg=$msg");
	exit();
}} catch(Service_Error $e) {
  $error = json_decode($e, true);
  $msg = $error["Error_Message"];
  header("Location: ../../viewinvoice.php?id=$invoiceId&msg=$msg");
	exit();
}


