<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'customer_account_id',
        'company_id',
        'title',
        'message',
        'data',
        'url_link',
        'subdomain',
        'category',
        'read_at',
        'delivered_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customerAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerAccount::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForUserLogin($query)
    {
        $userId = auth()->id();
        $companyId = auth()->user()->company_id;
        if ($companyId) {
            return $query->where('company_id', $companyId)
                ->where(function ($q) use ($userId) {
                    $q->where(function ($broadcast) {
                        $broadcast->whereNull('user_id')->whereNull('customer_account_id');
                    })
                        ->orWhere('user_id', $userId);
                });
        }

        return $query->where('user_id', $userId);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now('Asia/Ho_Chi_Minh')->subDays($days));
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public function markAsRead()
    {
        $this->update(['read_at' => now('Asia/Ho_Chi_Minh')]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }
}
