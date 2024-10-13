<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    // Add the fillable attributes
    protected $fillable = [
        'name',        // Allow mass assignment for name
        'description', // Allow mass assignment for description
        'image',       // Allow mass assignment for image
        'path',        // Allow mass assignment for path
    ];
}
