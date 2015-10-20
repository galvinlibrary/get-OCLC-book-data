<?php

function get_record_info($record, $type){
  $localDebug=true;
  $recordArr=array(
    "title"=>"245", 
    "author"=>"245", 
    "edition"=>"250"
  );
  if (!array_key_exists($type,$recordArr))return "error";
  $marcField = $recordArr[$type];
  if ($localDebug) echo "<p>Looking for $type in $marcField</p>";
  foreach($record->datafield as $item){
    echo "<p>item tag = " . $item[@tag] . "</p>";
    if ($item[@tag]==$marcField){
      switch ($type){

        case "title":
          echo "<p>in the title section for $type</p>";
          for($i=0; $i<count($item->subfield); $i++){
            if ($item->subfield[$i][@code]=="a"){
              $eleStr=$item->subfield[$i];
              $regExMatch="/ \/$/";
              $regExRepl="";
              echo "<p>LEAVING title section for $type</p>";
              break;
            }
          }
        break;

        case "author":
          echo "<p>in the author section for $type</p>";
          for($i=0; $i<count($item->subfield); $i++){
            if ($item->subfield[$i][@code]=="c"){
              $eleStr=$item->subfield[$i];
              $regExMatch="/.$/";
              $regExRepl="";
              echo "<p>LEAVING author section for $type</p>";
              break;
            }  
          }
        break;

        default:
          return -1;

      }//end switch
      return preg_replace($regExMatch,$regExRepl,$eleStr);
    } // end if
    else{
      continue;
    }
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
      echo "<p>title = $title</p>";
      $author=get_record_info($dataObj, "author");
      echo "<p>author = $author</p>";
   }
}
 
?>