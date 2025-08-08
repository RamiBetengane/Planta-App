<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Doner;

class DonerController extends Controller
{
    use ResponseTrait;


    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string',
            'image'=>'required|image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',

        ]);
        $image= str::random(32) . "." . $request->image->getClientOriginalExtension();

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'registration_date' => now(),
            'image' => $image,
            'user_type' => 'donor',

        ]);

        Doner::create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('donor-token')->plainTextToken;
        Storage::disk('public')->put($image,file_get_contents($request->image));

        return $this->getData('Donor registered successfully', 'Donor', $user);
    }


    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->where('user_type', 'donor')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid username or password.'],
            ]);
        }

        $token = $user->createToken('donor-token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->getSuccess('Logged out successfully.');
    }


    public function profile()
    {
        $user = Auth::user();
        $data = response()->json([
            'username' => $user->username,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'address' => $user->address,
            'registration_date' => $user->registration_date,
            'user_type' => $user->user_type,
            'image' => $user->image,
        ]);
        return $this->getData('Getting profile successfully', 'Donor', $data);
    }


    public function updatePersonalInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email' => 'required|email',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'];
        $user->address = $validated['address'] ?? $user->address;
        $user->save();

        return $this->getData('Personal information updated successfully', 'Donor', $user);
    }
}
