<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_USERPWD, "");
curl_setopt($ch, CURLOPT_URL, 'https://api.livechatinc.com/chats?date_from=2001-01-23&page=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
curl_setopt($ch, CURLOPT_TRANSFERTEXT, $curlFields);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json','X-API-Version: 2'));
$result = curl_exec($ch);
curl_close($ch);

$jsonDecoded = json_decode($result,true);
$pages = $jsonDecoded['pages'];

$merge = $jsonDecoded['chats'];


$fileName = 'archive.csv';
$output = fopen('php://output', 'w');
$header = array(
	'ID', 'NAME', 'EMAIL', 'IP', 'CITY', 'REGION', 'COUNTRY', 'COUNTRY CODE', 'TIMEZONE'
);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $fileName . '"');

for($i = 2; $i < $pages+1; $i++){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERPWD, "");
	curl_setopt($ch, CURLOPT_URL, 'https://api.livechatinc.com/chats?date_from=2001-01-23&page='.$i);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
	curl_setopt($ch, CURLOPT_TRANSFERTEXT, $curlFields);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json','X-API-Version: 2'));
	$result = curl_exec($ch);
	curl_close($ch);

	$jsonDecoded = json_decode($result,true);
	$merge = array_merge($merge, $jsonDecoded['chats']);
}


fputcsv($output, $header);
foreach ($merge as $row) {
	//print_r(array_keys(($row['visitor'])));
    fputcsv($output, array_values($row['visitor']));
}
fclose($output);
echo "Done";

function jsonToCsv ($json, $csvFilePath = false, $boolOutputFile = false) {
    
    // See if the string contains something
    if (empty($json)) { 
      die("The JSON string is empty!");
    }
    
    // If passed a string, turn it into an array
    if (is_array($json) === false) {
      $json = json_decode($json, true);
    }
    
    // If a path is included, open that file for handling. Otherwise, use a temp file (for echoing CSV string)
    if ($csvFilePath !== false) {
      $f = fopen($csvFilePath,'w+');
      if ($f === false) {
        die("Couldn't create the file to store the CSV, or the path is invalid. Make sure you're including the full path, INCLUDING the name of the output file (e.g. '../save/path/csvOutput.csv')");
      }
    }
    else {
      $boolEchoCsv = true;
      if ($boolOutputFile === true) {
        $boolEchoCsv = false;
      }
      $strTempFile = 'csvOutput' . date("U") . ".csv";
      $f = fopen($strTempFile,"w+");
    }
    
    $firstLineKeys = false;
    foreach ($json as $line) {
      if (empty($firstLineKeys)) {
        $firstLineKeys = array_keys($line);
        fputcsv($f, $firstLineKeys);
        $firstLineKeys = array_flip($firstLineKeys);
      }
      
      // Using array_merge is important to maintain the order of keys acording to the first element
      fputcsv($f, array_merge($firstLineKeys, $line));
    }
    fclose($f);
    
    // Take the file and put it to a string/file for output (if no save path was included in function arguments)
    if ($boolOutputFile === true) {
      if ($csvFilePath !== false) {
        $file = $csvFilePath;
      }
      else {
        $file = $strTempFile;
      }
      
      // Output the file to the browser (for open/save)
      if (file_exists($file)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Length: ' . filesize($file));
        readfile($file);
      }
    }
    elseif ($boolEchoCsv === true) {
      if (($handle = fopen($strTempFile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle)) !== FALSE) {
          echo implode(",",$data);
          echo "<br />";
        }
        fclose($handle);
      }
    }
    
    // Delete the temp file
    unlink($strTempFile);
}
?>