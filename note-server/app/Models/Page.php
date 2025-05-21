<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $appends = ['slug'];

    protected $hidden = ['user_id'];

    protected $fillable = [
        'title',
        'icon',
        'order',
        'parent_id',
        'user_id',
        'uuid'
    ];

    protected static function booted() {
        static::creating(function ($page) {
            $page->uuid = Str::uuid()->toString();
        });
    }

    // Beziehung zu Benutzer
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Beziehung zur übergeordneten Seite (Parent)
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    // Beziehung zu untergeordneten Seiten (Children)
    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function getSlugAttribute()
    {
        $slug = Str::slug($this->title);
        return "{$this->uuid}-{$slug}";
    }

}
