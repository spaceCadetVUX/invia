<?php

namespace App\Exceptions;

class QuotaExceededException extends \RuntimeException
{
    public function __construct(
        public readonly string $resource,
        public readonly int    $available,
        public readonly string $currentPlan,
    ) {
        parent::__construct("Quota exceeded: {$resource}");
    }
}
