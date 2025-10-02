<?php

// Em app/Http/Controllers/Tenant/NotificationController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Busca as notificações não lidas
    public function index()
    {
        return response()->json(auth()->user()->unreadNotifications);
    }

    // Marca uma notificação específica como lida
    public function markAsRead(Request $request, $notificationId)
    {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->noContent();
    }
}
