<?php

namespace Differentiator\Tests;

use PHPUnit\Framework\TestCase;
use function Differentiator\Differ\genDiff;

class DifferTest extends TestCase
{
    public function getFixtureFullPath($fixtureName)
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public function test_gendiff_simple_json(): void
    {
        $file1 = $this->getFixtureFullPath('file1.json');
        $file2 = $this->getFixtureFullPath('file2.json');

        $diff = $this->getFixtureFullPath('DiffBetweenJsonFiles.txt');

        $expected = file_get_contents($diff);

        $resultDiff = genDiff($file1, $file2);

        $this->assertEquals($resultDiff, $expected);
    }

    public function test_gendiff_simple_yml(): void
    {
        $file1 = $this->getFixtureFullPath('file1.yml');
        $file2 = $this->getFixtureFullPath('file2.yml');

        $diff = $this->getFixtureFullPath('DiffBetweenJsonFiles.txt');

        $expected = file_get_contents($diff);

        $resultDiff = genDiff($file1, $file2);

        $this->assertEquals($resultDiff, $expected);
    }

    public function test_gendiff_nested_json(): void
    {
        $file1 = $this->getFixtureFullPath('fileNested1.json');
        $file2 = $this->getFixtureFullPath('fileNested2.json');

        $diff = $this->getFixtureFullPath('DiffBetweenNestedFiles.txt');

        $expected = file_get_contents($diff);

        $resultDiff = genDiff($file1, $file2);

        $this->assertEquals($resultDiff, $expected);
    }
}