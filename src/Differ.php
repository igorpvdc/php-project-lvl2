<?php

namespace Differentiator\Differ;

use function Differentiator\Parsers\parse;
use function Functional\sort;
use Symfony\Component\Yaml\Yaml;
use function Functional\pick;

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
            $diff[] = "    {$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'deleted') {
            $diff[] = "  - {$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'added') {
            $diff[] = "  + {$key['key']}: {$key['value']}";
        } else {
            $diff[] = "  - {$key['key']}: {$key['value1']}";
            $diff[] = "  + {$key['key']}: {$key['value2']}";
        }
    }

    $result = implode("\n", $diff);

    return "{\n{$result}\n}";
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

function checkArrays($data1, $data2, $depth = 1, $diff = [])
{
    $uniqueKeys = array_keys(array_merge($data1, $data2));
    $sortedKeys = sort($uniqueKeys, fn ($left, $right) => strcmp($left, $right), true);

    foreach ($sortedKeys as $key) {
        if (!array_key_exists($key, $data1)) {
            $value = ifBollOrNullReturnString($data2[$key]);
            $diff[] = [
                'key' => $key,
                'status' => 'added',
                'value' => $value,
                'depth' => $depth,
            ];
            continue;
        }

        if (!array_key_exists($key, $data2)) {
            $value = ifBollOrNullReturnString($data1[$key]);
            $diff[] = [
                'key' => $key,
                'status' => 'deleted',
                'value' => $value,
                'depth' => $depth,
            ];
            continue;
        }

        if ($data1[$key] === $data2[$key] && !is_array($data1[$key]) && !is_array($data2[$key])) {
            $value = ifBollOrNullReturnString($data1[$key]);
            $diff[] = [
                'key' => $key,
                'status' => 'same',
                'value' => $value,
                'depth' => $depth,
            ];
            continue;
        }

        if ($data1[$key] !== $data2[$key] && !is_array($data1[$key]) && !is_array($data2[$key])) {
            $value1 = ifBollOrNullReturnString($data1[$key]);
            $value2 = ifBollOrNullReturnString($data2[$key]);
            $diff[] = [
                'key' => $key,
                'status' => 'different',
                'value' => 'different',
                'value1' => $value1,
                'value2' => $value2,
                'depth' => $depth,
            ];
            continue;
        }

        if (is_array($data1[$key]) && is_array($data2[$key])) {
            $diff[] = [
                'key' => $key,
                'status' => 'different',
                'depth' => $depth,
                'value' => checkArrays($data1[$key], $data2[$key], $depth + 1),
            ];
        }
    }

    return $diff;
}

function parseTest($file)
{
    $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

    switch ($fileExtension) {
        case 'json':
            $rawData = json_decode(getDataFromFile($file));
            $data = turnObjectToArray($rawData);
            break;
        case 'yml' || 'yaml':
            $rawData = Yaml::parse(getDataFromFile($file), Yaml::PARSE_OBJECT_FOR_MAP);
            $data = turnObjectToArray($rawData);
            break;
        default:
            throw new \Exception('file extension is not supported by this application');
    }

    return $data;
}

function getDataFromFile($fileName): string
{
    if (!file_exists($fileName)) {
        throw new \Exception('file is not exists');
    }

    if (!is_readable($fileName)) {
        throw new \Exception('file is not readable');
    }

    return file_get_contents($fileName);
}

function ifBollOrNullReturnString($value)
{
    if ($value === true) {
        return 'true';
    } elseif ($value === false) {
        return 'false';
    } elseif ($value === null) {
        return 'null';
    }
    return $value;
}


function turnObjectToArray($data, $acc = [])
{
    foreach ($data as $key=>$value) {
        if (is_object($value)) {
            $acc[$key] = turnObjectToArray(get_object_vars($value));
        } else {
            $acc[$key] = $value;
        }
    }
    return $acc;
}

function turnToText($array)
{
    $diff = [];
    $space = '    ';
    foreach ($array as $key) {
        $numberOfSpacesForKey = str_repeat($space, $key['depth']);
        $numberHalf = str_repeat($space, $key['depth'] / 2);
        if ($key['status'] === 'same' && !is_array($key['value'])) {
            $diff[] = "{$numberOfSpacesForKey}{$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'deleted' && !is_array($key['value'])) {
            $diff[] = "{$numberHalf}  - {$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'added' && !is_array($key['value'])) {
            $diff[] = "{$numberHalf}  + {$key['key']}: {$key['value']}";
        } elseif ($key['status'] === 'different' && !is_array($key['value'])) {
            $diff[] = "{$numberHalf}  - {$key['key']}: {$key['value1']}";
            $diff[] = "{$numberHalf}  + {$key['key']}: {$key['value2']}";
        } elseif ($key['status'] === 'added' && is_array($key['value']) && !array_key_exists('depth', $key['value'])) {
            foreach ($key['value'] as $keya=>$valuea) {
                $text .= "{$keya}: {$valuea}";
            }
            $newNumberOfSpaces = str_repeat(' ', $key['depth'] * 6);
            $diff[] = "{$numberHalf}  + {$key['key']}: {\n{$newNumberOfSpaces}{$text}\n{$numberOfSpacesForKey}}";
        } elseif ($key['status'] === 'different' && is_array($key['value'])) {
            $new = turnToText($key['value']);
            $toString = implode("\n", $new);
            $diff[] = "{$numberHalf}  + {$key['key']}: {\n\n{$newNumberOfSpaces}{$toString}\n\n{$newNumberOfSpaces}}";
        } else {
            $newArray = turnToText($key["value"]);
            $toString = implode("\n", $newArray);
            $diff[$key['key']] = "{\n{$toString}\n}";
        }
    }

    return $diff;
}

function checkArraysMap($data1, $data2, $diff = [])
{
    $uniqueKeys = array_keys(array_merge($data1, $data2));
    $sortedKeys = sort($uniqueKeys, fn ($left, $right) => strcmp($left, $right), true);

    $keysInfo = array_map(function ($key) use ($data1, $data2) {
        if (!array_key_exists($key, $data1)) {
            $value = ifBollOrNullReturnString($data2[$key]);
            return [
                'key' => $key,
                'status' => 'added',
                'value' => $value,
            ];
        }

        if (!array_key_exists($key, $data2)) {
            $value = ifBollOrNullReturnString($data1[$key]);
            return [
                'key' => $key,
                'status' => 'deleted',
                'value' => $value,
            ];
        }

        if ($data1[$key] === $data2[$key] && !is_array($data1[$key]) && !is_array($data2[$key])) {
            $value = ifBollOrNullReturnString($data1[$key]);
            return [
                'key' => $key,
                'status' => 'same',
                'value' => $value,
            ];
        }

        if ($data1[$key] !== $data2[$key] && (!is_array($data1[$key]) || !is_array($data2[$key]))) {
            $value1 = ifBollOrNullReturnString($data1[$key]);
            $value2 = ifBollOrNullReturnString($data2[$key]);
            return [
                'key' => $key,
                'status' => 'different',
                'value' => 'different',
                'value1' => $value1,
                'value2' => $value2,
            ];
        }

        if (is_array($data1[$key]) && is_array($data2[$key])) {
            return [
                'key' => $key,
                'status' => 'nested',
                'value' => checkArraysMap($data1[$key], $data2[$key]),
            ];
        }
    }, $sortedKeys);

    return $keysInfo;
}

function turnDiffToTextMap($diff, $depth = 1)
{
//    $status = pick($diff[0], 'status');

    $result = array_map(function ($data) use ($depth) {
        $numberOfSpaces = countSpaces($depth);


        if ($data['status'] === 'same' && !is_array($data['value'])) {
            return "{$numberOfSpaces}  {$data['key']}: {$data['value']}";
        }

        if ($data['status'] === 'deleted' && !is_array($data['value'])) {
            return "{$numberOfSpaces}  - {$data['key']}: {$data['value']}";
        }

        if ($data['status'] === 'deleted' && is_array($data['value'])) {
            $diffString = implode("{$numberOfSpaces}\n{$numberOfSpaces}", arrayToString($data['value']));
            return "{$numberOfSpaces}  - {$data['key']}: {\n{$numberOfSpaces}{$diffString}\n{$numberOfSpaces}}";
        }

        if ($data['status'] === 'added' && !is_array($data['value'])) {
            return "{$numberOfSpaces}  + {$data['key']}: {$data['value']}";
        }

        if ($data['status'] === 'added' && is_array($data['value'])) {
            $diffString = implode("{$numberOfSpaces}\n{$numberOfSpaces}", arrayToString($data['value']));
            return "{$numberOfSpaces}  + {$data['key']}: {\n{$numberOfSpaces}{$diffString}\n{$numberOfSpaces}}";
        }

        if ($data['status'] === 'different' && !is_array($data['value']) && !is_array($data['value1']) && !is_array($data['value2'])) {
            $value1 = "{$numberOfSpaces}  - {$data['key']}: {$data['value1']}";
            $value2 = "{$numberOfSpaces}  + {$data['key']}: {$data['value2']}";
            return "{$value1}\n{$value2}";
        }

        if ($data['status'] === 'different' && !is_array($data['value']) && is_array($data['value1']) && !is_array($data['value2'])) {
            $newValue = arrayToString($data['value1']);
            $diffString = implode("{$numberOfSpaces}\n{$numberOfSpaces}", $newValue);
            return "{$numberOfSpaces}  - {$data['key']}: {$diffString}\n{$numberOfSpaces}  + {$data['key']}: {$data['value2']}";
        }

        if ($data['status'] === 'different' && !is_array($data['value']) && !is_array($data['value1']) && is_array($data['value2'])) {
            $newValue = arrayToString($data['value2']);
            $diffString = implode("{$numberOfSpaces}\n{$numberOfSpaces}", $newValue);
            return "{$numberOfSpaces}  - {$data['key']}: {$data['value1']}{$numberOfSpaces}  + {$data['key']}: {\n{$diffString}\n{$numberOfSpaces}";
        }

        if ($data['status'] === 'nested') {
            $newStep = turnDiffToTextMap($data['value'], $depth++);
            $toString = implode("\n", $newStep);
            return "{$numberOfSpaces}  {$data['key']}: {\n{$numberOfSpaces}{$toString}\n{$numberOfSpaces}}";
        }


    }, $diff);

    return $result;
}

function arrayToString($array, $spaces = '    ', $depth = 1, $acc = [])
{
    foreach ($array as $key=>$value) {
        if (is_array($value)) {
            $newArray = arrayToString($value, str_repeat($spaces, $depth * 2));
            $string = implode("\n", $newArray);
            $acc[] = "{$key}: {\n{$spaces}{$string}\n{$spaces}}";
        } else {
            $acc[] = "{$key}: {$value}";
        }
    }
    return $acc;
}

function countSpaces ($depth)
{
    return str_repeat(' ', $depth * 4 - 2);
}

function test ($file1, $file2)
{
    $test1 = parseTest($file1);
    $test2 = parseTest($file2);

    $result = turnDiffToTextMap(checkArraysMap($test1, $test2));

    var_dump($result);
}