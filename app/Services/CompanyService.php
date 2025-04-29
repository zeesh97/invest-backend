<?php

namespace App\Services;

// use App\Http\Resources\LoginUserResource;

use App\Http\Helpers\Helper;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Company;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CompanyService
{

    public function store(array $dataCompany, StoreCompanyRequest $request): JsonResponse
    {

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            $fileName = Str::uuid() . '.' . $logo->getClientOriginalExtension();
            $path = $logo->storeAs('public/profiles/1', $fileName);
            $logoPath = 'storage/profiles/1/' . $fileName;
        }

        $company = Company::create([
            'code' => $dataCompany['code'],
            'name' => $dataCompany['name'],
            'long_name' => $dataCompany['long_name'],
            'ntn_no' => $dataCompany['ntn_no'],
            'sales_tax_no' => $dataCompany['sales_tax_no'],
            'postal_code' => $dataCompany['postal_code'],
            'address' => $dataCompany['address'],
            'phone' => $dataCompany['phone'],
            'logo' => $logoPath,
        ]);


        return Helper::sendResponse($company, "Successfully Added", 201);
    }

    public function update(array $dataCompany, $id, $request): JsonResponse
    {
            $company = Company::findOrFail($id);
            if(!Auth::user()->hasRole('admin') ){
                return Helper::sendError("Unauthorized Access Denied.", [], Response::HTTP_FORBIDDEN);
            }

            $dataToBeUpdate = [
                'code' => $dataCompany['code'] ?? $company->code,
                'name' => $dataCompany['name'] ?? $company->name,
                'long_name' => $dataCompany['long_name'] ?? $company->long_name,
                'ntn_no' => $dataCompany['ntn_no'] ?? $company->ntn_no,
                'sales_tax_no' => $dataCompany['sales_tax_no'] ?? $company->sales_tax_no,
                'postal_code' => $dataCompany['postal_code'] ?? $company->postal_code,
                'address' => $dataCompany['address'] ?? $company->address,
                'phone' => $dataCompany['phone'] ?? $company->phone,
            ];

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');

                $fileName = Str::uuid() . '.' . $logo->getClientOriginalExtension();
                $path = $logo->storeAs('public/profiles/1', $fileName);
                $logoPath = 'storage/profiles/1/' . $fileName;

                $company->logo = $logoPath;
                $dataToBeUpdate['logo'] = $logoPath;

            }else{
                // echo "no here ";
            }


            $company->update($dataToBeUpdate);

            return Helper::sendResponse($company, 'Successfully Updated', 201);
    }
}
