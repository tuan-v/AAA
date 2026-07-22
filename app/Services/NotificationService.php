<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;

class NotificationService
{
    /**
     * Tạo thông báo cho một user
     */
    public function create(
        ?int $userId,
        ?int $companyId,
        string $title,
        string $message,
        ?array $data = null,
        ?string $urlLink = null,
        ?string $subdomain = null,
        ?string $category = 'system'
    ): Notification {
        $notification = Notification::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'url_link' => $urlLink,
            'subdomain' => $subdomain,
            'category' => $category ?? 'system',
        ]);

        // Broadcast notification real-time (gửi cho tất cả kể cả user hiện tại)
        broadcast(new NotificationCreated($notification));

        return $notification;
    }

    /**
     * Tạo thông báo cho nhiều user
     */
    public function createForUsers(
        array $userIds,
        ?int $companyId,
        string $title,
        string $message,
        ?array $data = null,
        ?string $urlLink = null,
        ?string $subdomain = null,
        ?string $category = 'system'
    ): Collection {
        $notifications = collect();

        foreach ($userIds as $userId) {
            $notifications->push(
                $this->create($userId, $companyId, $title, $message, $data, $urlLink, $subdomain, $category)
            );
        }

        return $notifications;
    }

    /**
     * Tạo thông báo cho các thành viên trong công ty có một quyền cụ thể.
     */
    public function createForPermission(
        string $permission,
        int $companyId,
        string $title,
        string $message,
        ?array $data = null,
        ?string $urlLink = null,
        ?int $excludeUserId = null,
        ?string $category = 'system'
    ): Collection {
        $userIds = User::query()
            ->where('company_id', $companyId)
            ->when($excludeUserId, fn ($query) => $query->whereKeyNot($excludeUserId))
            ->where(function ($query) use ($permission) {
                $query->whereHas('permissions', fn ($permissionQuery) =>
                    $permissionQuery->where('name', $permission)
                )->orWhereHas('roles.permissions', fn ($permissionQuery) =>
                    $permissionQuery->where('name', $permission)
                );
            })
            ->distinct()
            ->pluck('id')
            ->all();

        return $this->createForUsers(
            $userIds,
            $companyId,
            $title,
            $message,
            $data,
            $urlLink,
            category: $category
        );
    }

    /**
     * Tạo thông báo cho tất cả user trong company
     */
    public function createForCompany(
        int $companyId,
        string $title,
        string $message,
        ?array $data = null,
        ?string $urlLink = null,
        ?string $subdomain = null,
        ?string $category = 'system'
    ): Collection {
        $userIds = User::where('company_id', $companyId)
            ->pluck('id')
            ->toArray();

        return $this->createForUsers($userIds, $companyId, $title, $message, $data, $urlLink, $subdomain, $category);
    }

    /**
     * Lấy danh sách thông báo của user
     */
    public function getUserNotifications(
        int $userId,
        ?bool $unreadOnly = false,
        int $perPage = 20
    ) {
        $query = Notification::forUserLogin()
            ->orderBy('created_at', 'desc')
            ->where(function ($query) {
                $query->whereNull('subdomain')
                    ->orWhere('subdomain', '=', $this->getSubdomainFromRequest(request()));
            });

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->paginate($perPage);
    }

    /**
     * Lấy danh sách thông báo của user theo category
     */
    public function getUserNotificationsByCategory(
        int $userId,
        string $category,
        ?bool $unreadOnly = false,
        int $perPage = 20
    ) {
        $query = Notification::forUserLogin()
            ->byCategory($category)
            ->orderBy('created_at', 'desc')
            ->where(function ($query) {
                $query->whereNull('subdomain')
                    ->orWhere('subdomain', '=', $this->getSubdomainFromRequest(request()));
            });

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->paginate($perPage);
    }

    private function getSubdomainFromRequest(Request $request): ?string
    {
        $host = $request->getHost(); // Lấy host từ request
        $parts = explode('.', $host);

        // Giả sử cấu trúc domain là subdomain.domain.tld
        if (count($parts) >= 3) {
            return $parts[0]; // Trả về phần subdomain
        }


        return 'main'; // Không có subdomain
    }

    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::forUserLogin()->find($notificationId);

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    /**
     * Đánh dấu tất cả là đã đọc
     */
    public function markAllAsRead(int $userId, ?string $category = null): int
    {
        $query = Notification::forUserLogin()->unread();
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }
        return $query->update(['read_at' => now('Asia/Ho_Chi_Minh')]);
    }

    /**
     * Xóa thông báo
     */
    public function delete(int $notificationId): bool
    {
        $notification = Notification::forUserLogin()->find($notificationId);

        if (!$notification) {
            return false;
        }

        return $notification->delete();
    }

    /**
     * Đếm số thông báo chưa đọc
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::forUserLogin()
            ->where(function ($query) {
                $query->whereNull('subdomain')
                    ->orWhere('subdomain', '=', $this->getSubdomainFromRequest(request()));
            })->unread()
            ->count();
    }

    /**
     * Đếm số thông báo chưa đọc theo category
     */
    public function getUnreadCountByCategory(int $userId): array
    {
        $notifications = Notification::forUserLogin()
            ->where(function ($query) {
                $query->whereNull('subdomain')
                    ->orWhere('subdomain', '=', $this->getSubdomainFromRequest(request()));
            })
            ->unread()->get();
        $counts = [
            'all' => $notifications->count(),
            'management' => 0,
            'purchase' => 0,
            'sale' => 0,
            'warehouse' => 0,
            'accountant' => 0,
            'system' => 0,
        ];
        foreach ($notifications as $notification) {
            $category = $notification->category ?? 'system';
            if (isset($counts[$category])) {
                $counts[$category]++;
            }
        }

        return $counts;
    }

    /**
     * Xóa thông báo cũ
     */
    public function deleteOldNotifications(int $days = 90): int
    {
        return Notification::where('created_at', '<', now('Asia/Ho_Chi_Minh')->subDays($days))
            ->delete();
    }

    /**
     * Lấy thông báo theo company
     */
    public function getCompanyNotifications(
        int $companyId,
        int $perPage = 20
    ) {
        return Notification::forCompany($companyId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
