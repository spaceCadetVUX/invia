<?php

namespace App\Repositories;

use App\Models\Template;
use Illuminate\Support\Collection;

class TemplateRepository
{
    public function listActive(): Collection
    {
        return Template::where('is_active', true)->orderBy('price')->get();
    }

    public function listFree(): Collection
    {
        return Template::where('is_active', true)->where('price', 0)->orderBy('name')->get();
    }
}
