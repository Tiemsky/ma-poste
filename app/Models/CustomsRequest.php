<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomsRequest extends Model
{
    /** @use HasFactory<\Database\Factories\CustomsRequestFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted() {
        static::creating(function ($model) {
            $model->reference = 'DOUANE-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        });
    }

       public function user()
    {
        return $this->belongsTo(User::class);
    }

   public function service()
{
    return $this->belongsTo(Service::class);
}

    public function items() {
        return $this->hasMany(CustomsItem::class);
    }
}
