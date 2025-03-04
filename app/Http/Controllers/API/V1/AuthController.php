<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            //Log will be used to trace error in servers
            Log::error('Error in register input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $create_user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($create_user) {
            //Generate token if the flow required auto logged in after user registration, if not can be removed
            $generate_token = $create_user->createToken('ASTUDIO')->accessToken;
            $data = [
                'user' => $create_user,
                'token' => $generate_token
            ];
            $response = [
                'data' => $data,
                'meta' => [
                    'message' => 'Successfully create user.',
                    'status_code' => Response::HTTP_OK
                ]
            ];
            return response()->json($response, Response::HTTP_OK);
        } else {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to create user.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            Log::error('Error login input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        //Validate email and password, return error if wrong
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to login. Wrong email or password.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        if ($user) {
            // Generate token
            $token = $user->createToken('ASTUDIO')->accessToken;
            Log::info('Security Audit: User ' . $user['email'] . ' logged in successfully.');
            $response = [
                'data' => $user,
                'auth_token' => $token,
                'meta' => [
                    'message' => 'Login successful.',
                    'status_code' => Response::HTTP_OK
                ]
            ];
            return response()->json($response, Response::HTTP_OK);
        } else {
            Log::info('Security Audit: User ' . $user['email'] . ' failed to login.');
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Unauthorized login. Wrong email or password.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }
    }

    public function changePassword(Request $request)
    {
        //Assuming that the flow require to input old password and new password for checking
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            Log::error('Error login input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $user = Auth::user(); // Get authenticated user
        // Check if old password is correct
        if (!Hash::check($request->old_password, $user->password)) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Old password is not matched.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        $response = [
            'data' => '',
            'meta' => [
                'message' => 'Password changed successfully.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function logout()
    {
        // Ensure the user is authenticated
        $user = auth()->user();

        if (!$user) {
            Log::error('Logout attempt failed: User not authenticated.');
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to logout.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        // Revoke the token
        $user->token()->revoke();

        Log::info('Security Audit: User ' . $user['email'] . ' logged out successfully.');
        $response = [
            'data' => '',
            'meta' => [
                'message' => 'Logout successful.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function deleteAccount()
    {
        // Ensure the user is authenticated
        $user = auth()->user();

        if (!$user) {
            Log::error('Logout attempt failed: User not authenticated.');
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to logout.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        DB::beginTransaction();

        try {
            // Delete user's timesheets
            $user->timesheets()->delete();

            // Detach user from projects
            $user->projects()->detach();

            // Delete the user account
            $user->delete();

            // Revoke all access tokens (for Laravel Passport)
            $user->tokens()->delete();

            DB::commit();

            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Account deleted successfully.',
                    'status_code' => Response::HTTP_OK
                ]
            ];
            return response()->json($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to delete account. Please try again.',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR
                ]
            ];
            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
