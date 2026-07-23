<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    /**
     * Danh sách subdomain hợp lệ
     */
    /**
     * Xác thực kênh user cá nhân theo subdomain
     *
     * @param  mixed  $user
     * @param  int  $id
     * @param  string  $sub
     * @return bool
     */
    public function authorizeUserChannel($user, $id, $sub)
    {
        // Kiểm tra user đã đăng nhập
        if (! $this->checkAuthenticated($user)) {
            return false;
        }

        // Kiểm tra user id có khớp không
        if (! $this->checkUserId($user, $id)) {
            return false;
        }

        // Kiểm tra subdomain có hợp lệ không
        if (! $this->checkSubdomain($user, $sub)) {
            return false;
        }

        // Kiểm tra origin từ request
        if (! $this->checkOrigin($user)) {
            return false;
        }

        // Log::info('User channel connection authorized', [
        //     'user_id' => $user->id,
        //     'subdomain' => $sub,
        //     'channel_type' => 'user'
        // ]);

        return true;
    }

    /**
     * Xác thực kênh company với subdomain
     *
     * @param  mixed  $user
     * @param  int  $companyId
     * @param  string  $sub
     * @return bool
     */
    public function authorizeCompanyChannel($user, $companyId, $sub)
    {
        // Kiểm tra user đã đăng nhập
        if (! $this->checkAuthenticated($user)) {
            return false;
        }

        // Kiểm tra subdomain có hợp lệ không
        if (! $this->checkSubdomain($user, $sub)) {
            return false;
        }

        // Kiểm tra origin từ request
        if (! $this->checkOrigin($user)) {
            return false;
        }

        // Kiểm tra user có thuộc công ty không
        if (! $this->checkCompanyMembership($user, $companyId)) {
            return false;
        }

        // Log::info('Company channel connection authorized', [
        //     'user_id' => $user->id,
        //     'company_id' => $companyId,
        //     'subdomain' => $sub,
        //     'channel_type' => 'company'
        // ]);

        return true;
    }

    /**
     * Xác thực kênh user trong company với subdomain
     *
     * @param  mixed  $user
     * @param  int  $companyId
     * @param  int  $id
     * @param  string  $sub
     * @return bool
     */
    public function authorizeUserInCompanyChannel($user, $companyId, $id, $sub)
    {
        // Kiểm tra user đã đăng nhập
        if (! $this->checkAuthenticated($user)) {
            return false;
        }

        // Kiểm tra user id có khớp không
        if (! $this->checkUserId($user, $id)) {
            return false;
        }

        // Kiểm tra subdomain có hợp lệ không
        if (! $this->checkSubdomain($user, $sub)) {
            return false;
        }

        // Kiểm tra origin từ request
        if (! $this->checkOrigin($user)) {
            return false;
        }

        // Kiểm tra user có thuộc công ty không
        if (! $this->checkCompanyMembership($user, $companyId)) {
            return false;
        }

        // Log::info('User in company channel connection authorized', [
        //     'user_id' => $user->id,
        //     'company_id' => $companyId,
        //     'subdomain' => $sub,
        //     'channel_type' => 'user_in_company'
        // ]);

        return true;
    }

    /**
     * Kiểm tra user đã đăng nhập
     *
     * @param  mixed  $user
     * @return bool
     */
    private function checkAuthenticated($user)
    {
        if (! $user) {
            Log::warning('Broadcast authentication failed: User not authenticated');

            return false;
        }

        return true;
    }

    /**
     * Kiểm tra user id có khớp không
     *
     * @param  mixed  $user
     * @param  int  $id
     * @return bool
     */
    private function checkUserId($user, $id)
    {
        if ($user->id !== (int) $id) {
            Log::warning('Broadcast authentication failed: User ID mismatch', [
                'user_id' => $user->id,
                'requested_id' => $id,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Kiểm tra subdomain có hợp lệ không
     *
     * @param  mixed  $user
     * @param  string  $subdomain
     * @return bool
     */
    private function checkSubdomain($user, $subdomain)
    {
        $validSubdomains = config('notifications.subdomains', ['main']);

        if (! in_array($subdomain, $validSubdomains, true)) {
            Log::warning('Broadcast authentication failed: Invalid subdomain', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'valid_subdomains' => $validSubdomains,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Kiểm tra origin có hợp lệ không
     *
     * @param  mixed  $user
     * @return bool
     */
    private function checkOrigin($user)
    {
        $request = request();
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');

        // Lấy danh sách domain được phép từ Sanctum config
        $statefulDomains = config('sanctum.stateful');
        $allowedDomains = is_array($statefulDomains)
            ? $statefulDomains
            : array_map('trim', explode(',', $statefulDomains));

        // Kiểm tra origin có trong danh sách domain được phép không
        if (! $origin) {
            Log::warning('Broadcast authentication failed: No origin header', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            return false;
        }

        $originHost = parse_url($origin, PHP_URL_HOST);
        $originPort = parse_url($origin, PHP_URL_PORT);
        $originHostWithPort = $originHost.($originPort ? ':'.$originPort : '');

        foreach ($allowedDomains as $allowedDomain) {
            if ($originHost === $allowedDomain || $originHostWithPort === $allowedDomain) {
                return true;
            }
        }

        Log::warning('Broadcast authentication failed: Invalid origin', [
            'user_id' => $user->id,
            'origin' => $origin,
            'referer' => $referer,
            'ip' => $request->ip(),
            'allowed_domains' => $allowedDomains,
        ]);

        return false;
    }

    /**
     * Kiểm tra user có thuộc công ty không
     *
     * @param  mixed  $user
     * @param  int  $companyId
     * @return bool
     */
    private function checkCompanyMembership($user, $companyId)
    {
        // Kiểm tra user có company_id không
        if (! $user->company_id) {
            Log::warning('Broadcast authentication failed: User has no company', [
                'user_id' => $user->id,
            ]);

            return false;
        }

        // Kiểm tra user có thuộc đúng công ty đang yêu cầu kết nối không
        if ($user->company_id != $companyId) {
            Log::warning('Broadcast authentication failed: Company mismatch', [
                'user_id' => $user->id,
                'user_company_id' => $user->company_id,
                'requested_company_id' => $companyId,
            ]);

            return false;
        }

        return true;
    }
}
