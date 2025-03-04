<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $table = 'timesheets';

    protected $fillable = [
        'user_id',
        'project_id',
        'task_name',
        'date',
        'hours'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'user_id',
        'project_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
