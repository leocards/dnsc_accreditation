<?php
namespace App\Http\Traits;

use App\Events\DocumentComment;
use App\Events\DocumentReview;
use App\Events\DocumentUpload;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

trait NotificationTrait {

    public function DocumentComment($document, $owner)
    {
        $notif = Notification::create([
            'userId' => $document['userId'],
            'userNotifier' => Auth::id(),
            'documentId' => $document['docuCurrId'],
            'action' => 'commented',
            'isOwner' => $document['userId'] == $owner?true:null,
            'details' => $document['title']
        ]);

        $notification = collect([
            'userId' => $notif['userId'],
            'userNotifier' => $notif['userNotifier'],
            'documentId' => $notif['documentId'],
            'action' => $notif['action'],
            'isOwner' => $notif['isOwner'],
            'details' => $notif['details'],
            "name" => $notif->user,
            "created_at" => $notif->created_at
        ]);

        DocumentComment::dispatch($notification);
    }

    public function DocumentReview($document)
    {
        $notif = Notification::create([
            'userId' => $document['userId'],
            'userNotifier' => Auth::id(),
            'documentId' => $document['docuCurrId'],
            'action' => 'review',
            'isOwner' => true,
            'details' => $document['details']
        ]);

        $notification = collect([
            'userId' => $notif['userId'],
            'userNotifier' => $notif['userNotifier'],
            'documentId' => $notif['documentId'],
            'action' => $notif['action'],
            'isOwner' => $notif['isOwner'],
            'details' => $notif['details'],
            "name" => $notif->user,
            "created_at" => $notif->created_at
        ]);

        DocumentReview::dispatch($notification);
    }

    public function DocumentUpload($document)
    {
        $notif = Notification::create([
            'userId' => $document['userId'],
            'userNotifier' => Auth::id(),
            'documentId' => $document['docuCurrId'],
            'action' => 'upload',
            'details' => $document['details']
        ]);
        
        $notification = collect([
            'userId' => $notif['userId'],
            'userNotifier' => $notif['userNotifier'],
            'documentId' => $notif['documentId'],
            'action' => $notif['action'],
            'isOwner' => $notif['isOwner'],
            'details' => $notif['details'],
            "name" => $notif->user,
            "created_at" => $notif->created_at
        ]);

        DocumentUpload::dispatch($notification);
    }
}