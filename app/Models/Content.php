<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;


    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'type',
        'file_url',
        'is_published',
        'published_at'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function tags()
    {
        return $this->hasMany(ContentTag::class, 'content_id');
    }

}
