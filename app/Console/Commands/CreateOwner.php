<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateOwner extends Command
{
    protected $signature = 'garmentos:owner {--email=} {--name=Owner}';

    protected $description = 'Create the single owner account; prompts securely for the password';

    public function handle(): int
    {
        if (User::withTrashed()->exists()) {
            $this->error('An owner account already exists. Use the account settings to update it.');

            return self::FAILURE;
        }
        $email = $this->option('email') ?: $this->ask('Owner email');
        $password = $this->secret('Owner password (at least 12 characters)');
        $data = ['email' => $email, 'name' => $this->option('name'), 'password' => $password];
        $validator = Validator::make($data, ['email' => ['required', 'email', 'max:255'], 'name' => ['required', 'string', 'max:255'], 'password' => ['required', Password::min(12)]]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        DB::transaction(function () use ($data): void {
            $user = User::create(['email' => $data['email'], 'name' => $data['name'], 'password' => Hash::make($data['password']), 'email_verified_at' => now()]);
            $user->forceFill(['created_by' => $user->id, 'updated_by' => $user->id])->save();
            Setting::putValue('default_weight_unit', 'lb');
            Setting::putValue('dia_presets', [32, 34, 36, 40]);
            Setting::putValue('currency_label', 'BDT');
        });
        $this->info('Owner created. Sign in with your email and password.');

        return self::SUCCESS;
    }
}
