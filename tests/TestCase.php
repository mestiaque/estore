<?php

namespace ME\Retail\Tests;

use ME\MEServiceProvider;
use ME\Models\User;
use ME\Retail\RetailServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            MEServiceProvider::class,
            RetailServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    /**
     * The Metheme migration seeds a super_admin user (id 1) whose
     * User::hasPermission() bypasses every check — the natural "acting as
     * an authorized operator" user for feature tests.
     */
    protected function admin(): User
    {
        return User::find(1);
    }
}
