<?php

namespace App\Exceptions;

class PlanFeatureException extends \RuntimeException
{
    public function __construct(
        public readonly string $feature,
        public readonly string $currentPlan,
    ) {
        parent::__construct("Feature not available: {$feature} on plan {$currentPlan}");
    }
}
