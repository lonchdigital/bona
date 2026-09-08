<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DiagnosePayments extends Command
{
    protected $signature = 'payments:diagnose {--strict : Return a failure code when an integration is incomplete}';

    protected $description = 'Check payment credentials and callback configuration without exposing secrets or charging anything';

    public function handle(): int
    {
        $checks = $this->checks();

        $this->table(
            ['Integration', 'Result', 'Details'],
            collect($checks)->map(fn (array $check) => [
                $check['name'],
                $check['ok'] ? 'OK' : 'ERROR',
                $check['details'],
            ])->all(),
        );

        $failed = collect($checks)->contains(fn (array $check) => ! $check['ok']);

        if ($failed) {
            $this->error('Payment configuration is incomplete. No secret values were printed.');
        } else {
            $this->info('Payment configuration is complete. This check does not charge a card or override provider-side merchant status.');
        }

        return $failed && $this->option('strict') ? self::FAILURE : self::SUCCESS;
    }

    /** @return array<int, array{name: string, ok: bool, details: string}> */
    private function checks(): array
    {
        $liqPayPublic = trim((string) config('liqpay.public_key'));
        $liqPayPrivate = trim((string) config('liqpay.private_key'));
        $liqPayModeMatches = str_starts_with($liqPayPublic, 'sandbox_') === str_starts_with($liqPayPrivate, 'sandbox_');
        $liqPayUsesSandbox = str_starts_with($liqPayPublic, 'sandbox_') || str_starts_with($liqPayPrivate, 'sandbox_');

        $monoUrl = trim((string) config('payment.monobank.api_url'));
        $monoCredentials = [
            config('payment.monobank.client_secret'),
            config('payment.monobank.store_id'),
            config('payment.monobank.point_id'),
        ];

        $appUrl = trim((string) config('app.url'));
        $monoHost = mb_strtolower((string) parse_url($monoUrl, PHP_URL_HOST));

        return [
            [
                'name' => 'LiqPay credentials',
                'ok' => $liqPayPublic !== ''
                    && $liqPayPrivate !== ''
                    && $liqPayModeMatches
                    && (! app()->environment('production') || ! $liqPayUsesSandbox),
                'details' => $liqPayPublic === '' || $liqPayPrivate === ''
                    ? 'Public/private key pair is incomplete.'
                    : match (true) {
                        ! $liqPayModeMatches => 'Sandbox and live keys are mixed.',
                        app()->environment('production') && $liqPayUsesSandbox => 'Sandbox keys cannot be used in production.',
                        default => 'Key pair is present and uses one mode.',
                    },
            ],
            [
                'name' => 'PrivatBank instalments',
                'ok' => filled(config('payment.privatbank.store_id')) && filled(config('payment.privatbank.password')),
                'details' => filled(config('payment.privatbank.store_id')) && filled(config('payment.privatbank.password'))
                    ? 'Store ID and password are present.'
                    : 'Store ID/password pair is incomplete.',
            ],
            [
                'name' => 'monobank instalments',
                'ok' => collect($monoCredentials)->every(fn ($value) => filled($value))
                    && filter_var($monoUrl, FILTER_VALIDATE_URL) !== false
                    && parse_url($monoUrl, PHP_URL_SCHEME) === 'https'
                    && (! app()->environment('production') || $monoHost === 'u2.monobank.com.ua'),
                'details' => collect($monoCredentials)->every(fn ($value) => filled($value))
                    ? 'Credentials are present; API URL is '.($monoUrl === '' ? 'missing.' : 'configured.')
                    : 'Client secret, store ID or point ID is missing.',
            ],
            [
                'name' => 'Public callback URL',
                'ok' => filter_var($appUrl, FILTER_VALIDATE_URL) !== false
                    && (! app()->environment('production') || parse_url($appUrl, PHP_URL_SCHEME) === 'https'),
                'details' => $appUrl === '' ? 'APP_URL is missing.' : 'APP_URL is configured; HTTPS is required in production.',
            ],
            [
                'name' => 'Instalment pricing tables',
                'ok' => $this->validPricingTable('privatbank') && $this->validPricingTable('monobank'),
                'details' => 'Every offered period must have one non-negative surcharge rate.',
            ],
        ];
    }

    private function validPricingTable(string $provider): bool
    {
        $periods = config('payment.'.$provider.'.periods', []);
        $rates = config('payment.'.$provider.'.installment_surcharges', []);

        if (! is_array($periods) || $periods === [] || ! is_array($rates)) {
            return false;
        }

        foreach ($periods as $period) {
            if (! array_key_exists($period, $rates) || ! is_numeric($rates[$period]) || (float) $rates[$period] < 0) {
                return false;
            }
        }

        return true;
    }
}
