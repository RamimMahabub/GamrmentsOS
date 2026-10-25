<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class DecimalCodeTest extends TestCase
{
    public function test_business_code_does_not_use_float_literals_casts_or_float_math_helpers(): void
    {
        $root = dirname(__DIR__, 2).'/app';
        $violations = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }
            if (str_contains(str_replace('\\', '/', $file->getPathname()), '/Http/Requests/Auth/')) {
                continue;
            }
            foreach (token_get_all(file_get_contents($file->getPathname())) as $token) {
                if (! is_array($token)) {
                    continue;
                }
                if (in_array($token[0], [T_DNUMBER, T_DOUBLE_CAST], true) ||
                    ($token[0] === T_STRING && in_array(strtolower($token[1]), ['floatval', 'doubleval', 'array_sum', 'number_format', 'round', 'floor', 'ceil'], true))) {
                    $violations[] = $file->getPathname().':'.$token[2].' '.$token[1];
                }
            }
        }
        $this->assertSame([], $violations);
    }
}
