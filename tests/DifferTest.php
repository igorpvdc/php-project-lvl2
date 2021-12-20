<?php

namespace Differentiator\Differ\Tests;

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
        $diff = $this->getFixtureFullPath('DiffBetweenTestsFiles.json');

        $expected = file_get_contents($diff);

        $resultDiff = genDiff('file1.json', 'file2.json');

        $this ->assertEquals($resultDiff, $expected);
    }
}