<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;

class QuotaController extends Controller
{
    public function show(Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        return response()->json(app(PlanService::class)->getQuota($event));
    }
}
