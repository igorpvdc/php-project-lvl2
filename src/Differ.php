<?php

namespace Differentiator\Differ;

function genDiff($file1, $file2)
{
    $pathToFile1 = pathToFile($file1);
    $pathToFile2 = pathToFile($file2);

    $jsonData1 = json_decode($pathToFile1, true);
    $jsonData2 = json_decode($pathToFile2, true);

    $diffBetweenFiles = [];

    foreach ($jsonData1 as $key => $value) {
        if (array_key_exists($key, $jsonData2)) {
            if ($value === $jsonData2[$key]) {
                $diffBetweenFiles[$key] = $value;
            } else {
                $diffBetweenFiles["- {$key}"] = $value;
                $diffBetweenFiles["+ {$key}"] = $jsonData2[$key];
            }
        } else {
            $diffBetweenFiles["- {$key}"] = $value;
        }
    }

    foreach ($jsonData2 as $key => $value) {
        if (!array_key_exists($key, $diffBetweenFiles)) {
            $diffBetweenFiles["+ {$key}"] = $value;
        }
    }

    return json_encode($diffBetweenFiles);
}


function pathToFile($fileName): string
{
    $path1 = __DIR__ . "/{$fileName}";
    $path2 = "../{$fileName}";

    if (file_exists($path1)) {
        return file_get_contents($path1);
    }

    return file_get_contents($path2);
}