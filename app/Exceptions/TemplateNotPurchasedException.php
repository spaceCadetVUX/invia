<?php

namespace App\Exceptions;

use App\Models\Template;
use Exception;
use Illuminate\Http\RedirectResponse;

class TemplateNotPurchasedException extends Exception
{
    public function __construct(Template $template)
    {
        parent::__construct(__('template.not_purchased', ['name' => $template->name]));
    }

    public function render(): RedirectResponse
    {
        return back()->withErrors(['template' => $this->getMessage()]);
    }
}
