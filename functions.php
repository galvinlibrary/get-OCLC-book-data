<?php

class Textbook { 
  public $isbn='';  
  public $title=''; 
  public $author=''; 
  public $edition='';
  public $crns='';
} 

function create_log_file(){
  global $logFile;
  if (file_exists($logFile)){
    unlink($logFile);
  }
  log_message("Started process at " . date("Y-m-d H:i"));
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
  $files = scandir($inputDir);
  $i=0;
  foreach ($files as $file){
    if (preg_match("/.csv$/i",$file)==1){
      $i++;
      $inputFiles[$i] = $file;
    }
    else{
      continue;
    }
  }
  if ($i==0){
    echo "No CSV files found in input directory";
    log_message("No input files found in directory:  \"$inputDir\"");
    die;
  }
  echo "\nSearching for input files in: $inputDir\n";
  return($inputFiles);
}

function display_inputs_to_user($filesArr){
  $msg="\nInput file should be in the following CSV format: ISBN, CRN.\nPlease enter the number corresponding to the file you want to process\n\n";
  for ($i=1; $i<=count($filesArr); $i++){
    $msg .= "\t$i\t$filesArr[$i]\n\n";
  }
  return $msg;
}

  function get_input_file_name_from_user(){
    $dir = get_input_directory_path();
    $filesArr=get_list_of_input_files();
    $msg=display_inputs_to_user($filesArr);
    echo "$msg\n";// show user options
    $fileNum = fgets(STDIN) + 0;//convert input to number
    if ( ($fileNum < 1)||($fileNum > count($filesArr)) ){
      echo "Invalid entry of $fileNum received for input";
      die;
    }
    else {
      $inputFile = $dir . $filesArr[$fileNum];
      echo "Using \"$inputFile\" for input.\n";
      log_message("Using \"$inputFile\" for input.");
    }
    return $inputFile;
  }
  
function get_ibsns_from_file($file){
//  global $debug;
  $inputFileTxt=file_get_contents($file);  
  if (!$inputFileTxt){
    echo "Could not read contents of " . $file;
    die;
  }
//  if ($debug){echo "$inputFileTxt\n\n";}
  $inputDataArr=split(PHP_EOL,$inputFileTxt);
  return $inputDataArr;
}

 function check_isbn($isbn){
     $strippedISBN=preg_replace("/-|_|\s/","",$isbn);
    if  (strlen($strippedISBN) === 10) {
      $exp = "/\b(^\d{10}$|^\d{9}x)$\b/i"; // ISBN-10 can be 10 digits, or 9 digits + x (checksum of 10)
    }
    else if (strlen($strippedISBN) === 13){
      $exp = "/^978\d{10}$/"; //ISBN 13 digits start with 978
    }
    if (preg_match($exp, $strippedISBN)===1){
       return $strippedISBN;
    }
    else{
      log_message("\"$strippedISBN\" is not a valid ISBN\n");
      return -1;
    }     
  }
  
function fetch_data($url){
  if (($response_xml_data = file_get_contents($url))===false){
      return -1;
  } 
  else {
    if (process_data($response_xml_data)==-1){
      return -1;
    }
  }  
}

function process_data($response_xml_data){
  libxml_use_internal_errors(true);
  $dataObj = simplexml_load_string($response_xml_data);
  if (!$dataObj) {
      foreach(libxml_get_errors() as $error) {
          echo "\t", $error->message;
      }
      return -1;
  } else {
     //print_r($dataObj);
     $title=get_record_info($dataObj, "title");
     $author=get_record_info($dataObj, "author");
     $edition=get_record_info($dataObj, "edition");
     echo "\nTITLE = $title and AUTHOR = $author and EDITION = $edition\n";
  }  
}  
  
function get_oclc_worldcat_record($isbn, $crns){
  $wskey=getenv('OCLC_DEV_KEY');
  
  if (!$isbn){
    echo "isbn is blank: \"$isbn\", \"$crns\"";
    return -1;
  }
  if (!$wskey){
    log_message("cannot get system variable OCLC_DEV_KEY");
    die;
  }
  $url="http://www.worldcat.org/webservices/catalog/content/isbn/" . $isbn . "?wskey=" . $wskey;
  if (fetch_data($url)==-1){
    log_message("Error response for isbn $isbn");
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