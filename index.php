<?php
date_default_timezone_set('America/Chicago');
$wskey=getenv('OCLC_DEV_KEY');
$logFile=date("Y-m-d").".log";
include_once 'functions.php';

function get_oclc_worldcat_record($isbn){
  global $wskey;
  
  if (!$isbn){
    log_message("isbn is blank");
    return -1;
  }
  if (!$wskey){
    log_message("cannot get system variable OCLC_DEV_KEY");
    die;
  }
  $url="http://www.worldcat.org/webservices/catalog/content/isbn/" . $isbn . "?wskey=" . $wskey;
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
  
}


create_log_file();

$inputDir=getcwd();
if (strstr($inputDir, "/")){ // change directory path if on linux
  $inputDir .="/input/";
}
else {
  $inputDir .="\\input\\";
}
$inputFiles=array();
log_message("using \"$inputDir\" for input directory");
$files = scandir($inputDir);
$i=0;
foreach ($files as $file){
  if (stristr($file, ".csv")){
    $i++;
    $inputFiles[$i] = $file;
  }
  else{
    continue;
  }
}
print_r($inputFiles);

//$line = fgets(STDIN);
//print "$line\n";

get_oclc_worldcat_record("978-0-02-391341-9");
log_message("Finished processing at " . date("Y-m-d H:i"));
?>