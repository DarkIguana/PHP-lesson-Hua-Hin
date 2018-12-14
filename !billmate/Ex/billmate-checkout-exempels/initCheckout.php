<?php

require_once('init.php');
require_once('config.php');
require_once('lib/Billmate.php');

$values = array();

/*****************
 * CheckoutData
 */

$values['CheckoutData'] = array();

/**
 * URL to the store terms, url will be linked in the Billmate Checkout window.
 * @var string
 */
$values['CheckoutData']['terms'] = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']).'/storeterms';

/**
 * URL to the store privacy terms, url will be linked in the Billmate Checkout window.
 * @var string
 */
$values['CheckoutData']['privacyPolicy'] = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']).'/privacepolicy';

/** 
 * Expected values true / false
 * If true the customer will always be redirected to the url defined in PaymentData.accepturl
 * If unset or if false, and customer pay with invoice and partpayment a javascript event will be sent to iframe parent and 
 * it is up to the store if customer should be redirected or not.
 * 
 * Recommended settings is true
 * @var string
 */
$values['CheckoutData']['redirectOnSuccess'] = 'true';

/*****************
 * PaymentData
 */

$values['PaymentData'] = array();
$values['PaymentData']['currency'] = 'SEK';
$values['PaymentData']['language'] = 'sv';
$values['PaymentData']['country'] = 'SE';

/**
 * A unique order id generated by the shop as a reference.
 * Is returned by Billmate API when requests are sent to PaymentData.accepturl, PaymentData.cancelurl and PaymentData.callbackurl
 * @var string
 */

$values['PaymentData']['orderid'] = '123456';

/** 
 * In this case we want customer to return to checkout-page to retry payment
 * @var string
 */
$values['PaymentData']['cancelurl']     = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']).'/index.php';;

/**
 * Redirect customer to this page when order is paid
 * Order status can be Paid or Pending
 * The store should do what it does when order is paid and then display a thank you page to customer
 * @var string
 */
$values['PaymentData']['accepturl']     = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']).'/accept.php';

/**
 * When order is paid, a request is sent to this url from Billmate
 * No human will se this page and do not display thank you page when making sure order is set as paid in store
 * Order status can be Paid or Pending
 * Requests can also be sent when order status is updated if order status first is Pending and then Paid
 * @var string
 */
$values['PaymentData']['callbackurl']   = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']).'/callback.php';

/**
 * Expected values GET / POST
 * If unset or value is POST return method will be sent by POST
 * If set values is GET return method will be GET
 * @var string
 */
$values['PaymentData']['returnmethod'] = 'GET';


/*****************
 * Articles
 */

$values["Articles"] = array();


/*****************
 * Article added to Articles
 */

$values['Articles'][] =  array(

    /**
     * Article number
     * @var string
     */
    "artnr" => "A123",

    /**
     * Article name
     * @var string
     */
    "title" => "Article 1",

    /**
     * Quantity of article
     * Example: 4
     * Example: 2.5
     * Note if not integer, use . as decimal separator
     * @var string
     */
    "quantity" => "2",  //  article quantity

    /**
     * Unit price without tax in 1/100 of currency (i.e. öre if currency is SEK, cent if currency is EUR)
     * @var int
     */
    "aprice" => 100,

    /**
     * Taxrate in percent
     * Example: 25
     * Note if not integer, use . as decimal separator
     * @var string
     */
    "taxrate" => "25",  //  

    /**
     * Article discount in percent
     * Example: 25
     * Note if not integer, use . as decimal separator
     * @var string
     */
    "discount" => "0",

    /**
     * Total row excluding tax in 1/100 of currency  
     * @var int
     */

    "withouttax" => 200
);

/*****************
 * Cart
 */

$values['Cart'] = array();

$values['Cart']['Handling'] = array();

/**
 * Handling charge in 1/100 of currency (i.e. öre if currency is SEK, cent if currency is EUR).
 * @var int
 */

$values['Cart']['Handling']['withouttax'] = 2320;

/**
 * Handling fee taxrate in %
 * @var int
 */
$values['Cart']['Handling']['taxrate'] = 25;

/**
 * Shipping charge in 1/100 of currency (i.e. öre if currency is SEK, cent if currency is EUR).
 * @var int
 */
$values['Cart']['Shipping']['withouttax'] = 1520;

/**
 * Shipping fee taxrate in %
 * @var int
 */
$values['Cart']['Shipping']['taxrate'] = 25;

/**
 * Cart totals for the order. This should be dynamiclly calculated.
 * @var int
 */
$values['Cart']['Total'] = array();

$values['Cart']['Total']['withouttax'] = 4040;
$values['Cart']['Total']['tax'] = 1010;
$values['Cart']['Total']['withtax'] = 5050;
$values['Cart']['Total']['rounding'] = 0;

$billmate = new Billmate($eid, $secret, $ssl, $test);
$result = $billmate->initCheckout($values);

if (!isset($result['code']) AND isset($result['number'])) {

    /**
     * Success initCheckout
     * In this example, store checkout url, hash and temporary invoice number in session
     * Checkout url will be used in iframe to display Billmate Checkout
     * Temporary invoice number will be used when update checkut from store
     */

    $_SESSION['checkoutUrl'] = $result['url'];
    $_SESSION['checkoutNumber'] = $result['number'];
    header('location: index.php');
    die();

} else {
    // Error
    echo '<pre>'.print_r(
        array(
            "__FILE__" => __FILE__,
            "__LINE__" => __LINE__,
            "result" => $result,
            "" => ""
    ), true).'</pre>';
}

