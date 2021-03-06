<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // $data = Post::all();

        // return response(['data' => $data, 'msg'=> 'data berhasil didapatkan']);

        // return response(['data' => $request->all()]);

        $email = $request->email;
        $password = $request->password;

        // if (empty($email) or empty($password)) {
        //     return response(['success' => false, 'message' => 'Some field are empty!'], 422);
        // }

        $client = new Client();

        try {
            $response = $client->request('POST', config('client.login_endpoint'), [
                'form_params' => [
                    "client_secret" => config('client.client_secret'),
                    "grant_type" => "password",
                    "client_id" => config('client.client_id'),
                    "username" => $request->email,
                    "password" => $request->password,
                ]
            ]);
            
            return json_decode($response->getBody(), true);
            // return response(['success' => true, 'data' => json_decode($response->getBody(), true)], 200);
            // return $response->getBody();
        } catch (BadResponseException $e) {
            return response(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function register(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        // Check if field is not empty
        if (empty($name) or empty($email) or empty($password)) {
            return response(['succes' => false, 'status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response(['succes' => false, 'status' => 'error', 'message' => 'You must enter a valid email']);
        }

        // Check if password is greater than 5 character
        // if (strlen($password) < 6) {
        //     return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        // }

        // Check if user already exist
        if (User::where('email', '=', $email)->exists()) {
            return response(['succes' => false, 'status' => 'error', 'message' => 'User already exists with this email']);
        }

        // Create new user
        try {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = app('hash')->make($password);

            if ($user->save()) {
                // Will call login method
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        try {
            auth()->user()->tokens()->each(function ($token) {
                $token->delete();
            });

            return response(['status' => 'success', 'message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response(['succes' => false, 'status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
