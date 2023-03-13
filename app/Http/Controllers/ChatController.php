<?php

namespace App\Http\Controllers;

use App\Events\SendMessageTo;
use App\Http\Traits\UserTrait;
use App\Models\Chat;
use App\Models\ChatConversation;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    use UserTrait;

    public function index()
    {
        $chats = Chat::where('sender', Auth::id())
            ->orWhere('receiver', Auth::id())
            ->paginate(20)
            ->map(function($convo){
                if($convo->sender == Auth::id())
                {
                    $chat = ChatConversation::where('convoId', $convo->id)->latest()->first(['sender', 'message', 'created_at', 'updated_at']);
                    $receiver = $convo->receivers;

                    $receiver->id = $receiver->id;
                    $receiver->avatar = $receiver->avatar;
                    $receiver->status = $receiver->status;
                    $receiver->seen = $convo->seen;
                    $receiver->convoId = $convo->id;
                    $receiver->sender = $chat->sender;
                    $receiver->message = $chat->message;
                    $receiver->created_at = $chat->created_at;
                    $receiver->updated_at = $chat->updated_at;
                    $receiver->name = $receiver->getFullName();
                    $receiver->status = $receiver->getStatus();

                    unset($receiver->first_name, $receiver->auth, $receiver->instituteId, $receiver->programId, $receiver->last_name, $receiver->designation);

                    return $receiver;
                }
                $chat = ChatConversation::where('convoId', $convo->id)->latest()->first(['sender', 'message', 'created_at', 'updated_at']);
                $sender = $convo->senders;

                $sender->id = $sender->id;
                $sender->avatar = $sender->avatar;
                $sender->status = $sender->status;
                $sender->created_at = $chat->created_at;
                $sender->updated_at = $chat->updated_at;
                $sender->convoId = $convo->id;
                $sender->message = $chat->message;
                $sender->sender = $chat->sender;
                $sender->seen = $convo->seen;
                $sender->name = $sender->getFullName();
                $sender->status = $sender->getStatus();

                unset($sender->first_name, $sender->auth, $sender->instituteId, $sender->programId, $sender->last_name, $sender->designation);

                return $sender/* ->only('avatar', 'id', 'name', 'message', 'seen', 'sender', 'status', 'created_at', 'convoId', 'updated_at') */;
            })
            ->sortByDesc('updated_at')
            ->unique();

        $counter = Chat::where('sender', Auth::id())
            ->orWhere('receiver', Auth::id())
            ->get(['id', 'seen'])
            ->filter(function ($value) {
                if(!$value->seen){
                    $chat = ChatConversation::where('convoId', $value->id)->latest()->first(['sender']);
                    if($chat->sender != Auth::id()){
                        $chat->seen = $value->seen;
                        return $chat;
                    }
                }
            })->count();

        return response()->json([
            "chat" => $chats,
            "count" => $counter
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $hasConvo = $this->hasConversation($request->user);
            
            $mess = null;

            if(!$hasConvo)
            {
                $convo = Chat::create([
                    'sender' => Auth::id(),
                    'receiver' => $request->user,
                ]);

                $mess = $this->sendMessage($convo->id, $request->message);
            }else{
                $mess = $this->sendMessage($hasConvo->id, $request->message);
                $hasConvo->seen = null;
                $hasConvo->save();
            }

            $col = collect([
                "convoId" => $mess->convoId,
                "created_at" => $mess->created_at,
                "id" => Auth::id(),//$request->user,//$mess->sender,
                "message" => $mess->message,
                "sender" => $mess->sender,
                "seen" => $mess->seen,
                "updated_at" => $mess->updated_at,
                "receiver" => $request->user,
                "status" => User::find($request->user)->getStatus(),
                "name" => $hasConvo?$hasConvo->receivers->getFullName():User::find(Auth::id())->getFullName(),
                "avatar" => $hasConvo?$hasConvo->receivers->only('avatar')['avatar']:Auth::user()->avatar
            ]);

            SendMessageTo::dispatch($col);
            
            $col['name'] = User::find($request->user)->getFullName();
            $col['avatar'] = User::find($request->user)->avatar;
            $col['id'] = $request->user;

            DB::commit();
            return response()->json($col);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 400);
        }
    }

    public function setSeenMsg(Request $request)
    {
        try{
            $lastMessage = ChatConversation::where('convoId', $request->convoId)->latest()->first(['sender']);

            if($lastMessage->sender != Auth::id())
                Chat::where('id', $request->convoId)->update(['seen'=>true]);
                
            return response()->json($request->convoId);
        }catch(\Throwable $e){
            return response()->json($e->getMessage());
        }
    }

    public function getMessages($id)
    {
        $convoId = $this->hasConversation($id);

        if($convoId)
            return response()->json(
                ChatConversation::where('convoId', $convoId->id)
                ->orderByDesc('created_at')
                ->paginate(30)
                ->map(function ($convo) use ($convoId, $id) {
                    return collect([
                        "convoId" => $convoId->id,
                        "created_at" => $convo->created_at,
                        "id" => intval($id),//Auth::id() == $convoId->sender ? $convoId->receiver : $convoId->sender,//$request->user,//$mess->sender,
                        "message" => $convo->message,
                        "sender" => $convo->sender,
                        "seen" => $convo->seen,
                        "updated_at" => $convo->updated_at,
                        "name" => User::find($id)->getFullName(),
                    ]);
                    //return $convo;
                })
            );

        return response()->json([]);
    }

    public function chatUserSearch($search)
    {
        return response()->json(
            $this->searchUser(true, $search)
            ->map(function ($user) {
                $user->name = $user->first_name.' '.$user->last_name;

                return $user->only('id', 'name', 'avatar', 'status');
            })
        );
    }
    function hasConversation($user)
    {
        return Chat::where('sender', Auth::id())->where('receiver', $user)
            ->orWhere('sender', $user)->where('receiver', Auth::id())
            ->first(['id', 'sender', 'receiver', 'seen']);

    }

    function sendMessage($convoId, $message)
    {
        return ChatConversation::create([
            'convoId' => $convoId,
            'sender' => Auth::id(),
            'message' => $message
        ]);
    }
}
