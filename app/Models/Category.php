<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'slug',
        'description'
    ];



    public function content() 
    {
        return $this->hasMany(Content::class, 'category_id');
    }

    public function plan()
    {
        return $this->hasMany(SubscriptionPlan::class, 'category_id');
    }
}
