<?php

function create_log_file(){
  global $logFile;
  if (file_exists($logFile)){
    unlink($logFile);
  }
  log_message("Starting process at " . date("Y-m-d H:i"));
}

function log_message ($msg){
  global $logFile;
  $fh = fopen($logFile, 'a') or die("can't open file");
  $msg .= "\r\n";
  fwrite($fh, $msg);
  fclose($fh);  
}

function get_input_directory_path(){
  $inputDir=getcwd();
  if (strstr($inputDir, "/")){ // change directory path if on linux
    $inputDir .="/input/";
  }
  else {
    $inputDir .="\\input\\";
  }
  return $inputDir;
}

function get_list_of_input_files(){
  $inputDir=get_input_directory_path();
  $inputFiles=array();
  log_message("Using \"$inputDir\" to get CSV files for input");
  $files = scandir($inputDir);
  $i=0;
  foreach ($files as $file){
    if (!ereg("/.csv$/",$file)){
      continue;
    }
    else{
      $i++;
      $inputFiles[$i] = $file;
    }
  }
  if ($i==0){
    echo "No CSV files found in input directory";
    die;
  }
  return($inputFiles);
}

function display_inputs_to_user($filesArr){
  $msg="\nInput file should be in the following CSV format: ISBN, CRN.\nPlease enter the number corresponding to the file you want to process\n\n";
  for ($i=1; $i<=count($filesArr); $i++){
    $msg .= "\t$i\t$filesArr[$i]\n\n";
  }
  return $msg;
}

function get_ibsns_from_file($file){
  global $debug;
  $inputFileTxt=file_get_contents($file);  
  if (!$inputFileTxt){
    echo "Could not read contents of " . $inputFile;
    die;
  }
  if ($debug){echo "$inputFileTxt\n\n";}
  $inputDataArr=split(PHP_EOL,$inputFileTxt);
  return $inputDataArr;
}


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


function get_record_info($record, $type){
  $localDebug=false;
  $regExMatch="";
  $recordArr=array(
    "title"=>"245", 
    "author"=>"245", 
    "edition"=>"250"
  );
  if (!array_key_exists($type,$recordArr))return "error";
  
  $marcField = $recordArr[$type];
  
  if ($localDebug) echo "<p>Looking for $type in $marcField</p>";
  
  foreach($record->datafield as $item){
    if ($item[@tag]==$marcField){
      
      switch ($type){

        case "title":
          $eleStr=loop_record_to_find_code($item, "a");
          if ($eleStr){
              $regExMatch="/ \/$/";
              $regExRepl="";
              break;
          }

        break;

        case "author":
          $eleStr=loop_record_to_find_code($item, "c");
            if ($eleStr){
              $regExMatch="/.$/";
              $regExRepl="";
              break;
            }  
        break;

        case "edition":
          $eleStr=loop_record_to_find_code($item, "a");
            if ($eleStr){
              break;
            }          
        break;
        
        default:
          return -1;

      }//end switch
      if ($regExMatch)
        return preg_replace($regExMatch,$regExRepl,$eleStr);
      else
        return $eleStr;
    } // end if
    else{
      continue;
    }
  }//end foreach      

}// end get_record_info function



function loop_record_to_find_code($item, $check){
  for($i=0; $i<count($item->subfield); $i++){
    if ($item->subfield[$i][@code]==$check)
      return $item->subfield[$i];
    else
      continue;
  }
}

?>