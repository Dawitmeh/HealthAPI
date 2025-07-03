<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $subscriptions = Subscription::with('plan', 'user')->get();

            return response()->json([
                'data' => $subscriptions
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'plan_id' => 'required|exists:subscription_plans,id'
            ]);

           $plan = SubscriptionPlan::findOrFail($data['plan_id']);
            
            $startDate = Carbon::now();
            $endDate = match ($plan->plan_type) {
                'trial'   => $startDate->copy()->addDays(7),
                'daily'   => $startDate->copy()->addDay(),
                'monthly' => $startDate->copy()->addMonth(),
                'yearly'  => $startDate->copy()->addYear(),
                default   => throw new Exception("Invalid plan type: " . $plan->plan_type),
            };

            $subscription = Subscription::create([
                'user_id'    => $data['user_id'],
                'plan_id'    => $plan->id,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);

            return response()->json([
                'data' => $subscription
            ], 201);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $subscription = Subscription::with('plan', 'user')->where('id', $id)->first();

            return response()->json([
                'data' => $subscription
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'plan_id' => 'required|exists:subscription_plans,id'
            ]);

            $subscription = Subscription::findOrFail($id);
            $plan = SubscriptionPlan::where('id', $data['plan_id'])->first();

            $startDate = Carbon::now();
            $endDate = match ($plan->type) {
                'trial'   => $startDate->copy()->addDays(7),
                'daily'   => $startDate->copy()->addDay(),
                'monthly' => $startDate->copy()->addMonth(),
                'yearly'  => $startDate->copy()->addYear(),
                default   => throw new Exception("Invalid plan type: " . $plan->type),
            };

            $subscription->update([
                'user_id' => $data['user_id'],
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return response()->json([
                'data' => $subscription
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $subscription->delete();

            return response()->json([
                'message' => 'The subscription is deleted'
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
