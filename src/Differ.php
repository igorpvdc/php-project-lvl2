<?php

namespace Differentiator\Differ;

use function Differentiator\Parsers\parse;
use function Functional\sort;

function genDiff($file1, $file2)
{
    $data1 = parse($file1);
    $data2 = parse($file2);
    $diff = [];

    $uniqueKeys = array_keys(array_merge($data1, $data2));

    $sortedKeys = sort($uniqueKeys, fn ($left, $right) => strcmp($left, $right), true);

    $keysInfo = array_map(function ($key) use ($data1, $data2) {
        if (!array_key_exists($key, $data1)) {
            $value = ifBollReturnString($data2[$key]);
            return [
                'key' => $key,
                'status' => 'added',
                'value' => $value,
            ];
        }

        if (!array_key_exists($key, $data2)) {
            $value = ifBollReturnString($data1[$key]);
            return [
                'key' => $key,
                'status' => 'deleted',
                'value' => $value,
            ];
        }

        if ($data1[$key] === $data2[$key]) {
            $value = ifBollReturnString($data1[$key]);
            return [
                'key' => $key,
                'status' => 'same',
                'value' => $value,
            ];
        }

        $value1 = ifBollReturnString($data1[$key]);
        $value2 = ifBollReturnString($data2[$key]);

        return [
            'key' => $key,
            'status' => 'different',
            'value1' => $value1,
            'value2' => $value2,
        ];
    }, $sortedKeys);

    foreach ($keysInfo as $key) {
        if ($key['status'] === 'same') {
            $diff[] = "  {$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'deleted') {
            $diff[] = "- {$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'added') {
            $diff[] = "+ {$key['key']}: {$key['value']}";
        } else {
            $diff[] = "- {$key['key']}: {$key['value1']}";
            $diff[] = "+ {$key['key']}: {$key['value2']}";
        }
    }

    return implode("\n", $diff);
}

function ifBollReturnString($value): string
{
    if ($value === true) {
        return 'true';
    } elseif ($value === false) {
        return 'false';
    }
    return $value;
}
