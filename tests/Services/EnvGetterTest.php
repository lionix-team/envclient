<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Services;

use Lionix\EnvClient\Services\EnvGetter;
use Lionix\EnvClient\Tests\TestCase;

class EnvGetterTest extends TestCase
{
    public function test_all_returns_every_declared_variable(): void
    {
        $all = (new EnvGetter)->all();

        $this->assertSame([
            'APP_NAME', 'BOOLEAN_VALUE', 'BOOLEAN_VALUE_TRUE', 'NUMERIC_VALUE', 'EMPTY_VALUE',
            'BASE_64_VALUE', 'URL_VALUE', 'FLOAT_VALUE', 'NULL_VALUE',
        ], array_keys($all));

        foreach ($all as $key => $value) {
            $this->assertSame(env($key), $value);
        }
    }

    public function test_get_casts_values_like_the_env_helper(): void
    {
        $getter = new EnvGetter;

        $this->assertSame('lionix/envclient', $getter->get('APP_NAME'));
        $this->assertFalse($getter->get('BOOLEAN_VALUE'));
        $this->assertTrue($getter->get('BOOLEAN_VALUE_TRUE'));
        $this->assertNull($getter->get('NULL_VALUE'));
        $this->assertSame('', $getter->get('EMPTY_VALUE'));
    }

    public function test_has(): void
    {
        $getter = new EnvGetter;

        $this->assertTrue($getter->has('APP_NAME'));
        $this->assertTrue($getter->has('EMPTY_VALUE'));
        $this->assertFalse($getter->has('APP'));
        $this->assertFalse($getter->has('MISSING_VALUE'));
    }

    public function test_ignores_comments_and_supports_export_prefix(): void
    {
        $this->writeEnv("# COMMENTED=1\nexport EXPORTED=yes\n  INDENTED=1\n");

        $getter = new EnvGetter;

        $this->assertSame(['EXPORTED', 'INDENTED'], array_keys($getter->all()));
        $this->assertFalse($getter->has('COMMENTED'));
        $this->assertTrue($getter->has('EXPORTED'));
    }

    public function test_missing_file_is_treated_as_empty(): void
    {
        unlink($this->envDirectory.'/.env');

        $getter = new EnvGetter;

        $this->assertSame([], $getter->all());
        $this->assertFalse($getter->has('APP_NAME'));
    }
}
