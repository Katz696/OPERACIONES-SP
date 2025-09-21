<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'color'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function phases()
    {
        return $this->hasMany(Phase::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
