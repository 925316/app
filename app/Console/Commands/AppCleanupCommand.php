<?php

namespace App\Console\Commands;

use App\Models\ApiSigningKey;
use App\Models\ClientSession;
use App\Models\EventLog;
use App\Services\ApiSigningKeyService;
use Illuminate\Console\Command;

class AppCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup
        {--logs-days=90 : Delete event logs older than this many days}
        {--sessions-minutes=1440 : Delete client sessions stale for this many minutes}
        {--retired-key-days=365 : Delete retired signing key metadata older than this many days}
        {--dry-run : Report cleanup counts without deleting records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean stale database records for cron-driven production maintenance';

    /**
     * Execute the console command.
     */
    public function handle(ApiSigningKeyService $apiSigningKeyService): int
    {
        $logsDays = max(1, (int) $this->option('logs-days'));
        $sessionsMinutes = max(1, (int) $this->option('sessions-minutes'));
        $retiredKeyDays = max(1, (int) $this->option('retired-key-days'));
        $dryRun = (bool) $this->option('dry-run');

        $oldLogsQuery = EventLog::query()
            ->where('created_at', '<=', now()->subDays($logsDays));
        $staleSessionsQuery = ClientSession::query()
            ->where(function ($query) use ($sessionsMinutes): void {
                $cutoff = now()->subMinutes($sessionsMinutes);

                $query->where(function ($nested) use ($cutoff): void {
                    $nested->whereNull('last_heartbeat_at')
                        ->where('created_at', '<=', $cutoff);
                })->orWhere('last_heartbeat_at', '<=', $cutoff);
            });

        $oldLogs = (clone $oldLogsQuery)->count();
        $staleSessions = (clone $staleSessionsQuery)->count();
        $retiredKeyMetadata = $this->countRetiredKeyMetadata($retiredKeyDays);

        if (! $dryRun) {
            $oldLogs = $oldLogsQuery->delete();
            $staleSessions = $staleSessionsQuery->delete();
            $retiredKeyMetadata = $apiSigningKeyService->cleanupRetiredMetadata($retiredKeyDays);
        }

        $prefix = $dryRun ? 'Would delete' : 'Deleted';

        $this->line("{$prefix} {$oldLogs} event log records older than {$logsDays} days.");
        $this->line("{$prefix} {$staleSessions} stale client session records older than {$sessionsMinutes} minutes.");
        $this->line("{$prefix} {$retiredKeyMetadata} retired API signing key metadata records older than {$retiredKeyDays} days.");

        return self::SUCCESS;
    }

    private function countRetiredKeyMetadata(int $days): int
    {
        return ApiSigningKey::query()
            ->where('is_active', false)
            ->whereNotNull('retired_at')
            ->where('retired_at', '<=', now()->subDays($days))
            ->count();
    }
}
