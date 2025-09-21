<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['tag'];

    public function deliveries()
    {
        return $this->belongsToMany(Delivery::class, 'delivery_tag');
    }
}
