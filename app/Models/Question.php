<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'question',
        'answers',
        'correct_answer',
        'source',
    ];

    protected $casts = [
        'answers' => 'array',
    ];
}
