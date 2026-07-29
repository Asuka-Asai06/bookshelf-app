<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Contracts\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポート。詳細なメソッドはReportServiceへ切り分け。
     */
    public function __construct(private readonly ReportService $reportService) {}

    public function index(): View
    {
        return view('reports.index', [
            'stats' => $this->reportService->getStats(auth()->user()),
        ]);
    }
}
