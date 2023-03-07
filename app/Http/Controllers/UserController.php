<?php

namespace App\Http\Controllers;

use App\Http\Traits\UserTrait;
use App\Models\Registration;
use App\Models\TemporaryFile;
use App\Models\User;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    use UserTrait;

    public $jobs;
    public function __construct()
    {
        $this->jobs = json_decode(file_get_contents(storage_path() . "/jobs.json"), true);      
    }

    public function index(Request $request)
    {
        return Inertia::render('User', [
            'users' => request()->route()->named('user_indx') ? 
                        $this->getUsers($request->search) : 
                        (!$request->r ? $this->getRequests(false, $request->search) : $this->getRequests(true, $request->search)),
            'requestCount' => $this->getRequests()->count(),
        ]);
    }

    public function designation_auth()
    {
        try{
            return response()->json(['des_auth' => $this->jobs], 200);
        }catch(\Throwable $th){
            return response()->json(['des_auth' => 'Something went wrong'], 400);
        }
    }

    public function storeUser(Request $request)
    {
        $this->validateUser($request);

        try {
            DB::transaction(function () use ($request) {
                $user = Registration::find($request->user);
                User::create([
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'designation' => $request->designation,
                    'password' => $user->password,
                    'auth' => $request->auth,
                    'programId' => $request->program,
                    'instituteId' => $request->institute,
                    'username' => $user->username, 
                ]);
                $user->delete();
            });
            return back()->with('success', 'Register successfully');
        }catch (\Throwable $e){
            return back()->with('error', 'Failed to register');
        }
    }

    public function searchUser_AreaAssign($search, $tfc = null)
    {
        if(!$tfc){
            $result = $this->searchUser(null, $search)->filter(function ($value) {
                return $value->auth != 5;
            })->map(function ($value) {
                $user = collect([]);
                $user['id'] = $value->id;
                $user['name'] = $value->getFullName();
                    
                return $user;
            });
        }else{
            $result = $this->searchUser(null, $search)->filter(function ($value) use ($tfc) {
                return $value->auth != 5 && $value->id != $tfc;
            })->map(function ($value) {
                $user = collect([
                    "id" => $value->id,
                    "name" => $value->getFullName()
                ]);
                    
                return $user;
            });
        }
        
        return response()->json(['response' => collect([...$result])]);
    }

    public function searchSA_AreaAssign($search)
    {
        $result = $this->returnCollections($this->searchUser(null, $search), function ($value) {
            return $value->designation == 7 ? $value : null;
        })->map(function ($value) {
            $value->id = $value->id;
            $value->name = $value->getFullName();
                
            return $value->only('id', 'name');
        });
        return response()->json(['result'=>$result]);
    }

    public function searchUserToShare($search)
    {
        return response()->json(
            User::where('first_name', 'LIKE', "%{$search}%")
                ->whereNot('auth', 5)
                ->where('id', '!=', Auth::id())
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->whereNot('auth', 5)
                ->where('id', '!=', Auth::id())
                ->get(['id', 'first_name', 'last_name'])
        );
    }

    public function getUserLogs($accred, $instrument)
    {
        try {

            return response()->json(
                UserLog::where('accredlvl', $accred)
                    ->where('instrumentId', $instrument)
                    ->orderBy('created_at', 'desc')
                    ->paginate(15)
                    ->map(function ($logs) {

                        $userLog = collect([
                            "title" => $logs->getDocument->where('id', $logs->documentId)->first()->title,
                            "name" => $logs->userId == Auth::id()? 'You' : $logs->user->name,
                            "detail" => $logs->details,
                            "time" => $logs->created_at
                        ]);

                        return $userLog;
                    })
            );

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

    public function userProfile($id)
    {
        try {
            $user = User::find($id);
            $user->user_name = $user->username;

            return response()->json(
                $user
            );

        } catch (\Throwable $th) {
            return response()->json('error', 400);
        }
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'last_name' => 'required',
            'first_name' => 'required',
            'username' => 'required|min:8'
        ]);

        try {

            DB::transaction(function () use ($request) {

                $upload = TemporaryFile::find($request->avatarId);
                if($upload)
                {
                    if($this->processFile($upload->folder, $upload->file))
                    {
                        $user = User::find(Auth::id());
                        if($user->avatar)
                        {
                            $path = public_path('storage/avatar/'.$user->avatar);
                            unlink($path);
                        }
                    }
                }

                $user = User::find(Auth::id());
                $user->last_name = $request->last_name;
                $user->first_name = $request->first_name;
                $user->username = $request->username;
                $user->instituteId = $request->institute;
                $user->avatar = $upload?$upload->file:Auth::user()->avatar;
                $user->save();

                $upload?Auth::user()->avatar = $upload->file:'';
            });

            return back()->with('success', 'Changes saved');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function get_user_profile(Request $request)
    {
        try {

            if($request->edit)
                return response()->json(User::find($request->id));
            else
                return response()->json(null);

        } catch (\Throwable $th) {
            return response()->json('error', 400);
        }
    }

    public function updateUser(Request $request)
    {
        $this->validateUser($request);
        
        try {
            DB::transaction(function () use ($request) {
                $user = User::find($request->user);
                $user->designation = $request->designation;
                $user->auth = $request->auth;
                $user->programId = $request->program;
                $user->instituteId = $request->institute;
                $user->save();
            });
            return back()->with('success', 'Updated successfully');
        }catch (\Throwable $th){
            return back()->with('error', 'Failed to Update');
        }
    }

    public function avatarTemporaryUpload(Request $request)
    {
        if($request->hasFile('fileUpload'))
        {
            $file = $request->file('fileUpload');//get file
            $newfilename = 'avatar'.Carbon::now()->format('YmdHis').'.'.$file->getClientOriginalExtension();//generate new file name
            $folder = 'avatar'.uniqid().'-'. now()->timestamp;//genearate folder name
            $file->storeAs('tmp/'.$folder, $newfilename);//store file inside generated folder
            
            //store file in the temporary table
            $tmpUp = TemporaryFile::create([
                'folder'=>$folder,
                'file'=>$newfilename
            ]);

            return $tmpUp->id;
        }

        return null;
    }

    public function avatarRevertUpload(Request $request)
    {   
        try {
            $revert = TemporaryFile::find($request->tempId);
    
            $path = storage_path('app/tmp/'.$revert->folder.'/'.$revert->file);
            unlink($path);
            rmdir(storage_path('app/tmp/'.$revert->folder));
    
            $revert->delete();

            return response()->json('success');
        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function setTheme(Request $request)
    {
        try {
            User::where('id', Auth::id())
            ->update(['theme' => $request->theme?'dark':null]);

            Auth::user()->theme = $request->theme?'dark':null;
            
            return response()->json(200);
        } catch (\Throwable $th) {
            return response()->json(400);
        }
    }

    public function removeRequest(Request $request)
    {
        try {

            $req = Registration::find($request->id);
            $req->removed = true;
            $req->save();

            return back()->with('success', 'Removed successfully');

        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to remove');
        }
    }

    public function restoreRequest(Request $request)
    {
        try {

            $req = Registration::find($request->id);
            $req->removed = null;
            $req->save();

            return back()->with('success', 'Restored successfully');

        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to restore');
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'confirm_password' => ['required', 'min:8', 'same:password'],
        ]);

        try {

            DB::transaction(function () use ($request) {
                User::where('id', Auth::id())->update(['password'=>Hash::make($request->password)]);
            });

            return back()->with('success', 'Password Changed');
        } catch (\Throwable $th) {
            return back()->with('success', 'Failed to change password');
        }
    }

    function validateUser(Request $request)
    {
        $request->validate([
            'designation' => ['required']
        ]);

        $roles1 = collect([2, 3]);
        $roles2 = collect([1, 4, 5]);

        if($roles2->contains($request->designation))
            $request->validate([
                'institute' => ['required']
            ]);

        if($roles1->contains($request->designation))
            $request->validate([
                'institute' => ['required'],
                'program' => ['required']
            ]);
    }

    function returnCollections($collect, $callback) 
    {
        $col = collect([]);
        foreach ($collect as $key => $value) {
            $callback($value) ? $col->push($callback($value)) : '';
        }
        return $col;
    }

    function getUsers($search)
    {
        if($search)
            $user = User::where('username', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('first_name', 'LIKE', "%{$search}%")
                ->get(['id', 'avatar', 'first_name', 'last_name']);
        else
            $user = User::all();

        return $user->map(function ($value) {
            return collect([
                'id' => $value->id,
                'name' => $value->getFullName(),
                'avatar' => $value->avatar
            ]);
        });
    }

    function getRequests($removed = false, $search = null)
    {
        $requests = Registration::query()
            ->when($search, function ($query, $search) {
                $query->where('username', 'LIKE', "%{$search}%")
                ->whereNull('removed')
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->whereNull('removed')
                ->orWhere('first_name', 'LIKE', "%{$search}%")
                ->whereNull('removed');
            })
            ->whereNull('removed')
            ->get(['id', 'first_name', 'last_name'])
            ->map(function ($value) {
                $user = collect([]);
                $user['id'] = $value->id;
                $user['name'] = $value->getFullName();
                return $user;
            });

        $requests_removed = Registration::query()
            ->when($search, function ($query, $search) {
                $query->where('username', 'LIKE', "%{$search}%")
                ->whereNotNull('removed')
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->whereNotNull('removed')
                ->orWhere('first_name', 'LIKE', "%{$search}%")
                ->whereNotNull('removed');
            })
            ->whereNotNull('removed')
            ->get(['id', 'first_name', 'last_name'])
            ->map(function ($value) {
                $user = collect([]);
                $user['id'] = $value->id;
                $user['name'] = $value->getFullName();
                return $user;
            });

        return !$removed ? $requests : $requests_removed;
    }

    function processFile($folder, $file)
    {
        $path = storage_path('app/tmp/'.$folder.'/'.$file);

        if(file_exists($path))
        {
            copy($path, public_path('storage/avatar/'.$file));
            unlink($path);
            rmdir(storage_path('app/tmp/'.$folder));

            return true;
        }

        return false;
    }

}
