<?php

/**
 * @file
 * Read data from DS18x20 one wire digital thermometer.
 */

function getSensors() {
    $baseDirectory = '/sys/bus/w1/devices';
    $content = dir($baseDirectory);
    while (false !== ($entry = $content->read())) {
        if (strstr($entry, '10-')) {
            $sensors[] = $entry;
        }
    }
    return $sensors;
}


function getStreams(array $sensors) {
    $slaveFile = 'w1_slave';
    $baseDirectory = '/sys/bus/w1/devices';
    foreach($sensors as $sensor) {
        $streams[] = fopen($baseDirectory . '/' . $sensor . '/' . $slaveFile, 'r');
    }
    return $streams;
}

function readSensors(array $streams) {
    if (!$streams) {
        print "No streams, giving up. \n";
        return FALSE;
    }
    
    $fileName = 'temp.log';
    $logString = '';
    $temps = '';
    print (date('Y-m-d H:i:s') . "\n");
    foreach($streams as $stream) {
        $raw = '';
        $raw = stream_get_contents($stream, -1);
        $temp = strstr($raw, 't=');
        $temp = trim($temp, "t=");
        $temp = number_format($temp/1000, 3);
        print ($temp . "ºC\n");
        if ($temps == '') {
            $logString = date('Y-m-d H:i:s') . ', ';
            $logString .= $temp;
        }
        else {
            $logString .= ', ' . $temp . "\n";
        }
        $temps[] = $temp;
    }
    print ("Avarage: " . array_sum($temps)/2 . "ºC \n");
    $logFile = fopen($fileName, 'a');
    fwrite($logFile, $logString);
    fclose($logFile);
}

/**
 * Close stream.
 */
function closeStreams(array $streams) {
    if ($streams) {
        foreach($streams as $stream) {
            fclose($stream);
            print("Closing $stream. \n");
        }
    }
}

$end = FALSE;
$endTime = strtotime("+24 hour");
// $endTime = strtotime("+10 second");

while (!$end) {
    $sensors = getSensors();
    $streams = getStreams($sensors);
    readSensors($streams);
    if (time() > $endTime) {
        $end = TRUE;
    }
    sleep(1);
}

if ($end) {
    closeStreams($streams);
}

