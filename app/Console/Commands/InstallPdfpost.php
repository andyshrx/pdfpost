<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class InstallPdfpost extends Command
{
    protected $signature = 'pdfpost:install';

    protected $description = 'First run setup, creates your account and an API token';

    public function handle(): int
    {
        // prompts need a real terminal, without one they blow up mid wizard.
        // symfony only looks at the -n flag so check the actual stream too
        $interactive = $this->input->isInteractive()
            && defined('STDIN')
            && stream_isatty(STDIN);

        if (! $interactive && ! app()->runningUnitTests()) {
            $this->error("pdfpost:install is interactive. Run it with a terminal attached (drop the -T if you're using docker compose exec).");

            return self::FAILURE;
        }

        $this->callSilently('migrate', ['--force' => true]);

        if (User::exists()) {
            $this->warn('PDFPost is already set up, an account exists.');
            $this->line('Need another API token? Run: php artisan pdfpost:token <name>');

            return self::SUCCESS;
        }

        $this->info("Welcome to PDFPost, let's get you set up.");
        $this->newLine();

        $name = text(
            label: 'Your name',
            required: true,
        );

        $email = text(
            label: 'Email address (you log in with this)',
            required: true,
            validate: fn (string $value) => filter_var($value, FILTER_VALIDATE_EMAIL)
                ? null
                : "That doesn't look like an email address.",
        );

        $password = password(
            label: 'Password',
            required: true,
            validate: fn (string $value) => strlen($value) >= 8
                ? null
                : 'Use at least 8 characters.',
        );

        // the register page is open while there are no users, so if this box
        // is exposed someone could have grabbed the instance while you typed
        if (User::exists()) {
            $this->error('An account was created through the web while the wizard was running. Not making another one, go check who that was.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => Str::lower($email),
            'password' => Hash::make($password),
        ]);

        $this->info('Account created. Signups are closed now, this instance is yours.');

        if (confirm(label: 'Seed the sample template gallery?', default: true)) {
            $this->callSilently('db:seed', ['--force' => true]);
            $this->info('Sample templates are in.');
        }

        if (confirm(label: 'Mint an API token now?', default: true)) {
            $tokenName = text(
                label: 'A label for the token, e.g. the app that will use it',
                default: 'my-app',
                required: true,
            );

            $token = $user->createToken($tokenName, ['render', 'templates']);

            $this->warn("Save this token now, it won't be shown again:");
            $this->line($token->plainTextToken);
        }

        $this->newLine();
        $this->info('Done. Log in at '.config('app.url').' and start building templates.');

        return self::SUCCESS;
    }
}
