<?php
date_default_timezone_set('America/Chicago');
$logFile=date("Y-m-d").".log";
include_once 'functions.php';




$wskey=getenv('OCLC_DEV_KEY');
$url="http://www.worldcat.org/webservices/catalog/content/isbn/978-0-02-391341-9?wskey=" . $wskey;
//echo $url;

if (($response_xml_data = file_get_contents($url))===false){
    echo "Error fetching XML\n";
} 
else {
   libxml_use_internal_errors(true);
   $dataObj = simplexml_load_string($response_xml_data);
   if (!$dataObj) {
       echo "Error loading XML\n";
       foreach(libxml_get_errors() as $error) {
           echo "\t", $error->message;
       }
   } else {
      //print_r($dataObj);
      $title=get_record_info($dataObj, "title");
      $author=get_record_info($dataObj, "author");
      $edition=get_record_info($dataObj, "edition");
      echo "<p>TITLE = $title and AUTHOR = $author and EDITION = $edition</p>";
   }
}


?>