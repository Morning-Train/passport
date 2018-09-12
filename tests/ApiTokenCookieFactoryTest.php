<?php

use Illuminate\Encryption\Encrypter;
use Laravel\Passport\ApiTokenCookieFactory;

class ApiTokenCookieFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_cookie_can_be_successfully_created()
    {
        $config = Mockery::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')->with('session')->andReturn([
            'lifetime' => 120,
            'path' => '/',
            'domain' => null,
            'secure' => true,
        ]);
        $encrypter = new Encrypter(str_repeat('a', 16));
        $factory = new ApiTokenCookieFactory($config, $encrypter);
		$user = new ApiTokenCookieFactoryFakeUser;

        $cookie = $factory->make($user, 'token');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Cookie', $cookie);
    }
}

class ApiTokenCookieFactoryFakeUser
{
    public $id = 1;
    public function getKey()
    {
        return $this->id;
    }
}
