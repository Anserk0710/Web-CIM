<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AdminShellController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Admin/Login', [
            'phase' => 'Phase 1',
        ]);
    }

    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'phase' => 'Phase 1',
        ]);
    }
}
