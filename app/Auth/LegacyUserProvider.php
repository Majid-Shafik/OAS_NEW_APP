<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Hash;

class LegacyUserProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'] ?? '';
        $hashedPassword = (string) $user->getAuthPassword();

        \Illuminate\Support\Facades\Log::info('LegacyUserProvider validating credentials', [
            'user_id' => $user->getAuthIdentifier(),
            'plain_length' => strlen($plain),
            'stored_hash_prefix' => substr($hashedPassword, 0, 10),
            'stored_hash_length' => strlen($hashedPassword),
        ]);

        if (empty($plain) || empty($hashedPassword)) {
            \Illuminate\Support\Facades\Log::warning('LegacyUserProvider: empty plain password or empty stored hash');
            return false;
        }

        // 1. Try standard Laravel Hash (Bcrypt / Argon2) safely
        try {
            if (Hash::check($plain, $hashedPassword)) {
                \Illuminate\Support\Facades\Log::info('LegacyUserProvider: Bcrypt hash matched successfully');
                return true;
            }
        } catch (\Throwable $e) {
            // Not a valid Bcrypt hash, continue to legacy checks
        }

        // 2. Legacy crypt with dynamic hash salt (standard UNIX / MD5-crypt / SHA-crypt)
        if (hash_equals($hashedPassword, (string) crypt($plain, $hashedPassword))) {
            $this->upgradePassword($user, $plain);

            return true;
        }

        // 3. Legacy crypt with hardcoded project salt
        if (hash_equals($hashedPassword, (string) crypt($plain, '$1$somethin$'))) {
            $this->upgradePassword($user, $plain);

            return true;
        }

        // 4. MD5 hash check
        if (hash_equals(strtolower($hashedPassword), md5($plain))) {
            $this->upgradePassword($user, $plain);

            return true;
        }

        // 5. SHA1 hash check
        if (hash_equals(strtolower($hashedPassword), sha1($plain))) {
            $this->upgradePassword($user, $plain);

            return true;
        }

        // 6. Plain text check (fallback for unconverted accounts)
        if (hash_equals($hashedPassword, $plain)) {
            $this->upgradePassword($user, $plain);

            return true;
        }

        return false;
    }

    protected function upgradePassword(UserContract $user, string $plain): void
    {
        try {
            $user->forceFill([
                $user->getAuthPasswordName() => Hash::make($plain),
            ])->save();
        } catch (\Throwable $e) {
            // Ignore upgrade failure if DB connection or permissions issue
        }
    }
}
