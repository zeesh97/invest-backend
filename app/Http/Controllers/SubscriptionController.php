<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\Subscription;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Subscription::query();
            // $query->with('package');

            if ($request->has('package_id')) {
                $query->where('package_id', $request->package_id);
            }

            if ($request->has('start_date')) {
                $query->whereDate('start_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('end_date', '<=', $request->end_date);
            }

            if ($request->has('expired_at')) {
                $query->whereDate('expired_at', '<=', $request->expired_at);
            }

            return response()->json($query->paginate($request->get('per_page', 10)),  200);
        } catch (\Exception $e) {
            return Helper::sendError('Error retrieving subscriptions: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'subscriptions' => 'required|array',
                'subscriptions.*.id' => 'nullable|exists:subscriptions,id',
                'subscriptions.*.package_id' => 'nullable|exists:packages,id',
                'subscriptions.*.start_date' => 'required|date_format:Y-m-d',
                'subscriptions.*.end_date' => 'required|date_format:Y-m-d|after:subscriptions.*.start_date',
                'subscriptions.*.price' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
                'subscriptions.*.number_of_transactions' => 'required|numeric',
                'subscriptions.*.data_mb' => 'required|numeric',
                'subscriptions.*.total_users' => 'required|numeric',
                'subscriptions.*.login_users' => 'required|numeric',
                'subscriptions.*.transaction_id' => 'nullable|exists:transactions,id',
            ]);

            DB::beginTransaction();

            $toInsert = [];
            $toUpdate = [];

            foreach ($request->subscriptions as $subscriptionData) {
                if (isset($subscriptionData['id'])) {

                    $updateData = Arr::only($subscriptionData, [
                        'package_id',
                        'start_date',
                        'end_date',
                        'price',
                        'number_of_transactions',
                        'data_mb',
                        'total_users',
                        'login_users',
                        'transaction_id'
                    ]);

                    $toUpdate[] = [
                        'id' => $subscriptionData['id'],
                        'fields' => $updateData,
                        'updated_at' => now(),
                    ];
                } else {
                    $toInsert[] = array_merge(
                        $subscriptionData,
                        [
                            'usage_number_of_transactions' => 0,
                            'usage_data_mb' => 0,
                            'usage_total_users' => 0,
                            'usage_login_users' => 0,
                            'expired_at' => null,
                        ]
                    );
                }
            }

            if (!empty($toInsert)) {
                Subscription::insert($toInsert);
            }

            foreach ($toUpdate as $update) {
                Subscription::where('id', $update['id'])->update($update['fields']);
            }

            DB::commit();
            return Helper::sendResponse(null, 'Subscriptions created/updated successfully', 201);
        } catch (QueryException $e) {
            DB::rollBack();
            return Helper::sendError('Database Error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::sendError('Unexpected Error: ' . $e->getMessage(), 500);
        }
    }

    public function showCurrentSubscriptions()
    {
        $subscriptions = Subscription::whereNull('expired_at')
        // ->where('start_date', '<=', now())
            ->get();
        $total_price = 0;
        $total_number_of_transactions = 0;
        $total_data_mb = 0;
        $total_total_users = 0;
        $total_login_users = 0;
        $total_usage_number_of_transactions = 0;
        $total_usage_data_mb = 0;
        $total_usage_total_users = 0;
        $total_usage_login_users = 0;

        foreach ($subscriptions as $subscription) {
            $total_price += $subscription->price;
            $total_number_of_transactions += $subscription->number_of_transactions;
            $total_data_mb += $subscription->data_mb;
            $total_total_users += $subscription->total_users;
            $total_login_users += $subscription->login_users;
            $total_usage_number_of_transactions += $subscription->usage_number_of_transactions;
            $total_usage_data_mb += $subscription->usage_data_mb;
            $total_usage_total_users += $subscription->usage_total_users;
            $total_usage_login_users += $subscription->usage_login_users;
        }

        $totals = [
            'price' => number_format($total_price, 2),
            'number_of_transactions' => $total_number_of_transactions,
            'data_mb' => $total_data_mb,
            'total_users' => $total_total_users,
            'login_users' => $total_login_users,
            'usage_number_of_transactions' => $total_usage_number_of_transactions,
            'usage_data_mb' => $total_usage_data_mb,
            'usage_total_users' => $total_usage_total_users,
            'usage_login_users' => $total_usage_login_users,
        ];

        return Helper::sendResponse([
            'subscriptions' => $subscriptions,
            'totals' => $totals,
        ], 'Success', 200);
    }

    public function destroy($id)
    {
        try {
            $subscription = Subscription::find($id);

            if (!$subscription) {
                return Helper::sendError('Subscription not found', 404);
            }

            $subscription->delete();
            return Helper::sendResponse(null, 'Subscription deleted successfully', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Error deleting subscription: ' . $e->getMessage(), 500);
        }
    }
}
