<?php

namespace Bundsgaard\ChatSupport\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatsupport:install {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Chatsupports resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Chatsupport Assets...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'chatsupport-assets',
            '--force' => true,
        ]);

        $this->comment('Publishing Chatsupport Configuration...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'chatsupport-config',
            '--force' => $this->option('force'),
        ]);

        $this->info('Chatsupport scaffolding installed successfully. Please run php artisan migrate.');
    }
}
