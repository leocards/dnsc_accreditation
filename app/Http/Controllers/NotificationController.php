<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json([
            "notif" => Notification::where('userId', Auth::id())
                ->select(['id', 'userNotifier', 'documentId', 'instrumentId', 'seen', 'action', 'details', 'created_at', 'isOwner'])
                ->orderByDesc('created_at')
                ->paginate(20)
                ->map(function ($notif) {
                    if(!$notif->userNotifier)
                        return $notif;

                    $notif->name = $notif->user;

                    unset($notif->user);

                    return $notif;
                }),
            "count" => Notification::where('userId', Auth::id())
                ->whereNull('seen')
                ->get(['id'])
                ->count()
        ]);
    }

    public function markAsRead(Request $request)
    {
        try {

            if(!$request->id){
                Notification::where('userId', Auth::id())
                    ->update(['seen' => true]);
            }else{
                $seen = Notification::find($request->id);
                $seen->seen = true;
                $seen->save();
            }

            return response()->json('ok');

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }
}
