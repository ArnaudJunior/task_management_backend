<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Comment;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $comments = $task->comments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    public function store(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = $task->comments()->create([
            'content' => $request->content,
            'user_id' => auth()->id()
        ]);

        return new CommentResource($comment->load('user'));
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment->update([
            'content' => $request->content
        ]);

        return new CommentResource($comment->load('user'));
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        
        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
