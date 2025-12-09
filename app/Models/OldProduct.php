<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldProduct extends Model
{
    // 👉 পুরনো DB connection
    protected $connection = 'mysql_legacy';

    // 👉 পুরনো products table এর actual নাম
    protected $table = 'products'; // যদি অন্য কিছু হয়, এখানে সেটাও দাও

    protected $fillable = [
        'name',
        'images',
        'category',
        'price',
        'color',
        'offer_price',
        'offer_duration',
        'sale_count',
        'size',
        'specification',
        'is_fav',
        'is_featured',
        'in_stock',
        'status',
    ];
}

