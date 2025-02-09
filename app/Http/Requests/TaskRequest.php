<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


    /**
     * @OA\Schema(
     *     schema="TaskRequest",
     *     type="object",
     *     required={"title", "due_date", "priority", "assigned_to"},
     *     @OA\Property(property="title", type="string"),
     *     @OA\Property(property="description", type="string"),
     *     @OA\Property(property="due_date", type="string", format="date-time"),
     *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}),
     *     @OA\Property(property="assigned_to", type="integer")
     * )
     */

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'required|exists:users,id'
        ];
    }
}
