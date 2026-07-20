<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class LegacyUserProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];
        $hashedPassword = $user->getAuthPassword();

        if (empty($plain) || empty($hashedPassword)) {
            return false;
        }


        // First check standard Laravel hash (Bcrypt) if the hash starts with $2y$
        if (str_starts_with($hashedPassword, '$2y$')) {
            return \Illuminate\Support\Facades\Hash::check($plain, $hashedPassword);
        }

        // Generate hash using the legacy mechanism
        $legacyHashed = crypt($plain, '$1$somethin$');

        if (hash_equals((string) $hashedPassword, (string) $legacyHashed)) {
            // Upgrade the password to Bcrypt automatically
            $user->forceFill([
                $user->getAuthPasswordName() => \Illuminate\Support\Facades\Hash::make($plain)
            ])->save();

            return true;
        }

        return false;
    }
}
