<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\DashboardService;
use App\Services\SuspensionService;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        // La suspensión se verifica al cargar el dashboard (reemplaza el cron en local)
        (new SuspensionService())->run();

        $metricas = (new DashboardService())->metricas();

        $this->render('dashboard/index', ['metricas' => $metricas]);
    }
}
