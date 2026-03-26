<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomsItem extends Model
{
    /** @use HasFactory<\Database\Factories\CustomsItemFactory> */
    use HasFactory;
    protected $guarded = [];

    public function request() {
        return $this->belongsTo(CustomsRequest::class, 'customs_request_id');
    }
}
