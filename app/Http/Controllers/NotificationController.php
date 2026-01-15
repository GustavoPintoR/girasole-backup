<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\BroadcastMessageNotification;
use Illuminate\Http\Request;;

use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Notification::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|string',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number');
        $sortBy = $validated['sort_by'] ?? 'type';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = [];

        $query = Notification::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $notifications = $query
            ->where('notifiable_id', auth()->id())
            ->orderBy($sortBy, $sortOrder)
            ->with('user')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('notifications/Index', [
            'notifications' => $notifications,
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
        Gate::authorize('create', Notification::class);
        $users = User::orderBy('first_name')->get();
        return Inertia::render('notifications/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Notification::class);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'send_to_email' => ['required', 'boolean'],
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'assign_to_all' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();

        $users = User::all();

        $validated['created_by'] = auth()->id();

        if ($request->input('assign_to_all') === true) {
            foreach ($users as $user) {
                $user->notify(new BroadcastMessageNotification($validated, $validated['send_to_email']));
            }
        } elseif (! empty($validated['user_ids'])) {
            $users = $users->whereIn('id', $validated['user_ids']);
            foreach ($users as $user) {
                $user->notify(new BroadcastMessageNotification($validated, $validated['send_to_email']));
            }
        }

        return redirect()->route('notifications.index')
            ->with('success', __('ui.model_created', ['model' => __('ui.notification')]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification): Response
    {
        Gate::authorize('view', $notification);

        $notification->load('user');
        $notification->markAsRead();

        return Inertia::render('notifications/Show', ['notification' => $notification]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notification $notification): Response
    {
        Gate::authorize('update', $notification);

        return Inertia::render('notifications/Edit', ['notification' => $notification]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification): RedirectResponse
    {
        Gate::authorize('update', $notification);
        //
        return redirect()->route('notifications.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        Gate::authorize('delete', $notification);

        $notification->delete();

        return redirect()->route('notifications.index')
            ->with('success', __('ui.model_deleted', ['model' => __('ui.notification')]));
    }

    /**
     * @param Notification $notification
     * @return RedirectResponse
     */
    public function markAsRead(Notification $notification): RedirectResponse
    {
        Gate::authorize('viewAny', $notification);

        $notification->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', __('ui.model_updated', ['model' => __('ui.notification')]));
    }
}
