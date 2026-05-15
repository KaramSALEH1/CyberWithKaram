<?php

namespace App\Console\Commands;

use App\Models\AgentStatus;
use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;

class CheckOfflineAgents extends Command
{
    protected $signature = 'agents:check-offline {--minutes=5}';

    protected $description = 'Mark stale agents offline and send Telegram alerts';

    public function handle(TelegramService $telegramService): int
    {
        $threshold = now()->subMinutes((int) $this->option('minutes'));

        $staleAgents = AgentStatus::with(['user', 'service'])
            ->where('status', 'online')
            ->where(function ($query) use ($threshold) {
                $query->whereNull('last_heartbeat')->orWhere('last_heartbeat', '<', $threshold);
            })
            ->get();

        foreach ($staleAgents as $agentStatus) {
            $agentStatus->update(['status' => 'offline']);
            $telegramService->notifyAgentOffline($agentStatus);
            $this->info("Agent offline: user {$agentStatus->user_id}, service {$agentStatus->service_id}");
        }

        $this->info("Processed {$staleAgents->count()} stale agents.");

        return self::SUCCESS;
    }
}
