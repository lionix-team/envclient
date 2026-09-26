<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Rules;

use Lionix\EnvClient\Rules\AppRules;
use Lionix\EnvClient\Rules\AwsRules;
use Lionix\EnvClient\Rules\DatabaseRules;
use Lionix\EnvClient\Rules\MailRules;
use Lionix\EnvClient\Rules\QueueRules;
use Lionix\EnvClient\Rules\RedisRules;
use Lionix\EnvClient\Services\EnvValidator;
use Lionix\EnvClient\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RuleSetsTest extends TestCase
{
    /**
     * @return array<string, array{class-string<EnvValidator>, array<string, mixed>, list<string>}>
     */
    public static function cases(): array
    {
        $key = 'base64:'.base64_encode(str_repeat('k', 32));

        return [
            'app valid' => [AppRules::class, [
                'APP_NAME' => 'Laravel', 'APP_ENV' => 'production', 'APP_KEY' => $key,
                'APP_DEBUG' => false, 'APP_URL' => 'https://example.com',
            ], []],
            'app raw 16 byte key' => [AppRules::class, [
                'APP_NAME' => 'Laravel', 'APP_ENV' => 'local', 'APP_KEY' => str_repeat('k', 16), 'APP_DEBUG' => true,
            ], []],
            'app invalid' => [AppRules::class, [
                'APP_NAME' => 'Laravel', 'APP_ENV' => 'production', 'APP_KEY' => 'base64:short',
                'APP_DEBUG' => true, 'APP_URL' => 'not a url',
            ], ['APP_KEY', 'APP_DEBUG', 'APP_URL']],
            'database sqlite' => [DatabaseRules::class, ['DB_CONNECTION' => 'sqlite'], []],
            'database mysql' => [DatabaseRules::class, [
                'DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '3306', 'DB_DATABASE' => 'app',
            ], []],
            'database invalid' => [DatabaseRules::class, [
                'DB_CONNECTION' => 'oracle', 'DB_PORT' => '99999',
            ], ['DB_CONNECTION', 'DB_PORT', 'DB_HOST', 'DB_DATABASE']],
            'database mysql missing host' => [DatabaseRules::class, ['DB_CONNECTION' => 'mysql'], ['DB_HOST', 'DB_DATABASE']],
            'mail valid' => [MailRules::class, [
                'MAIL_MAILER' => 'smtp', 'MAIL_HOST' => 'mailpit', 'MAIL_PORT' => '1025', 'MAIL_FROM_ADDRESS' => 'hi@example.com',
            ], []],
            'mail invalid' => [MailRules::class, [
                'MAIL_MAILER' => 'smtp', 'MAIL_FROM_ADDRESS' => 'nope',
            ], ['MAIL_HOST', 'MAIL_PORT', 'MAIL_FROM_ADDRESS']],
            'queue valid' => [QueueRules::class, ['QUEUE_CONNECTION' => 'database', 'CACHE_STORE' => 'file', 'SESSION_DRIVER' => 'file'], []],
            'queue invalid' => [QueueRules::class, ['QUEUE_CONNECTION' => 'kafka', 'SESSION_DRIVER' => 'mongo'], ['QUEUE_CONNECTION', 'SESSION_DRIVER']],
            'redis valid' => [RedisRules::class, ['REDIS_CLIENT' => 'phpredis', 'REDIS_HOST' => '127.0.0.1', 'REDIS_PORT' => '6379'], []],
            'redis invalid' => [RedisRules::class, ['REDIS_CLIENT' => 'other', 'REDIS_DB' => '-1'], ['REDIS_CLIENT', 'REDIS_HOST', 'REDIS_DB']],
            'aws valid' => [AwsRules::class, [
                'AWS_ACCESS_KEY_ID' => 'id', 'AWS_SECRET_ACCESS_KEY' => 'secret', 'AWS_DEFAULT_REGION' => 'eu-central-1',
            ], []],
            'aws invalid' => [AwsRules::class, ['AWS_DEFAULT_REGION' => 'Frankfurt'], [
                'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_DEFAULT_REGION',
            ]],
        ];
    }

    /**
     * @param  class-string<EnvValidator>  $class
     * @param  array<string, mixed>  $values
     * @param  list<string>  $failing
     */
    #[DataProvider('cases')]
    public function test_rule_sets(string $class, array $values, array $failing): void
    {
        $validator = new $class;

        $this->assertSame($failing === [], $validator->validate($values));
        $this->assertEqualsCanonicalizing($failing, $validator->errors()->keys());
    }
}
