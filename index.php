<?php

$key=getenv('OCLC_DEV_KEY');
$url="http://www.worldcat.org/webservices/catalog/content/isbn/978-0-02-391341-9?wskey=" . $key;

echo "<p>$url</p>";
$file = file_get_contents($url);
echo "<p>$file</p>";

?>