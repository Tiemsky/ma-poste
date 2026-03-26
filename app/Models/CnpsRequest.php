<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CnpsRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'has_cnps_number' => 'boolean',
        'hire_date' => 'date',
        'monthly_gross_salary' => 'decimal:2',
        'additional_documents' => 'array',
        'processed_at' => 'datetime',
    ];

    // Auto-génération de la référence (Ex: CNPS-2026-ABC12)
       protected static function booted(): void
    {
        // BUG #6 FIX: Génération automatique de la référence unique
        // Format: CNPS-YYYYMMDD-XXXXXXXX (ex: CNPS-20240315-A3F7B2C1)
        static::creating(function (CnpsRequest $model) {
            if (empty($model->reference)) {
                $model->reference = 'CNPS-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
            }
            if (empty($model->status)) {
                $model->status = 'pending';
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function service(){
      return $this->belongsTo(Service::class);
    }
}
