<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Workshop;

class WorkshopController extends Controller
{
    use ResponseTrait;

//    public function register(Request $request)
//    {
//        $request->validate([
//            'username' => 'required|string|max:50|unique:users,username',
//            'email' => 'required|string|email|max:100|unique:users,email',
//            'password' => 'required|string|min:6|confirmed',
//            'phone_number' => 'required|string|max:20',
//            'address' => 'nullable|string',
//            'license_number' => 'required|string|max:50',
//            'workshop_name' => 'required|string|max:100',
//            'years_of_experience' => 'required|nullable|integer',
//            'rating' => 'required|nullable|numeric|between:0,5',
//            'specialization' => 'required|nullable|string|max:100',
//            'image'=>'required|image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
//        ]);
//        $image= str::random(32) . "." . $request->image->getClientOriginalExtension();
//
//
//
//        $user = User::create([
//            'username' => $request->username,
//            'email' => $request->email,
//            'password' => Hash::make($request->password),
//            'phone_number' => $request->phone_number,
//            'address' => $request->address,
//            'registration_date' => now(),
//            'image' => $image,
//            'user_type' => 'workshop',
//        ]);
//
//        Workshop::create([
//            'user_id' => $user->id,
//            'years_of_experience' => $request->years_of_experience,
//            'rating' => $request->rating,
//            'specialization' => $request->specialization,
//            'license_number' => $request->license_number,
//            'workshop_name' => $request->workshop_name,
//        ]);
//
//        $token = $user->createToken('workshop-token')->plainTextToken;
//        Storage::disk('public')->put($image,file_get_contents($request->image));
//
//        return $this->getData('Workshop registered successfully', 'Workshop', $user);
//    }
    public function register(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        // 2. إنشاء المستخدم بدون الصورة أولًا
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'registration_date' => now(),
            'user_type' => 'workshop',
        ]);

        // 3. حفظ الصورة إذا كانت موجودة
        if ($request->hasFile('image')) {
            $imageName = Str::random(32) . "." . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->image));
            $user->image = $imageName;
            $user->save();
        }

        // 4. إنشاء الورشة المرتبطة بالمستخدم (القيم الأخرى null)
        $workshop = Workshop::create([
            'user_id' => $user->id,
            'workshop_name' => null,
            'license_number' => null,
            'years_of_experience' => null,
            'rating' => null,
            'specialization' => null,
        ]);

        // 5. إنشاء توكن للمصادقة
        $token = $user->createToken('workshop-token')->plainTextToken;

        // 6. الرد
        return response()->json([
            'message' => 'Workshop registered successfully',
            'user' => $user,
            'workshop' => $workshop,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. البحث عن المستخدم مع التأكد أنه workshop
        $user = User::where('email', $request->email)
            ->where('user_type', 'workshop')
            ->first();

        // 3. التحقق من كلمة المرور
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid email or password.'],
            ]);
        }

        // 4. إنشاء توكن جديد للورشة
        $token = $user->createToken('workshop-token')->plainTextToken;

        // 5. الرد
        return response()->json([
            'status' => 200,
            'message' => 'Workshop logged in successfully',
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
            'Detail' => $user->workshop
        ]);
        return $this->getData('Getting workshop profile successfully', 'Workshop', $data);
    }


    public function updatePersonalInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:50',
            'workshop_name' => 'required|string|max:100',
        ]);

        $user->phone_number = $validated['phone_number'];
        $user->address = $validated['address'] ?? $user->address;
        $user->save();

        $workshop = $user->workshop;
        $workshop->license_number = $validated['license_number'];
        $workshop->workshop_name = $validated['workshop_name'];
        $workshop->save();

        return $this->getData('Workshop personal information updated successfully', 'Workshop', $workshop);
    }


}
