<?php

/**
 * @file
 * Read data from DS18x20 one wire digital thermometer.
 */

function getSensors() {
    echo exec('sudo modprobe w1-gpio');
    echo exec('sudo modprobe w1-therm');
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
        return FALSE;
    }
    
    $average = FALSE;
    $logString = '';
    $temps = '';
    print date('Y-m-d H:i:s');
    foreach($streams as $key => $stream) {
        $raw = '';
        $raw = stream_get_contents($stream, -1);
        $temp = strstr($raw, 't=');
        $temp = trim($temp, "t=");
        $temp = number_format($temp/1000, 3);
        print (' - Sensor' . $key . ' ' . $temp . 'ºC');

        if ($temps) {
            $logString = date('Y-m-d H:i:s') . ', ';
            $logString .= $temp;
        }
        else {
            $logString .= ', ' . $temp;
        }
        
        $temps[] = $temp;
    }

    $logString .= "\r\n";
    print "\n";

    return $logString;
}

function writeLogFile($logString) {
    $fileName = 'temp.log'; 
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
$endTime = strtotime("+2 week");

while (!$end) {
    $sensors = getSensors();
    $streams = getStreams($sensors);
    $logString = readSensors($streams);
    if ($logString) {
        writeLogFile($logString);
        if (time() > $endTime) {
            $end = TRUE;
        }
        sleep(60);
    }
    else {
        print "No streams, giving up. \n";
    }
}

if ($end) {
    closeStreams($streams);
}

