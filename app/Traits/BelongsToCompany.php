<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        // 1. TỰ ĐỘNG LỌC: Mỗi khi lấy dữ liệu (Select), hệ thống sẽ tự gắn thêm "WHERE company_id = ..."
        static::addGlobalScope('company_filter', function (Builder $builder) {
            if (Auth::check() && Auth::user()->company_id) {
                $builder->where($builder->getModel()->getTable() . '.company_id', Auth::user()->company_id);
            }
        });

        // 2. TỰ ĐỘNG GÁN: Mỗi khi tạo mới (Insert), hệ thống tự điền company_id của User đang đăng nhập vào
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->company_id && !isset($model->company_id)) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }
}
