<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $guarded = [];

    public function administration():BelongsTo{
        return $this->belongsTo(Administration::class);
    }
    public function cnpsRequests()
{
    return $this->hasMany(CnpsRequest::class);
}

public function customsRequests()
{
    return $this->hasMany(CustomsRequest::class);
}
}
