<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class TestDusk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-dusk {--filter= : Filter which tests to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Dusk tests with proper environment setup';

    /**
     * Execute the console command.
     */
    public function handle(): ?int
    {
        $this->info('Setting up for Dusk tests...');

        // Create .env.dusk.local by copying .env.dusk
        $this->info('Creating .env.dusk.local configuration...');
        if (! file_exists(base_path('.env.dusk.local'))) {
            copy(base_path('.env.dusk'), base_path('.env.dusk.local'));
        }

        // Install Chrome driver
        $this->info('Installing Chrome driver...');
        $this->call('dusk:chrome-driver', ['--detect' => true]);

        // Start ChromeDriver
        $this->info('Starting ChromeDriver...');
        $chromeDriverProcess = new Process([base_path('vendor/laravel/dusk/bin/chromedriver-linux')]);
        $chromeDriverProcess->start();

        // Give the ChromeDriver a moment to start
        sleep(3);

        // Start the Laravel dev server in the background
        $this->info('Starting Laravel development server on port 8001...');
        $serverProcess = new Process(['php', 'artisan', 'serve', '--port=8001']);
        $serverProcess->setWorkingDirectory(base_path());
        $serverProcess->start();

        // Give the server a moment to start
        sleep(2);

        // Run tests
        $this->info('Running Dusk tests...');
        $filter = $this->option('filter');
        $command = ['php', 'artisan', 'dusk'];

        if ($filter) {
            $command[] = '--filter='.$filter;
        }

        $testProcess = new Process($command);
        $testProcess->setWorkingDirectory(base_path());
        $testProcess->setTimeout(null);
        $testProcess->setTty(true);
        $testProcess->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        // Stop the Laravel dev server
        $this->info('Stopping Laravel development server...');
        $serverProcess->stop();

        // Stop ChromeDriver
        $this->info('Stopping ChromeDriver...');
        $chromeDriverProcess->stop();

        $this->info('Dusk tests completed.');

        return $testProcess->getExitCode();
    }
}
