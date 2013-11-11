<?php
print "<pre>";

if($_SERVER['SERVER_PORT'] == '443') {
    $httpHostName = "https://".preg_replace("/\/++/","/", $_SERVER['HTTP_HOST']."/"); 
}
elseif($_SERVER['SERVER_PORT'] == '80') {
    $httpHostName = "http://".preg_replace("/\/++/","/", $_SERVER['HTTP_HOST']."/"); 
}
else {
    $httpHostName = "http://".preg_replace("/\/++/","/", $_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT']."/"); 
}    

//var_dump($httpHostName);
//var_dump(dirname($_SERVER['REQUEST_URI']));
//die();

//-------------------------------------------------------------------
//-------------------------------------------------------------------
//-------
//-------      Configs comes here
//-------
//-------------------------------------------------------------------
//-------------------------------------------------------------------
$baseUrl = 'https://staging.payu.co.za';
$soapWdslUrl = $baseUrl.'/service/PayUAPI?wsdl';
$payuRppUrl = $baseUrl.'/rpp.do?PayUReference=';
$apiVersion = 'ONE_ZERO';

/*
Using staging integartion store 1 details
Store ID: 100284
Webservice username : Staging Integration Store 1
Webservice password : 78cXrW1W
Safekey: {45D5C765-16D2-45A4-8C41-8D6F84042F8C} 
 * 
 * Webservice username : Staging Enterprise Integration Store 1
Webservice password : j3w8swi5
Safekey: {E7A333D4-CC48-4463-BEC6-A4BC1F16DC30}
*/

$safeKey = '{CF86C6D5-016C-4E98-9E4F-0F4FE3A0C1BA}';
$soapUsername = 'Staging Enterprise With Fraud Integration Store 1';
$soapPassword = 'xoV3PFor';
$merchantReference = "merchant_ref_".time();;


//$soapUsername = 'Staging Integration Store 3';
//$soapPassword = 'WSAUFbw6';


//Webservice username: Staging Integration Store 3
//Webservice password: WSAUFbw6
//Safekey: {07F70723-1B96-4B97-B891-7BF708594EEA}

$doTransactionArray = array();
$doTransactionArray['Api'] = $apiVersion;
$doTransactionArray['Safekey'] = $safeKey;
$doTransactionArray['TransactionType'] = 'PAYMENT';		

$doTransactionArray['AdditionalInformation']['merchantReference'] = $merchantReference;
//$doTransactionArray['AdditionalInformation']['demoMode'] = 'true'	;
//$doTransactionArray['AdditionalInformation']['notificationUrl'] = 'http://www.style36.co.za/checkout/success/getNoticedFromPayU/';
$doTransactionArray['AdditionalInformation']['notificationUrl'] = $httpHostName.dirname($_SERVER['REQUEST_URI'])."/payu-enterprise-log-ipn.php";



$doTransactionArray['AdditionalInformation']['known'] = 'Y';
$doTransactionArray['AdditionalInformation']['postCode'] = '8000';
	
	
$doTransactionArray['Basket']['description'] = "Product Description";
$doTransactionArray['Basket']['amountInCents'] = "10000";
$doTransactionArray['Basket']['currencyCode'] = 'ZAR';
$doTransactionArray['Basket']['shippingDetails']['shippingAddress1'] = '1 Test street';
$doTransactionArray['Basket']['shippingDetails']['shippingAddressCity'] = 'Cape Town';
$doTransactionArray['Basket']['shippingDetails']['shippingCountryCode'] = 'ZAR';
$doTransactionArray['Basket']['shippingDetails']['shippingFax'] = null;
$doTransactionArray['Basket']['shippingDetails']['shippingMethod'] = '0';
$doTransactionArray['Basket']['shippingDetails']['shippingPostCode'] = '8000';

$doTransactionArray['Fraud']['checkFraudOverride'] = false; 
$doTransactionArray['Fraud']['merchantWebSite'] = 'http://name_of_merchant_website/'; 
$doTransactionArray['Fraud']['pcFingerPrint'] = null; 
$doTransactionArray['Fraud']['budgetPeriod'] = '12'; 

$doTransactionArray['Customer']['merchantUserId'] = "7";
$doTransactionArray['Customer']['email'] = "john@doe.com";
$doTransactionArray['Customer']['firstName'] = 'John';
$doTransactionArray['Customer']['lastName'] = 'Doe';
$doTransactionArray['Customer']['mobile'] = '27828888888';
$doTransactionArray['Customer']['regionalId'] = '1234512345122';
$doTransactionArray['Customer']['countryCode'] = '27';
$doTransactionArray['Customer']['ip'] = '196.28.151.4';

$doTransactionArray['Creditcard']['nameOnCard'] = "Mr John Doe";
$doTransactionArray['Creditcard']['cardNumber'] = "5470443148312467";
$doTransactionArray['Creditcard']['cardExpiry'] = "042014";
$doTransactionArray['Creditcard']['cvv'] = "123";  
$doTransactionArray['Creditcard']['amountInCents'] = $doTransactionArray['Basket']['amountInCents']; 


try {
    // 1. Building the Soap array  of data to send - This will make it into falsethe xml as described in the documentation
    $soapDataArray = array();    
    $soapDataArray = array_merge($soapDataArray, $doTransactionArray );
    
    
    
    // 2. Creating a XML header for sending in the soap heaeder (creating it raw a.k.a xml mode)
    $headerXml = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
    $headerXml .= '<wsse:UsernameToken wsu:Id="UsernameToken-9" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
    $headerXml .= '<wsse:Username>'.$soapUsername.'</wsse:Username>';
    $headerXml .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$soapPassword.'</wsse:Password>';
    $headerXml .= '</wsse:UsernameToken>';
    $headerXml .= '</wsse:Security>';
    $headerbody = new SoapVar($headerXml, XSD_ANYXML, null, null, null);

    // 3. Create Soap Header.        
    $ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'; //Namespace of the WS. 
    $header = new SOAPHeader($ns, 'Security', $headerbody, true);        

    // 4. Make new instance of the PHP Soap client
    $soap_client = new SoapClient($soapWdslUrl, array("trace" => 1, "exception" => 0)); 

    // 5. Set the Headers of soap client. 
    $soap_client->__setSoapHeaders($header); 

    // 6. Do the setTransaction soap call to PayU
    $soapCallResult = $soap_client->doTransaction($soapDataArray); 

    // 7. Decode the Soap Call Result
    $returnData = json_decode(json_encode($soapCallResult),true);
    
    /*
    $decodedXmlData = json_decode(json_encode((array) simplexml_load_string($returnData)),true);    
    
    print "<pre>";
    var_dump($decodedXmlData);
    print "</pre>";       
     */

}
catch(Exception $e) {
    var_dump($e);
}

//-------------------------------------------------------------------
//-------------------------------------------------------------------
//-------
//-------      Checking response
//-------
//-------------------------------------------------------------------
//-------------------------------------------------------------------
if(is_object($soap_client)) {    
    //print "Request Header:"; 
    //echo str_replace( '&gt;&lt;' , '&gt;<br />&lt;', htmlspecialchars( $httpClient->getLastRequestHeaders(), ENT_QUOTES)); 
    print "-----------------------------------------------\r\n";
    print "Request:\r\n";
    print "-----------------------------------------------\r\n";            
    $requestString = str_replace( '&gt' , '>', $soap_client->__getLastRequest() );    
    $requestString = str_replace( '&gt' , '>', $requestString );    
    $requestString = str_replace( '>' , ">\r\n", $requestString );
    $requestString = str_replace( '</' , "\r\n</", $requestString );
    $requestString = str_replace( "\r\n\r\n" , "\r\n", $requestString );
    $requestString = str_replace( "\r\n\r\n" , "\r\n", $requestString );
    print $requestString;            
    print "\r\n";
    print "-----------------------------------------------\r\n";
    print "Response:\r\n";
    print "-----------------------------------------------\r\n";
    $responseString = str_replace( '&gt' , '>', $soap_client->__getLastResponse() );    
    $responseString = str_replace( '&gt' , '>', $responseString );    
    $responseString = str_replace( '>' , ">\r\n", $responseString );
    $responseString = str_replace( '</' , "\r\n</", $responseString );
    $responseString = str_replace( "\r\n\r\n" , "\r\n", $responseString );    
    print $responseString;            
} 