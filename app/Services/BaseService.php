<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class BaseService
{
    /**
     * Trả về user hiện tại (lazy — an toàn với queue/artisan commands).
     */
    protected function user(): ?Authenticatable
    {
        return auth()->user();
    }

    /**
     * Trả về company_id của user hiện tại.
     */
    protected function companyId(): int|null
    {
        return $this->user()?->company_id;
    }
}