<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'List notifications'
        ]);
    }
    public function unreadCount()
    {
        return response()->json([
            'count' => 0
        ]);
    }
}
