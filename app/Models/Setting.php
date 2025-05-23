<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['key', 'value'];
}
