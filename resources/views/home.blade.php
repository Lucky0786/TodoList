@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="row">
            <div class="col-12 text-right">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addTaskModal">Add Task</button>
            </div>
            </div>
            <div class="row" style="clear: both;margin-top: 18px;">
                <div class="col-12">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>User_id</th>
                            <th>status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr id="task_{{$task->id}}">
                            <td>{{ $task->id  }}</td>
                            <td>{{ $task->name }}</td>
                            <td>{{ $task->user_id }}</td>
                            <td>{{ $task->status }}</td>
                            <td>
                                <a data-id="{{ $task->id }}" onclick="editTask(event.target)" class="btn btn-info">Edit</a>
                                <a class="btn btn-danger" onclick="deleteTask({{ $task->id }})">Delete</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
    
</div>
<div class="modal fade" id="addTaskModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Add Task</h4>
        </div>
        <div class="modal-body">

                <div class="form-group">
                    <label for="name" class="col-sm-2">Task</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="task" name="name" placeholder="Enter name">
                        <span id="taskError" class="alert-message"></span>
                    </div>
                </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="addTask()">Save</button>
        </div>
    </div>
  </div>
  
</div>
<div class="modal fade" id="editTaskModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Edit Task</h4>
        </div>
        <div class="modal-body">

               <input type="hidden" name="task_id" id="task_id">
                <div class="form-group">
                    <label for="name" class="col-sm-2">Task</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="edittask" name="name" placeholder="Enter name">
                        <span id="taskError" class="alert-message"></span>
                    </div>
                </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="updateTask()">Save</button>
        </div>
    </div>
  </div>
<script>

    function addTask() {
        var task = $('#task').val();
        let _url     = `/tasks/create`;
        let _token   = $('meta[name="csrf-token"]').attr('content');
// alert(task);
        $.ajax({
            url: _url,
            type: "POST",
            data: {
                name: task,
                _token: _token
            },
            success: function(data) {
                    task = data
                    $('table tbody').append(`
                        <tr id="task${task.id}">
                            <td>${task.id}</td>
                            <td>${ task.name }</td>
                            <td>${ task.user_id }</td>
                            <td>${ task.status}</td>
                            <td>
                                <a data-id="${ task.id }" onclick="editTask(${task.id})" class="btn btn-info">Edit</a>
                                <a data-id="${task.id}" class="btn btn-danger" onclick="deleteTask(${task.id})">Delete</a>
                            </td>
                        </tr>
                    `);

                    $('#task').val('');

                    $('#addTaskModal').modal('hide');
            },
            error: function(response) {
                $('#taskError').text(response.responseJSON.errors.task);
            }
        });
    }

    function deleteTask(id) {
        let url = `/tasks/${id}`;
        let token   = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: url,
            type: 'DELETE',
            data: {
             name: id,
            _token: token
            },
            success: function(response) {
                $("#task_"+id).remove();
            }
        });
    }

    function editTask(e) {
        var id  = $(e).data("id");
        var task  = $("#task_"+id+" td:nth-child(2)").html();
        $("#task").val(id);
        $("#edittask").val(task);
        $('#editTaskModal').modal('show');
    }

    function updateTask() {
        var task = $('#edittask').val();
        var id = $('#task_id').val();
        let _url     = `/tasks/${id}`;
        let _token   = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: _url,
            type: "PUT",
            data: {
                name: task,
                _token: _token
            },
            success: function(data) {
                    task = data
                    $("#task_"+id+" td:nth-child(2)").html(name.task);
                    $('#task_id').val('');
                    $('#edittask').val('');
                    $('#editTaskModal').modal('hide');
            },
            error: function(response) {
                $('#taskError').text(response.responseJSON.errors.task);
            }
        });
    }

</script>
@endsection
