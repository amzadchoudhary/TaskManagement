<?php
// tests/Feature/TaskControllerTest.php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Task;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_all_tasks()
    {
        Task::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('task');
        $this->assertCount(3, $response->viewData('task'));
    }

    /** @test */
    public function it_can_create_a_task()
    {
        $response = $this->postJson('/tasks', ['name' => 'New Task']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('task', ['name' => 'New Task']);
    }

    /** @test */
    public function it_cannot_create_duplicate_tasks()
    {
        $task = Task::factory()->create(['name' => 'Unique Task']);

        $response = $this->postJson('/tasks', ['name' => 'Unique Task']);

        $response->assertStatus(422); // Validation error for duplicate
        $this->assertCount(1, Task::where('name', 'Unique Task')->get());
    }

    /** @test */
    public function it_can_update_task_completion_status()
    {
        $task = Task::factory()->create(['completed' => 0]);

        // First update: Mark the task as completed
        $response = $this->patchJson("/tasks/{$task->id}", ['completed' => 1]);
        $response->assertStatus(200);
        $this->assertTrue($task->fresh()->completed); 

        // Second update: Mark the task as incomplete
        $response = $this->patchJson("/tasks/{$task->id}", ['completed' => 0]);
        $response->assertStatus(200);
        $this->assertFalse($task->fresh()->completed); 
    }


    /** @test */
    public function it_can_delete_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/tasks/{$task->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('task', ['id' => $task->id]);
    }
}