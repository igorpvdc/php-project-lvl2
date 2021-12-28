<?php

namespace Differentiator\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse($file)
{
    $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

    if ($fileExtension === 'json') {
        $data = json_decode(getDataFromFile($file), true);
    } elseif ($fileExtension === 'yml' || $fileExtension === 'yaml') {
        $data = Yaml::parse(getDataFromFile($file));
    }

    return $data;
}

function getDataFromFile($fileName): string
{
    if (!file_exists($fileName)) {
        throw new \Exception('error');
    }

    return file_get_contents($fileName);
}
