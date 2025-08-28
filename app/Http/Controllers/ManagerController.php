<?php

namespace App\Http\Controllers;
use App\Models\Manager;
use App\Models\Tender;
use App\Models\Workshop;
use HttpRequest;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PlantRequest;

class ManagerController extends Controller
{
    use ResponseTrait;


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('user_type', 'manager')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid email or password.'],
            ]);
        }

        $token = $user->createToken('manager-token')->plainTextToken;

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

        return $this->getData('Profile fetched successfully.', 'User', $data);
    }



    public function updatePersonalInfo(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'department' => 'nullable|string|max:50',
            'position' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp,svg|max:2048', // max 2MB
        ]);

        $user = Auth::user();

        if ($request->hasFile('image')) {
            $imageName = Str::random(32) . '.' . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->image));
            $user->image = $imageName;
        }

        $user->phone_number = $request->phone_number;
        $user->address = $request->address;
        $user->save();

        $manager = Manager::firstOrNew(['user_id' => $user->id]);
        $manager->department = $request->department;
        $manager->position = $request->position;
        $manager->save();

        $user->load('manager');

        return $this->getData('Profile completed successfully', 'user', $user);
    }


//
//    public function reviewRequest(Request $request, $id)
//    {
//        $request->validate([
//            'status' => 'required|in:approved,rejected',
//            'rejection_reason' => 'required_if:status,rejected|string|max:1000',
//        ]);
//
//        $plantingRequest = PlantRequest::findOrFail($id);
//
//        $plantingRequest->status = $request->status;
//
//        if ($request->status === 'rejected') {
//            $plantingRequest->rejection_reason = $request->rejection_reason;
//        } else {
//            $plantingRequest->rejection_reason = null;
//        }
//
//        $plantingRequest->save();
//
//        return $this->getData('Request reviewed successfully.', 'data', $plantingRequest);
//
//    }
    public function reviewRequest(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:1000',
        ]);

        $requestModel = \App\Models\Request::with('land')->findOrFail($id);

        $requestModel->status = $request->status;

        if ($request->status === 'rejected') {
            $requestModel->rejection_reason = $request->rejection_reason;
        } else {
            $requestModel->rejection_reason = null;
        }

        $requestModel->save();

        // رجع الريكويست + بيانات الأرض
        return $this->getData('Request reviewed successfully.', 'data', $requestModel->load('land'));
    }




    public function getRequestById($id)
    {
        $requestModel = \App\Models\Request::with([
            'land',
            'plants' => function ($query) {
                // هون خلينا كل الأعمدة من جدول plants
                $query->withPivot('id', 'quantity', 'line_number', 'request_id', 'plant_id');
            }
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Request details',
            'request' => $requestModel
        ]);
    }


/*

    public function createTender(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:requests,id|unique:tenders,request_id',
            'manager_id' => 'required|exists:managers,id',
            'creation_date' => 'required|date',
            'open_date' => 'required|date',
            'close_date' => 'required|date',
            'status' => 'required|in:open,closed,awarded',
            'technical_requirements' => 'nullable|string',
            'tender_title' => 'nullable|string',
        ]);

        $tender = Tender::create($validated);


        // تحميل البيانات المرتبطة (PlantRequests + Plant + Request + Land)
        $tender->load([
            'request.land',
            'request.plantRequests.plant'
        ]);

        return response()->json([
            'message' => 'Tender created successfully',
            'tender_detail' => $tender,
            'plant_requests' => $tender->request->plantRequests
        ]);
    }

*/

    public function createTender(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'request_id' => 'required|exists:requests,id|unique:tenders,request_id',
            'manager_id' => 'required|exists:managers,id',
            'creation_date' => 'required|date',
            'open_date' => 'required|date',
            'close_date' => 'required|date',
            'status' => 'required|in:open,closed,awarded',
            'technical_requirements' => 'nullable|string',
            'tender_title' => 'nullable|string',
        ]);

        // Create Tender
        $tender = Tender::create($validated);

        // Load related data (Request + Land + PlantRequests + Plant)
        $tender->load([
            'request.land',
            'request.plantRequests.plant'
        ]);

        // Return clean response
        return response()->json([
            'message' => 'Tender created successfully',
            'tender_detail' => [
                'id' => $tender->id,
                'tender_title' => $tender->tender_title,
                'request_id' => $tender->request_id,
                'manager_id' => $tender->manager_id,
                'creation_date' => $tender->creation_date,
                'open_date' => $tender->open_date,
                'close_date' => $tender->close_date,
                'status' => $tender->status,
                'technical_requirements' => $tender->technical_requirements,
                'request' => [
                    'id' => $tender->request->id,
                    'status' => $tender->request->status,
                    'notes' => $tender->request->notes,
                    'area' => $tender->request->area,
                    'rejection_reason' => $tender->request->rejection_reason,
                    'land' => $tender->request->land,
                    'plant_requests' => $tender->request->plantRequests
                ]
            ]
        ]);
    }

    public function getAllTenders()
    {
        try {
            $tenders = Tender::with([
                'request.plants'  // جلب الـ request المرتبط بكل tender مع النباتات المرتبطة
            ])->get();

            return $this->getData('Tenders fetched successfully.', 'tenders', $tenders);

        } catch (\Exception $e) {
            return $this->getError(500, 'Server error: ' . $e->getMessage());
        }
    }

    public function getTenderById($id)
    {
        try {
            // جلب tender مع request وplants المرتبطة به
            $tender = Tender::with([
                'request.plants'  // كل tender له request واحد مع النباتات المرتبطة
            ])->findOrFail($id);

            return $this->getData('Tender fetched successfully.', 'tender', $tender);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->getError(404, 'Tender not found.');
        } catch (\Exception $e) {
            return $this->getError(500, 'Server error: ' . $e->getMessage());
        }
    }


    public function getError($status, $msg)
    {
        return response()->json([
            'status' => $status,
            'message' => $msg,
        ], $status);
    }

    public function getData($message, $key, $value)
    {
        return response()->json([
            'message' => $message,
            $key => $value
        ]);
    }


    public function getAllApprovedReq()
    {
        $requests = \App\Models\Request::with(['land', 'plants']) // إذا عندك علاقات
        ->where('status', 'approved')
            ->get();

        return response()->json([
            'message' => 'Approved Requests List',
            'requests' => $requests
        ]);
    }


    public function getAllRejectedReq()
    {
        $requests = \App\Models\Request::with(['land', 'plants']) // إذا عندك علاقات
        ->where('status', 'rejected')
            ->get();

        return response()->json([
            'message' => 'Rejected Requests List',
            'requests' => $requests
        ]);
    }
    public function getAllPendingReq()
    {
        $requests = \App\Models\Request::with(['land', 'plants']) // إذا عندك علاقات
        ->where('status', 'pending')
            ->get();

        return response()->json([
            'message' => 'Pending Requests List',
            'requests' => $requests
        ]);
    }


    public function getAllWorkshops()
    {
        // استرجاع كل الورش مع بيانات المستخدم المرتبطة
        $workshops = \App\Models\Workshop::with('user')->get();

        return response()->json([
            'status' => 200,
            'message' => 'All workshops retrieved successfully',
            'workshop' => $workshops
        ]);
    }
    public function getWorkshopById($id)
    {
        // جلب الورشة مع بيانات المستخدم المرتبط
        $workshop = \App\Models\Workshop::with('user')->find($id);

        if (!$workshop) {
            return response()->json([
                'status' => 404,
                'message' => 'Workshop not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Workshop retrieved successfully',
            'workshop' => $workshop
        ]);
    }
// AdminController.php
    public function evaluateWorkshop(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        // 1. جلب الورشة
        $workshop = Workshop::findOrFail($id);

        // 2. تحديث الحالة
        $workshop->status = $request->status;

        if ($request->status === 'rejected') {
            $workshop->rejection_reason = $request->rejection_reason ?? 'Rejected by admin';
        } else {
            $workshop->rejection_reason = null; // ما في سبب إذا Approved
        }

        $workshop->save();

        // 3. إرجاع الرد
        return response()->json([
            'status' => 200,
            'message' => "Workshop evaluation updated successfully",
            'data' => $workshop->load('user'),
        ]);
    }

    // استرجاع جميع الورش المقبولة
    public function getAllWorkshopsApproved()
    {
        $workshops = Workshop::with('user')->where('status', 'approved')->get();

        return response()->json([
            'status' => 200,
            'message' => 'All approved workshops retrieved successfully',
            'data' => $workshops
        ]);
    }

// استرجاع جميع الورش قيد الانتظار
    public function getAllWorkshopsPending()
    {
        $workshops = Workshop::with('user')->where('status', 'pending')->get();

        return response()->json([
            'status' => 200,
            'message' => 'All pending workshops retrieved successfully',
            'data' => $workshops
        ]);
    }

// استرجاع جميع الورش المرفوضة
    public function getAllWorkshopsRejected()
    {
        $workshops = Workshop::with('user')->where('status', 'rejected')->get();

        return response()->json([
            'status' => 200,
            'message' => 'All rejected workshops retrieved successfully',
            'data' => $workshops
        ]);
    }


}
