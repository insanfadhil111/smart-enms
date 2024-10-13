<?php
// app/Models/PvData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PvData extends Model
{
    use HasFactory;
    protected $table = 'pv_data';
    protected $fillable = [
        'Vdc',
        'Idc',
        'Power',
        'Energydc',
        'Vac',
        'Iac',
        'Power_ac',
        'Frequency',
        'Power_factor',
        'Energy_ac',
    ];
}