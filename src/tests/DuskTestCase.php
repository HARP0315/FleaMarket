<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

/**
 * DuskTestcase
 */
abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function driver()
    {
        return RemoteWebDriver::create(
            'http://selenium:4444/wd/hub', DesiredCapabilities::chrome()
        );
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Dusk用DBを毎回リフレッシュ
        exec('php artisan migrate:fresh --env=dusk.local');
    }
}
