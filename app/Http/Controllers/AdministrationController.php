<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdministrationResource;
use App\Models\Administration;
use App\Models\City;
use App\Models\Commune;
use App\Models\Country;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdministrationController extends Controller
{
  public function index()
  {
    $administrations = Administration::query()->with(['country', 'city', 'commune'])->get();
    $countries = Country::query()->orderBy('name', 'ASC')->get();
    return Inertia::render('Administration/Index', [
      'administrations' => AdministrationResource::collection($administrations),
      'countries' => $countries
    ]);
  }

  public function show(Administration $administration)
  {

    $services = Service::where('administration_id', $administration->id)
      ->orderBy('name')
      ->get();
    return Inertia::render('Service/Show', [
      'administration' => $administration,
      'services' => $services
    ]);
  }


  public function showServiceForm(string $serviceKey, string $userType, Request $request)
  {
      $user = Auth::user();
      $service = Service::query()->with(['administration'])->where('key', $serviceKey)->firstOrFail();

      $serviceFields = $service->fields ?? [];

      $administration = $service->administration;

      $data =  [
        'service' => $service,
        'userType' => $userType,
        'userData' => [
          'last_name' => $user->last_name,
          'first_name' => $user->first_name,
          'email' => $user->email,
          'phone' => $user->phone,
          'address' => $user->address,
          'city' => $user->city,
          'postal_code' => $user->postal_code,
          'company_name' => $user->company_name,
          'company_registration' => $user->company_registration,
          'company_address' => $user->company_address,
        ],
         'serviceFields' => $serviceFields
      ];

      if($administration->slug === 'cnps'){
       return Inertia::render('CNPS/DeclarationForm', $data);
      }
      if($administration->slug === 'guichet'){
        //Pour le moment la douane
       return Inertia::render('Douane/DeclarationForm', $data);
      }


    }
  }

