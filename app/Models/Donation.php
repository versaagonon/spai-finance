<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'donor_name',
        'bank_receiver',
        'amount',
        'amil_percentage',
        'amil_amount',
        'managed_fund',
        'project_id',
        'program',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'amil_percentage' => 'decimal:2',
        'amil_amount' => 'decimal:2',
        'managed_fund' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Boot method to auto-calculate amil_amount and managed_fund before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($donation) {
            if ($donation->amount && $donation->amil_percentage !== null) {
                // Calculate amil amount based on percentage
                $donation->amil_amount = $donation->amount * ($donation->amil_percentage / 100);
                // Calculate managed fund (Sisa Dana Kelola)
                $donation->managed_fund = $donation->amount - $donation->amil_amount;
            }
        });
    }
}
