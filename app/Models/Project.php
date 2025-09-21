<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'index',
        'title',
        'status_id',
        'days',
        'percentage',
        'percentage_planned',
        'percentage_progress',
        'start_date',
        'end_date',
        'real_end_date',
        'restriction_start_date',
        'restriction_end_date',
    ];

    // Relaciones

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function phases()
    {
        return $this->hasMany(Phase::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function stakeholders()
    {
        return $this->hasMany(Stakeholder::class);
    }

    public function requirements()
    {
        return $this->hasMany(Requirement::class);
    }
}
