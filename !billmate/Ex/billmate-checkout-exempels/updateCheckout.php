<?php

require_once('init.php');
require_once('config.php');
require_once('lib/Billmate.php');

/**
 * In this example simulate a simple cart
 * Pretend all store logic is done and end up in an API request
 */


$articleAmount = (isset($_POST['cart_item_amount_1'])) ? intval($_POST['cart_item_amount_1']) : 1;

$result = array();

$totalWithouttax    = 0;
$totalTax           = 0;

$values = array();
$values['Articles'] = array();
$values['Cart'] = array();
$values['Cart']['Total'] = array();
$values['PaymentData'] = array();

/**
 * When update checkout, send in values to update
 * In this case we want to update the articles and order total amount
 */

$articleAprice = 100;
$articleTaxrate = 25;

/** Article row total */
$articleWithouttax = $articleAprice * $articleAmount;
$articleTax = ($articleWithouttax * (1 - ($articleTaxrate / 100)));

$values['Articles'][] =  array(
    "artnr" => "A123",
    "title" => "Article 1",
    "quantity" => $articleAmount,
    "aprice" => $articleAprice,
    "taxrate" => $articleTaxrate,
    "discount" => "0",
    "withouttax" => $articleWithouttax
);

$totalWithouttax    += $articleWithouttax;
$totalTax           += $articleTax;

/**
 * Update order shipping
 */
$shipping = 1520;
$shippingTaxRate = 25;
$shippingTax = 1520 * (1 - ($shippingTaxRate / 100));

$values['Cart']['Shipping'] = array(
    'withouttax' => $shipping,
    'taxrate' => $shippingTaxRate
);

$totalWithouttax    += $shipping;
$totalTax           += $shippingTax;


/**
 * Total amount to pay
 */
$totalWithtax = $totalWithouttax + $totalTax;
$totalRounding = 0;

/*
 * In this example we want to round the price to pay to closest integer
 * Simulate that the store rounded the amount to pay
 * This rounding is on purpouse and if the code is in production mode the rounding is up to the store if total amount to pay should be rounded or not
 */

$totalWithtax = round(($totalWithtax/100)) * 100;
$totalRounding = $totalWithtax - $totalWithouttax - $totalTax;

$values['Cart']['Total']                = array();
$values['Cart']['Total']['withouttax']  = $totalWithouttax;
$values['Cart']['Total']['tax']         = $totalTax;
$values['Cart']['Total']['withtax']     = $totalWithtax;
$values['Cart']['Total']['rounding']    = $totalRounding;

/**
 * Define the temporary invoicenumber so Billmate API know what checkout order to update
 */
$values['PaymentData']['number'] = $_SESSION['checkoutNumber'];

$billmate = new Billmate($eid, $secret, $ssl, $test);
$result = $billmate->updateCheckout($values);

if (!isset($result['code']) AND isset($result['number'])) {
    /** Successful update, echo something that make sense as a response to the AJAX client */
    echo 'OK';
} else {
    /** Failed update, this should not return when in production */

    echo "<pre>";
    print_r(array(
        "__FILE__" => __FILE__,
        "__LINE__" => __LINE__,
        "articleAmount" => $articleAmount,
        "_GET" => $_GET,
        "_POST" => $_POST,
        "values" => $values,
        "result" => $result,
        "" => ""
    ));
    echo "</pre>";
}
