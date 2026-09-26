<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Services;

use Dotenv\Dotenv;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Lionix\EnvClient\Events\EnvironmentFileUpdated;
use Lionix\EnvClient\Services\EnvSetter;
use Lionix\EnvClient\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EnvSetterTest extends TestCase
{
    public function test_replaces_existing_variable(): void
    {
        $setter = new EnvSetter;

        $setter->set(['APP_NAME' => 'Test!']);
        $setter->save();

        $this->assertStringContainsString("APP_NAME=Test!\n", $this->envContents());
        $this->assertSame(1, substr_count($this->envContents(), 'APP_NAME='));
    }

    public function test_appends_new_variable(): void
    {
        $setter = new EnvSetter;

        $setter->set(['NEW_VALUE' => 'hello']);
        $setter->save();

        $this->assertStringEndsWith("\nNEW_VALUE=hello\n", $this->envContents());
    }

    public function test_appends_to_file_without_trailing_newline(): void
    {
        $this->writeEnv('FIRST=1');

        $setter = new EnvSetter;
        $setter->set(['SECOND' => '2']);
        $setter->save();

        $this->assertSame("FIRST=1\nSECOND=2\n", $this->envContents());
    }

    public function test_does_not_touch_similar_or_commented_keys(): void
    {
        $this->writeEnv("# APP_NAME=commented\nMY_APP_NAME=other\nAPP_NAME_SUFFIX=x\nAPP_NAME=old\n");

        $setter = new EnvSetter;
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

        $setter = new EnvSetter;
        $setter->set(['APP_NAME' => 'new']);
        $setter->save();

        $this->assertSame("export APP_NAME=new\n", $this->envContents());
    }

    public function test_creates_missing_file(): void
    {
        unlink($this->envDirectory.'/.env');

        $setter = new EnvSetter;
        $setter->set(['APP_NAME' => 'created']);
        $setter->save();

        $this->assertSame("APP_NAME=created\n", $this->envContents());
    }

    public function test_replacement_backreferences_are_written_literally(): void
    {
        $setter = new EnvSetter;
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
        $setter = new EnvSetter;
        $setter->set(['APP_NAME' => $input]);
        $setter->save();

        $this->assertStringContainsString("APP_NAME={$written}\n", $this->envContents());
        $this->assertSame($parsed, Dotenv::parse($this->envContents())['APP_NAME']);
    }

    public function test_rejects_invalid_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EnvSetter)->set(['INVALID KEY' => 'value']);
    }

    public function test_rejects_multiline_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EnvSetter)->set(['APP_NAME' => "line\nbreak"]);
    }

    public function test_queue_is_cleared_after_save(): void
    {
        $setter = new EnvSetter;
        $setter->set(['APP_NAME' => 'first']);
        $setter->save();

        $this->writeEnv("APP_NAME=manual\n");
        $setter->save();

        $this->assertSame("APP_NAME=manual\n", $this->envContents());
    }

    public function test_forget_removes_variables(): void
    {
        $this->writeEnv("A=1\nexport B=2\nAB=3\n# B=comment\nC=4");

        $setter = new EnvSetter;
        $setter->forget(['B', 'C']);
        $setter->save();

        $this->assertSame("A=1\nAB=3\n# B=comment\n", $this->envContents());
    }

    public function test_set_after_forget_wins(): void
    {
        $setter = new EnvSetter;
        $setter->forget(['APP_NAME']);
        $setter->set(['APP_NAME' => 'kept']);
        $setter->save();

        $this->assertStringContainsString("APP_NAME=kept\n", $this->envContents());
    }

    public function test_saved_values_are_visible_through_env(): void
    {
        $setter = new EnvSetter;
        $setter->set(['APP_NAME' => 'Runtime Name', 'BRAND_NEW' => 'yes']);
        $setter->forget(['NUMERIC_VALUE']);
        $setter->save();

        $this->assertSame('Runtime Name', Env::get('APP_NAME'));
        $this->assertSame('yes', Env::get('BRAND_NEW'));
        $this->assertNull(Env::get('NUMERIC_VALUE'));
    }

    public function test_other_files_do_not_touch_the_runtime(): void
    {
        $this->app->loadEnvironmentFrom('.env.other');

        $setter = new EnvSetter;
        $setter->set(['APP_NAME' => 'Other']);
        $setter->save();

        $this->assertSame('lionix/envclient', Env::get('APP_NAME'));
        $this->assertSame("APP_NAME=Other\n", file_get_contents($this->envDirectory.'/.env.other'));
    }

    public function test_backup_is_written_when_enabled(): void
    {
        $original = $this->envContents();

        $setter = new EnvSetter;
        $setter->set(['APP_NAME' => 'first']);
        $setter->save();

        $this->assertFileDoesNotExist($this->envDirectory.'/.env.backup');

        config()->set('env.backup', true);

        $setter->set(['APP_NAME' => 'second']);
        $setter->save();

        $this->assertStringContainsString('APP_NAME=first', (string) file_get_contents($this->envDirectory.'/.env.backup'));
        $this->assertNotSame($original, $this->envContents());
    }

    public function test_event_is_dispatched(): void
    {
        Event::fake();

        $setter = new EnvSetter;
        $setter->set(['APP_NAME' => 'evented']);
        $setter->forget(['NUMERIC_VALUE']);
        $setter->save();

        Event::assertDispatched(EnvironmentFileUpdated::class, function (EnvironmentFileUpdated $event): bool {
            return $event->set === ['APP_NAME' => 'evented']
                && $event->forgotten === ['NUMERIC_VALUE']
                && $event->keys() === ['APP_NAME', 'NUMERIC_VALUE']
                && $event->path === $this->envDirectory.'/.env';
        });
    }

    public function test_unchanged_file_is_not_written(): void
    {
        Event::fake();

        $setter = new EnvSetter;
        $setter->set(['NUMERIC_VALUE' => '220']);
        $setter->save();

        Event::assertNotDispatched(EnvironmentFileUpdated::class);
    }
}
