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

//error_reporting(E_ALL);
//ini_set('display_errors', 1);


function startsWith2bm($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}


function sendBillMate2($vars)
{
   global $gatewayModuleName, $gatewayPath, $gatewayCompany, $gatewayParams;

//error_reporting(E_ALL);
//ini_set('display_errors', 1);


/** A Billmate class file is required that can be downloaded here: 
 * http://developer.billmate.se/Billmate.zip
 *
 * Note: hash, serverdata and time is automatically computed and added in class file 
 */

/* Server settings */

$test = false;//true;
$ssl = true;
$debug = false;//true;

/* Credentials for Auth */


$id = $gatewayParams['username'];
$key = $gatewayParams['userkey'];
define("BILLMATE_SERVER", "2.1.6");	/* API version */
define("BILLMATE_CLIENT", "Pluginname:BillMateTrustbox:1.0");
define("BILLMATE_LANGUAGE", "sv");

$bm = new BillMate($id, $key, $ssl, $test, $debug);
$values = array();




/* Payment Data */
/**
 * @param array Payment Data : Buyer details.
 */


$values['CheckoutData']['terms'] = 'http://'.$_SERVER['SERVER_NAME'].'/storeterms';
$values['CheckoutData']['privacyPolicy'] = 'http://'.$_SERVER['SERVER_NAME'].'/privacepolicy';
$values['CheckoutData']['redirectOnSuccess'] = 'true';


$values["PaymentData"] = array(
	"currency" => "SEK",
	"language" => "sv",
        "country" => "SE",
	"orderid" => $gatewayCompany.$vars['invoiceid'],
        "cancelurl" => 'https://'.$_SERVER['SERVER_NAME'].'/client/modules/gateways/callback/billmatetrustboxfp3.php?ResponseType=Cancel',
        "accepturl" => 'https://'.$_SERVER['SERVER_NAME'].'/client/modules/gateways/callback/billmatetrustboxfp3.php?ResponseType=Accept',
        "callbackurl" => 'https://'.$_SERVER['SERVER_NAME'].'/client/modules/gateways/callback/billmatetrustboxfp3.php?ResponseType=Callback',
        "returnmethod" => 'GET',
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
			"withtax" => $vars['total'],
			"rounding" => 0,
		)
);


//print_r($values);

$result = $bm->initCheckout($values);

if (!isset($result['code']) AND isset($result['number'])) 
{
    /**
     * Success initCheckout
     * In this example, store checkout url, hash and temporary invoice number in session
     * Checkout url will be used in iframe to display Billmate Checkout
     * Temporary invoice number will be used when update checkut from store
     */

    //$_SESSION['checkoutUrl'] = $result['url'];
    //$_SESSION['checkoutNumber'] = $result['number'];
    //header('location: index.php');
    //die();

    echo '<br/><br/><a href="' . $result['url'] . '"> Register Invoice ID=' . $result['number'] . '</a>';

    return $result['url'];

} 
else 
{
    // Error
    echo '<pre>'.print_r(
        array(
            "__FILE__" => __FILE__,
            "__LINE__" => __LINE__,
            "result" => $result,
            "" => ""
    ), true).'</pre>';
}


 
   
}

function sendBillMateActivatePayment($invoiceId)
{
   global $gatewayModuleName, $gatewayPath, $gatewayCompany, $gatewayParams;

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

/** A Billmate class file is required that can be downloaded here: 
 * http://developer.billmate.se/Billmate.zip
 *
 * Note: hash, serverdata and time is automatically computed and added in class file 
 */

/* Server settings */

$test = false;//true;
$ssl = true;
$debug = false;//true;

/* Credentials for Auth */

$id = $gatewayParams['username'];
$key = $gatewayParams['userkey'];

define("BILLMATE_SERVER", "2.1.6");	/* API version */
define("BILLMATE_CLIENT", "Pluginname:BillMateTrustbox:1.0");
define("BILLMATE_LANGUAGE", "sv");

$bm = new BillMate($id, $key, $ssl, $test, $debug);
$values3 = array();

/* Payment Data */
/**
 * @param array Payment Data : Buyer details.
 */



    $values3["PaymentData"] = array( "number" => $invoiceId );
    $result3 = $bm->activatePayment($values3);


if (!isset($result3['code']) AND isset($result3['status'])) 
{
    return $result3['status'];
} 
else 
{
    // Error
    echo '<pre>'.print_r(
        array(
            "__FILE__" => __FILE__,
            "__LINE__" => __LINE__,
            "result" => $result3,
            "" => ""
    ), true).'</pre>';
}


    return $result3; 
}
?>
