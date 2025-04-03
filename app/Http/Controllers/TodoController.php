<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TodoController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // Retrieve all todos that belong to the authenticated user
        $todos = Todo::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($todos, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming request to ensure email and password are provided
        $request->validate([
            'title' => 'required|string',
            'completed' => 'nullable|boolean',
            'reminder_at' => 'nullable|date'
        ]);

        // Parse and format the reminder date if provided
        $reminderAt = null;
        if ($request->reminder_at) {
            // Convert the reminder_at string to a standardized datetime format
            // using Carbon for consistent database storage
            $reminderAt = Carbon::parse($request->reminder_at)->format('Y-m-d H:i:s');
        }
        $todo = Todo::create([
            'title' => $request->title,
            'completed' => $request->completed ?? false,
            'reminder_at' => $reminderAt,
            'user_id' => Auth::id()
        ]);

        return response()->json($todo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $todo = Todo::find($id);

        if (!$todo || $todo->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($todo, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Todo $todo): JsonResponse
    {
        // Using policy to check if the user can update the todo
        $this->authorize('update', $todo);

        // Validate only the completed status
        $request->validate([
            'completed' => 'nullable|boolean',
        ]);

        // Only update the fields that are provided in the request
        $todo->update([
            'completed' => $request->has('completed') ? $request->completed : $todo->completed,
        ]);

        return response()->json($todo, 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo): JsonResponse
    {
        // Using policy to check if the user can delete the todo
        $this->authorize('delete', $todo);

        $todo->delete();

        return response()->json(null, 204);
    }
}
