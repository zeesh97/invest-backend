<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\IpRestriction;
use Arr;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class IpRestrictionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $ipRestrictions = IpRestriction::all();
        return Helper::sendResponse($ipRestrictions, 'Ip Restrictions retrieved successfully.', Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'ip_restrictions' => 'required|array',
                'ip_restrictions.*.id' => 'nullable|exists:ip_restrictions,id',
                'ip_restrictions.*.ip_address' => 'required|ip',
                'ip_restrictions.*.type' => ['required', Rule::in(['allow', 'restrict'])],
                'ip_restrictions.*.description' => 'nullable',
            ]);

            DB::beginTransaction();

            $toInsert = [];
            $toUpdate = [];

            foreach ($request->ip_restrictions as $ipRestrictionData) {
                if (isset($ipRestrictionData['id'])) {

                    $updateData = Arr::only($ipRestrictionData, [
                        'ip_address',
                        'type',
                        'description'
                    ]);

                    $toUpdate[] = [
                        'id' => $ipRestrictionData['id'],
                        'fields' => $updateData,
                        'updated_at' => now(),
                    ];
                }
                else {

                    $toInsert[] = array_merge(
                        $ipRestrictionData,
                        [
                            'created_at' => now(),
                        ]
                    );
                }
            }

            if (!empty($toInsert)) {
                IpRestriction::insert($toInsert);
            }

            foreach ($toUpdate as $update) {
                IpRestriction::where('id', $update['id'])->update($update['fields']);
            }

            DB::commit();
            return Helper::sendResponse(null, 'IP Restrictions created/updated successfully', 201);
        } catch (QueryException $e) {
            DB::rollBack();
            return Helper::sendError('Database Error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::sendError('Unexpected Error: ' . $e->getMessage(), 500);
        }
    }


    public function destroy(IpRestriction $ipRestriction): JsonResponse
    {
        $ipRestriction->delete();
        return Helper::sendResponse('', 'Deleted Successfully', 204);
    }
}
