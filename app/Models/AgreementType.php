<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgreementType extends Model
{
    /** @use HasFactory<\Database\Factories\AgreementTypeFactory> */
    use HasFactory;
    protected $fillable = ['type'];
}
