<?php

namespace App\Http\Controllers;

use App\Models\CadastralGroup;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\CustomField;

class EventController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Event::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => ['nullable', Rule::in(['start', 'title', 'created_at'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
            'search' => 'nullable|string|max:255',
        ]);

        $perPage   = $validated['per_page'] ?? config('app.pagination_number', 15);
        $sortBy    = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search    = $validated['search'] ?? null;

        $searchableColumns = [
            'title',
            'description',
            'start_date',
            'end_date',
            'start',
            'end',
        ];

        $user = Auth::user();

        $query = Event::query();

        // Non-admin users only see events linked to their cadastral groups
        if (! $user->isAdmin()) {
            $query->whereHas('attendees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', "%{$search}%");
                }
            });
        }

        $events = $query
            ->with(['attendees'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('events/Index', [
            'events' => $events,
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
        Gate::authorize('create', Event::class);

        $customFields = CustomField::forModel(Event::class);

        return Inertia::render('events/Create', [
            'cadastralGroups' => CadastralGroup::with('user')->get(),
            'customFields' => $customFields,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Event::class);

        $validated = $request->validate(array_merge([
            'all_day' => 'required|boolean',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required_if:all_day,true|date|nullable',
            'end_date' => 'required_if:all_day,true|date|after_or_equal:start_date|nullable',
            'start' => 'required_if:all_day,false|date|nullable',
            'end' => 'required_if:all_day,false|date|after:start|nullable',
            'cadastral_group_ids' => 'nullable|array',
            'cadastral_group_ids.*' => 'exists:cadastral_groups,id',
            'assign_to_all' => 'nullable|boolean',
            'calendarId' => 'string|max:255',
        ], CustomField::getValidationRules(Event::class)));

        if ($validated['all_day'] === true) {
            unset($validated['start'], $validated['end']);
        } else {
            unset($validated['start_date'], $validated['end_date']);
        }

        $eventData = collect($validated)->except(['cadastral_group_ids', 'assign_to_all'])->toArray();

        $event = auth()->user()->events()->create($eventData);

        if ($request->input('assign_to_all') === true) {
            $event->assignToAllFields();
        } elseif (! empty($validated['cadastral_group_ids'])) {
            $event->attendees()->attach($validated['cadastral_group_ids']);
        } else {
            return redirect()->route('events.edit', $event)->with('info', __('ui.select_one_event'));
        }

        $event->syncCustomFields($validated['custom_fields'] ?? []);

        return redirect()->route('events.index')
            ->with('success', __('ui.created_successfully', ['resource' => 'Event']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        Gate::authorize('view', $event);

        $event->load('attendees', 'user', 'customFieldValues.customField');
        $customFieldValues = $event->getCustomFieldsArray();

        return Inertia::render('events/Show', [
            'event' => $event,
            'customFieldValues' => $customFieldValues,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        Gate::authorize('update', $event);

        $cadastralGroups = CadastralGroup::orderBy('name')->get();
        $event->load('attendees', 'customFieldValues.customField');

        $customFields = CustomField::forModel(Event::class);

        $customFieldValues = $event->getCustomFieldValuesOnly();

        return Inertia::render('events/Edit', [
            'event' => $event,
            'cadastralGroups' => $cadastralGroups,
            'customFields' => $customFields,
            'customFieldValues' => $customFieldValues,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        Gate::authorize('update', $event);

        $validated = $request->validate(array_merge([
            'all_day' => 'required|boolean',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required_if:all_day,true|date|nullable',
            'end_date' => 'required_if:all_day,true|date|after_or_equal:start_date|nullable',
            'start' => 'required_if:all_day,false|date|nullable',
            'end' => 'required_if:all_day,false|date|after:start|nullable',
            'cadastral_group_ids' => 'nullable|array',
            'cadastral_group_ids.*' => 'exists:cadastral_groups,id',
            'assign_to_all' => 'nullable|boolean',
            'calendarId' => 'string|max:255',
        ], CustomField::getValidationRules(Event::class)));

        // Match store logic for date handling
        if ($validated['all_day'] === true) {
            unset($validated['start'], $validated['end']);
        } else {
            unset($validated['start_date'], $validated['end_date']);
        }

        $eventData = collect($validated)
            ->except(['cadastral_group_ids', 'assign_to_all', 'custom_fields'])
            ->toArray();

        $event->update($eventData);

        // Sync attendees (cadastral groups)
        if ($request->boolean('assign_to_all')) {
            $event->assignToAllFields();
        } elseif (! empty($validated['cadastral_group_ids'])) {
            $event->attendees()->sync($validated['cadastral_group_ids']);
        } else {
            return redirect()
                ->route('events.edit', $event)
                ->with('info', __('ui.select_one_event'));
        }

        $event->syncCustomFields($validated['custom_fields'] ?? []);

        return redirect()
            ->route('events.index')
            ->with('success', __('ui.updated_successfully', ['resource' => 'Event']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        Gate::authorize('delete', $event);

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', __('ui.deleted_successfully', ['resource' => 'Event']));
    }

    public function calendar(Request $request)
    {
        $user = $request->user();

        // view events where user is part of attendees
        $events = Event::where(function ($query) use ($user) {
            $query->whereHas('attendees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })
            ->get()
            ->map(fn ($event) => $event->toCalendarArray())
            ->values();

        return Inertia::render('events/Calendar', compact('events'));
    }
}
