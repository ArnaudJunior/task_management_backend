<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\TaskRequest;
Use App\Models\Comment;



class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/tasks",
     *     summary="Liste des tâches",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filtrer par priorité",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="due_date",
     *         in="query",
     *         description="Filtrer par date d'échéance",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tâches",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Task"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Task::with(['creator', 'assignee'])
            ->withCount(['comments', 'attachments'])
            ->where(function ($query) {
                $query->where('created_by', auth()->id())
                    ->orWhere('assigned_to', auth()->id());
            });

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        $tasks = $query->orderBy('due_date')->paginate(10);

        return TaskResource::collection($tasks);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks",
     *     summary="Créer une nouvelle tâche",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TaskRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tâche créée",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function store(TaskRequest $request)
    {
        $task = DB::transaction(function () use ($request) {
            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'status' => 'pending',
                'created_by' => auth()->id(),
                'assigned_to' => $request->assigned_to,
            ]);
            
            return $task;
        });

        return new TaskResource($task->load(['creator', 'assignee']));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tasks/{task}",
     *     summary="Afficher une tâche",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la tâche",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['creator', 'assignee', 'comments.user', 'attachments.user'])
            ->loadCount(['comments', 'attachments']);

        return new TaskResource($task);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/tasks/{task}",
     *     summary="Mettre à jour une tâche",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TaskRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tâche mise à jour",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task = DB::transaction(function () use ($request, $task) {
            $task->update([
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'assigned_to' => $request->assigned_to,
            ]);

            // Gérer les notifications de mise à jour ici

            return $task;
        });

        return new TaskResource($task->load(['creator', 'assignee']));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tasks/{task}",
     *     summary="Supprimer une tâche",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tâche supprimée",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        DB::transaction(function () use ($task) {
            $task->delete();
        });

        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/tasks/{task}/status",
     *     summary="Mettre à jour le statut d'une tâche",
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
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "on_hold"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,on_hold'
        ]);

        $task->update(['status' => $request->status]);

        return new TaskResource($task->load(['creator', 'assignee']));
    }
}
