<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramNotification;
use App\Services\Telegram\TelegramNotificationService;
use Illuminate\Console\Command;
use Throwable;

class TestTelegramNotifications extends Command
{
    protected $signature = 'telegram:test';

    protected $description = 'Send one harmless test message to the configured Telegram chat';

    public function handle(TelegramNotificationService $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('Telegram notifications are disabled or the bot token/chat ID is missing.');

            return self::FAILURE;
        }

        try {
            (new SendTelegramNotification(implode("\n", [
                '✅ TELEGRAM ПІДКЛЮЧЕНО',
                'Тестове повідомлення Bona Doors.',
                'Сайт: '.config('app.url'),
                'Час: '.now()->timezone('Europe/Kyiv')->format('d.m.Y H:i'),
            ])))->handle();
        } catch (Throwable $exception) {
            $this->error('Telegram rejected the test message. Check the token, chat ID and bot access.');

            return self::FAILURE;
        }

        $this->info('Telegram test message was delivered.');

        return self::SUCCESS;
    }
}
