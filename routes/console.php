<?php

use App\Services\VeilleTarifaireIA;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('fees:update-from-ia')->dailyAt('03:00');


