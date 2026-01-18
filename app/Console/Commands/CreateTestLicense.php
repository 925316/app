<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class CreateTestLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:create-test {privilege=1} {--notes=Test license}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test license for manual testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $privilege = (int) $this->argument('privilege');
        $notes = $this->option('notes');

        $license = LicenseService::createLicense(
            1, // type: base
            $privilege,
            null, // not assigned to any user
            null, // auto-generate key
            now()->addYear(),
            $notes
        );

        $this->info('Created test license successfully!');
        $this->line('License Key: <comment>'.$license->key.'</comment>');
        $this->line('Privilege: <info>'.$license->privilege->getLabel().' (level '.$license->privilege->value.')</info>');
        $this->line('Status: <info>'.$license->status->getLabel().'</info>');
        $this->line('Expires: <info>'.$license->expires_at->format('Y-m-d').'</info>');
        $this->line('');
        $this->comment('You can now use this license key to test activation in the web interface.');

        return self::SUCCESS;
    }
}
