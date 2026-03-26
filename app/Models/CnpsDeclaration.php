<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CnpsDeclaration extends Model
{
    protected $guarded = [];
    public function declarant() {
        return $this->belongsTo(User::class, 'declarant_id');
    }

    public function beneficiary() {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }
}
