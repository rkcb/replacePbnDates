<?php
/*
 * call like this php changedate.php [d 2017.07.15] [p <path>] [f /.*pbn/i]
 * if any option is missing then it is replaced by a default:
 * - date is current date
 * - path is current path
 * - filter is all pbn files
 */

function isValidDate($date_str, $format = 'Y.m.d') {
    $d = DateTime::createFromFormat($format, $date_str);
    return $d && $d->format($format) == $date_str;
}


/**
 * @param $file_name
 * @param string $new_date
 * @return int number of replaced dates
 */
function replaceDates($file_name, $new_date = '1111.22.33') {
    $lines = file($file_name);
    $lines = $lines ? $lines : [];
    $pattern = "/\[(Date|EventDate)\s+\"(\d{4}\.\d{2}\.\d{2})\"\]/i";
    $dates_replaced = 0;

    foreach ($lines as $index => $line) {
        if (preg_match_all($pattern, $line, $matches)) {
            $old_date = trim($matches[2][0]);
            $lines[$index] = str_replace($old_date, $new_date, $line);
            $dates_replaced++;
        }
    }
    $contents = implode($lines);
    file_put_contents($file_name, $contents);
    return $dates_replaced;
}

$options = [];

// check that options have values
if (count($argv) % 2 == 0)
    die("not every option has a value\n");

// store option values
if (count($argv) > 1) {
    for ($i = 1; $i < count($argv); $i = $i + 2) {
        if (!is_integer(strpos("dfp", $argv[$i] ))){
            echo $argv[$i];
            die(" is invalid; only d,f and p allowed\n");
        }
        $options[$argv[$i]] = $argv[$i+1];
    }
}

// default values
$path = key_exists('p', $options) ? $options['p'] : '.';
$filter = key_exists('f', $options) ? $options['f'] : '/.*.pbn$/i';
$new_date = key_exists('d', $options) ? $options['d'] : date('Y.m.d');


if (file_exists($path)){
    $dir = scandir($path);
} else {
    die("path does not exist\n");
}


if(!isValidDate($new_date)){
    die("invalid date parameter\n");
}



$replaced_files = 0;

foreach ($dir as $file_name) {
    if (preg_match($filter, $file_name)){
        $replaced_files += replaceDates($file_name, $new_date) > 0;
    }
}

echo "path was " . $path . "\n";
echo "filter was " . $filter . "\n";

echo "replaced " . $replaced_files . " files\n";
if ($replaced_files){
    echo "using the new date " . $new_date . "\n";
}

