<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    use ApiResponse;
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        // dd($credentials);
        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }
        return $this->errorResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
        // return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        // dd(Auth::guard('api')->user());
        return $this->successResponse($this->guard()->user(), 'Succesfully Fetched', Response::HTTP_OK);
        // return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60 * 24 * 30,
        ];
        return $this->successResponse($data, 'Successfully Created', Response::HTTP_OK);
        // return response()->json([
        //     'access_token' => $token,
        //     'token_type' => 'bearer',
        //     'expires_in' => $this->guard()->factory()->getTTL() * 60
        // ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }

    public function register(RegisterRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!empty($user)) {
            return response()->json(
                [
                    'message' => 'user already exists with this email',

                ],
                Response::HTTP_NOT_ACCEPTABLE
            );
        }
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'lat' => $request->lat,
            'lon' => $request->lon,
        ];
        if ($request->hasFile('image')) {

            // $path = $request->file('image')->store('images');
            $imageName = time(). '.' . $request->file('image')->extension();
            $request->file('image')->move(public_path('/user/images'), $imageName);
            $data['image'] =  asset('/user/images/' . $imageName);
        }
        // dd($data);
        try {
            User::create($data);
            $credentials = $request->only('email', 'password');

            if ($token = $this->guard()->attempt($credentials)) {
                return $this->respondWithToken($token);
            }
        } catch (Exception $e) {
            return response()->json($e);
        }
    }
}
