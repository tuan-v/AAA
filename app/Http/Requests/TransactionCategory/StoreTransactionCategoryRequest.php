<?php

namespace App\Http\Requests\TransactionCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // 'type' => 'required|in:income,expense,transfer',
            'description' => 'nullable|string',
            'status' => 'nullable|in:1,0',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'tên loại giao dịch',
            // 'type' => 'loại giao dịch',
            'description' => 'mô tả',
            'status' => 'trạng thái',
        ];
    }
}