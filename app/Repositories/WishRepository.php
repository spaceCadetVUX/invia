<?php

namespace App\Repositories;

use App\Models\Wish;

class WishRepository
{
    public function create(array $data): Wish
    {
        return Wish::create($data);
    }

    public function pin(Wish $wish, bool $pinned): void
    {
        $wish->update(['is_pinned' => $pinned]);
    }

    public function hide(Wish $wish, bool $hidden): void
    {
        $wish->update(['is_hidden' => $hidden]);
    }
}
