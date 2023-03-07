<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public $pepper;

    public function __construct()
    {
        $this->pepper = "Dn$(@((reDitaion2023!@#";
    }

    public function index()
    {
        return Inertia::render('Auth/Index');
    }

    public function login(Request $request)
    {   
        if(Auth::check())
            return back()->with('success', true);

        $credentials = $request->validate([
            'username' => ['required', 'min:8', 'exists:users,username'],
            'password' => ['required', 'min:8'],
        ],[
            'username.exists' => 'This username is pending for admin confirmation or unregistered',
        ]);

        try {
            $validUsername = User::where('username', $request->username)->first();

            if(Auth::attempt($credentials))
                return back()->with('success', true);
            else
                return back()->with('error', 'invalid');

        }catch(\Throwable $e){
            return back()->with('error', false);
        }
    }

    public function apiLogin(Request $request)
    {   

        /* $credentials = Validator::make($request->all(), [
            'username' => ['required', 'min:8', 'exists:users,username'],
            'password' => ['required', 'min:8'],
        ],[
            'username.exists' => 'The username does not exist',
        ]);
        
        if ($credentials->fails())
            return response()->json($credentials->errors(), 400);

        $encrypt = Crypt::encryptString($this->pepper.$request->password.$this->pepper);//(new Encrypter($this->pepper, 'aes-128-cbc'))->encrypt($request->password);
        
        try {
            if(Auth::attempt($credentials->validated())){
                $save_apiToken = User::find(Auth::id());
                $save_apiToken->api_token = $request->_token;
                $save_apiToken->save();

                return $encrypt;
            }else
                return response()->json(['error' => 'Invalid credentials']);

        }catch(\Throwable $e){
            return response()->json(['error' => 'Something went wrong!']);
        } */
    }

    public function redirectedLogin(Request $request)
    {   
        $decryptPass = Crypt::decryptString($request->password);
        $decryptUser = Crypt::decryptString($request->username);

        $request['password'] = str_replace($this->pepper, '', $decryptPass);
        $request['username'] = str_replace($this->pepper, '', $decryptUser);

        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required', 'min:8'],
        ]);

        try {
            if(Auth::attempt($credentials)){                

                return $this->verifyUserAuth($request);
            }else
                return response()->json(['error' => "error"]);

        }catch(\Throwable $e){
            return response()->json(['error' => 'Something went wrong!']);
        }
    }

    public function verifyUserAuth(Request $request, $api_token = null)
    {

        if(Auth::user()->auth === 5){
            return redirect('/accreditor/verify');
        }else{
            if($api_token){
                $request['_token'] = $api_token;
                return $this->redirects($request);
            }
            return redirect()->route('dashboard');
        }
    }

    public function redirects(Request $request)
    {        
        if($request->_token)
        {
            $token = User::where('api_token', $request->_token)->first();
            User::where('id', $token->id)->update(['api_token' => null]);

            return Inertia::render('PageRedirect', [
                'authU' => Crypt::encryptString($this->pepper . $token->username . $this->pepper),
                'authA' => $request->user,
                'token' => $request->_token
            ]);
        }

        return Inertia::render('PageRedirect', ['errorPage' => 'something went wrong']);
        
        //return to login page
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required'],
            'last_name' => ['required'],
            'username' => ['required', 'min:8', 'unique:users,username', 'unique:registrations,username'],
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
            $this->storeRegistration($request);
            return back()->with('success', 'Resistered successfully');
        }catch(\Throwable $e){
            return back()->with('error', 'Failed to register');
        }
    }

    public function logOut()
    {
        Auth::logout();

        return redirect()->route('index');
    }

    public function apiVerifyRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'username' => ['required', 'min:8', 'unique:users,username', 'unique:registrations,username'],
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
        
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        else{
            try {
                $this->storeRegistration($request);
    
                return response()->json('success');
            }catch (\Throwable $th) {
                return response()->json('error', 400);
            }
        }
    }

    public function change_pass(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => ['required'],
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
    
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
    
        try {
            return response()->json(
                User::where('username', $request->username)
                    ->update(['password' => Hash::make($request->password)])
            );
        }catch (\Throwable $th) {
            return response()->json('error', 400);
        }
    }

    public function verifyUsername(Request $request)
    {
        $request->validate([
            'username' => ['required', 'min:8', 'exists:users,username']
        ], [
            'username.exists' => 'The username does not exist',
        ]);

        try {
            return response()->json(
                User::where('username', $request->username)
                ->first(['username'])->username
            );
        } catch (\Throwable $th) {
            return response()->json('error', 400);
        }
    }

    public function changeMyPassword(Request $request)
    {
        $request->validate([
            'username' => ['required', 'min:8', 'exists:users,username'],
            'new_password' => [
                'required',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'confirm_password' => ['required', 'min:8', 'same:new_password']
        ], [
            'username.exists' => 'The username does not exist',
        ]);

        try{

            DB::transaction(function() use ($request) {
                User::where('username', $request->username)
                    ->update(['password' => Hash::make($request->new_password)]);
            });

            return redirect()->route('index')->with('success', 'Password changed successfully');
        } catch (\Throwable $th) {
            return back();
        }

    }

    function storeRegistration(Request $request) {
        DB::transaction(function () use ($request) {
            Registration::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);
        });
    }
}
