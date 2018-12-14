<?php

function billmatetrustbox_config() {
    # ini_set("memory_limit","60M");
    $configarray = array(
//        "FriendlyName" => array("Type" => "System", "Value" => "QuickpayTrustbox"),
        "FriendlyName" => array("Type" => "System", "Value" => "Bill Mate"),
        "username" => array("FriendlyName" => "Bill Mate Merchant ID", "Type" => "text", "Size" => "10"),
        "userkey" => array("FriendlyName" => "Bill Mate Merchant Key", "Type" => "text", "Size" => "10"),
        "testmode" => array("FriendlyName" => "Test Mode", "Type" => "yesno", "Description" => "Tick this to test", ),
    );
    return $configarray;
}


function billmatetrustbox_link($params) { 
 
    $gatewayusername = $params['username'];
    $gatewayuserkey = $params['userkey'];
    $gatewaytestmode = $params['testmode'];

    $testmodefield = '';
    $hid = 'TYPE="hidden"';

    $myUrlFP = 'https://my-data.se/client/modules/gateways/callback/billmatetrustboxfp.php';

    # Invoice Variables
    $invoiceId = $params['invoiceid'];
    $currency = $params['currency']; # Currency Code
    $country = $params['clientdetails']['country'];

    $paytxt = "Pay Now";
    if ($country == "DK") {
        $sprog = 'da';
        $paytxt = "Betal nu";
    } elseif ($country == "SE") {
        $sprog = 'sv';
        $paytxt = "Betala";
    } else {
        $sprog = 'en';
        $paytxt = "Pay Now";
    }

    // lang set up  
 
    $lang=array('danish'=>'da','english'=>'en','spanish'=>'es','finnish'=>'fi','faroese'=>'fo',
        'french'=>'fr','italian'=>'it','dutch'=>'nl','Norwegian'=>'no','polish'=>'pl','swedish'=>'sv','greenlandic'=>'kl');

    $langfield='';

    if(isset($params['clientdetails']['language']) && !empty($params['clientdetails']['language']))
    {
        $params['clientdetails']['language'] = strtolower($params['clientdetails']['language']);
        $langvariable=$lang[$params['clientdetails']['language']];
        $langfield='<INPUT TYPE="hidden" NAME="lang" VALUE="' . $langvariable . '">';
    }
    
    $user_id = $params['clientdetails']['userid'];


    $query="SELECT count(*) as col 
FROM `tblinvoiceitems` ii, tblhosting h, tblproducts p, tblproductgroups pg
where ii.invoiceid=".$invoiceId."
and ii.relid=h.id
and h.packageid=p.id
and p.gid=pg.id
and pg.name='SlIP ID SHOP'";
    $result_aman=mysql_query($query) or die(mysql_error()); 
    $row_aman=mysql_fetch_assoc($result_aman);
    $isSlip=$row_aman['col'];
    $isSlipText = $isSlip > 0 ? ' SLIP_PRODUCT ' : ''; 
    $isNeedCaptureNow = '<input '.$hid.' name="capturenow" value="yes" />';



    $query="SELECT total, notes FROM tblinvoices WHERE id=".$invoiceId;
    $result_aman=mysql_query($query) or die(mysql_error()); 
    $row_aman=mysql_fetch_assoc($result_aman);
    $totalnotax=$row_aman['subtotal'];
    $taxrate=$row_aman['taxrate'];
    $total=$row_aman['total'];
    $notes=$row_aman['notes'];

    if(empty($total) || $total <= 0)
    {
        logTransaction($params['name'], $params, "Zero Amount!!! Auto ".$type." Amount ". $total ." invoice ". $old_orderid . '-' . $invoiceId ." ticketId ". $old_ticketid . " client ". $user_id);
        $code = 'Internal error. Amount is zero. Please ask support.';
        return $code; # Code submit to the DIBS D2 FlexWin gateway
    }
    

       logTransaction($params['name'], $params, "Start OneTime payment orderId ". $invoiceId . $isSlipText);

//print_r($params['clientdetails']);

       $code = '<FORM name="frmSecondPay" id="frmSecondPay" ACTION="'.$myUrlFP.'" METHOD="POST" CHARSET="UTF-8">
                   <INPUT '.$hid.' NAME="newsletter" ID="newsletter">
                   <INPUT '.$hid.' NAME="ssn" ID="ssn">
                   <INPUT '.$hid.' NAME="totalnotax" VALUE="'.sprintf("%.0f", $totalnotax*100).'">'. $langfield .' 
                   <INPUT '.$hid.' NAME="taxrate" VALUE="'.sprintf("%.0f", $taxrate*100).'">'. $langfield .' 
                   <INPUT '.$hid.' NAME="total" VALUE="'.sprintf("%.0f", $total*100).'">'. $langfield .' 
                   <INPUT '.$hid.' NAME="currency" VALUE="' . $currency . '">
                   <INPUT '.$hid.' NAME="merchantid" VALUE="'. $gatewayusername .'">
                   <INPUT '.$hid.' NAME="merchantkey" VALUE="'. $gatewayuserkey .'">
                   <INPUT '.$hid.' NAME="invoiceid" VALUE="' . $invoiceId . '">
                   <INPUT '.$hid.' NAME="firstname" VALUE="' . $params['clientdetails']['firstname'] . '">
                   <INPUT '.$hid.' NAME="lastname" VALUE="' . $params['clientdetails']['lastname'] . '">
                   <INPUT '.$hid.' NAME="company" VALUE="' . $params['clientdetails']['companyname'] . '">
                   <INPUT '.$hid.' NAME="street" VALUE="' . $params['clientdetails']['address1'] . '">
                   <INPUT '.$hid.' NAME="street2" VALUE="' . $params['clientdetails']['address2'] . '">
                   <INPUT '.$hid.' NAME="zip" VALUE="' . $params['clientdetails']['postcode'] . '">
                   <INPUT '.$hid.' NAME="city" VALUE="' . $params['clientdetails']['city'] . '">
                   <INPUT '.$hid.' NAME="phone" VALUE="' . $params['clientdetails']['phonenumber'] . '">
                   <INPUT '.$hid.' NAME="email" VALUE="' . $params['clientdetails']['email'] . '">
                   <INPUT '.$hid.' NAME="vat" VALUE="' . $params['clientdetails']['customfields1'] . '">
                   <INPUT '.$hid.' NAME="clientid" VALUE="' . $params['clientdetails']['userid'] . '">
                   <INPUT '.$hid.' NAME="user_id" VALUE="' . $params['clientdetails']['userid'] . '">
                   '. $testmodefield .'
                   <INPUT type="button" class="btn btn-success" data-toggle="modal" data-target="#acceptQuoteModal" VALUE="' . $paytxt . '">
                 </FORM>';

    return $code; # Code submit to the DIBS D2 FlexWin gateway
}

?>