<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Support;

use Lionix\EnvClient\Services\EnvGetter;
use Lionix\EnvClient\Support\EnvironmentFile;
use Lionix\EnvClient\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EnvironmentFileTest extends TestCase
{
    /**
     * @return array<string, array{?string, mixed}>
     */
    public static function casts(): array
    {
        return [
            'true' => ['true', true],
            '(true)' => ['(true)', true],
            'false' => ['FALSE', false],
            'empty' => ['(empty)', ''],
            'null' => ['null', null],
            'missing' => [null, null],
            'quoted' => ['"quoted"', 'quoted'],
            'plain' => ['plain', 'plain'],
        ];
    }

    #[DataProvider('casts')]
    public function test_cast_matches_the_env_helper(?string $raw, mixed $expected): void
    {
        $this->assertSame($expected, EnvironmentFile::cast($raw));
    }

    public function test_keys_and_lines(): void
    {
        $contents = "# COMMENT=1\nA=1\nexport B=\"two words\"\n\nA=duplicate\n";

        $this->assertSame(['A', 'B'], EnvironmentFile::keys($contents));
        $this->assertSame(['A' => 'A=1', 'B' => 'export B="two words"'], EnvironmentFile::lines($contents));
    }

    public function test_invalid_contents_parse_to_empty_array(): void
    {
        $this->assertSame([], EnvironmentFile::parse("A=\"unterminated\n"));
    }

    public function test_loaded_file_is_detected(): void
    {
        $this->assertTrue(EnvironmentFile::isLoaded());

        $this->app->loadEnvironmentFrom('.env.other');

        $this->assertFalse(EnvironmentFile::isLoaded());
    }

    public function test_getter_reads_values_from_a_file_that_was_not_loaded(): void
    {
        file_put_contents($this->envDirectory.'/.env.other', "APP_NAME=Other\nFLAG=true\nQUOTED=\"a b\"\n");

        $this->app->loadEnvironmentFrom('.env.other');

        $getter = new EnvGetter;

        $this->assertSame('Other', $getter->get('APP_NAME'));
        $this->assertTrue($getter->get('FLAG'));
        $this->assertSame(['APP_NAME' => 'Other', 'FLAG' => true, 'QUOTED' => 'a b'], $getter->all());
        $this->assertNull($getter->get('BOOLEAN_VALUE'));
    }
}
