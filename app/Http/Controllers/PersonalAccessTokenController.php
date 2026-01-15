<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ApiAccess;
use App\Models\PersonalAccessToken;
use Illuminate\Http\Request;;

use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PersonalAccessTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', PersonalAccessToken::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name'];

        $query = PersonalAccessToken::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        if($request->user()->isSuperAdmin()) {
            $tokens = $query
                ->orderBy($sortBy, $sortOrder)
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString();
        }else {
            $tokens = $query
                ->where('tokenable_id', $request->user()->id)
                ->orderBy($sortBy, $sortOrder)
                ->orderBy('name')
                ->paginate($perPage);
        }

        return Inertia::render('personal-access-tokens/Index', [
            'tokens' => $tokens,
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
    public function create(): Response
    {
        Gate::authorize('create', PersonalAccessToken::class);
        //
        return Inertia::render('personal-access-tokens/Create', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', PersonalAccessToken::class);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();

        $name = ApiAccess::APP_TOKEN_PREFIX . $validated['name'];
        $token = $request->user()->createToken(name:  $name );

        if($request->user()->isIntegration()) {
            $token = $request->user()->createToken(name: $name , abilities: ['read-only']);
        }

        $personalAccessToken = $token->accessToken;
        $plainTextToken = $token->plainTextToken;

        return redirect()->route('api-keys.show', $personalAccessToken->id)
            ->with('plainTextToken', $plainTextToken)
            ->with('success', __('ui.model_created', ['model' => __('ui.api_keys')]));
    }

    /**
     * Regenerate the specified token.
     */
    public function regenerate(Request $request, int $personalAccessToken): RedirectResponse
    {
        $personalAccessToken = PersonalAccessToken::where('id', $personalAccessToken)->firstOrFail();

        Gate::authorize('update', $personalAccessToken);

        $personalAccessToken->delete();

        $token = $request->user()->createToken(name: $personalAccessToken->name);
        $newPersonalAccessToken = $token->accessToken;
        $plainTextToken = $token->plainTextToken;

        return redirect()->route('api-keys.show', $newPersonalAccessToken->id)
            ->with('plainTextToken', $plainTextToken)
            ->with('success', __('ui.token_regenerated', ['model' => __('ui.api_keys')]));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $personalAccessToken): Response
    {
        $personalAccessToken = PersonalAccessToken::where('id', $personalAccessToken)->with('tokenable')->firstOrFail();

        Gate::authorize('view', $personalAccessToken);

        return Inertia::render('personal-access-tokens/Show', [
            'token' => $personalAccessToken,
            'plainTextToken' => session('plainTextToken'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PersonalAccessToken $personalAccessToken): Response
    {
        Gate::authorize('update', $personalAccessToken);
        //
        return Inertia::render('personal-access-tokens/Edit', ['token' => $personalAccessToken]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PersonalAccessToken $personalAccessToken): RedirectResponse
    {
        Gate::authorize('update', $personalAccessToken);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();

        $personalAccessToken->delete();

        $request->user()->createToken(name: $validated['name']);

        return redirect()->route('api-keys.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $personalAccessToken): RedirectResponse
    {
        $personalAccessToken = PersonalAccessToken::where('id', $personalAccessToken)->firstOrFail();

        Gate::authorize('delete', $personalAccessToken);

        $personalAccessToken->delete();

        return redirect()->route('api-keys.index');
    }
}
