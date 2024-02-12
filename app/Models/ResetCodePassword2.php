<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetCodePassword2 extends Model
{
    use HasFactory;
    protected $fillable = [
        'email',
        'code',

    ];
}
