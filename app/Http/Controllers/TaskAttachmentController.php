<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use App\Http\Resources\TaskAttachmentResource;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $attachments = $task->attachments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return TaskAttachmentResource::collection($attachments);
    }

    public function store(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $request->validate([
            'file' => 'required|file|max:10240' // Max 10MB
        ]);

        $file = $request->file('file');
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        
        $path = $file->storeAs('task_attachments', $filename, 'public');

        $attachment = $task->attachments()->create([
            'user_id' => auth()->id(),
            'filename' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize()
        ]);

        return new TaskAttachmentResource($attachment->load('user'));
    }

    public function download(TaskAttachment $attachment)
    {
        $this->authorize('view', $attachment->task);

        return Storage::disk('public')->download(
            $attachment->filename,
            $attachment->original_filename
        );
    }

    public function destroy(TaskAttachment $attachment)
    {
        $this->authorize('delete', $attachment);

        Storage::disk('public')->delete($attachment->filename);
        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully']);
    }
}
