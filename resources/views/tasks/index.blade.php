<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Task Management</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr/build/toastr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastr/build/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* General styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .task-input {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .task-input input {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .task-input button {
            padding: 10px 20px;
            margin-left: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .task-input button:hover {
            background-color: #218838;
        }

        .filter-buttons {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter-buttons button {
            padding: 10px 15px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #007bff;
            color: #fff;
        }

        .filter-buttons button.active {
            background-color: #0056b3;
        }

        .filter-buttons button:hover {
            background-color: #0056b3;
        }

        ul#task-list {
            list-style-type: none;
            padding: 0;
        }

        ul#task-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        ul#task-list li.completed {
            text-decoration: line-through;
            color: #888;
        }

        ul#task-list li button {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 18px;
        }

        ul#task-list li button:hover {
            color: #bd2130;
        }

        /* Loading spinner */
        .spinner {
            display: none;
            text-align: center;
        }

        .spinner i {
            font-size: 24px;
            color: #007bff;
        }

        /* Modal Styling */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.4); 
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px; 
            border-radius: 8px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
        }

        .modal-footer button {
            padding: 10px 20px;
            margin-left: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-footer .btn-confirm {
            background-color: #dc3545;
            color: #fff;
        }

        .modal-footer .btn-confirm:hover {
            background-color: #bd2130;
        }

        .modal-footer .btn-cancel {
            background-color: #6c757d;
            color: #fff;
        }

        .modal-footer .btn-cancel:hover {
            background-color: #5a6268;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Task Management</h1>

        <!-- Task Input and Add Button -->
        <div class="task-input">
            <input type="text" id="task-name" placeholder="Enter task name">
            <button onclick="addTask()">Add Task</button>
        </div>
        
        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <button onclick="filterTasks('all')" class="active">Show All</button>
            <button onclick="filterTasks('completed')">Show Completed</button>
            <button onclick="filterTasks('incomplete')">Show Incomplete</button>
        </div>
        
        <!-- Task List -->
        <ul id="task-list">
            @foreach($task as $task)
                <li data-id="{{ $task->id }}" class="{{ $task->completed ? 'completed' : '' }}">
                    <input type="checkbox" {{ $task->completed ? 'checked' : '' }} onclick="toggleTask({{ $task->id }})">
                    {{ $task->name }}
                    <button onclick="confirmDeleteTask({{ $task->id }})"><i class="fas fa-trash"></i></button>
                </li>
            @endforeach
        </ul>

        <!-- Loading Spinner -->
        <div class="spinner" id="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="delete-modal" class="modal">
            <div class="modal-content">
                <h2>Confirm Deletion</h2>
                <p>Are you sure you want to delete this task?</p>
                <div class="modal-footer">
                    <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button class="btn-confirm" id="confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let taskIdToDelete = null;

        function addTask() {
            const name = document.getElementById('task-name').value;

            document.getElementById('loading-spinner').style.display = 'block';

            axios.post('/tasks', { name })
                .then(response => {
                    const task = response.data;
                    document.getElementById('task-list').insertAdjacentHTML('beforeend', `
                        <li data-id="${task.id}">
                            <input type="checkbox" onclick="toggleTask(${task.id})">
                            ${task.name}
                            <button onclick="confirmDeleteTask(${task.id})"><i class="fas fa-trash"></i></button>
                        </li>
                    `);
                    document.getElementById('task-name').value = '';
                    Swal.fire({
                       icon: 'success',
                       title: 'Task added successfully!',
                       text: 'The task has been added to your list.',
                    });
                })
                .catch(error => {
                    
                    if (error.response && error.response.status === 422) {
                        const errors = error.response.data.errors;
                        Swal.fire({
                       icon: 'error',
                       title: 'Failed!',
                       text: errors.name[0],
                    });
                    } else {
                        Swal.fire({
                       icon: 'error',
                       title: 'Failed!',
                       text: 'An error occurred while adding the task.',
                        });
            }
                })
                .finally(() => {
                    document.getElementById('loading-spinner').style.display = 'none';
                });
        }

        function toggleTask(id) {
            const checkbox = document.querySelector(`li[data-id="${id}"] input[type="checkbox"]`);
            const completed = checkbox.checked;

            document.getElementById('loading-spinner').style.display = 'block';

            axios.patch(`/tasks/${id}`, { completed })
                .then(response => {
                    const task = response.data;
                    const taskElement = document.querySelector(`li[data-id="${id}"]`);
                    checkbox.checked = task.completed;
                    taskElement.classList.toggle('completed', task.completed);
                })
                .catch(error => {
                    console.error('There was an error updating the task:', error);
                    checkbox.checked = !completed;
                    Swal.fire({
                       icon: 'error',
                       title: 'Failed!',
                       text: 'Failed to update the task.',
                    });
                    // toastr.error('Failed to update the task.', 'Error');
                })
                .finally(() => {
                    document.getElementById('loading-spinner').style.display = 'none';
                });
        }

        function confirmDeleteTask(id) {
            taskIdToDelete = id;
            document.getElementById('delete-modal').style.display = 'block';
        }

        function deleteTask() {
            document.getElementById('loading-spinner').style.display = 'block';

            axios.delete(`/tasks/${taskIdToDelete}`)
                .then(response => {
                    const taskElement = document.querySelector(`li[data-id="${taskIdToDelete}"]`);
                    taskElement.remove();
                    closeModal();
                    Swal.fire({
                       icon: 'success',
                       title:  'Success',
                       text: 'Task deleted successfully.',
                    });
                    // toastr.success('Task deleted successfully.', 'Success');
                })
                .catch(error => {
                    Swal.fire({
                       icon: 'error',
                       title: 'Failed!',
                       text: 'Failed to delete the task.',
                    });
                })
                .finally(() => {
                    document.getElementById('loading-spinner').style.display = 'none';
                });
        }

        function closeModal() {
            document.getElementById('delete-modal').style.display = 'none';
        }

        document.getElementById('confirm-delete').addEventListener('click', deleteTask);

        function filterTasks(filter) {
            document.getElementById('loading-spinner').style.display = 'block';

            axios.get(`/tasks/filter/${filter}`)
                .then(response => {
                    const tasks = response.data;
                    const taskList = document.getElementById('task-list');
                    taskList.innerHTML = '';

                    tasks.forEach(task => {
                        taskList.insertAdjacentHTML('beforeend', `
                            <li data-id="${task.id}" class="${task.completed ? 'completed' : ''}">
                                <input type="checkbox" ${task.completed ? 'checked' : ''} onclick="toggleTask(${task.id})">
                                ${task.name}
                                <button onclick="confirmDeleteTask(${task.id})"><i class="fas fa-trash"></i></button>
                            </li>
                        `);
                    });

                    document.querySelectorAll('.filter-buttons button').forEach(button => {
                        button.classList.remove('active');
                    });
                    document.querySelector(`.filter-buttons button[onclick="filterTasks('${filter}')"]`).classList.add('active');
                })
                .finally(() => {
                    document.getElementById('loading-spinner').style.display = 'none';
                });
        }
    </script>
</body>
</html>
