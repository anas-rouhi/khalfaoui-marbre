<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Garde-fou : les tests ne tournent que sur SQLite.
     *
     * Un `bootstrap/cache/config.php` oublié fait ignorer les variables de
     * phpunit.xml : les tests pointeraient alors vers la base MySQL de
     * développement, que `RefreshDatabase` viderait sans prévenir. La
     * vérification est faite ici, dès la création de l'application, car
     * `RefreshDatabase` migre la base pendant `parent::setUp()` — un contrôle
     * placé après arriverait trop tard.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $this->assertSafeDatabase($app['config']->get('database.default'));

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Double vérification, une fois l'application entièrement démarrée.
        $this->assertSafeDatabase(config('database.default'));
    }

    private function assertSafeDatabase(?string $connection): void
    {
        if ($connection !== 'sqlite') {
            throw new RuntimeException(
                "Tests interrompus : connexion « {$connection} » au lieu de « sqlite ». "
                .'Lancez `php artisan config:clear` avant la suite de tests.'
            );
        }
    }
}
