<?php
namespace App\Http\Traits;

use App\Models\User;
use App\Models\UserLog;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;

trait UserTrait {

    public function searchUser($restrict = null, $search)
    {
        return User::query()
                    ->when($restrict, function ($query, $restrict) {
                        $query->whereNot('auth', 5)
                            ->where('id', '!=', Auth::id());
                    })
                    ->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->when($restrict, function ($query) {
                        $query->whereNot('auth', 5)
                            ->where('id', '!=', Auth::id());
                    })
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->when($restrict, function ($query) {
                        $query->whereNot('auth', 5)
                            ->where('id', '!=', Auth::id());
                    })
                    ->get(['id', 'first_name', 'last_name', 'auth', 'designation', 'avatar', 'status']);
    }

    public function userLog($accred, $document, $instrumentId, $details)
    {
        UserLog::create([
            'userId' => Auth::id(),
            'details' => $details,
            'accredlvl' => $accred,
            'documentId' => $document,
            'instrumentId' => $instrumentId,
        ]);
    }   
}
