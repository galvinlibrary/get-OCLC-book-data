<?php

$logFile=date("Y-m-d").".log";

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


function log_message ($msg){
  global $logFile;
  $fh = fopen($logFile, 'a') or die("can't open file");
  $msg .= "\n";
  fwrite($fh, $msg);
  fclose($fh);  
}

function loop_record_to_find_code($item, $check){
  for($i=0; $i<count($item->subfield); $i++){
    if ($item->subfield[$i][@code]==$check)
      return $item->subfield[$i];
    else
      continue;
  }
}

?>