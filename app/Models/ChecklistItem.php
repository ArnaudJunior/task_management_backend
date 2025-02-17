<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'item', 'isCompleted', 'order'];

     public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
