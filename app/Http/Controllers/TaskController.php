<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('home', ['tasks' => $tasks]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            ]);

        $tasks = new Task();
        $tasks->name = $request->name;
        $tasks->user_id = Auth::user()->id;
        $tasks->save();
        return Response::json($tasks);
    }

    public function update(Task $tasks, Request $request)
    {
        $request->validate([
            'name' => 'required',
            ]);

        $tasks->name = $request->name;
        $tasks->user_id = Auth::user()->id;
        $tasks->save();
        return Response::json($tasks);
    }

    public function destroy(Task $tasks,Request $request)
    {
        $id = $request->name;
        Task::where('id', $id)->delete();
        // $tasks = Task::all();
        return Response::json($id);
    }
}
