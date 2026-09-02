<?php

namespace App\Services\Instagram;

use App\Models\ApplicationConfig;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

class InstagramCredentialStore
{
    private const ENCRYPTED_PREFIX = 'encrypted:';

    public function accessToken(): string
    {
        $storedToken = $this->value('instagramAccessToken');

        if (! str_starts_with($storedToken, self::ENCRYPTED_PREFIX)) {
            return $storedToken;
        }

        try {
            return Crypt::decryptString(substr($storedToken, strlen(self::ENCRYPTED_PREFIX)));
        } catch (Throwable) {
            return '';
        }
    }

    public function accountId(): string
    {
        return $this->value('instagramBusinessAccountId')
            ?: (string) config('services.instagram.business_account_id', '');
    }

    public function username(): string
    {
        return $this->value('instagramUsername');
    }

    public function expiresAt(): ?CarbonImmutable
    {
        $expiresAt = $this->value('instagramTokenExpiresAt');

        if ($expiresAt === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($expiresAt);
        } catch (Throwable) {
            return null;
        }
    }

    public function store(string $accessToken, string $accountId, string $username, int $expiresIn): void
    {
        $values = [
            'instagramAccessToken' => $this->encrypt($accessToken),
            'instagramBusinessAccountId' => $accountId,
            'instagramUsername' => $username,
            'instagramTokenExpiresAt' => now()->addSeconds($expiresIn)->toIso8601String(),
            'instagramTokenRefreshedAt' => now()->toIso8601String(),
        ];

        DB::transaction(function () use ($values): void {
            foreach ($values as $name => $value) {
                ApplicationConfig::updateOrCreate(
                    ['config_name' => $name],
                    ['config_data' => $value],
                );
            }
        });
    }

    public function updateAccessToken(string $accessToken, int $expiresIn): void
    {
        $values = [
            'instagramAccessToken' => $this->encrypt($accessToken),
            'instagramTokenExpiresAt' => now()->addSeconds($expiresIn)->toIso8601String(),
            'instagramTokenRefreshedAt' => now()->toIso8601String(),
        ];

        DB::transaction(function () use ($values): void {
            foreach ($values as $name => $value) {
                ApplicationConfig::updateOrCreate(
                    ['config_name' => $name],
                    ['config_data' => $value],
                );
            }
        });
    }

    private function value(string $name): string
    {
        return trim((string) ApplicationConfig::where('config_name', $name)->value('config_data'));
    }

    private function encrypt(string $value): string
    {
        return self::ENCRYPTED_PREFIX.Crypt::encryptString($value);
    }
}
