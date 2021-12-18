<?php

namespace Differentiator\Differ;

function genDiff($file1, $file2)
{
    if (file_exists("/home/igor/php-project-lvl2/{$file1}")) {
        $str1 = file_get_contents("/home/igor/php-project-lvl2/{$file1}");
    }
    if (file_exists("/home/igor/php-project-lvl2/{$file2}")) {
        $str2 = file_get_contents("/home/igor/php-project-lvl2/{$file2}");
    }

    $array1 = json_decode($str1, true);

    $array2 = json_decode($str2, true);

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

$array = genDiff('file1.json', 'file2.json');

var_dump(json_encode($array));
