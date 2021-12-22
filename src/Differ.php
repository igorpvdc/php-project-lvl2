<?php

namespace Differentiator\Differ;

use function Functional\sort;

function genDiff($file1, $file2)
{
    $jsonData1 = json_decode(pathToFile($file1), true);
    $jsonData2 = json_decode(pathToFile($file2), true);
    $diffBetweenFiles = [];

    foreach ($jsonData1 as $key => $value) {
        if (array_key_exists($key, $jsonData2)) {
            if ($value === $jsonData2[$key]) {
                $diffBetweenFiles["  {$key}"] = $value;
            } else {
                $diffBetweenFiles["- {$key}"] = $value;
                $diffBetweenFiles["+ {$key}"] = $jsonData2[$key];
            }
        } else {
            $diffBetweenFiles["- {$key}"] = $value;
        }
    }

    foreach ($jsonData2 as $key => $value) {
        if (!array_key_exists("  {$key}", $diffBetweenFiles)) {
            $diffBetweenFiles["+ {$key}"] = $value;
        }
    }

    sort($collection, fn ($left, $right) => strcmp($left, $right));

    return json_encode($diffBetweenFiles);
}


function pathToFile($fileName): string
{
    $path1 = __DIR__ . "/{$fileName}";
    $path2 = "../{$fileName}";
    $path3 = "./{$fileName}";

    if (file_exists($path1)) {
        return file_get_contents($path1);
    } elseif (file_exists($path2)) {
        return file_get_contents($path2);
    }
    return file_get_contents($path3);
}

$result = genDiff('file1.json', 'file2.json');

var_dump($result);

