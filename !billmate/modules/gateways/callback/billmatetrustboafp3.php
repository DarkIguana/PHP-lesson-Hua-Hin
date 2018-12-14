<?php

// Require libraries needed for gateway module functions.
require_once 'billmatetrustboai.php';


$id = $pid = $transactionId = null;

$invoiceId0 = $invoiceId = $invoiceid = $_POST['orderid'];


logTransaction($gatewayParams['name'], $_REQUEST, "[1] Call back result for invoice: " . $invoiceId);



$result2 = "invoice: " . $invoiceId . ' ' . $_REQUEST['ResponseType'] . ' ' . $_REQUEST['status'] . ' ' . $_REQUEST['number'] . ' ' . $_REQUEST['code'] . ' ' . $_REQUEST['message'] . ' ' . $_REQUEST['url'];

logTransaction($gatewayParams['name'], $_REQUEST, "[2] Call back result: " . $result2);






$approvalcodeOK    = ($_POST['status'] == 'Paid');
$transactionStatus = $approvalcodeOK ? 'Success' : 'Failure';
$rezervResult = $approvalcodeOK ? 'payed' : 'badpayed';
$result       = 'Result: ' . $result2;

mysql_query('update tblinvoices set invoicenum="' . $invoiceId . '-' . $invoiceId . '", notes="' . $rezervResult . '  ' . $result . '" where id=' . $invoiceId);


$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);


if ($approvalcodeOK) 
{
    //echo '!!a7z';
    
    
    $paymentAmount = 0;
    
    $query = "SELECT total FROM tblinvoices WHERE id='$invoiceId'";
    $result_aman = mysql_query($query) or die(mysql_error());
    if (mysql_num_rows($result_aman) > 0) //first payment
    {
        $row_aman = mysql_fetch_assoc($result_aman);
        $paymentAmount = $row_aman['total'];
    }
    
    
    
    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment($invoiceId, $pid, $paymentAmount, 0, $gatewayModuleName);
    
    $paymentSuccess = true;
    
    $query = "SELECT id FROM tblorders WHERE invoiceid='$invoiceId'";
    $result_aman = mysql_query($query) or die(mysql_error());
    if (mysql_num_rows($result_aman) > 0) //first payment
    {
        $query = "update tblhosting set 
               regdate=CURDATE(), 
               nextduedate     = IF( billingcycle='Annually', DATE_ADD(CURDATE(), INTERVAL 1 YEAR), DATE_ADD(CURDATE(), INTERVAL 1 MONTH) ),
               nextinvoicedate = IF( billingcycle='Annually', DATE_ADD(CURDATE(), INTERVAL 1 YEAR), DATE_ADD(CURDATE(), INTERVAL 1 MONTH) )
               where tblhosting.billingcycle in ('Annually','Monthly') and tblhosting.orderid in (select tblorders.id from tblorders WHERE tblorders.invoiceid='$invoiceId')";
        $result_aman = mysql_query($query) or die(mysql_error());
        
        logTransaction($gatewayParams['name'], $_GET, "Fixed dates for services (hosting) records " . $result_aman . " for invoce " . $invoiceId0 . " (" . $invoiceId . ")");
    }
    
    
    logTransaction($gatewayParams['name'], $_GET, "Finished payment orderId " . $invoiceId0 . " (" . $invoiceId . ") result " . $transactionStatus);
    
    
    
    $query = "SELECT c.notes 
FROM `tblaffiliatesaccounts` aa, `tblhosting` h, tblorders o, tblaffiliates a, tblclients c
where aa.affiliateid=a.id
and aa.relid=h.id
and h.orderid=o.id
and o.invoiceid='$invoiceId'
and a.clientid=c.id";
    $result_aman = mysql_query($query) or die(mysql_error());
    $row_aman = mysql_fetch_assoc($result_aman);
    $cl_notes = $row_aman['notes'];
    if (startsWith2xbb2($cl_notes, 'callback:')) {
        $cl_notes = substr($cl_notes, 9);
        $cl_notes = str_replace('{invoiceId}', $invoiceId0, $cl_notes);
        $cl_notes = str_replace('{amount}', $paymentAmount, $cl_notes);
        logTransaction($gatewayParams['name'], $_GET, "Go Callback: " . $cl_notes /*. " " . $query*/ );
        $res = file_get_contents($cl_notes);
    }
    
    echo '!!!OK!!!';
} 
else 
{
    echo '!!!Error!!! ' . $_REQUEST['ResponseType'] . ' ' . $_REQUEST['code'] . ' ' . $_REQUEST['message'];
}



?>