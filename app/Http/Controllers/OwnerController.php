<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Land;
use App\Models\Plant;
use App\ResponseTrait;
use HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    return $this->getData('Owner registered successfully', 'User', $user);
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

//        return response()->json([
//            'message' => 'Profile updated successfully',
//            'user' => $user
//        ]);
        return $this->getData('Profile updated successfully','user',$user);
    }



    public function getProfile(Request $request)
    {
        $user = Auth::user(); // أو JWT::user()

        $user->load('owner');

        // دمج بيانات owner مع user
        $merged = collect($user)->merge([
            'owner_id' => $user->owner->id ?? null,
            'id_number' => $user->owner->id_number ?? null,
            'estate_number' => $user->owner->estate_number ?? null,
            'owner_created_at' => $user->owner->created_at ?? null,
            'owner_updated_at' => $user->owner->updated_at ?? null,
        ])->except(['owner']); // نحذف المفتاح "owner" الأساسي

        return $this->getData('Profile retrieved successfully', 'owner', $merged);
    }




    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

//        return response()->json([
//            'message' => 'Logged out successfully.'
//        ]);
        return $this->getSuccess('Logged out successfully.');
    }


    public function addLand(Request $request)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'total_area' => 'required|numeric',
            'land_type' => 'required|in:private,government,unused',
            'soil_type' => 'required|string',
            'status' => 'required|in:available,reserved,planted,inactive',
            'description' => 'required|string',
            'water_source' => 'required|string',
            'owner_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // توليد اسم عشوائي للصورة مع الامتداد
            $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();

            // حفظ الصورة في مجلد public باستخدام Storage
            Storage::disk('public')->put($imageName, file_get_contents($request->image));

            // تخزين اسم الصورة فقط في قاعدة البيانات
            $validated['image'] = $imageName;
        }

        $land = Land::create($validated);

        return $this->getData('Land created successfully.', 'land', $land);
    }

    public function getAllLands(){
        $lands = Land::all();
        if(!$lands){
            return $this->getError(401,'Not found any lands');
        }
        else{
            return $this->getData('Getting lands successfully','lands',$lands);

        }
    }

    public function getLnadById($id){
        $land = Land::find($id);
        if(!$land){
            return $this->getError(401,'Not found any lands');
        }
        else{
            return $this->getData('Getting land successfully','land',$land);

        }
    }
    public function getAllPlants()
    {
        $plants = Plant::all();

        if ($plants->isEmpty()) {
            return $this->getError(401, 'No plants found.');
        }

        return $this->getData('Plants retrieved successfully', 'plants', $plants);
    }

    public function getPlantById($id)
    {
        $plant = Plant::find($id);

        if (!$plant) {
            return $this->getError(401, 'Plant not found.');
        }

        return $this->getData('Plant retrieved successfully', 'plant', $plant);
    }
    public function addRequest(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => 'required|exists:users,id',
            'land_id' => 'required|exists:lands,id',
            'notes' => 'nullable|string',
            'plants' => 'required|array|min:1',
            'plants.*.plant_id' => 'required|exists:plants,id',
            'plants.*.quantity' => 'required|integer|min:1',
        ]);

        // جلب الأرض والتحقق من ملكيتها
        $land = Land::where('id', $validated['land_id'])
            ->where('owner_id', $validated['owner_id'])
            ->first();

        if (!$land) {
            return response()->json(['message' => 'الأرض غير موجودة أو لا تتبع لهذا المستخدم'], 404);
        }

        // حساب المساحة المطلوبة لكل نبات وجمعها
        $totalRequestedArea = 0;
        $plantData = [];

        foreach ($validated['plants'] as $plantInput) {
            $plant = Plant::find($plantInput['plant_id']);
            $quantity = $plantInput['quantity'];
            $areaForThisPlant = $quantity * $plant->required_area;

            $totalRequestedArea += $areaForThisPlant;
            $plantData[$plant->id] = ['quantity' => $quantity];
        }

        // التحقق من مساحة الأرض
        if ($totalRequestedArea > $land->total_area) {
            return response()->json([
                'message' => 'المساحة المطلوبة تتجاوز مساحة الأرض المتوفرة',
                'required_area' => $totalRequestedArea,
                'available_area' => $land->total_area
            ], 422);
        }

        // تخزين الطلب وربط النباتات
        DB::beginTransaction();
        try {
            $newRequest = \App\Models\Request::create([
                'land_id' => $land->id,
                'notes' => $validated['notes'] ?? null,
                'area' => $totalRequestedArea,
                'status' => 'pending'
            ]);

            $newRequest->plants()->attach($plantData);

            DB::commit();

            return response()->json([
                'message' => 'created request successfully',
                'data' => $newRequest->load('plants')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'فشل في إنشاء الطلب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllRequests()
    {
        $requests = \App\Models\Request::with(['plants', 'land'])->get();

        return response()->json([
            'message' => 'قائمة الطلبات',
            'data' => $requests
        ]);
    }

}
