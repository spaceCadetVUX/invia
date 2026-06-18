<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminTemplateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Templates', [
            'templates' => Template::withCount(['events'])
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function store(StoreTemplateRequest $request): JsonResponse
    {
        $template = Template::create($request->validated());

        return response()->json($template, 201);
    }

    public function update(UpdateTemplateRequest $request, Template $template): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['blade_file']) && $data['blade_file'] !== $template->blade_file) {
            $data['version'] = $template->version + 1;
        }

        $template->update($data);

        return response()->json($template->fresh());
    }

    public function destroy(Template $template): JsonResponse
    {
        $template->update(['is_active' => false]);

        return response()->json(['deactivated' => true]);
    }
}
