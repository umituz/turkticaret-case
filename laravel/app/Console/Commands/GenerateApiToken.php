<?php

namespace App\Console\Commands;

use App\Enums\User\UserTypeEnum;
use App\Models\User\User;
use Illuminate\Console\Command;

/**
 * Console command for generating API tokens.
 * 
 * Generates API tokens for users with optional user selection by email.
 * Includes automatic clipboard copying on macOS and user priority selection.
 * Used for testing and development purposes.
 *
 * @package App\Console\Commands
 */
class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:token
                            {email? : The user email to generate token for}
                            {--name=api-testing : Name of the token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API token for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if ($email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->error("User with email {$email} not found");
                return 1;
            }
        } else {
            $user = $this->findUserByPriority();

            if (!$user) {
                $this->error('No users found in the system');
                return 1;
            }
        }

        $tokenName = $this->option('name');

        $user->tokens()->where('name', $tokenName)->delete();

        $token = $user->createToken($tokenName)->plainTextToken;

        $this->info('User: ' . $user->name . ' (' . $user->email . ')');
        $this->info('Token: ' . $token);

        if (PHP_OS_FAMILY === 'Darwin') {
            $this->runExternalCommand('echo "' . $token . '" | pbcopy');
            $this->info('Token copied to clipboard');
        }

        return 0;
    }

    /**
     * Find user by priority: USER first (not ADMIN)
     *
     * @return User|null
     */
    protected function findUserByPriority(): ?User
    {
        $user = User::where('email', UserTypeEnum::USER->getEmail())->first();

        if ($user) {
            $this->info("Using " . UserTypeEnum::USER->getLabel() . ": {$user->email}");
            return $user;
        }

        return null;
    }

    /**
     * Run an external shell command
     *
     * @param string $command
     * @return void
     */
    protected function runExternalCommand($command)
    {
        exec($command);
    }
}
