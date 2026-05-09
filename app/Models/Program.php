<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = ['name', 'description', 'amil_percentage'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
