<?php

namespace App\Http\Controllers;
use App\Models\Manager;
use App\Models\Tender;
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

        return $this->getData('Profile fetched successfully.', 'Manager', $data);
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

        return $this->getData('Profile completed successfully', 'Manager', $user);
    }



    public function reviewRequest(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:1000',
        ]);

        $plantingRequest = PlantRequest::findOrFail($id);

        $plantingRequest->status = $request->status;

        if ($request->status === 'rejected') {
            $plantingRequest->rejection_reason = $request->rejection_reason;
        } else {
            $plantingRequest->rejection_reason = null;
        }

        $plantingRequest->save();

        return $this->getData('Request reviewed successfully.', 'data', $plantingRequest);

    }
    public function createTender(Request $request)
    {
        $validated = $request->validate([
            'plant_request_id' => 'required|exists:plant_request,id|unique:tenders,plant_request_id',
            'manager_id' => 'required|exists:managers,id',
            'creation_date' => 'required|date',
            'open_date' => 'required|date|after_or_equal:creation_date',
            'close_date' => 'required|date|after:open_date',
            'status' => 'required|in:open,closed,awarded',
            'technical_requirements' => 'nullable|string',
        ]);

        $tender = Tender::create($validated);

        return $this->getData('Tender created successfully.', 'tender', $tender);

    }

    public function update(Request $request, $id)
    {
        try {
            $tender = Tender::findOrFail($id);

            $validated = $request->validate([
                'plant_request_id' => 'required|exists:plant_request,id|unique:tenders,plant_request_id,' . $id,
                'manager_id' => 'required|exists:managers,id',
                'creation_date' => 'required|date',
                'open_date' => 'required|date|after_or_equal:creation_date',
                'close_date' => 'required|date|after:open_date',
                'status' => 'required|in:open,closed,awarded',
                'technical_requirements' => 'nullable|string',
            ]);

            $tender->update($validated);

            return $this->getData('Tender updated successfully.', 'tender', $tender);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->getError(404, 'Tender not found.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return $this->getError(500, 'Server error: ' . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $tender = Tender::findOrFail($id);
            $tender->delete();

            return $this->getData('Tender deleted successfully.', 'tender', $tender);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->getError(404, 'Tender not found.');
        } catch (\Exception $e) {
            return $this->getError(500, 'Server error: ' . $e->getMessage());
        }
    }


    public function getAllTenders()
    {
        try {
            $tenders = Tender::with(['plantRequest', 'manager'])->get();

            return $this->getData('Tenders fetched successfully.', 'tenders', $tenders);

        } catch (\Exception $e) {
            return $this->getError(500, 'Server error: ' . $e->getMessage());
        }
    }


    public function getTenderById($id)
    {
        try {
            $tender = Tender::with(['plantRequest', 'manager'])->findOrFail($id);

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

}
