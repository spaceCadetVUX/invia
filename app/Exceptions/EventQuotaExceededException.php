<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class EventQuotaExceededException extends Exception
{
    public function __construct(string $plan, int $limit)
    {
        parent::__construct(__('event.quota_exceeded', ['plan' => $plan, 'limit' => $limit]));
    }

    public function render(): RedirectResponse
    {
        return back()->withErrors(['quota' => $this->getMessage()]);
    }
}
