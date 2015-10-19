<?php

function get_record_info($record, $type){
  switch ($type){
    case title:
      foreach($record->datafield as $item){
        if ($item[@tag]==245){
          foreach ($item->subfield as $subField){
            if ($subField[@code]=="a"){
              $pattern='/\s\/$/'; 
              $string=$subField;
              $replacement="";
              break;
            }
          }
        }
      }      
      
  }
  return preg_replace($pattern, $replacement, $string);
}

$wskey=getenv('OCLC_DEV_KEY');
$url="http://www.worldcat.org/webservices/catalog/content/isbn/978-0-02-391341-9?wskey=" . $wskey;

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
      print_r($dataObj);
      $title=get_record_info($dataObj, "title");
      echo "<p>$title</p>";
   }
}
 
?>