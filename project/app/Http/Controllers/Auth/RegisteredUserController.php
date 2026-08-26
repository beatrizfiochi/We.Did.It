<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the form for creating an administrator account.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/CreateUser');
    }

    /**
     * Store a new administrator account created from the admin area.
     *
     * @throws ValidationException
     */
    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // user_id é quem criou; record_id é o administrador novo
        ActivityLog::record($user, 'created');

        event(new Registered($user));

        return redirect()->route('dashboard')->with('success', 'Administrador criado com sucesso.');
    }
}
