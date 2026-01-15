<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cultivar;
use App\Models\CustomField;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomFieldController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', CustomField::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => ['nullable', Rule::in(['label', 'key', 'model_type', 'type', 'is_required', 'order', 'created_at'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number', 15);
        $sortBy = $validated['sort_by'] ?? 'order';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['label', 'key', 'model_type', 'type', 'description'];

        $query = CustomField::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%' . $search . '%');
                }
            });
        }

        $fields = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        $availableModels = [
            'App\\Models\\Event' => 'Event',
        ];

        return Inertia::render('custom-fields/Index', [
            'fields' => $fields,
            'sorting' => compact('sortBy', 'sortOrder'),
            'filtering' => [
                'search' => $search,
                'searchableColumns' => $searchableColumns,
            ],
            'availableModels' => $availableModels,
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', CustomField::class);

        $availableModels = [
            Event::class => 'Event',
            Cultivar::class => 'Cultivar',
        ];

        $fieldTypes = [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'float' => 'Float',
            'date' => 'Date',
            'datetime' => 'Date & Time',
            'select' => 'Select',
            'checkbox' => 'Checkbox',
        ];

        return Inertia::render('custom-fields/Create', [
            'availableModels' => $availableModels,
            'fieldTypes' => $fieldTypes,
            'preselectedModel' => $request->input('model_type'),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', CustomField::class);

        $validated = $request->validate([
            'model_type' => 'required|string',
            'key' => ['required', 'string', 'max:255', 'regex:/^[a-z_]+$/', 'unique:custom_fields,key'],
            'label' => 'required|string|max:255',
            'type' => ['required', Rule::in(['text', 'textarea', 'number', 'float', 'date', 'datetime', 'select', 'checkbox'])],
            'unit' => 'nullable|string|max:50',
            'is_required' => 'boolean',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'order' => 'integer|min:0',
        ]);

        CustomField::create($validated);

        return redirect()->route('custom-fields.index')
            ->with('success', __('ui.created_successfully', ['resource' => 'Custom Field']));
    }

    public function edit(CustomField $customField)
    {
        Gate::authorize('update', $customField);

        $availableModels = [
            Event::class => 'Event',
            Cultivar::class => 'Cultivar',
        ];

        $fieldTypes = [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'float' => 'Float',
            'date' => 'Date',
            'datetime' => 'Date & Time',
            'select' => 'Select',
            'checkbox' => 'Checkbox',
        ];

        return Inertia::render('custom-fields/Edit', [
            'field' => $customField,
            'availableModels' => $availableModels,
            'fieldTypes' => $fieldTypes,
        ]);
    }

    public function update(Request $request, CustomField $customField)
    {
        Gate::authorize('update', $customField);

        $validated = $request->validate([
            'model_type' => 'required|string',
            'key' => ['required', 'string', 'max:255', 'regex:/^[a-z_]+$/', Rule::unique('custom_fields', 'key')->ignore($customField->id)],
            'label' => 'required|string|max:255',
            'type' => ['required', Rule::in(['text', 'textarea', 'number', 'float', 'date', 'datetime', 'select', 'checkbox'])],
            'unit' => 'nullable|string|max:50',
            'is_required' => 'boolean',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'order' => 'integer|min:0',
        ]);

        $customField->update($validated);

        return redirect()->route('custom-fields.index')
            ->with('success', __('ui.updated_successfully', ['resource' => 'Custom Field']));
    }

    public function destroy(CustomField $customField)
    {
        Gate::authorize('delete', $customField);

        $customField->delete();

        return redirect()->route('custom-fields.index')
            ->with('success', __('ui.deleted_successfully', ['resource' => 'Custom Field']));
    }
}
