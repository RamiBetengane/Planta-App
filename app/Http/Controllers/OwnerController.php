<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Owner;

class OwnerController extends Controller
{
    use ResponseTrait;
/*
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string',
        ]);

        // إنشاء مستخدم جديد
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'registration_date' => now(),
            'user_type' => 'land_owner',
        ]);

        // إنشاء سجل خاص بالمالك
        Owner::create([
            'user_id' => $user->id,
            // ضع هنا الحقول الخاصة بالمالك لو في
        ]);

        // إنشاء توكن
        $token = $user->createToken('owner-token')->plainTextToken;
//
//        return response()->json([
//            'message' => 'Owner registered successfully.',
//            'user' => $user,
//            'token' => $token,
//        ], 201);
        return $this->getData('Owner registered successfully','Owner',$user);
    }

*/

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
            'user_type' => 'land_owner',
        ]);

        Owner::create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('owner-token')->plainTextToken;
        Storage::disk('public')->put($image,file_get_contents($request->image));

        return $this->getData('Owner registered successfully', 'Owner', $user);
    }


    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->where('user_type', 'land_owner')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid username or password.'],
            ]);
        }

        $token = $user->createToken('owner-token')->plainTextToken;

        return response()->json([
            'status'=>200,
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
        ]);
//        return $this->getData('Owner logged in successfully','Owner',$user);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

//        return response()->json([
//            'message' => 'Logged out successfully.'
//        ]);
        return $this->getSuccess('Logged out successfully.');
    }

    public function profile()
    {
        // جلب البيانات للمستخدم المتصل
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
        return $this->getData('Getting profile successfully','Owner',$data);
    }

    public function updatePersonalInfo(Request $request)
    {
        // التحقق من وجود المستخدم المتصل
        $user = Auth::user();

        // التحقق من الحقول المدخلة
        $validated = $request->validate([
            'email' => 'required|email',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // تحديث البيانات
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'];
        $user->address = $validated['address'] ?? $user->address; // إذا لم يتم إدخال عنوان، يبقى كما هو
        $user->save();

//        return response()->json([
//            'message' => 'Personal information updated successfully',
//            'user' => $user,
//        ]);
        return $this->getData('Personal information updated successfully','Owner',$user);

    }
}
