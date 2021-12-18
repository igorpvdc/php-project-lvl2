<?php

namespace Differentiator\Differ;

$file1 = '{
  "host": "hexlet.io",
  "timeout": 50,
  "proxy": "123.234.53.22",
  "follow": false
}';

$file2 = '{
  "timeout": 20,
  "verbose": true,
  "host": "hexlet.io"
}';

$file3 = '/home/igor/php-project-lvl2/src/file1.json';

function jsonDecode($file)
{
    return json_decode($file, true);
}

$array = jsonDecode($file3);

var_dump($array);

function gendiff($file1, $file2)
{
    $array1 = json_decode($file1, true);

    $array2 = json_decode($file2, true);

    $newArray = [];

    foreach ($array1 as $key => $value) {
        if (array_key_exists($key, $array2)) {
            if ($value === $array2[$key]) {
                $newArray[$key] = $value;
            } else {
                $newArray["-{$key}"] = $value;
                $newArray["+{$key}"] = $array2[$key];
            }
        } else {
            $newArray["-{$key}"] = $value;
        }
    }

    foreach ($array2 as $key => $value) {
        if (!array_key_exists($key, $newArray)) {
            $newArray["+{$key}"] = $value;
        }
    }

    return $newArray;
}

$array = gendiff($file1, $file2);

var_dump(json_encode($array));
