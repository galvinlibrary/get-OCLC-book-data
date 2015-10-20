<?php

function get_record_info($record, $type){
  $localDebug=true;
  $recordArr=array(
    "title"=>"245", 
    "author"=>"245", 
    "edition"=>"250"
  );

  foreach($record->datafield as $item){ 
    $element = array_search($item[@tag],$recordArr);
    if($localDebug) echo "<p>tag= " . $item[@tag] . " ele: $element</p>";
    if ($element){
      switch ($element){
        
        default: // for title and author, which both use field 245
          for($i=0; $i<count($item->subfield); $i++){
             if($localDebug) echo "<p>i=$i value=" . $item->subfield[$i] . " tag=" . $item->subfield[$i][@code] . "</p>";
//            if ($subF[@code]=="a"){
//              if($localDebug) echo "<p>TITLE subf code=" . $subF[@code] . " subF= $subF</p>";
//              $regExMatch="/ \/$/";
//              $regExRepl="";
//              $eleStr=$subF;
//            }
          }
        

          
      }//end switch
      return preg_replace($regExMatch,$regExRepl,$eleStr);

    }//end if
  }//end foreach      
      
}// end get_record_info function

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
      //print_r($dataObj);
      $title=get_record_info($dataObj, "title");
      $author=get_record_info($dataObj, "author");
      echo "<p>title = $title author = $author</p>";
   }
}
 
?>