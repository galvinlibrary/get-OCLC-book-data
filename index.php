<?php
  date_default_timezone_set('America/Chicago');
  $wskey=getenv('OCLC_DEV_KEY');
  $logFile=date("Y-m-d").".log";
  $inputFile="";
  include_once 'functions.php';

  create_log_file();
  $filesArr=get_list_of_input_files();
  $msg=display_inputs_to_user($filesArr);
  echo "$msg\n";// show user options
  $fileNum = fgets(STDIN) + 0;//convert input to number
  if ( ($fileNum < 1)||($fileNum > count($filesArr)) ){
    echo "Invalid entry of $fileNum received for input";
    die;
  }
  else {
    $inputFile = $filesArr[$fileNum];
    echo "Using \"$inputFile\" for input.\n";
    log_message("Using \"$inputFile\" for input.");
  }

  //get_oclc_worldcat_record("978-0-02-391341-9");
  //log_message("Finished processing at " . date("Y-m-d H:i"));
?>