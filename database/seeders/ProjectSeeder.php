<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Project;
use App\Models\Timesheet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Project A',
                'status' => 'active',
                'attributes' => [
                    ['attribute_id' => 1, 'value' => 'Engineering'],
                    ['attribute_id' => 2, 'value' => '2025-01-01'],
                    ['attribute_id' => 3, 'value' => '2026-01-01']
                ],
                'assigned_users' => [1] // Assign user ID 1
            ],
            [
                'name' => 'Project B',
                'status' => 'active',
                'attributes' => [
                    ['attribute_id' => 1, 'value' => 'Finance'],
                    ['attribute_id' => 2, 'value' => '2026-01-01'],
                    ['attribute_id' => 3, 'value' => '2026-01-01']
                ],
                'assigned_users' => [1]
            ]
        ];

        foreach ($projects as $projectData) {
            // Create Project
            $project = Project::create([
                'name' => $projectData['name'],
                'status' => $projectData['status'],
            ]);

            // Insert Attributes
            foreach ($projectData['attributes'] as $attribute) {
                AttributeValue::create([
                    'attribute_id' => $attribute['attribute_id'],
                    'project_id' => $project->id,
                    'value' => $attribute['value'],
                ]);
            }

            // Assign Users and Create Timesheets
            if (isset($projectData['assigned_users'])) {
                foreach ($projectData['assigned_users'] as $userId) {
                    $project->users()->attach($userId); // Assign user to project

                    // Create timesheet for user in this project
                    Timesheet::create([
                        'user_id' => $userId,
                        'project_id' => $project->id,
                        'task_name' => 'Survey Location',
                        'hours' => rand(1, 40), // Random hours between 5-40
                        'date' => '2025-01-01'
                    ]);
                }
            }
        }
    }
}
