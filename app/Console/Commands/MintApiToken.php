<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MintApiToken extends Command
{
    protected $signature = 'pdfpost:token
        {name : A label for the token, e.g. the app that will use it}
        {--abilities=render,templates : Comma separated list of abilities}';

    protected $description = 'Create an API token for calling the PDFPost API';

    public function handle(): int
    {
        $user = User::query()->oldest('id')->first();

        if ($user === null) {
            $this->error('No users exist yet. Register an account first, then mint a token.');

            return self::FAILURE;
        }

        $abilities = array_values(array_filter(array_map('trim', explode(',', $this->option('abilities')))));

        $token = $user->createToken($this->argument('name'), $abilities);

        $this->info('Token created with abilities: '.implode(', ', $abilities));
        $this->warn('Save it now, it will not be shown again:');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
