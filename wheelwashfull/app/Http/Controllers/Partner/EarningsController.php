<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerJobService;

class EarningsController extends Controller
{
    public function __construct(
        protected PartnerJobService $partnerJobService
    ) {}

    public function index()
    {
        $summary = $this->partnerJobService->earningsSummary(auth()->id());
        $recentJobs = $this->partnerJobService->completedJobs(auth()->id())->take(10);

        return view('partner.earnings.index', compact('summary', 'recentJobs'));
    }
}
