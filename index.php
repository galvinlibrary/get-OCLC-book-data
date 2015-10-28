<?php
  $debug=true;
  date_default_timezone_set('America/Chicago');
  $logFile=date("Y-m-d").".log";
  include_once 'functions.php';
  $invalidISBNs=0;
  $dupeISBNs=0;
  $isbnsToProcess=[];
  $counter=0;
  
  create_log_file();

  $inputFile=get_input_file_name_from_user();
  $msg="\nEnter desired output file name, or <return> to use \"textbook-output-file.csv\".\n\n";
  if (!trim($msg)){
    $outputFile = "";
  }
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
          $isbnsToProcess[$tempISBN].= "," . $lineArr[1];
        }
      }
      else{
        $isbnsToProcess[$tempISBN]=$lineArr[1];
      }
    }
  }
//  if($debug){print_r($isbnsToProcess);}
  log_message("There were " . $counter . " lines in the file. " . count($isbnsToProcess) . " will be sent to the OCLC API. " .  
          $invalidISBNs . " did not contain a valid ISBN, and " . $dupeISBNs . " were duplicates.");
  log_message("*** Finished processing ISBN file");
  
  $isbnKeysArr=array_keys($isbnsToProcess);
  foreach ($isbnKeysArr as $isbn){
    $book=new Book;
    $book->isbn=$isbn;
    $book->crns=preg_replace("/,$|\s$/", "", $isbnsToProcess[$isbn]);    
    get_oclc_worldcat_record($isbn);
    if ($debug){var_dump($book);}
  }
  
  
  //get_oclc_worldcat_record($isbn);
  //log_message("Finished processing at " . date("Y-m-d H:i"));
?>