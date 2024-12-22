<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viewer extends Model
{

    // Defining the table name explicitly (optional if table name is plural form of model name).
    protected $table = 'viewers';

    // Columns that are mass assignable.
    protected $fillable = [
        'ip',
        'country',
        'region', 
        'city',
        'device_type',
        'operating_system',
        'error_reports',
        'frequency_of_visits',
        'first_visit',
        'last_visit',
    ];

    // If you need to specify attributes that are dates, add the following:
    protected $dates = ['first_visit', 'last_visit'];

    // Additional settings, if needed (e.g., for appending values or custom methods).
}
