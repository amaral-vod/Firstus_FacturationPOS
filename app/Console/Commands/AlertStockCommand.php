<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class AlertStockCommand extends Command
{
    protected $signature = 'stock:alert';

    protected $description = 'Notifier les stocks faibles et les surstocks';

    public function handle(): int
    {
        $low = NotificationService::alertStockFaible();
        $over = NotificationService::alertSurstock();

        $this->info("Alertes envoyées — stock faible: {$low}, surstock: {$over}");

        return self::SUCCESS;
    }
}
