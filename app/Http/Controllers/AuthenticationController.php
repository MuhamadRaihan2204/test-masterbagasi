<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        return response()->json([
            'success' => true,
            'data' => 'User register successfully',
        ], 200);
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('secToken')->plainTextToken;
            $success['name'] =  $user->name;

            return response()->json([
                'success' => true,
                'data' => $success,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'data' => 'Unauthorised',
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = auth('sanctum')->user();

        if ($user) {
            $user->tokens()->delete();
            return response([
                'message' => 'Successfully logged out'
            ]);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ], 200);
    }
    
}
