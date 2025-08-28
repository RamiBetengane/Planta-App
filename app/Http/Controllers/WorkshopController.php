<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferDetail;
use App\Models\Plant;
use App\Models\PlantRequest;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'registration_date' => now(),
            'user_type' => 'workshop',
        ]);

        if ($request->hasFile('image')) {
            $imageName = Str::random(32) . "." . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->image));
            $user->image = $imageName;
            $user->save();
        }

        $workshop = Workshop::create([
            'user_id' => $user->id,
            'workshop_name' => null,
            'license_number' => null,
            'years_of_experience' => null,
            'rating' => null,
            'specialization' => null,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        $token = $user->createToken('workshop-token')->plainTextToken;


        return response()->json([
            'message' => 'Workshop registered successfully. Await admin approval.',
            'user' => $user->load('workshop'),
            'token' => $token
        ], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('workshop')
            ->where('email', $request->email)
            ->where('user_type', 'workshop')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid email or password.'],
            ]);
        }

        $token = $user->createToken('workshop-token')->plainTextToken;

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
            'workshop' => $user->workshop
        ]);
        return $this->getData('Getting workshop profile successfully', 'Workshop', $data);
    }


    public function completeWorkshopProfile(Request $request)
    {
        $user = $request->user();

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


        $user->phone_number = $request->phone_number;
        $user->address = $request->address;

        if ($request->hasFile('user_image')) {
            $imageName = Str::random(32) . "." . $request->user_image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($request->user_image));
            $user->image = $imageName;
        }

        $user->save();

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

        return response()->json([
            'status' => 200,
            'message' => 'Workshop profile updated successfully',
            'user' => $user->load('workshop')
        ]);
    }
    public function getAllTenders()
    {
        $tenders = \App\Models\Tender::all();

        return response()->json([
            'status' => 200,
            'message' => 'All tenders retrieved successfully',
            'tender' => $tenders
        ]);
    }
    public function getTenderById($id)
    {
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
            'tender' => $tender
        ]);
    }


    public function createOffer(Request $request)
    {
        $validated = $request->validate([
            'tender_id' => 'required|exists:tenders,id',
            'workshop_id' => 'required|exists:workshops,id',
            'estimated_completion' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'plants' => 'required|array|min:1',
            'plants.*.plant_id' => 'required|exists:plants,id',
            'plants.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $totalOfferAmount = 0;


            $offer = Offer::create([
                'tender_id' => $validated['tender_id'],
                'workshop_id' => $validated['workshop_id'],
                'estimation_completion' => $validated['estimated_completion'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'total_offer_amount' => 0,
            ]);

            $offerDetails = [];

            foreach ($validated['plants'] as $plantData) {
                $plantRequest = PlantRequest::where('request_id', $validated['tender_id'])
                ->where('plant_id', $plantData['plant_id'])
                    ->first();

                if (!$plantRequest) {
                    continue;
                }

                $quantity = $plantRequest->quantity;
                $unitPrice = $plantData['unit_price'];
                $totalPrice = $quantity * $unitPrice;

                $totalOfferAmount += $totalPrice;

                $detail = OfferDetail::create([
                    'offer_id' => $offer->id,
                    'plant_request_id' => $plantRequest->id,
                    'plant_id' => $plantData['plant_id'],
                    'unit_cost' => $unitPrice,
                    'total_cost' => $totalPrice,
                ]);

                $offerDetails[] = $detail;
            }

            $offer->update([
                'total_offer_amount' => $totalOfferAmount,
            ]);

            return response()->json([
                'message' => 'Offer created successfully',
                'offer' => $offer->load('offerDetails.plant'),
                'total_offer_amount' => $totalOfferAmount,
            ], 201);
        });
    }



/*
    public function getAllOffers()
    {
        $offers = Offer::with([
            'workshop',
            'tender',
            'offerDetails.plantRequest.plant'
        ])->get();

        return response()->json([
            'status' => 200,
            'message' => 'All offers retrieved successfully',
            'data' => $offers
        ]);
    }
*/
    public function getAllOffers()
    {
        // جلب كل البيانات من جدول offers فقط
        $offers = \App\Models\Offer::with('offerDetails')->get();

        return response()->json([
            'status' => 200,
            'message' => 'All offers retrieved successfully',
            'offers' => $offers
        ]);
    }





    public function getOfferById($id)
    {
        $offer = Offer::with([
            'tender',
            'offerDetails.plantRequest.plant'
        ])->find($id);

        if (!$offer) {
            return response()->json([
                'status' => 404,
                'message' => 'Offer not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Offer retrieved successfully',
            'offer' => $offer
        ]);
    }
}
