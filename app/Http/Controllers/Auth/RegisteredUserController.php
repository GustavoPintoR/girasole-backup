<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\PostalCode;
use App\Models\BillingInfo;
use Illuminate\Http\Request;
use App\Models\BillingAddress;
use App\Models\ShippingAddress;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use PeterColes\Countries\CountriesFacade;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        $countries = CountriesFacade::keyValue(locale: app()->getLocale());

        return Inertia::render('auth/Register', compact('countries'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type' => ['required', 'string', 'max:50', 'in:business,sole_business'],
            'business_name' => ['nullable', 'string', 'max:255', 'required_if:type,business'],
            'fiscal_code' => ['required', 'string', 'max:16'],
            'vat_number' => ['nullable', 'string', 'max:20', 'required_if:type,business'],
            'mobile_phone' => ['required', 'string', 'max:20'],
            'sdi_code' => ['nullable', 'string', 'max:7', 'required_if:type,business'],
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10', 'exists:postal_codes,code'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'state' => ['required', 'string', 'max:2'],
            // shipping
            'same_as_billing' => ['nullable', 'boolean'],
            'shipping_region_id' => ['required_if:same_as_billing,false', 'integer', 'exists:regions,id'],
            'shipping_province_id' => ['required_if:same_as_billing,false', 'integer', 'exists:provinces,id'],
            'shipping_postal_code' => ['required_if:same_as_billing,false', 'string', 'max:10', 'exists:postal_codes,code'],
            'shipping_city_id' => ['required_if:same_as_billing,false', 'integer', 'exists:cities,id'],
            'name' => ['required_if:same_as_billing,false', 'string', 'max:255'],
            'address' => ['required_if:same_as_billing,false', 'string', 'max:255'],
            'cellular' => ['required_if:same_as_billing,false', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = null;

        try {
            DB::transaction(function () use ($request, &$user) {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'mobile_number' => $request->mobile_phone,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // create company
                $companyName = $request->type === 'business'
                    ? $request->business_name
                    : $request->first_name . ' ' . $request->last_name;

                $company = Company::create([
                    'name' => $companyName,
                    'owner_id' => $user->id,
                ]);

                $company->users()->sync([$user->id]);

                // Update user with main company
                $user->update(['main_company_id' => $company->id]);

                BillingAddress::create([
                    'street' => $request->street,
                    'street_number' => $request->street_number,
                    'postal_code_id' => PostalCode::whereCode($request->postal_code)->first()->id,
                    'city_id' => $request->city_id,
                    'province_id' => $request->province_id,
                    'region_id' => $request->region_id,
                    'state' => strtoupper($request->state),
                    'company_id' => $company->id,
                ]);

                BillingInfo::create([
                    'fiscal_type' => $request->type,
                    'fiscal_code' => $request->fiscal_code,
                    'sdi' => $request->sdi_code,
                    'vat_number' => $request->vat_number,
                    'business_name' => $request->business_name,
                    'company_id' => $company->id,
                ]);

                // create shipping address: either same as billing or provided separately
                $shippingPostal = $request->same_as_billing
                    ? PostalCode::whereCode($request->postal_code)->first()
                    : PostalCode::whereCode($request->shipping_postal_code)->first();

                ShippingAddress::create([
                    'name' => $request->same_as_billing ? ($request->first_name . ' ' . $request->last_name) : $request->name,
                    'postal_code_id' => $shippingPostal ? $shippingPostal->id : null,
                    'city_id' => $request->same_as_billing ? $request->city_id : $request->shipping_city_id,
                    'province_id' => $request->same_as_billing ? $request->province_id : $request->shipping_province_id,
                    'region_id' => $request->same_as_billing ? $request->region_id : $request->shipping_region_id,
                    'address' => $request->same_as_billing ? ($request->street_number . ', ' . $request->street) : $request->address,
                    'phone_number' => $request->same_as_billing ? preg_replace('/\D/', '', $request->mobile_phone) : preg_replace('/\D/', '', $request->cellular),
                    'company_id' => $company->id,
                    'same_as_billing' => $request->same_as_billing ?? false,
                ]);

                event(new Registered($user));

                $user->assignRole(UserRole::USER->value);
            });

            Auth::login($user);
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Registration failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}
