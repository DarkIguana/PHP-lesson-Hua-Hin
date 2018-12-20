<?php

// Require libraries needed for gateway module functions.
require_once 'billmatetrustboxi.php';


$id = $pid = $transactionId = null;

/*
	function verify_hash($response) {
		$response_array = is_array($response)?$response:json_decode($response,true);
		//If it is not decodable, the actual response will be returnt.
		if(!$response_array && !is_array($response))
			return $response;
		if(is_array($response)) {
			$response_array['credentials'] = json_decode($response['credentials'], true);
			$response_array['data'] = json_decode($response['data'],true);
		}
		//If it is a valid response without any errors, it will be verified with the hash.
		if(isset($response_array["credentials"])){
			//$hash = $this->hash(json_encode($response_array["data"]));
			//If hash matches, the data will be returnt as array.
			//if($response_array["credentials"]["hash"]==$hash)
				return $response_array["data"];
			//else return array("code"=>9511,"message"=>"Verification error","hash"=>$hash,"hash_received"=>$response_array["credentials"]["hash"]);
		}
		return array_map("utf8_decode",$response_array);
	}
*/

//echo ' GET: ';
//print_r($_GET);
//echo ' end. ';


//echo ' POST: ';
//print_r($_POST);
//echo ' end. ';


$data = $_GET['data'];

//echo ' GET[data]: ';
//print_r($data);
//echo ' end. ';

/*

function strToHex($string)
{
    $hex = '';

    for ($i = 0; $i < strlen($string); $i++)
    {
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }

    return strToUpper($hex);
}


$dataHex = strToHex($data);

echo ' GET[data] hex: ';
print_r($dataHex);
echo ' end. ';



$dataHex2 = bin2hex($data);

echo ' GET[data] hex2: ';
print_r($dataHex2);
echo ' end. ';

*/

$data = str_replace('&quot;', '"', $data);


$data = json_decode($data,true);


//echo ' GET[data]2: ';
//print_r($data);
//echo ' end. ';

/*



$data = '{"number":"512104","status":"Created","orderid":"MyDataSe619","url":"https://api.billmate.se/invoice/18039/20181218b4b468df71203c616bf1d6a30ab0a533"}';

echo ' GET[data]3: ';
print_r($data);
echo ' end. ';

$data = json_decode($data,true);

echo ' GET[data]4: ';
print_r($data);
echo ' end. ';

*/



//$data = verify_hash($_GET);

$invoiceId0 = $invoiceId = $data['orderid'];

if( startsWith2bm($invoiceId, $gatewayCompany) )
{
    $invoiceId = substr($invoiceId, strlen($gatewayCompany) );
}


logTransaction($gatewayParams['name'], $_REQUEST, "[1] Call back result for invoice: " . $invoiceId);


$newStatus = $data['status'];

$result2 = "invoice: " . $invoiceId . ' ResponseType: ' . $_REQUEST['ResponseType'] . ' status: ' . $data['status'] . ' billmateInvoiceNumber: ' . $data['number'] . ' code: ' . $data['code'] . ' message: ' . $data['message'] . ' url: ' . $data['url'];

logTransaction($gatewayParams['name'], $_REQUEST, "[2] Call back result: " . $result2);

if ($data['status'] == "Created" && $_REQUEST['ResponseType'] == "Accept")
{
    $billMateInvoiceId = $data["number"];

    mysql_query('update tblinvoices set notes="BillMate Invoice Created ' . $result2 . '" where id=' . $invoiceId);

    $newStatus = sendBillMateActivatePayment($billMateInvoiceId);

    $result2 = "invoice: " . $invoiceId . ' ResponseType: ' . $_REQUEST['ResponseType'] . ' status: ' . $newStatus . ' billmateInvoiceNumber: ' . $data['number'] . ' code: ' . $data['code'] . ' message: ' . $data['message'] . ' url: ' . $data['url'];
    mysql_query('update tblinvoices set notes="BillMate Invoice Created ' . $result2 . '" where id=' . $invoiceId);

    if($newStatus != 'Partpayment')
       exit;
}




$approvalcodeOK    = ($newStatus == 'Partpayment');
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
    echo '!!!Error!!! ' . $result2;
}



?>
