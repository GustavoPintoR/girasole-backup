<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PostalCode;
use Illuminate\Support\Facades\Gate;
use PeterColes\Countries\CountriesFacade;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Company::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|string',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number');
        $sortBy = $validated['sort_by'] ?? 'name';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name'];

        $query = Company::query()->with(['owner', 'users']);
        $userId = $request->user()->id;

        if (!$request->user()->isSuperAdmin()) {
            $query->where(function ($q) use ($userId) {
                // Find companies the user owns directly (via owner_id)
                $q->where('owner_id', $userId);
            })->orWhereHas('users', function ($q) use ($userId) {
                // Find companies the user is associated with (via the pivot table)
                $q->where('users.id', $userId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%' . $search . '%');
                }
            });
        }

        $companies = $query
            ->with('owner')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($company) use ($request) {
                $company->is_main = $company->id === $request->user()->main_company_id;
                return $company;
            });

        return Inertia::render('companies/Index', [
            'companies' => $companies,
            'sorting' => compact('sortBy', 'sortOrder'),
            'filtering' => [
                'search' => $search,
                'searchableColumns' => $searchableColumns,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Company::class);

        $users = User::orderBy('last_name')->get();

        return Inertia::render('companies/Create', [
            'users' => $users,
            'countries' => CountriesFacade::keyValue(locale: app()->getLocale()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Company::class);

        $rules = [
            'name' => 'required|string|max:255|unique:companies,name',
            'description' => 'nullable|string|max:1000',
            'fiscal_type' => 'required|string|max:50|in:business,sole_business',
            'fiscal_code' => 'required|string|max:16',
            'vat_number' => 'nullable|string|max:11|required_if:fiscal_type,business',
            'sdi_code' => 'nullable|string|max:7|required_if:fiscal_type,business',
            // Billing Address
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10', 'exists:postal_codes,code'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'state' => ['required', 'string', 'max:2'],
            'mobile_number' => ['required', 'string', 'max:20'],
            // Shipping Address
            'same_as_billing' => ['required', 'boolean'],
            'shipping_region_id' => ['nullable', 'required_if:same_as_billing,false', 'integer', 'exists:regions,id'],
            'shipping_province_id' => ['nullable', 'required_if:same_as_billing,false', 'integer', 'exists:provinces,id'],
            'shipping_postal_code' => ['nullable', 'required_if:same_as_billing,false', 'string', 'max:10', 'exists:postal_codes,code'],
            'shipping_city_id' => ['nullable', 'required_if:same_as_billing,false', 'integer', 'exists:cities,id'],
            'shipping_name' => ['nullable', 'required_if:same_as_billing,false', 'string', 'max:255'],
            'shipping_address' => ['nullable', 'required_if:same_as_billing,false', 'string', 'max:255'],
            'shipping_cellular' => ['nullable', 'required_if:same_as_billing,false', 'string', 'max:255'],
        ];

        if ($request->user()->can('assign_company_owner')) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        if ($request->user()->can('assign_company_users')) {
            $rules['user_ids'] = 'nullable|array';
            $rules['user_ids.*'] = 'exists:users,id';
            $rules['assign_to_all'] = 'nullable|boolean';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();

        $ownerId = $request->user()->can('assign_company_owner') ? $validated['owner_id'] : $request->user()->id;

        $company = Company::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'owner_id' => $ownerId,
        ]);

        if (!empty($validated['fiscal_type'])) {
            $company->billingInfo()->create([
                'fiscal_type' => $validated['fiscal_type'],
                'business_name' => $validated['name'],
                'fiscal_code' => $validated['fiscal_code'],
                'vat_number' => $validated['vat_number'],
                'sdi' => $validated['sdi_code'],
            ]);
        }

        if (!empty($validated['street'])) {
            $company->billingAddress()->create([
                'street' => $validated['street'],
                'street_number' => $validated['street_number'],
                'postal_code_id' => PostalCode::where('code', $validated['postal_code'])->first()->id,
                'city_id' => $validated['city_id'],
                'province_id' => $validated['province_id'],
                'region_id' => $validated['region_id'],
                'state' => $validated['state'],
                'mobile_number' => $validated['mobile_number'] ?? null,
            ]);
        }

        if (isset($validated['same_as_billing'])) {
            $shippingAddressData = $validated['same_as_billing']
                ? [
                    'city_id' => $validated['city_id'],
                    'province_id' => $validated['province_id'],
                    'region_id' => $validated['region_id'],
                    'postal_code_id' => PostalCode::where('code', $validated['postal_code'])->first()->id,
                    'address' => $validated['street'] . ' ' . $validated['street_number'],
                    'name' => $validated['name'],
                    'phone_number' => $validated['mobile_number'] ?? null,
                    'same_as_billing' => true,
                ]
                : [
                    'city_id' => $validated['shipping_city_id'],
                    'province_id' => $validated['shipping_province_id'],
                    'region_id' => $validated['shipping_region_id'],
                    'postal_code_id' => PostalCode::where('code', $validated['shipping_postal_code'])->first()->id,
                    'address' => $validated['shipping_address'],
                    'name' => $validated['shipping_name'],
                    'phone_number' => $validated['shipping_cellular'],
                    'same_as_billing' => false,
                ];
            $company->shippingAddress()->create($shippingAddressData);
        }

        if ($request->user()->can('assign_company_users')) {
            if (!empty($validated['assign_to_all']) && $validated['assign_to_all'] === true) {
                $company->users()->sync(User::all()->pluck('id')->toArray());
            } elseif (!empty($validated['user_ids'])) {
                $company->users()->sync($validated['user_ids']);
            }
        } else {
            $company->users()->sync([$ownerId]);
        }

        return redirect()->route('companies.show', $company)
            ->with('success', __('ui.company_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        Gate::authorize('view', $company);

        $company->load([
            'owner',
            'users',
            'billingInfo',
            'billingAddress.postalCode',
            'billingAddress.city',
            'billingAddress.province',
            'billingAddress.region',
            'shippingAddress.postalCode',
            'shippingAddress.city',
            'shippingAddress.province',
            'shippingAddress.region'
        ]);

        $company->is_main = $company->id === request()->user()->main_company_id;

        return Inertia::render('companies/Show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        Gate::authorize('update', $company);

        $users = User::orderBy('last_name')->get();
        $company->load(['owner', 'users', 'billingInfo', 'billingAddress.postalCode', 'shippingAddress.postalCode']);

        return Inertia::render('companies/Edit', [
            'company' => $company,
            'users' => $users,
            'countries' => CountriesFacade::keyValue(locale: app()->getLocale()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
        Gate::authorize('update', $company);

        $rules = [
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'description' => 'nullable|string|max:1000',
            'fiscal_type' => 'required|string|max:50|in:business,sole_business',
            'fiscal_code' => 'required|string|max:16',
            'vat_number' => 'nullable|string|max:11|required_if:fiscal_type,business',
            'sdi_code' => 'nullable|string|max:7|required_if:fiscal_type,business',
            // Billing Address
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10', 'exists:postal_codes,code'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'state' => ['required', 'string', 'max:2'],
            'mobile_number' => ['required', 'string', 'max:20'],
            // Shipping Address
            'same_as_billing' => ['required', 'boolean'],
            'shipping_region_id' => ['nullable', 'required_if:same_as_billing,false', 'integer', 'exists:regions,id'],
            'shipping_province_id' => ['nullable', 'required_if:same_as_billing,false', 'integer', 'exists:provinces,id'],
            'shipping_postal_code' => ['nullable', 'required_if:same_as_billing,false', 'string', 'max:10', 'exists:postal_codes,code'],
            'shipping_city_id' => ['nullable', 'required_if:same_as_billing,false', 'integer', 'exists:cities,id'],
            'shipping_name' => ['nullable', 'required_if:same_as_billing,false', 'string', 'max:255'],
            'shipping_address' => ['nullable', 'required_if:same_as_billing,false', 'string', 'max:255'],
            'shipping_cellular' => ['nullable', 'required_if:same_as_billing,false', 'string', 'max:255'],
        ];

        if ($request->user()->can('assign_company_owner')) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        if ($request->user()->can('assign_company_users')) {
            $rules['user_ids'] = 'nullable|array';
            $rules['user_ids.*'] = 'exists:users,id';
            $rules['assign_to_all'] = 'nullable|boolean';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();

        $ownerId = $request->user()->can('assign_company_owner') ? $validated['owner_id'] : $company->owner_id;

        $company->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'owner_id' => $ownerId,
        ]);

        if (!empty($validated['fiscal_type'])) {
            $billingInfoData = [
                'fiscal_type' => $validated['fiscal_type'],
                'business_name' => $validated['name'],
                'fiscal_code' => $validated['fiscal_code'],
                'vat_number' => $validated['vat_number'],
                'sdi' => $validated['sdi_code'],
            ];

            if ($company->billingInfo) {
                $company->billingInfo->update($billingInfoData);
            } else {
                $company->billingInfo()->create($billingInfoData);
            }
        }

        if (!empty($validated['street'])) {
            $billingAddressData = [
                'street' => $validated['street'],
                'street_number' => $validated['street_number'],
                'postal_code_id' => PostalCode::where('code', $validated['postal_code'])->first()->id,
                'city_id' => $validated['city_id'],
                'province_id' => $validated['province_id'],
                'region_id' => $validated['region_id'],
                'state' => $validated['state'],
                'mobile_number' => $validated['mobile_number'] ?? null,
            ];

            if ($company->billingAddress) {
                $company->billingAddress->update($billingAddressData);
            } else {
                $company->billingAddress()->create($billingAddressData);
            }
        }

        if (isset($validated['same_as_billing'])) {
            $shippingAddressData = $validated['same_as_billing']
                ? [
                    'city_id' => $validated['city_id'],
                    'province_id' => $validated['province_id'],
                    'region_id' => $validated['region_id'],
                    'postal_code_id' => PostalCode::where('code', $validated['postal_code'])->first()->id,
                    'address' => $validated['street'] . ' ' . $validated['street_number'],
                    'name' => $validated['name'],
                    'phone_number' => $validated['mobile_number'] ?? null,
                    'same_as_billing' => true,
                ]
                : [
                    'city_id' => $validated['shipping_city_id'],
                    'province_id' => $validated['shipping_province_id'],
                    'region_id' => $validated['shipping_region_id'],
                    'postal_code_id' => PostalCode::where('code', $validated['shipping_postal_code'])->first()->id,
                    'address' => $validated['shipping_address'],
                    'name' => $validated['shipping_name'],
                    'phone_number' => $validated['shipping_cellular'],
                    'same_as_billing' => false,
                ];

            if ($company->shippingAddress) {
                $company->shippingAddress->update($shippingAddressData);
            } else {
                $company->shippingAddress()->create($shippingAddressData);
            }
        }

        if ($request->user()->can('assign_company_users')) {
            if (!empty($validated['assign_to_all']) && $validated['assign_to_all'] === true) {
                $company->users()->sync(User::all()->pluck('id')->toArray());
            } elseif (!empty($validated['user_ids'])) {
                $company->users()->sync($validated['user_ids']);
            } else {
                $company->users()->sync([$ownerId]);
            }
        } else {
            $company->users()->sync([$company->owner_id]);
        }

        return redirect()->route('companies.show', $company)
            ->with('success', __('ui.company_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        Gate::authorize('delete', $company);

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', __('ui.company_deleted'));
    }
}
