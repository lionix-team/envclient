<?php

namespace Lionix\EnvClient\Tests\Services;

use Lionix\EnvClient\Services\EnvSetter;
use Lionix\EnvClient\Tests\TestCase;

class EnvSetterTest extends TestCase
{
    /**
     * Testing save
     *
     * @return void
     */
    public function testSetterSave()
    {
        $setter = new EnvSetter();

        $testValue = 'Test!';

        $setter->set(['APP_NAME' => $testValue]);

        $setter->save();

        $filepath = app()->environmentFilePath();

        $contents = file_get_contents($filepath);

        $this->assertTrue(
            boolval(preg_match('/^' . 'APP_NAME' . '=' . preg_quote($testValue) . '/', $contents))
        );
    }
}
