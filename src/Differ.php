<?php

namespace Differentiator\Differ;

use function Functional\sort;

function genDiff($file1, $file2)
{

    $jsonData1 = json_decode(getPathToFile($file1), true);
    $jsonData2 = json_decode(getPathToFile($file2), true);
    $diffBetweenFiles = [];

    $uniqueKeys = array_keys(array_merge($jsonData1, $jsonData2));

    $sortedKeys = sort($uniqueKeys, fn ($left, $right) => strcmp($left, $right), true);

    $keysInfo = array_map(function ($key) use ($jsonData1, $jsonData2) {
        if (array_key_exists($key, $jsonData1) && !array_key_exists($key, $jsonData2)) {
            $value = isBoll($jsonData1[$key]);
            return [
                'key' => $key,
                'status' => 'deleted',
                'value' => $value,
            ];
        }

        if (array_key_exists($key, $jsonData2) && !array_key_exists($key, $jsonData1)) {
            $value = isBoll($jsonData2[$key]);
            return [
                'key' => $key,
                'status' => 'added',
                'value' => $value,
            ];
        }

        if ($jsonData1[$key] === $jsonData2[$key]) {
            $value = isBoll($jsonData1[$key]);
            return [
                'key' => $key,
                'status' => 'same',
                'value' => $value,
            ];
        }

        $value1 = isBoll($jsonData1[$key]);
        $value2 = isBoll($jsonData2[$key]);

        return [
            'key' => $key,
            'status' => 'different',
            'value1' => $value1,
            'value2' => $value2,
        ];
    }, $sortedKeys);

    foreach ($keysInfo as $key) {
        if ($key['status'] === 'same') {
            $diffBetweenFiles[] = "  {$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'deleted') {
            $diffBetweenFiles[] = "- {$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'added') {
            $diffBetweenFiles[] = "+ {$key['key']}: {$key['value']}";
        } else {
            $diffBetweenFiles[] = "- {$key['key']}: {$key['value1']}";
            $diffBetweenFiles[] = "+ {$key['key']}: {$key['value2']}";
        }
    }

    return implode("\n", $diffBetweenFiles);
}


function getPathToFile($fileName): string
{
    if (!file_exists($fileName)) {
        throw new \Exception('error');
    }

    return file_get_contents($fileName);
}

function isBoll($value): string
{
    if ($value === true) {
        return 'true';
    } elseif ($value === false) {
        return 'false';
    }
    return $value;
}
