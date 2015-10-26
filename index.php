<?php
  $debug=true;
  date_default_timezone_set('America/Chicago');
  $wskey=getenv('OCLC_DEV_KEY');
  $logFile=date("Y-m-d").".log";
  include_once 'functions.php';
  
  create_log_file();

  $inputFile=get_input_file_name_from_user();
  
  $dataArr=get_ibsns_from_file($inputFile);
  if ($debug){print_r($dataArr);}


  //get_oclc_worldcat_record("978-0-02-391341-9");
  //log_message("Finished processing at " . date("Y-m-d H:i"));
?>