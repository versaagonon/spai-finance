<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'pilar',
        'target_amount',
        'amil_percentage',
        'use_custom_amil',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function disbursements()
    {
        return $this->hasMany(Disbursement::class);
    }

    // Helper to calculate total income for this project
    public function getTotalIncomeAttribute()
    {
        if (array_key_exists('total_income', $this->attributes)) {
            return $this->attributes['total_income'];
        }
        if (array_key_exists('donations_sum_amount', $this->attributes)) {
            return $this->attributes['donations_sum_amount'];
        }
        return $this->donations()->sum('amount');
    }

    // Helper to calculate total expense for this project
    public function getTotalExpenseAttribute()
    {
        if (array_key_exists('total_expense', $this->attributes)) {
            return $this->attributes['total_expense'];
        }
        if (array_key_exists('disbursements_sum_amount', $this->attributes)) {
            $amount = $this->attributes['disbursements_sum_amount'] ?? 0;
            $admin = $this->attributes['disbursements_sum_admin_fee'] ?? 0;
            return $amount + $admin;
        }
        if (isset($this->total_expense_amount)) {
            return ($this->total_expense_amount ?? 0) + ($this->total_expense_fee ?? 0);
        }
        return $this->disbursements()->sum('amount') + $this->disbursements()->sum('admin_fee');
    }

    // Helper to calculate total amil taken from this project
    public function getTotalAmilAttribute()
    {
        if (array_key_exists('total_amil', $this->attributes)) {
            return $this->attributes['total_amil'];
        }
        if (array_key_exists('donations_sum_amil_amount', $this->attributes)) {
            return $this->attributes['donations_sum_amil_amount'];
        }
        return $this->donations()->sum('amil_amount');
    }

    // Helper to calculate remaining balance (Sisa Saldo)
    public function getBalanceAttribute()
    {
        if (trim(strtoupper($this->name)) === 'SPAI') {
            $totalAmilGlobal = Donation::sum('amil_amount');
            return $totalAmilGlobal - $this->getTotalExpenseAttribute();
        }

        if (isset($this->donations_sum_managed_fund)) {
             return $this->donations_sum_managed_fund - $this->getTotalExpenseAttribute();
        }

        return $this->donations()->sum('managed_fund') - $this->getTotalExpenseAttribute();
    }
}
