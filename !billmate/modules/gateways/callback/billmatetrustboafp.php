<?php
//echo '!1';

// Require libraries needed for gateway module functions.
require_once 'billmatetrustboai.php';

//print_r($_POST);


$result = sendBillMate2($_POST);

echo '!!!';
print_r($result);
echo '!!!';

?>