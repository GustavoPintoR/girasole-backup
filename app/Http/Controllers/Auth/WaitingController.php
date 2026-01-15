<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class WaitingController extends Controller
{
    public function __invoke()
    {
        if (auth()->check() && auth()->user()->active == 0) {
            return Inertia::render('auth/Waiting');
        }
        return redirect()->route('home');
    }
}
