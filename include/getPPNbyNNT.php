<?php
libxml_use_internal_errors(true);    
require_once("utils.php");
 
 $q = getParam("nnt");
 if((isset($q) && !empty($q))){   

   $url = "http://www.theses.fr/".urlencode($q);
   $these = getResponse($url, $proxy_server, $proxy_port);
   $thesePage = new DOMDocument();
   $thesePage->loadHTML( $these);
foreach($thesePage->getElementsByTagName('a') as $div){
    if($div->getAttribute('class') == "overlay_sudoc"){
    	
    	$str = $div->getAttribute('href');
    	
    	if(str_starts_with($str, 'http://www.sudoc.fr/')) {
			$ppn = substr($str, strlen('http://www.sudoc.fr/'));		
		}
		else {
			$prefix = 'http://www.sudoc.abes.fr/DB=2.1/SRCH?IKT=12&TRM=';
	    	if (substr($str, 0, strlen($prefix)) == $prefix) {
				 $ppn = substr($str, strlen($prefix));
			} 
		}
    	
    }
 }


$ppn = explode(" OR ", $ppn);
$json['results']['nnt'] = $q;
$json['results']['ppn'] = $ppn;

return_json($json);
 


}
else {
	die;
}

function return_json($obj) {
	    if (isset($_REQUEST['callback'])) {
	       header('Content-Type: text/javascript; charset=utf8');
		   header('Access-Control-Allow-Methods: GET, POST');
	        echo $_REQUEST['callback'] . '(' . json_encode($obj) . ')';
	        exit();
	    } else {
	        header('Content-type: application/json; charset=utf-8');
	        echo json_encode($obj);
	        exit();
	    }
	}
function str_starts_with($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
}