<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Return companies that the given user either owns or belongs to.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $validated['user_id'];

        $companies = Company::where('owner_id', $userId)
            ->orWhereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->select('id', 'name', 'description')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
            ]);

        return response()->json(['companies' => $companies]);
    }
}
