<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sprint extends Model
{
    use HasFactory;

    protected $fillable = ['sprint'];

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
