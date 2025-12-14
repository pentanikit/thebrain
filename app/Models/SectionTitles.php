<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionTitles extends Model
{
    protected $fillable = [
        'category_type',
        'section_title',
        'key',
        'value',
    ];
}
