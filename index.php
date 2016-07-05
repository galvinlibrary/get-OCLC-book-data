<?php
  $debug=true;
  date_default_timezone_set('America/Chicago');
  $logFile=date("YMd").".log";
  include_once 'functions.php';
  $invalidISBNs=0;
  $dupeISBNs=0;
  $isbnsToProcess=array();
  $ISBNcrns=array();
  $ISBNsemesters=array();
  $counter=0;
  
  $processType=get_process_type_from_user(); // leisure or textbooks
  $inputFile=get_input_file_name_from_user($processType);
  $outputFileName=get_output_file_name_from_user($processType);
  create_log_file();
  create_output_file($outputFileName, $processType);

  $dataArr=get_ibsns_from_file($inputFile);
//  if ($debug){print_r($dataArr);}
  

  foreach ($dataArr as $item){
    $counter++;
    $lineArr=split(",",$item);
    $tempISBN=check_isbn($lineArr[0]);
    if ($tempISBN<=0){
       $invalidISBNs++;
    }
    else{
      if (array_key_exists($tempISBN,$isbnsToProcess)==true){
        $dupeISBNs++;
        if ($lineArr[1]){
          $ISBNcrns[$tempISBN].= "," . $lineArr[1];
        }
        if (($lineArr[2])&&(stristr($ISBNsemesters[$tempISBN],$lineArr[2])===FALSE)){
          $ISBNsemesters[$tempISBN].= "," . strtolower($lineArr[2]);
        }      
        
      }
      else{
        $isbnsToProcess[$tempISBN]=1;
        $ISBNcrns[$tempISBN]=$lineArr[1];
        if (($lineArr[2])&&(stristr($ISBNsemesters[$tempISBN],$lineArr[2])===FALSE)){
          $ISBNsemesters[$tempISBN]=strtolower($lineArr[2]);
        }   
      }
    }
  }
  if($debug){
    print_r($isbnsToProcess);
    print_r($ISBNcrns);
    print_r($ISBNsemesters);
  }
  log_message("There were " . $counter . " lines in the file. " . count($isbnsToProcess) . " will be sent to the OCLC API. " .  
          $invalidISBNs . " did not contain a valid ISBN, and " . $dupeISBNs . " were duplicates.");
  log_message("*** Finished processing ISBN file");
  echo("\n\nInput file processed. Getting data from OCLC\n");

  $isbnKeysArr=array_keys($isbnsToProcess);
  foreach ($isbnKeysArr as $isbn){
    $book=new Book;
    $book->isbn=$isbn;
    $book->crns=preg_replace("/,$|\s$/", "", $ISBNcrns[$isbn]);    
    $book->semesters=preg_replace("/,$|\s$/", "", $ISBNsemesters[$isbn]);    
    $rc=get_oclc_worldcat_record($isbn);
    if (($rc != -1)&&($book->title)){
      log_message("$book->isbn was processed successfully");
      write_output_line($outputFileName, $book, $processType);
    }
    else{
      log_message("Error getting data for $book->isbn");
      continue;
    }
    if ($debug){var_dump($book);}
  }
  
  if ($processType=="leisure"){
    finish_and_check_JSON_file($outputFileName);
  }
  
  log_message("Finished processing at " . date("Y-m-d H:i") . "\r\n-----------------\r\n");
  echo "\n******\nFinished processing. See log file for details\n\n";  
?>