<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Services;

use Dotenv\Dotenv;
use InvalidArgumentException;
use Lionix\EnvClient\Services\EnvSetter;
use Lionix\EnvClient\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EnvSetterTest extends TestCase
{
    public function test_replaces_existing_variable(): void
    {
        $setter = new EnvSetter();

        $setter->set(['APP_NAME' => 'Test!']);
        $setter->save();

        $this->assertStringContainsString("APP_NAME=Test!\n", $this->envContents());
        $this->assertSame(1, substr_count($this->envContents(), 'APP_NAME='));
    }

    public function test_appends_new_variable(): void
    {
        $setter = new EnvSetter();

        $setter->set(['NEW_VALUE' => 'hello']);
        $setter->save();

        $this->assertStringEndsWith("\nNEW_VALUE=hello\n", $this->envContents());
    }

    public function test_appends_to_file_without_trailing_newline(): void
    {
        $this->writeEnv('FIRST=1');

        $setter = new EnvSetter();
        $setter->set(['SECOND' => '2']);
        $setter->save();

        $this->assertSame("FIRST=1\nSECOND=2\n", $this->envContents());
    }

    public function test_does_not_touch_similar_or_commented_keys(): void
    {
        $this->writeEnv("# APP_NAME=commented\nMY_APP_NAME=other\nAPP_NAME_SUFFIX=x\nAPP_NAME=old\n");

        $setter = new EnvSetter();
        $setter->set(['APP_NAME' => 'new']);
        $setter->save();

        $this->assertSame(
            "# APP_NAME=commented\nMY_APP_NAME=other\nAPP_NAME_SUFFIX=x\nAPP_NAME=new\n",
            $this->envContents()
        );
    }

    public function test_keeps_export_prefix(): void
    {
        $this->writeEnv("export APP_NAME=old\n");

        $setter = new EnvSetter();
        $setter->set(['APP_NAME' => 'new']);
        $setter->save();

        $this->assertSame("export APP_NAME=new\n", $this->envContents());
    }

    public function test_creates_missing_file(): void
    {
        unlink($this->envDirectory.'/.env');

        $setter = new EnvSetter();
        $setter->set(['APP_NAME' => 'created']);
        $setter->save();

        $this->assertSame("APP_NAME=created\n", $this->envContents());
    }

    public function test_replacement_backreferences_are_written_literally(): void
    {
        $setter = new EnvSetter();
        $setter->set(['APP_NAME' => 'pa$1$0word']);
        $setter->save();

        $this->assertStringContainsString("APP_NAME=pa$1$0word\n", $this->envContents());
    }

    /**
     * @return array<string, array{mixed, string, mixed}>
     */
    public static function values(): array
    {
        return [
            'plain' => ['value', 'value', 'value'],
            'trimmed' => ['  value  ', 'value', 'value'],
            'spaces' => ['hello world', '"hello world"', 'hello world'],
            'hash' => ['secret#1', '"secret#1"', 'secret#1'],
            'double quotes' => ['say "hi"', '"say \"hi\""', 'say "hi"'],
            'backslash' => ['C:\\path', '"C:\\\\path"', 'C:\\path'],
            'already quoted' => ['"hello world"', '"hello world"', 'hello world'],
            'single quoted' => ["'hello world'", "'hello world'", 'hello world'],
            'true' => [true, 'true', 'true'],
            'false' => [false, 'false', 'false'],
            'null' => [null, 'null', 'null'],
            'int' => [42, '42', '42'],
        ];
    }

    #[DataProvider('values')]
    public function test_values_are_written_in_a_dotenv_compatible_format(mixed $input, string $written, mixed $parsed): void
    {
        $setter = new EnvSetter();
        $setter->set(['APP_NAME' => $input]);
        $setter->save();

        $this->assertStringContainsString("APP_NAME={$written}\n", $this->envContents());
        $this->assertSame($parsed, Dotenv::parse($this->envContents())['APP_NAME']);
    }

    public function test_rejects_invalid_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EnvSetter())->set(['INVALID KEY' => 'value']);
    }

    public function test_rejects_multiline_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EnvSetter())->set(['APP_NAME' => "line\nbreak"]);
    }

    public function test_queue_is_cleared_after_save(): void
    {
        $setter = new EnvSetter();
        $setter->set(['APP_NAME' => 'first']);
        $setter->save();

        $this->writeEnv("APP_NAME=manual\n");
        $setter->save();

        $this->assertSame("APP_NAME=manual\n", $this->envContents());
    }
}
