<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTelegramCommand extends Command
{
    protected $signature = 'telegram:test
                            {--resolve : List chats the bot can see (use to find a chat id)}';

    protected $description = 'Verify the CyberLogia Telegram configuration and send a test message.';

    public function handle(TelegramService $telegram): int
    {
        $this->line('');
        $this->line('  CyberLogia — Telegram Diagnostics');
        $this->line('  ---------------------------------');

        $token = config('telegram.bot_token');
        $label = config('telegram.chat_label');
        $chatId = $telegram->targetChatId();

        $this->line('  Target chat label : '.$label);
        $this->line('  TELEGRAM_CHAT_ID  : '.($chatId ?? '<not set / invalid>'));
        $this->line('  Bot token         : '.(filled($token) ? 'set' : '<missing>'));
        $this->line('');

        if (blank($token)) {
            $this->error('TELEGRAM_BOT_TOKEN is missing — notifications are disabled.');
            $this->line('  Set it in .env, then run: php artisan config:clear');

            return self::FAILURE;
        }

        if ($chatId === null) {
            $this->error('TELEGRAM_CHAT_ID is missing or not numeric — notifications are disabled.');
            $this->line('');
            $this->line('  Telegram cannot deliver to an @username directly. To find the numeric id:');
            $this->line('    1. Add the bot to '.$label);
            $this->line('    2. Send any message in that chat');
            $this->line('    3. Run: php artisan telegram:test -- --resolve');
            $this->line('    4. Copy the id into TELEGRAM_CHAT_ID in .env');

            return self::FAILURE;
        }

        if ($this->option('resolve')) {
            try {
                $response = Http::timeout((int) config('telegram.timeout', 10))
                    ->get('https://api.telegram.org/bot'.$token.'/getUpdates');
            } catch (\Throwable $exception) {
                $this->error('Could not reach Telegram: '.$exception->getMessage());

                return self::FAILURE;
            }

            $updates = $response->json('result', []);

            if ($updates === []) {
                $this->warn('No updates visible to the bot. Send a message in '.$label.' and retry.');

                return self::SUCCESS;
            }

            $this->line('  Chats visible to the bot:');
            $seen = [];
            foreach ($updates as $update) {
                $chat = $update['message']['chat'] ?? $update['my_chat_member']['chat'] ?? null;
                if (! $chat || isset($seen[$chat['id']])) {
                    continue;
                }
                $seen[$chat['id']] = true;
                $marker = ((string) $chat['id'] === $chatId) ? '  <-- configured' : '';
                $this->line(sprintf('    %-16s %s%s', $chat['id'], ($chat['title'] ?? $chat['type']), $marker));
            }

            return self::SUCCESS;
        }

        $ok = $telegram->sendMessage(
            '<b>CyberLogia</b> — Telegram configuration is working.'."\n"
            .'Chat: '.htmlspecialchars((string) $label).' ('.$chatId.')'
        );

        if ($ok) {
            $this->info('  Test message delivered successfully to '.$label.' ('.$chatId.')');

            return self::SUCCESS;
        }

        $this->error('  Could not deliver the test message. Check the bot membership and permissions.');
        $this->line('  The bot must be a member of the chat with permission to post messages.');

        return self::FAILURE;
    }
}