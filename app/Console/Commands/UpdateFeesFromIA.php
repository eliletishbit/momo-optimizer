<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VeilleTarifaireIA;

class UpdateFeesFromIA extends Command
{
    protected $signature = 'fees:update-from-ia';
    protected $description = 'Met à jour les frais d\'envoi et de retrait via l\'IA';

    public function handle(VeilleTarifaireIA $veille)
    {
        $this->info('🔄 Lancement de la veille tarifaire IA...');
        $veille->run();
        $this->info('✅ Veille tarifaire terminée.');
    }
}