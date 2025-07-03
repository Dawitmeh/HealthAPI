<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;


    protected $fillable = [
        'category_id',
        'name',
        'price',
        'plan_type',
        'description'
    ];

    public function category() 
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
