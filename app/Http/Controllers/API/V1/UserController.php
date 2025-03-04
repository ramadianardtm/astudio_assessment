<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function getAllUsers()
    {
        $users = User::with(['projects.attributes', 'projects.timesheets'])->get();

        $response = [
            'data' => $users,
            'meta' => [
                'message' => 'Successfully get users.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function getDetailUser($id)
    {
        $user = User::with(['projects.attributes', 'projects.timesheets'])->find($id);

        if (!$user) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'User not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $response = [
            'data' => $user,
            'meta' => [
                'message' => 'Successfully get user.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users', 'email')->ignore(auth()->id()), // Ignore current user's email
            ]
        ]);

        if ($validator->fails()) {
            //Log will be used to trace error in servers
            Log::error('Error in update profile input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }
        //Assuming that we only allow to update our own account
        $user = auth()->user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->save();

        $response = [
            'data' => '',
            'meta' => [
                'message' => 'Update profile successful.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }
}
