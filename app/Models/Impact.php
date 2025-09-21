<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Impact extends Model
{
    use HasFactory;

    protected $fillable = ['impact'];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
