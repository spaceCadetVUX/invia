<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\TemplateRepository;
use Illuminate\Support\Collection;

class TemplateService
{
    public function __construct(private TemplateRepository $templateRepo) {}

    public function listForUser(User $user): Collection
    {
        if ($user->hasRole('admin')) {
            return $this->templateRepo->listActive();
        }

        // Host free chỉ thấy template miễn phí
        // TODO: khi Phase 3 xong, check user_templates để thêm premium đã mua
        return $this->templateRepo->listFree();
    }
}
