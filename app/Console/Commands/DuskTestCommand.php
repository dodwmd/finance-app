<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DuskTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-dusk
                            {--filter= : Filter which tests to run (e.g. LoginTest)}
                            {--fresh : Run migrations:fresh before running tests}
                            {--seed : Seed the database after migrations}
                            {--setup : Just set up the testing environment without running tests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Laravel Dusk browser tests with proper environment setup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Setting up Dusk testing environment...');

        // Ensure we're using the dusk environment
        $this->call('config:clear');

        // Update ChromeDriver if needed
        $this->info('Checking ChromeDriver version...');
        $this->call('dusk:chrome-driver', ['--detect' => true]);

        // Run migrations if requested
        if ($this->option('fresh')) {
            $this->info('Running fresh migrations...');
            $this->call('migrate:fresh', [
                '--env' => 'dusk',
                '--seed' => $this->option('seed'),
            ]);
        }

        // Setup categories if seeding
        if ($this->option('seed') && ! $this->option('fresh')) {
            $this->info('Seeding categories...');
            $this->call('db:seed', [
                '--env' => 'dusk',
                '--class' => 'CategorySeeder',
            ]);
        }

        // If setup only, exit here
        if ($this->option('setup')) {
            $this->info('Dusk environment setup complete.');

            return 0;
        }

        // Build the Dusk command options
        $duskOptions = [];
        if ($filter = $this->option('filter')) {
            $duskOptions['--filter'] = $filter;
        }

        // Run the tests
        $this->info('Running Dusk tests...');
        $result = $this->call('dusk', $duskOptions);

        return $result;
    }
}
