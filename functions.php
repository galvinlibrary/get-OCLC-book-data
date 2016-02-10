<?php

class Book { 
  public $isbn='';  
  public $title=''; 
  public $author=''; 
  public $edition='';
  public $summary='';
  public $subjects='';
  public $crns='';
} 

function create_log_file(){
  global $logFile;
//  if (file_exists($logFile)){
//    unlink($logFile);
//  }
  log_message("Started process at " . date("Y-m-d H:i"));
}

function create_output_file($filename){
  if (file_exists($filename)){
    unlink($filename);
  }
  $fh = fopen($filename, 'a') or die("can't open file");
  $msg .= "\"isbn\",\"crn\",\"title\",\"author\",\"edition\"\r\n";
  fwrite($fh, $msg);
  fclose($fh);
}

function log_message ($msg){
  global $logFile;
  $fh = fopen($logFile, 'a') or die("can't open file");
  $msg .= "\r\n";
  fwrite($fh, $msg);
  fclose($fh);  
}

function get_path_type(){
  $tmpDir = getcwd();
  if (strstr($tmpDir, "/")){ // change directory path if on linux
     return  "/";
   } 
  else{
    return "\\";
  } 
}

function set_path(){
  $dir=getcwd();
  $pathType=get_path_type();
  $dir .= $pathType;
  if ($path){
    $dir .= $path . $pathType;
  }
  return $dir;
}


function get_list_of_input_files($processType){
  $inputDir=set_path();
  $inputFiles=array();
  $files = scandir($inputDir);
  $i=0;
  foreach ($files as $file){
    if (preg_match("/.csv$/i",$file)==1){
      $i++;
      $inputFiles[$i] = $file;
    }
    else if ($processType==="leisure"){
      if (preg_match("/.txt$/i",$file)==1){
        $i++;
        $inputFiles[$i] = $file;        
      }
    }
    else {
      continue;
    }     
  }
  
  if ($i==0){
    if ($process=="textbooks"){
    echo "No CSV files found in input directory to process textbooks";
    log_message("No CSV input files found in directory to process textbooks:  \"$inputDir\"");
    }
    else{
    echo "No CSV or TXT files found in input directory";
    log_message("No input files found in directory to process leisure books:  \"$inputDir\"");
    }
    die;
  }
  echo "\n\n\n\nSearching for input files in: $inputDir\n";
  return($inputFiles);
  
}

function display_inputs_to_user($filesArr){
  $msg="\n\nINPUT file should be in the following CSV format: ISBN, CRN.\nPlease enter the number corresponding to the file you want to process\n\n";
  for ($i=1; $i<=count($filesArr); $i++){
    $msg .= "\t$i\t$filesArr[$i]\n\n";
  }
  return $msg;
}

  function get_process_type_from_user(){
    $msg="\n\nWhat type of file do you want to process?\n\n\t1 = textbooks \n\t2 = leisure books\n";
    echo $msg;
    $type = fgets(STDIN);
    $comp=strtolower(rtrim($type));
    if (strcmp($comp,"1")===0){
      return "textbooks";
    }
    else if (strcmp($comp,"2")===0){
      return "leisure";
    }
    else {
      echo "Invalid entry: $type";
      die;
    }
//      if (stristr($type,"b")==FALSE){
//        echo "Invalid entry: $type";
//        die;
//      }
//      else{
//        return "leisure";
//      }
//    }
//    else {
//
//    }
  }

  function get_input_file_name_from_user($processType){
    $dir = set_path();
    $filesArr=get_list_of_input_files($processType);
    $msg=display_inputs_to_user($filesArr);
    echo "$msg\n";// show user options
    $fileNum = fgets(STDIN) + 0;//convert input to number
    if ( ($fileNum < 1)||($fileNum > count($filesArr)) ){
      echo "Invalid entry of $fileNum received for input";
      die;
    }
    else {
      $inputFile = $dir . $filesArr[$fileNum];
      echo "\nUsing \"$inputFile\" for input.\n";
      log_message("\r\n-----------------\r\nUsing \"$inputFile\" for input.");
    }
    return $inputFile;
  }

  function get_output_file_name_from_user($processType){
    $tmpOutputFile=$processType . "-processed-". date("Y-m-d.") . "csv";
    $msg="\nEnter desired OUTPUT file name, or <return> to use\n\"$tmpOutputFile\".\n";
    echo "\n$msg\n";// show user options
    $outputFile = trim(fgets(STDIN));  
    if (!$outputFile){
      $outputFile = $tmpOutputFile;
    }
    echo "Writing data to $outputFile\n";
    return $outputFile;
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
     $strippedISBN=rtrim(ltrim(preg_replace("/-|_|\s|\"|\'|\`/","",$isbn))); // strip special characters and line feed items
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
  global $book;// declared outside to capture ISBN and CRNs
  libxml_use_internal_errors(true);
  $dataObj = simplexml_load_string($response_xml_data);
  if (!$dataObj) {
      foreach(libxml_get_errors() as $error) {
          echo "\t", $error->message;
      }
      return -1;
  } else {
    $book->title=substr(get_record_info($dataObj, "title"), 0, 250);//Take substring in case of very long fields. Match to field limits in Drupal
    $book->author=substr(get_record_info($dataObj, "author"),0,90); // example: 9780071795531
    $book->edition=substr(get_record_info($dataObj, "edition"),0,50);  
    $book->summary=get_record_info($dataObj,"summary");
  }  
}  
  
function get_oclc_worldcat_record($isbn){
  $debug=false;
  $wskey=getenv('OCLC_DEV_KEY');
  
  if (!$isbn){
    echo "isbn is blank: \"$isbn";
    log_message("isbn is blank");
    return -1;
  }
  if (!$wskey){
    log_message("cannot get system variable OCLC_DEV_KEY");
    die;
  }
  $url="http://www.worldcat.org/webservices/catalog/content/isbn/" . $isbn . "?wskey=" . $wskey;
  if($debug){
    echo "\n$url\n";
  }
  if (fetch_data($url)==-1){
    log_message("Error response for isbn $isbn");
    return -1;
  }
  
}


function get_record_info($record, $type){
  $localDebug=true;
  $regExMatch="";
  $recordArr=array(
    "title"=>"245", 
    "author"=>"245", 
    "edition"=>"250", 
    "summary"=>"520",
    "subject"=>"650"
  );
  if (!array_key_exists($type,$recordArr))return "error";
  
  $marcField = $recordArr[$type];
  
  if ($localDebug) echo "Looking for $type in $marcField\n";
  
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

        case "summary":
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

  function write_output_line($outputFile, $bookObj, $processType){
    if ($processType==="textbooks"){
      $line= "\"$bookObj->isbn\",\"$bookObj->crns\",\"$bookObj->title\",\"$bookObj->author\",\"$bookObj->edition\"\r\n";
    }
    else{
      $line= "\"$bookObj->isbn\",\"$bookObj->title\",\"$bookObj->author\",\"$bookObj->summary\"\r\n";
    }
    $fh = fopen($outputFile, 'a') or die("can't open file");
    fwrite($fh, $line);
    fclose($fh);    
  }

?>