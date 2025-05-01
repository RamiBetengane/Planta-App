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

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string',
            'license_number' => 'required|string|max:50', // إضافة رقم الرخصة
            'workshop_name' => 'required|string|max:100',  // إضافة اسم الورشة
            'years_of_experience' => 'required|nullable|integer', // تحقق من عدد سنوات الخبرة (يمكن أن يكون فارغًا)
            'rating' => 'required|nullable|numeric|between:0,5',    // تحقق من التقييم (بين 0 و 5)
            'specialization' => 'required|nullable|string|max:100', // تحقق من التخصص (يمكن أن يكون فارغًا)
            'image'=>'required|image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
        ]);
        $image= str::random(32) . "." . $request->image->getClientOriginalExtension();



        // إنشاء مستخدم جديد
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'registration_date' => now(),
            'image' => $image,
            'user_type' => 'workshop',
        ]);

        // إنشاء سجل خاص بالورشة
        Workshop::create([
            'user_id' => $user->id,
            'years_of_experience' => $request->years_of_experience, // إذا كان موجود في الطلب
            'rating' => $request->rating, // إذا كان موجود في الطلب
            'specialization' => $request->specialization, // إذا كان موجود في الطلب
            'license_number' => $request->license_number,
            'workshop_name' => $request->workshop_name,
        ]);

        // إنشاء توكن
        $token = $user->createToken('workshop-token')->plainTextToken;
        Storage::disk('public')->put($image,file_get_contents($request->image));

        return $this->getData('Workshop registered successfully', 'Workshop', $user);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->where('user_type', 'workshop')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid username or password.'],
            ]);
        }

        $token = $user->createToken('workshop-token')->plainTextToken;

        return response()->json([
            'status'=>200,
            'message' => 'Workshop logged in successfully.',
            'user' => $user,
            'token' => $token,
        ]);
//        return $this->getData('Workshop logged in successfully', 'Workshop', $user, $token);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->getSuccess('Logged out successfully.');
    }

    /**
     * عرض بيانات الملف الشخصي للورشة.
     */
    public function profile()
    {
        // جلب البيانات للمستخدم المتصل (الورشة)
        $user = Auth::user();

        // جلب بيانات الورشة باستخدام العلاقة مع جدول workshops
        // assuming the relationship is defined in the User model
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

    /**
     * تحديث المعلومات الشخصية للورشة.
     */
    public function updatePersonalInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:50',
            'workshop_name' => 'required|string|max:100',
        ]);

        // تحديث بيانات الورشة
        $user->phone_number = $validated['phone_number'];
        $user->address = $validated['address'] ?? $user->address;
        $user->save();

        // تحديث بيانات الورشة المرتبطة
        $workshop = $user->workshop;
        $workshop->license_number = $validated['license_number'];
        $workshop->workshop_name = $validated['workshop_name'];
        $workshop->save();

        return $this->getData('Workshop personal information updated successfully', 'Workshop', $workshop);
    }


}
