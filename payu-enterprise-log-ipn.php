<?php
$postdata = file_get_contents("php://input"); 

var_dump($postdata);
$stringToLog .= "";
$stringToLog .= "\r\n---------------------------------------------\r\n";
$stringToLog .= "\r\nDate Recieved: ".date("Y-m-d. H:i:s")."\r\n";
$stringToLog .= "\r\nData: \r\n".$postdata;
file_put_contents ( 'payu-enterprise-log-ipn.log' , $stringToLog, FILE_APPEND | LOCK_EX );