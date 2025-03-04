<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function getProjects(Request $request)
    {
        // Load project with attributes and assigned users
        $query = Project::with(['attributes.attribute', 'users.timesheets']);

        // Apply filters dynamically
        if ($request->has('filters')) {
            foreach ($request->filters as $key => $condition) {
                foreach ($condition as $operator => $value) {
                    if (in_array($key, ['name', 'status'])) {
                        // Regular fields filtering
                        if ($operator === 'LIKE') {
                            $query->where("projects.$key", 'LIKE', "%$value%");
                        } else {
                            $query->where("projects.$key", $operator, $value);
                        }
                    } else {
                        // EAV attributes filtering
                        $query->whereHas('attributes', function ($q) use ($key, $operator, $value) {
                            $q->whereHas('attribute', function ($subQuery) use ($key) {
                                $subQuery->where('name', $key);
                            });

                            if ($operator === 'LIKE') {
                                $q->where('value', 'LIKE', "%$value%");
                            } else {
                                $q->where('value', $operator, $value);
                            }
                        });
                    }
                }
            }
        }

        // Get the projects
        $projects = $query->get();

        // Transform the response to include attributes
        $projects->transform(function ($project) {
            $attributes = [];
            foreach ($project->attributes as $attributeValue) {
                $attributes[$attributeValue->attribute->name] = $attributeValue->value;
            }

            return [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'attributes' => $attributes,
                'assigned_users' => $project->users
            ];
        });

        $response = [
            'data' => $projects,
            'meta' => [
                'message' => 'Successfully get projects.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function getDetailProject($id)
    {
        // Load project with attributes and assigned users
        $project = Project::with(['attributes.attribute', 'users'])->find($id);

        if (!$project) {
            Log::error("Get project failed: Project with ID $id not found.");
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Project not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        // Transform attributes into key-value pairs
        $attributes = [];
        foreach ($project->attributes as $attributeValue) {
            $attributes[$attributeValue->attribute->name] = $attributeValue->value;
        }

        // Format the project data
        $projectData = [
            'id' => $project->id,
            'name' => $project->name,
            'status' => $project->status,
            'attributes' => $attributes,
            'assigned_users' => $project->users
        ];

        $response = [
            'data' => $projectData,
            'meta' => [
                'message' => 'Successfully get project.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function createProject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects',
            'status' => 'required|in:active,inactive,completed',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required_with:attributes|exists:attributes,id|distinct',
            'attributes.*.value' => 'required_with:attributes|string'
        ]);

        if ($validator->fails()) {
            //Log will be used to trace error in servers
            Log::error('Error in project input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            // Create project
            $project = Project::create([
                'name' => $request->name,
                'status' => $request->status
            ]);

            // Store EAV attributes if provided
            if ($request->has('attributes')) {

                $attributes = $request->input('attributes', []);

                foreach ($attributes as $attr) {
                    //Check attribute value to match with the attribute type
                    $attribute = Attribute::find($attr['attribute_id']);

                    // Validate value based on attribute type
                    if (!$this->isValidAttributeValue($attribute->type, $attr['value'])) {
                        $response = [
                            'data' => '',
                            'meta' => [
                                'message' => 'Attribute value is not valid for the selected attribute type',
                                'status_code' => Response::HTTP_BAD_REQUEST
                            ]
                        ];
                        return response()->json($response, Response::HTTP_BAD_REQUEST);
                    }

                    AttributeValue::create([
                        'attribute_id' => $attr['attribute_id'],
                        'project_id' => $project->id,
                        'value' => $attr['value']
                    ]);
                }
            }

            //If all creation is correct commit the DB creation.
            DB::commit();
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Successfully create project.',
                    'status_code' => Response::HTTP_OK
                ]
            ];
            return response()->json($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            //If some creation are failed cancel all the DB creation. Because it has relation.
            DB::rollBack();
            Log::error("Failed to create project: " . $e->getMessage());
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to create project.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateProject(Request $request, $id)
    {
        // Find the project by ID
        $project = Project::find($id);
        if (!$project) {
            Log::error("Update failed: Project ID $id not found.");
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Project not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('projects', 'name')->ignore($project->id), // Ignore current project's name
            ],
            'status' => 'required|in:active,inactive,completed',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_value_id' => 'nullable|exists:attributes_value,id',
            'attributes.*.attribute_id' => 'required_with:attributes|exists:attributes,id|distinct',
            'attributes.*.value' => 'required_with:attributes|string',
            'removed_attributes' => 'nullable|array',
            'removed_attributes.*' => 'integer|exists:attributes_value,id',
        ]);

        if ($validator->fails()) {
            Log::error('Error in project update validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            // Update project details
            $project->update([
                'name' => $request->name,
                'status' => $request->status
            ]);

            // Remove attribute values if provided
            if ($request->has('removed_attributes') && is_array($request->removed_attributes)) {
                AttributeValue::whereIn('id', $request->removed_attributes)->delete();
            }

            // Process EAV attributes if provided
            if ($request->has('attributes')) {
                $attributes = $request->input('attributes', []);
                foreach ($attributes as $attr) {
                    $attribute = Attribute::find($attr['attribute_id']);

                    // Validate attribute value type
                    if (!$this->isValidAttributeValue($attribute->type, $attr['value'])) {
                        $response = [
                            'data' => '',
                            'meta' => [
                                'message' => 'Attribute value is not valid for the selected attribute type',
                                'status_code' => Response::HTTP_BAD_REQUEST
                            ]
                        ];
                        return response()->json($response, Response::HTTP_BAD_REQUEST);
                    }

                    // Check if attribute_value_id provided, then update the attribute value
                    if ($attr['attribute_value_id'] != null) {
                        $existingValue = AttributeValue::where('id', $attr['attribute_value_id'])->first();
                        if ($existingValue) {
                            $existingValue->update([
                                'attribute_id' => $attr['attribute_id'],
                                'value' => $attr['value']
                            ]);
                        }
                        //Else create new attribute value
                    } else {
                        AttributeValue::create([
                            'attribute_id' => $attr['attribute_id'],
                            'project_id' => $project->id,
                            'value' => $attr['value']
                        ]);
                    }
                }
            }

            //If all update are correct commit the DB update.
            DB::commit();
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Successfully update project.',
                    'status_code' => Response::HTTP_OK
                ]
            ];
            return response()->json($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            //If some updates are failed cancel all the DB updates.
            DB::rollBack();
            Log::error("Failed to update project: " . $e->getMessage());
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to update project.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }
    }

    public function assignUsers(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            Log::error('Error input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $project = Project::find($id);

        if (!$project) {
            Log::error("Get project failed: Project with ID $id not found.");
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Project not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        // Prevent duplicate assignments
        $project->users()->syncWithoutDetaching($request->user_ids);

        $response = [
            'data' => '',
            'meta' => [
                'message' => 'Successfully assigned project.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function unassignUser(Request $request, $projectId, $userId)
    {
        $project = Project::find($projectId);
        $user = User::find($userId);

        if (!$project || !$user) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Project or User not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        // Check if user is assigned to the project
        if (!$project->users()->where('user_id', $userId)->exists()) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'User is not assigned to this project.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        // Delete timesheets associated with this user and project
        Timesheet::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->delete();

        // Remove user from project
        $project->users()->detach($userId);

        $response = [
            'data' => '',
            'meta' => [
                'message' => 'User unassigned from project and timesheets deleted.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function deleteProject($id)
    {
        $project = Project::find($id);

        if (!$project) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Project not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        // Check if the project has assigned users
        if ($project->users()->exists()) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Cannot delete project. Users are still assigned.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        // Delete related timesheets
        $project->timesheets()->delete();

        //Delete related attribute value
        AttributeValue::where('project_id', $id)
            ->delete();

        // Delete the project
        $project->delete();

        $response = [
            'data' => '',
            'meta' => [
                'message' => 'Successfully delete project.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function logTimesheet(Request $request)
    {
        // Ensure the user is authenticated
        $user = auth()->user();

        if (!$user) {
            Log::error('User not authenticated.');
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to log timesheet.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'task_name' => 'required|string|max:100',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0',
            'project_id' => 'required|exists:projects,id'
        ]);

        if ($validator->fails()) {
            Log::error('Error input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        // Ensure the user is assigned to the project
        $project = Project::findOrFail($request->project_id);
        if (!$project->users()->where('user_id', $user->id)->exists()) {
            Log::error("Failed to log timesheet. User is not Assigned to this Project.");
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to log timesheet. User is not Assigned to this Project.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        //Assuming that user only allowed to input their own timesheet based on authenticated user
        $timesheet = Timesheet::create([
            'task_name' => $request->task_name,
            'date' => $request->date,
            'hours' => $request->hours,
            'project_id' => $request->project_id,
            'user_id' => $user->id
        ]);

        if ($timesheet) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Timesheet logged successfully.',
                    'status_code' => Response::HTTP_OK
                ]
            ];
            return response()->json($response, Response::HTTP_OK);
        } else {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to log timesheet.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }
    }

    public function getAllTimesheets()
    {
        $timesheets = Timesheet::with(['project.users'])->get();
        $response = [
            'data' => $timesheets,
            'meta' => [
                'message' => 'Successfully get timesheets.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function updateTimesheet(Request $request, $timesheet_id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'task_name' => 'required|string|max:100',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0',
            'project_id' => 'required|exists:projects,id'
        ]);

        if ($validator->fails()) {
            Log::error('Error input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        // Ensure the user is authenticated
        $user = auth()->user();

        if (!$user) {
            Log::error('User not authenticated.');
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'User not authenticated.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        //Assuming that we only allowed to update our own timesheet
        $timesheet = Timesheet::where(['id' => $timesheet_id, 'user_id' => $user->id])->first();

        if (!$timesheet) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to update timesheet. Timesheet not found.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        // Ensure the user is assigned to the project
        $project = Project::findOrFail($request->project_id);
        if (!$project->users()->where('user_id', $user->id)->exists()) {
            Log::error("Failed to update timesheet. User is not Assigned to this Project.");
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to update timesheet. User is not Assigned to this Project.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $timesheet->task_name = $request->task_name;
        $timesheet->date = $request->date;
        $timesheet->hours = $request->hours;
        $timesheet->project_id = $request->project_id;
        $timesheet->save();

        $response = [
            'data' => '',
            'meta' => [
                'message' => 'Successfully update timesheet.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function deleteTimesheet($id)
    {
        // Ensure the user is authenticated
        $user = auth()->user();

        if (!$user) {
            Log::error('User not authenticated.');
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'User not authenticated.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        //Assuming that we only allowed to delete our own timesheet
        $timesheet = Timesheet::where(['id' => $id, 'user_id' => $user->id])->first();

        if (!$timesheet) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to update timesheet. Timesheet not found.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $timesheet->delete();
        $response = [
            'data' => '',
            'meta' => [
                'message' => 'Successfully delete timesheet.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }
    //Function to validate attribute value based on the attribute type
    private function isValidAttributeValue($type, $value)
    {
        switch ($type) {
            case 'number':
                return is_numeric($value);
            case 'date':
                return strtotime($value) !== false;
            case 'text':
                return is_string($value);
            case 'select':
                return is_string($value); //I dont know whats the proper input for select
            default:
                return false;
        }
    }
}
