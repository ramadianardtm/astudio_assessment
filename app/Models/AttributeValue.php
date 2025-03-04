<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $table = 'attributes_value';

    protected $fillable = [
        'attribute_id',
        'project_id',
        'value'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'attribute_id',
        'project_id'
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
