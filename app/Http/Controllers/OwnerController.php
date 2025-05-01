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

    public function register(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:50|unique:users,username',
        'email' => 'required|string|email|max:100|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'image'=>'required|image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',

    ]);
    $image= str::random(32) . "." . $request->image->getClientOriginalExtension();


    $user = User::create([
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'registration_date' => now(),
        'image' => $image,
        'user_type' => 'land_owner',
    ]);

    Owner::create([
        'user_id' => $user->id,

//            'id_number' =>$user->id_number,
//            'estate_number'=>$user->estate_number,
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
        'message' => 'Owner logged in successfully',
        'user' => $user,
        'token' => $token,
    ]);
//        return $this->getData('Owner logged in successfully','Owner',$user);

}


    public function completeProfile(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'estate_number' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        $user = Auth::user(); // أو JWT::user()

        // رفع الصورة إن وُجدت بنفس طريقة register
        if ($request->hasFile('image')) {
            $imageName = Str::random(32) . "." . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->image));
            $user->image = $imageName;
        }

        // تحديث بيانات user
        $user->phone_number = $request->phone_number;
        $user->address = $request->address;
        $user->save();

        // تحديث بيانات owner
        $owner = Owner::where('user_id', $user->id)->first();
        if ($owner) {
            $owner->estate_number = $request->estate_number;
            $owner->id_number = $request->id_number;
            $owner->save();
        }

        $user->load('owner');

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = Auth::user(); // أو JWT::user() إن كنت تستخدم jwt

        // تحميل العلاقة مع owner
        $user->load('owner');

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'user' => $user
        ]);
    }




    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

//        return response()->json([
//            'message' => 'Logged out successfully.'
//        ]);
        return $this->getSuccess('Logged out successfully.');
    }



}
