<?php
error_reporting(0);
set_time_limit(0);
include 'simple_html_dom.php';

// Testing purpose
function ll($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

/**
 * @description: read urls from file and put into an array
 * @param $filename
 * @param string $delimiter
 * @return array
 */
function convertDataFromFileToArray($filename, $delimiter = "\n") {
    $fp = @fopen($filename, 'r') or die('Cannot open input file ' . $filename);
    $data = fread($fp, filesize($filename));
    return explode($delimiter, $data);
}

/**
 * @description: Write data to file
 * @param $filename
 * @param $mode
 * @param $data
 */
function saveData($filename, $mode, $data) {
    $fp = fopen($filename, $mode) or die('Cannot open file ' . $filename);
    fwrite($fp, $data);
    fclose($fp);
}

/**
 * @description: Handle select data from Dom Node list
 * @param $html
 * @return stdClass
 */
function collectData($html) {
    $rootPath = "body div[id=container] div.company-content div.company-page div.headers ";
    $logo = $html->find($rootPath.'div.logo-container div.logo img')[0]->src;
    $name = $html->find($rootPath.'div.name-and-info h1')[0]->innertext;
    $address = $html->find($rootPath.'div.name-and-info span')[0]->plaintext;
    $type = $html->find($rootPath.'div.name-and-info div.company-info span.gear-icon')[0]->innertext;
    $size = $html->find($rootPath.'div.name-and-info div.company-info span.group-icon')[0]->innertext;
    $country = $html->find($rootPath.'div.name-and-info div.company-info div.country span.name')[0]->innertext;
    $flag = $html->find($rootPath.'div.name-and-info div.company-info div.country i')[0]->class;

    $data = new stdClass();
    $data->logo = explode('?', strtolower(trim($logo)))[0];
    $data->name = trim($name);
    $data->address = trim($address);
    $data->type = trim($type);
    $data->size = trim($size);
    $data->country = trim($country);
    $data->flag = trim($flag);

    return $data;
}

$input = 'urls';
$output = 'result.json';
$dataSource = convertDataFromFileToArray($input);
$length = count($dataSource);
$result = new stdClass();
for ($i = 0; $i < $length; $i++) {
    $html = file_get_html($dataSource[$i]);
    $result->$i = collectData($html);
}
saveData($output, 'w', json_encode($result));