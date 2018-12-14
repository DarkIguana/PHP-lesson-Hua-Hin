<?php

require_once('init.php');
require_once('config.php');
require_once('lib/Billmate.php');

 /**
  * Customer will be redirected to this page when success payment
  * In this example we have set PaymentData.returnmethod to be GET and expect to find values in $_GET['data'] in JSON
  */

$data = (isset($_GET['data'])) ? json_decode($_GET['data'], true) : array();


/**
 * The created invoice number, save this number to the store order for getPaymentinfo, cancelPayment, activatePayment and creditPayment
 */

$number = isset($data['number']) ? $data['number'] : '';

/**
 * Expected status: Created, Paid, Pending
 * Please note that if the order status is Pending the order is under control and will change stauts to Created or Cancelled,
 * If status is Pending to not treat the order as paid and log in to online.billmate.se to check the current order status
 * If status is Pending, a callback can be sent to PaymentData.callbackurl when status is changed
 */
$status = isset($data['status']) ? $data['status'] : '';

/**
 * Url to generated invoice, ignore this value
 */
$url = isset($data['url']) ? $data['url'] : '';


/**
 * Store orderid so the store know what order is paid
 */
$orderid = isset($data['orderid']) ? $data['orderid'] : '';


if ($number != '' AND in_array($status, array('Created', 'Paid', 'Pending'))) {

    /**
     * Order is done, do what the store does with created orders
     * Check the order to verify order status, paid amount and the payment method
     * The reason to check the payment method is if handling fee is set in Cart.Handling in initCheckout request and handling
     * need to be added to store order after payment to make sure the amount customer paid match the store order and order in online.billmate.se


    * För att se om en order är betald, hur mycket som är betald och hur en order är betald så gör ett getPaymentinfo anrop
    * http://developer.billmate.se/api-integration/getpaymentinfo/

    * Expected values in return that will be useful for the verification and get payment method is: 
    * PaymentData.method is the id of payment method, for example 1 if invoice and 8 if card, for more information
    * http://developer.billmate.se/api-integration/addpayment/
    * 
    * PaymentData.method_name is name of payment method
    * PaymentData.status is order status, expected status: Created, Paid, Pending
    * Cart.Total.withtax is amount customer paid
     */

    $billmate = new Billmate($eid, $secret, $ssl, $test);
    $result = $billmate->getPaymentinfo(array('number' => $number));   // Credited, card, live

    echo '<pre>';
    print_r(array(
        'result' => $result,
        '' => ''
    ));
    echo '</pre>';

    if ($status == 'Created' OR $status == 'Paid') {
        /** Order is paid, set order as paid */
    }

    if ($status == 'Pending') {
        /** Order is created and pending, make sure store administrator know that the order is created but pending */
    }

    /** Make sure to clear cart and Billmate Checkut, in this example it is stored in session */
    unset($_SESSION['checkoutUrl']);
    unset($_SESSION['checkoutNumber']);


    /** Maby redirect customer to the store thank you page */

}

/** Log when developing */
error_log(print_r(array(
    "_GET" => $_GET,
    "_POST" => $_POST,
    "data" => $data,
), true));
