<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

 /**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="due_date", type="string", format="date-time"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "on_hold"}),
 *     @OA\Property(property="created_by", type="integer"),
 *     @OA\Property(property="assigned_to", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class Task extends Model
{
    use HasFactory, SoftDeletes;

     protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'created_by',
        'assigned_to'
     ];

     protected $casts = [
        'due_date' => 'datetime',
    ];

     public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }
}