<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Boots the service providers necessary for Dusk.
     */
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        // Force the application environment to 'testing' for Dusk tests
        $this->app['env'] = 'testing';

        if (isset($uses[RefreshDatabase::class])) {
            $this->refreshDatabase();
        }

        return $uses;
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--no-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            // Only use headless in CI environment, not locally
            $this->runningInCI() ? '--headless' : null,
            // Use a unique user-data-dir for each test run to avoid conflicts
            '--user-data-dir=/tmp/chrome-user-data-'.time().'-'.rand(10000, 99999),
        ])->filter()->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            ),
            $this->runningInCI() ? 120000 : 60000, // Increased connection timeout in CI
            $this->runningInCI() ? 120000 : 60000  // Increased request timeout in CI
        );
    }

    /**
     * Determine if running in CI environment.
     */
    protected function runningInCI(): bool
    {
        return getenv('CI') !== false;
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the browser window should start maximized.
     */
    protected function shouldStartMaximized(): bool
    {
        return isset($_SERVER['DUSK_START_MAXIMIZED']) ||
               isset($_ENV['DUSK_START_MAXIMIZED']);
    }
}
