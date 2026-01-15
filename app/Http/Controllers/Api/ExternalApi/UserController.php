<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Enums\UserRole;
use App\Helpers\PlanHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\BillingAddress;
use App\Models\BillingInfo;
use App\Models\Company;
use App\Models\PostalCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @group User management
 *
 * APIs for managing users
 *
 */
class UserController extends Controller
{
    public function __construct(){}

    /**
     * Authenticate a user
     *
     * This endpoint allows you to authenticate a user and issue a personal access token.
     *
     * @header Content-Type application/json
     * @header Accept application/json
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @bodyParam email string required The email of the user. Example: xyz@example.com
     * @bodyParam password string required The password of the user.
     * @bodyParam device_name string required The name of the device that's connecting. Example: mobile_app
     *
     * @response 200 {
     *   "message": "Login successful",
     *   "token": "1|randomtokenstring",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "xyz@example.com",
     *     "created_at": "2025-10-03T08:48:00Z"
     *   }
     * }
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email field is required."],
     *     "password": ["The password field is required."],
     *     "device_name": ["The device name field is required."]
     *   }
     * }
     * @response 429 {
     *   "message": "Too many login attempts. Please try again in 60 seconds."
     * }
     * @response 403 {
     *   "message": "Account is not active. Please contact an admin."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('ui.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $key = 'login|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => __('ui.login_too_many_attempts', ['seconds' => $seconds]),
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages([
                'email' => [__('ui.incorrect_credentials')],
            ]);
        }

        if (!$user->active) {
            RateLimiter::hit($key, 300);
            return response()->json([
                'message' => __('ui.account_not_active'),
            ], 403);
        }

        RateLimiter::clear($key);

        $existingToken = $user->tokens()->where('name', $request->device_name)->first();

        if ($existingToken) {
            $existingToken->delete();
        }

        if($user->isIntegration()) {
            $token = $user->createToken(name: $request->device_name, abilities: ['read-only'])->plainTextToken;
        }else{
            $token = $user->createToken($request->device_name)->plainTextToken;
        }

        return response()->json([
            'message' => __('ui.login_successful'),
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Register a new user.
     *
     * This endpoint creates a new user account, including billing address and billing information.
     * The user is created with active status set to false, pending admin activation.
     * Requires a subscription if active plans exist (post-registration).
     *
     * @header Content-Type application/json
     * @header Accept application/json
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @bodyParam first_name string required The user's first name. Example: John
     * @bodyParam last_name string required The user's last name. Example: Doe
     * @bodyParam email string required The user's email address (must be unique). Example: john.doe@example.com
     * @bodyParam password string required The user's password (minimum 8 characters, confirmed). Example: password123
     * @bodyParam password_confirmation string required Confirmation of the password. Example: password123
     * @bodyParam type string required The fiscal type (business or sole_business). Example: business
     * @bodyParam business_name string nullable The business name (required if type is business). Example: Doe Enterprises
     * @bodyParam fiscal_code string required The fiscal code (max 16 characters). Example: ABC1234567890123
     * @bodyParam vat_number string nullable The VAT number (required if type is business, max 20 characters). Example: IT12345678901
     * @bodyParam mobile_phone string required The user's mobile phone number (max 20 characters). Example: +1234567890
     * @bodyParam sdi_code string nullable The SDI code (required if type is business, max 7 characters). Example: ABC1234
     * @bodyParam street string required The street name of the billing address. Example: Main Street
     * @bodyParam street_number string required The street number of the billing address. Example: 123
     * @bodyParam postal_code string required The postal code (must exist in postal_codes table). Example: 12345
     * @bodyParam city_id integer required The city ID (must exist in cities table). Example: 1
     * @bodyParam province_id integer required The province ID (must exist in provinces table). Example: 1
     * @bodyParam region_id integer required The region ID (must exist in regions table). Example: 1
     * @bodyParam state string required The state code (max 2 characters). Example: NY
     * @bodyParam device_name string required The device name. Example: mobile_app
     *
     * @response 201 {
     *   "message": "User registered successfully. An admin will activate your account shortly.",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john.doe@example.com"
     *   }
     * }
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email has already been taken."],
     *     "password": ["The password field is required."],
     *     "business_name": ["The business name field is required when type is business."]
     *   }
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type' => ['required', 'string', 'max:50', 'in:business,sole_business'],
            'business_name' => ['nullable', 'string', 'max:255', 'required_if:type,business'],
            'fiscal_code' => ['required', 'string', 'max:16'],
            'vat_number' => ['nullable', 'string', 'max:20', 'required_if:type,business'],
            'mobile_phone' => ['required', 'string', 'max:20'],
            'sdi_code' => ['nullable', 'string', 'required_if:type,business'],
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10', 'exists:postal_codes,code'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'state' => ['required', 'string', 'max:2'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('ui.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => trim("{$request->first_name} {$request->last_name}"),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile_number' => $request->mobile_phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'active' => false, // Set user as inactive
            ]);

            BillingAddress::create([
                'street' => $request->street,
                'street_number' => $request->street_number,
                'postal_code_id' => PostalCode::whereCode($request->postal_code)->first()->id,
                'city_id' => $request->city_id,
                'province_id' => $request->province_id,
                'region_id' => $request->region_id,
                'state' => strtoupper($request->state),
                'user_id' => $user->id,
            ]);

            // Create company
            $companyName = $request->type === 'business'
                ? $request->business_name
                : $request->first_name . ' ' . $request->last_name;

            $company = Company::create([
                'name' => $companyName,
                'owner_id' => $user->id,
            ]);
            $company->users()->sync([$user->id]);

            BillingInfo::create([
                'fiscal_type' => $request->type,
                'fiscal_code' => $request->fiscal_code,
                'sdi' => $request->sdi_code,
                'vat_number' => $request->vat_number,
                'business_name' => $request->business_name,
                'company_id' => $company->id,
            ]);

            event(new Registered($user));
            $user->assignRole(UserRole::USER->value);

            return $user;
        });

        return response()->json([
            'message' => __('ui.register_successful') . ' ' . __('ui.admin_activation_pending'),
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Update the authenticated user's profile, billing info, billing address, and/or shipping address.
     *
     * This endpoint allows partial updates to any combination of user profile, billing information,
     * billing address, and shipping address. All fields are optional.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     *
     * @bodyParam first_name string nullable The user's first name (max 255 chars). Example: John
     * @bodyParam last_name string nullable The user's last name (max 255 chars). Example: Doe
     * @bodyParam email string nullable The user's email (unique, lowercase, max 255 chars). Example: john.doe@example.com
     * @bodyParam mobile_number string nullable The user's mobile number (max 255 chars). Example: +1234567890
     * @bodyParam type string nullable The fiscal type (business or sole_business, max 50 chars). Example: business
     * @bodyParam business_name string nullable The business name (max 255 chars, required if type=business). Example: Doe Enterprises
     * @bodyParam fiscal_code string nullable The fiscal code (max 16 chars). Example: ABC1234567890123
     * @bodyParam vat_number string nullable The VAT number (max 20 chars, required if type=business). Example: IT12345678901
     * @bodyParam sdi_code string nullable The SDI code (max 7 chars, required if type=business). Example: ABC1234
     * @bodyParam street string nullable The billing street name (max 255 chars). Example: Main Street
     * @bodyParam street_number string nullable The billing street number (max 255 chars). Example: 123
     * @bodyParam postal_code string nullable The billing postal code (max 10 chars, must exist). Example: 12345
     * @bodyParam city_id integer nullable The billing city ID (must exist). Example: 1
     * @bodyParam province_id integer nullable The billing province ID (must exist). Example: 1
     * @bodyParam region_id integer nullable The billing region ID (must exist). Example: 1
     * @bodyParam state string nullable The billing state code (max 2 chars). Example: NY
     * @bodyParam same_as_billing boolean nullable Whether shipping address matches billing (default false). Example: true
     * @bodyParam shipping_region_id integer nullable The shipping region ID (required if same_as_billing=false, must exist). Example: 1
     * @bodyParam shipping_province_id integer nullable The shipping province ID (required if same_as_billing=false, must exist). Example: 1
     * @bodyParam shipping_postal_code string nullable The shipping postal code (max 10 chars, required if same_as_billing=false). Example: 12345
     * @bodyParam shipping_city_id integer nullable The shipping city ID (required if same_as_billing=false, must exist). Example: 1
     * @bodyParam name string nullable The shipping name (required if same_as_billing=false). Example: John Doe
     * @bodyParam address string nullable The shipping address (required if same_as_billing=false). Example: 456 Oak Ave
     * @bodyParam cellular string nullable The shipping phone number (required if same_as_billing=false). Example: +1234567890
     *
     * @response 200 {
     *   "message": "Profile updated successfully",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "email": "john.doe@example.com",
     *     "mobile_number": "+1234567890",
     *     "billing_info": {
     *       "id": 1,
     *       "fiscal_type": "business",
     *       "fiscal_code": "ABC1234567890123",
     *       "sdi": "ABC1234",
     *       "vat_number": "IT12345678901",
     *       "business_name": "Doe Enterprises"
     *     },
     *     "billing_address": {
     *       "id": 1,
     *       "street": "Main Street",
     *       "street_number": "123",
     *       "postal_code": "12345",
     *       "city": "New York",
     *       "province": "NYC",
     *       "region": "NY",
     *       "state": "NY"
     *     },
     *     "shipping_address": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "address": "456 Oak Ave",
     *       "postal_code": "12345",
     *       "city": "New York",
     *       "province": "NYC",
     *       "region": "NY",
     *       "phone_number": "+1234567890",
     *       "same_as_billing": false
     *     }
     *   }
     * }
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email has already been taken."],
     *     "business_name": ["The business name field is required when type is business."]
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *     "message": "Technicians are not allowed to access this endpoint."
     * }
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if(PlanHelper::checkIfTokenIsReadOnly($user)){
            return response()->json([
                'message' => __('ui.token_read_only'),
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'mobile_number' => ['nullable', 'string', 'max:255'],

            'type' => ['nullable', 'string', 'max:50', 'in:business,sole_business'],
            'business_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $request->type === 'business'),
            ],
            'fiscal_code' => ['nullable', 'string', 'max:16'],
            'vat_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::requiredIf(fn() => $request->type === 'business'),
            ],
            'sdi_code' => [
                'nullable',
                'string',
                Rule::requiredIf(fn() => $request->type === 'business'),
            ],

            'street' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10', 'exists:postal_codes,code'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'state' => ['nullable', 'string', 'max:2'],

            'same_as_billing' => ['nullable', 'boolean'],
            'shipping_region_id' => [
                'nullable',
                'integer',
                'exists:regions,id',
                Rule::requiredIf(fn() => $request->same_as_billing === false),
            ],
            'shipping_province_id' => [
                'nullable',
                'integer',
                'exists:provinces,id',
                Rule::requiredIf(fn() => $request->same_as_billing === false),
            ],
            'shipping_postal_code' => [
                'nullable',
                'string',
                'max:10',
                Rule::requiredIf(fn() => $request->same_as_billing === false),
            ],
            'shipping_city_id' => [
                'nullable',
                'integer',
                'exists:cities,id',
                Rule::requiredIf(fn() => $request->same_as_billing === false),
            ],
            'name' => ['nullable', 'string', Rule::requiredIf(fn() => $request->same_as_billing === false)],
            'address' => ['nullable', 'string', Rule::requiredIf(fn() => $request->same_as_billing === false)],
            'cellular' => ['nullable', 'string', Rule::requiredIf(fn() => $request->same_as_billing === false)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('ui.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($user, $validated) {
            if (array_key_exists('first_name', $validated) || array_key_exists('last_name', $validated) || array_key_exists('email', $validated) || array_key_exists('mobile_number', $validated)) {
                $profileData = array_filter([
                    'first_name' => $validated['first_name'] ?? $user->first_name,
                    'last_name' => $validated['last_name'] ?? $user->last_name,
                    'mobile_number' => $validated['mobile_number'] ?? $user->mobile_number,
                ], fn($value) => $value !== null);

                if (array_key_exists('email', $validated)) {
                    $profileData['email'] = $validated['email'];
                    $user->email_verified_at = null;
                }

                $user->update($profileData);
            }

            if (array_key_exists('type', $validated) || array_key_exists('fiscal_code', $validated)) {
                $billingInfo = $user->billingInfo;
                $billingInfoData = array_filter([
                    'fiscal_type' => $validated['type'] ?? $billingInfo?->fiscal_type,
                    'fiscal_code' => $validated['fiscal_code'] ?? $billingInfo?->fiscal_code,
                    'sdi' => $validated['sdi_code'] ?? $billingInfo?->sdi,
                    'vat_number' => $validated['vat_number'] ?? $billingInfo?->vat_number,
                    'business_name' => $validated['business_name'] ?? $billingInfo?->business_name,
                ], fn($value) => $value !== null);

                if ($billingInfo) {
                    $billingInfo->update($billingInfoData);
                } else {
                    $company = $user->companyOwner()->first() ?? $user->companies()->first();
                    if ($company) {
                        $company->billingInfo()->create($billingInfoData);
                    }
                }
            }

            if (array_key_exists('street', $validated) || array_key_exists('postal_code', $validated)) {
                $postalCode = PostalCode::where('code', $validated['postal_code'] ?? $user->billingAddress?->postalCode?->code)->firstOrFail();

                $billingAddressData = array_filter([
                    'street' => $validated['street'] ?? $user->billingAddress?->street,
                    'street_number' => $validated['street_number'] ?? $user->billingAddress?->street_number,
                    'postal_code_id' => $postalCode->id,
                    'city_id' => $validated['city_id'] ?? $user->billingAddress?->city_id,
                    'province_id' => $validated['province_id'] ?? $user->billingAddress?->province_id,
                    'region_id' => $validated['region_id'] ?? $user->billingAddress?->region_id,
                    'state' => strtoupper($validated['state'] ?? $user->billingAddress?->state ?? ''),
                ], fn($value) => $value !== null);

                if ($user->billingAddress) {
                    $user->billingAddress->update($billingAddressData);
                } else {
                    $user->billingAddress()->create($billingAddressData);
                }
            }


            if (array_key_exists('same_as_billing', $validated)) {
                $sameAsBilling = $validated['same_as_billing'] ?? false;
                $billingAddress = $user->billingAddress;

                if ($sameAsBilling && $billingAddress) {
                    $shippingData = [
                        'name' => trim("{$user->first_name} {$user->last_name}"),
                        'postal_code_id' => $billingAddress->postal_code_id,
                        'city_id' => $billingAddress->city_id,
                        'province_id' => $billingAddress->province_id,
                        'region_id' => $billingAddress->region_id,
                        'address' => trim("{$billingAddress->street_number}, {$billingAddress->street}"),
                        'phone_number' => $user->mobile_number,
                        'user_id' => $user->id,
                        'same_as_billing' => true,
                    ];
                } else {
                    $shippingPostalCode = PostalCode::where('code', $validated['shipping_postal_code'])->firstOrFail();

                    $shippingData = [
                        'name' => $validated['name'],
                        'postal_code_id' => $shippingPostalCode->id,
                        'city_id' => $validated['shipping_city_id'],
                        'province_id' => $validated['shipping_province_id'],
                        'region_id' => $validated['shipping_region_id'],
                        'address' => $validated['address'],
                        'phone_number' => $validated['cellular'],
                        'user_id' => $user->id,
                        'same_as_billing' => false,
                    ];
                }

                if ($user->shippingAddress) {
                    $user->shippingAddress->update($shippingData);
                } else {
                    $user->shippingAddress()->create($shippingData);
                }
            }

            $user->load(['companyOwner.billingInfo', 'companies.billingInfo', 'billingAddress', 'shippingAddress']);
        });

        return response()->json([
            'message' => __('ui.profile_updated'),
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Send a password reset link to the user's email address.
     *
     * This endpoint sends a password reset link to the provided email address if it exists.
     * @header Content-Type application/json
     * @header Accept application/json
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @bodyParam email string required The user's email address. Example: xyz@Example.com
     *
     * @response 200 {
     *   "message": "Password reset link sent to your email"
     * }
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email field is required."]
     *   }
     * }
     * @response 400 {
     *   "message": "Unable to send recovery link"
     * }
     */
    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('ui.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email',
            ]);
        }

        return response()->json([
            'message' => 'Unable to send reset link',
        ], 400);
    }

    /**
     * Retrieve the authenticated user's profile and associated data.
     *
     * This endpoint returns the currently authenticated user's profile along with related information
     * such as billing info, billing address, shipping address, events, cadastral group, terms and conditions,
     * company details, and sensors.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response {
     *   "message": "Profile successfully loaded.",
     *   "user": {
     *       "id": 1,
     *       "name": "Example User",
     *       ...
     *   }
     * }
     * @response 403 {
     *     "message": "Technicians are not allowed to access this endpoint."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load([
            'companyOwner.billingInfo',
            'companies.billingInfo',
            'billingAddress',
            'shippingAddress',
            'events',
            'cadastralGroup',
            'termsAndConditions',
            'company',
            'sensors'
        ]);

        return response()->json([
            'message' => __('ui.profile_updated'),
            'user' => new UserResource($user),
        ]);
    }
}
