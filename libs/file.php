<?php

/**
 * @description: read urls from file and put into an array
 * @param $filename
 * @param string $mode
 * @return string
 */
function readFromFile($filename, $mode = "r") {
    $fp = @fopen($filename, $mode) or die('Cannot open input file ' . $filename);
    return fread($fp, filesize($filename));
}

/**
 * @description: Write data to file
 * @param $filename
 * @param $mode
 * @param $data
 */
function saveToFile($filename, $data, $mode = 'w+') {
    $fp = fopen($filename, $mode) or die('Cannot open file ' . $filename);
    fwrite($fp, $data);
    fclose($fp);
}