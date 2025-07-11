<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;



    protected $fillable = [
        'name',
        'slug'
    ];



    public function content() 
    {
        return $this->hasMany(ContentTag::class, 'tag_id');
    }
}
