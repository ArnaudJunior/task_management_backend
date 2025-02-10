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
    /**
     * @OA\Get(
     *     path="/api/v1/tasks/{task}/attachments",
     *     summary="Liste des pièces jointes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des pièces jointes",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TaskAttachment"))
     *     )
     * )
     */
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $attachments = $task->attachments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return TaskAttachmentResource::collection($attachments);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/tasks/{task}/attachments",
     *     summary="Ajouter une pièce jointe",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pièce jointe ajoutée",
     *         @OA\JsonContent(ref="#/components/schemas/TaskAttachment")
     *     )
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/api/v1/attachments/{attachment}/download",
     *     summary="Télécharger une pièce jointe",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="attachment",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pièce jointe téléchargée",
     *         @OA\JsonContent(type="string", format="binary")
     *     )
     * )
     */
    public function download(TaskAttachment $attachment)
    {
        $this->authorize('view', $attachment->task);

        return Storage::disk('public')->download(
            $attachment->filename,
            $attachment->original_filename
        );
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/attachments/{attachment}",
     *     summary="Supprimer une pièce jointe",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="attachment",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pièce jointe supprimée",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Attachment deleted successfully"))
     *     )
     * )
     */
    public function destroy(TaskAttachment $attachment)
    {
        $this->authorize('delete', $attachment);

        Storage::disk('public')->delete($attachment->filename);
        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully']);
    }
}
