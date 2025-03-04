<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AttributeController extends Controller
{

    public function getAttributes()
    {
        $attributes = Attribute::with(['values.project'])->get();

        $response = [
            'data' => $attributes,
            'meta' => [
                'message' => 'Successfully get attributes.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function getDetailAttribute($id)
    {
        $attribute = Attribute::with(['values.project'])->find($id);

        if (!$attribute) {
            Log::error("Get attribute failed: Attribute with ID $id not found.");
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Attribute not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $response = [
            'data' => $attribute,
            'meta' => [
                'message' => 'Successfully get attribute.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    public function createAttribute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //Assuming attributes name can't be the same with unique validation
            'name' => 'required|string|max:100|unique:attributes',
            'type' => 'required|in:text,date,number,select'
        ]);

        if ($validator->fails()) {
            //Log will be used to trace error in servers
            Log::error('Error in attribute input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $attributes = Attribute::create($request->all());

        if ($attributes) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Successfully create attributes.',
                    'status_code' => Response::HTTP_OK
                ]
            ];
            return response()->json($response, Response::HTTP_OK);
        } else {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to create attributes.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateAttribute(Request $request, $id)
    {
        // Find the attribute by ID
        $attribute = Attribute::find($id);
        if (!$attribute) {
            Log::error("Update failed: Attribute ID $id not found.");
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Attribute not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('attributes', 'name')->ignore($attribute->id), // Ignore current attribute's name
            ],
            'type' => 'required|in:text,date,number,select'
        ]);

        if ($validator->fails()) {
            //Log will be used to trace error in servers
            Log::error('Error in attribute update input validation: ' . $validator->errors()->all()[0]);
            $response = [
                'data' => '',
                'meta' => [
                    'message' => $validator->errors()->all()[0],
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        // Check if there are existing attribute values before updating the type
        if ($request->type !== $attribute->type) {
            $hasValues = AttributeValue::where('attribute_id', $id)->exists();

            if ($hasValues) {
                Log::error("Update failed: Cannot change type of attribute ID $id because values already exist for particular type.");
                $response = [
                    'data' => '',
                    'meta' => [
                        'message' => 'Failed to update attribute. Attribute value found for current type.',
                        'status_code' => Response::HTTP_BAD_REQUEST
                    ]
                ];
                return response()->json($response, Response::HTTP_BAD_REQUEST);
            }
        }

        $update_attribute = $attribute->update([
            'name' => $request->name,
            'type' => $request->type
        ]);

        if ($update_attribute) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Attribute updated successfully.',
                    'status_code' => Response::HTTP_OK
                ]
            ];
            return response()->json($response, Response::HTTP_OK);
        } else {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to update attribute.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteAttribute($id)
    {
        $attribute = Attribute::find($id);

        if (!$attribute) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Attribute not found.',
                    'status_code' => Response::HTTP_NOT_FOUND
                ]
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $check_attribute = AttributeValue::where('attribute_id', $id)->exists();

        if ($check_attribute) {
            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Failed to delete attribute. Attribute is in used.',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $attribute->delete();

        $response = [
            'data' => '',
            'meta' => [
                'message' => 'Successfully delete attribute.',
                'status_code' => Response::HTTP_OK
            ]
        ];
        return response()->json($response, Response::HTTP_OK);
    }
}
