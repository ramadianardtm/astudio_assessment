<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $table = 'attributes';

    protected $fillable = [
        'name',
        'type'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
