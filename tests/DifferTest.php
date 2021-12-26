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

    public function test_genDiff(): void
    {
        $file1 = $this->getFixtureFullPath('file1.json');
        $file2 = $this->getFixtureFullPath('file2.json');

        $diff = $this->getFixtureFullPath('DiffBetweenTestsFiles.txt');

        $expected = file_get_contents($diff);

        $resultDiff = genDiff($file1, $file2);

        $this->assertEquals($resultDiff, $expected);
    }
}