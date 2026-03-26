<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administration extends Model
{

  protected $guarded = [];

   public function getRouteKeyName(): string
    {
        return 'key';
    }
    public function services()
{
    return $this->hasMany(Service::class);
}

public function country()
{
    return $this->belongsTo(Country::class);
}
public function city()
{
    return $this->belongsTo(City::class);
}

public function commune()
{
    return $this->belongsTo(Commune::class);
}
}
