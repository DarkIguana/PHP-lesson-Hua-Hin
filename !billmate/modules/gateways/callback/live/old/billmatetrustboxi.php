<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Require libraries needed for gateway module functions.
require_once '/var/www/html/mydata.se/client/init.php';
require_once '/var/www/html/mydata.se/client/includes/gatewayfunctions.php';
require_once '/var/www/html/mydata.se/client/includes/invoicefunctions.php';

require_once 'quickpaytrustbox_newsletter.php';
require_once 'Billmate.php';


$gatewayModuleName = 'billmatetrustbox';
$gatewayParams = getGatewayVariables($gatewayModuleName);
$gatewayPath = 'https://my-data.se/client/modules/gateways/callback/';
$gatewayCompany = 'MyDataSe';

if (!$gatewayParams['type']) {
    die("Module Not Activated");
} 

error_reporting(E_ALL);
ini_set('display_errors', 1);


function startsWith2bm($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}


function sendBillMate2($vars)
{
   global $gatewayModuleName, $gatewayPath, $gatewayCompany;

error_reporting(E_ALL);
ini_set('display_errors', 1);










/** A Billmate class file is required that can be downloaded here: 
 * http://developer.billmate.se/Billmate.zip
 *
 * Note: hash, serverdata and time is automatically computed and added in class file 
 */

/* Server settings */

$test = true;
$ssl = true;
$debug = true;

/* Credentials for Auth */

$id = $vars["merchantid"];
$key = $vars["merchantkey"];
define("BILLMATE_SERVER", "2.1.6");	/* API version */
define("BILLMATE_CLIENT", "Pluginname:BillMateTrustbox:1.0");
define("BILLMATE_LANGUAGE", "sv");
$bm = new BillMate($id,$key,$ssl,$test,$debug);
$values = array();



$values["PaymentData"] = array(
	"currency" => "SEK",
	"language" => "sv",
	"country" => "se",
	"totalwithtax" => "50000"
);

$plans = $bm->getPaymentplans($values);


//print_r($plans);








/* Payment Data */
/**
 * @param array Payment Data : Buyer details.
 */

$values["PaymentData"] = array(
	"method" => "4",//Invoice Part Payment     //"1",// Invoice Factoring      "4",//Invoice Part Payment      //8=cart
        "paymentplanid" => "3", //1
	"currency" => "SEK",
	"language" => "sv",
        "country" => "SE",
	"autoactivate" => "1",
	"orderid" => $gatewayCompany.$vars['invoiceid'],
	"logo" => "Logo.jpg",
);

/**
 * @param array $details : Detailed information about the invoice.
 */

$values["PaymentInfo"] = array(
        "paymentdate" => date('Y-m-d'),
	"paymentterms" => "14",
	"yourreference" => "Purchaser ".$vars["firstname"]." ".$vars["lastname"],
	"ourreference" => "",
	"projectname" => $gatewayCompany,
        "delivery" => "Post",
        "deliveryterms" => "FOB",
        "autocredit" => "true",
);

/**
 * @param array card and bank data : Card and bank details.
 */

$values["Card"] = array(
	"promptname" => "",
	"3dsecure" => "1",
	"recurring" => "",
	"recurringnr" => "",
	"accepturl" => $gatewayPath.'billmatetrustboxfp3.php?ResponseType=accept',
	"cancelurl" => $gatewayPath.'billmatetrustboxfp3.php?ResponseType=cancel',
	"returnmethod" => "",
	"callbackurl" => $gatewayPath.'billmatetrustboxfp3.php?ResponseType=callback',
);

$values["Customer"] = array(
	"nr" => $gatewayCompany.$vars["clientid"],
	"pno" => !empty($vars["ssn"]) ? $vars["ssn"] : $vars["vat"],
	"Billing" => array(
		"firstname" => $vars["firstname"],
		"lastname" => $vars["lastname"],
		"company" => $vars["company"],
		"street" => $vars["street"],
		"street2" => $vars["street2"],
		"zip" => $vars["zip"],
		"city" => $vars["city"],
		"country" => "Sverige",
		"phone" => $vars["phone"],
		"email" => $vars["email"],
	),
	"Shipping" => array(
		"firstname" => $vars["firstname"],
		"lastname" => $vars["lastname"],
		"company" => $vars["company"],
		"street" => $vars["street"],
		"street2" => $vars["street2"],
		"zip" => $vars["zip"],
		"city" => $vars["city"],
		"country" => "Sverige",
		"phone" => $vars["phone"],
	)
);

/**
 * @param array articles : article details.
 */

$values["Articles"][0] = array(
        "artnr" => "TR",
        "title" => $gatewayCompany,
        "quantity" => "1",
        "aprice" => $vars['total'],
        "taxrate" =>  $vars['taxrate'],
        "discount" => "0",
        "withouttax" =>  $vars['totalnotax'],
);

/**
 * @param array Cart Data : Cart details.
 */

$values["Cart"] = array(
	"Total" => array(
			"withouttax" => $vars['totalnotax'],
			"tax" => ( intval($vars['total']) - intval($vars['totalnotax']) ),
			"withtax" => $vars['total']
		)
);


//print_r($values);

return $bm->addPayment($values);







   
   
}



?>