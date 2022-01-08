<?php

namespace Differentiator\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse($file)
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
