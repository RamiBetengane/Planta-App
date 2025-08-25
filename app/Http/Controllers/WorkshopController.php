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

        // 3. حفظ صورة المستخدم إذا كانت موجودة
        if ($request->hasFile('image')) {
            $imageName = Str::random(32) . "." . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->image));
            $user->image = $imageName;
            $user->save();
        }

        // 4. إنشاء الورشة المرتبطة بالمستخدم (القيم الأخرى null) مع وضع status = pending
        $workshop = Workshop::create([
            'user_id' => $user->id,
            'workshop_name' => null,
            'license_number' => null,
            'years_of_experience' => null,
            'rating' => null,
            'specialization' => null,
            'status' => 'pending', // تلقائيًا pending
            'rejection_reason' => null,
        ]);

        // 5. إنشاء توكن للمصادقة
        $token = $user->createToken('workshop-token')->plainTextToken;

        // 6. الرد
        return response()->json([
            'message' => 'Workshop registered successfully. Await admin approval.',
            'user' => $user->load('workshop'), // نرجع بيانات الورشة مع المستخدم
            'token' => $token
        ], 201);
    }

    /*
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

    */

    public function login(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. البحث عن المستخدم مع التأكد أنه workshop وجلب بيانات الورشة
        $user = User::with('workshop')
            ->where('email', $request->email)
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
            'user' => $user, // يحتوي على بيانات الـ user + الورشة عبر العلاقة
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
            'workshop' => $user->workshop
        ]);
        return $this->getData('Getting workshop profile successfully', 'Workshop', $data);
    }


    public function completeWorkshopProfile(Request $request)
    {
        $user = $request->user(); // المستخدم الحالي

        // 1. التحقق من البيانات
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'specialization' => 'nullable|string|max:100',
            'workshop_name' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50',
            'user_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'workshop_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 2. تحديث بيانات المستخدم
        $user->phone_number = $request->phone_number;
        $user->address = $request->address;

        if ($request->hasFile('user_image')) {
            $imageName = Str::random(32) . "." . $request->user_image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->user_image));
            $user->image = $imageName;
        }

        $user->save();

        // 3. تحديث بيانات الورشة المرتبطة بالمستخدم
        $workshop = $user->workshop;

        if (!$workshop) {
            return response()->json(['message' => 'Workshop not found for this user'], 404);
        }

        if ($request->has('years_of_experience')) $workshop->years_of_experience = $request->years_of_experience;
        if ($request->has('rating')) $workshop->rating = $request->rating;
        if ($request->has('specialization')) $workshop->specialization = $request->specialization;
        if ($request->has('workshop_name')) $workshop->workshop_name = $request->workshop_name;
        if ($request->has('license_number')) $workshop->license_number = $request->license_number;

        if ($request->hasFile('workshop_image')) {
            $workshopImageName = Str::random(32) . "." . $request->workshop_image->getClientOriginalExtension();
            Storage::disk('public')->put($workshopImageName, file_get_contents($request->workshop_image));
            $workshop->image = $workshopImageName;
        }

        $workshop->save();

        // 4. الرد بالبيانات الجديدة
        return response()->json([
            'status' => 200,
            'message' => 'Workshop profile updated successfully',
            'user' => $user->load('workshop')
        ]);
    }
    public function getAllTenders()
    {
        // استرجاع كل المناقصات مع البيانات المرتبطة
        $tenders = \App\Models\Tender::with([

        ])->get();

        return response()->json([
            'status' => 200,
            'message' => 'All tenders retrieved successfully',
            'data' => $tenders
        ]);
    }
    public function getTenderById($id)
    {
        // جلب المناقصة مع الـ Request المرتبط، الأرض، وطلبات النباتات مع بيانات النباتات
        $tender = \App\Models\Tender::with([
            'request',
            'request.land',
            'request.plant_requests.plant'
        ])->find($id);

        if (!$tender) {
            return response()->json([
                'status' => 404,
                'message' => 'Tender not found'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Tender details retrieved successfully',
            'data' => $tender
        ]);
    }


}
