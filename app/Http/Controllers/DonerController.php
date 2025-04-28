<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Doner; // إذا كان لديك موديل خاص بالمتبرع

class DonerController extends Controller
{
    use ResponseTrait;

    /**
     * تسجيل المتبرع.
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string',
        ]);

        // إنشاء مستخدم جديد (متبرع)
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'registration_date' => now(),
            'user_type' => 'donor',
        ]);

        // إنشاء سجل خاص بالمتبرع
        Doner::create([
            'user_id' => $user->id,
            // ضع هنا الحقول الخاصة بالمتبرع إذا كانت موجودة
        ]);

        // إنشاء توكن
        $token = $user->createToken('donor-token')->plainTextToken;

        return $this->getData('Donor registered successfully', 'Donor', $user);
    }

    /**
     * تسجيل دخول المتبرع.
     */
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

    /**
     * تسجيل خروج المتبرع.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->getSuccess('Logged out successfully.');
    }

    /**
     * عرض بيانات الملف الشخصي للمتبرع.
     */
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
        ]);
        return $this->getData('Getting profile successfully', 'Donor', $data);
    }

    /**
     * تحديث المعلومات الشخصية للمتبرع.
     */
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

        return $this->getData('Personal information updated successfully', 'Donor', $user);
    }
}
