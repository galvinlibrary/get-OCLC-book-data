<?php
date_default_timezone_set('America/Chicago');
$wskey=getenv('OCLC_DEV_KEY');
$logFile=date("Y-m-d").".log";
include_once 'functions.php';

create_log_file();
$filesArr=get_list_of_input_files();
$msg=display_inputs_to_user($filesArr);
echo "$msg\n";
get_oclc_worldcat_record("978-0-02-391341-9");
log_message("Finished processing at " . date("Y-m-d H:i"));
?>