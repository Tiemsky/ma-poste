<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $countries = Country::query()
            ->orderBy('name', 'ASC')
            ->select('id', 'name') // Optimisation : ne charger que le nécessaire
            ->get();

        return Inertia::render('Auth/Register', [
            'countries' => $countries,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
    // dd($request->all());
        $user = User::create([
            'key' => (string) Str::uuid(),
            'last_name' => strtoupper($request->last_name),
            'first_name' => ucfirst($request->first_name),
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'commune_id' => $request->commune_id,
            'phone' => $request->phone,
            'account_type' => $request->boolean('is_company') ? 'entreprise' : 'particulier',
            'role' => 'user',
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        // event(new Registered($user));
        Auth::login($user);
        return redirect(route('dashboard', absolute: false));
    }

}
