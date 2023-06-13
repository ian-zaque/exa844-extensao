<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Role;

class AuthController extends Controller {

    /**
     * Cria uma nova instância desta classe
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register',]]);
    }

    /**
     * Loga o usuário através do JWT das credenciais dadas.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) { return response()->json($validator->errors(), 422); }

        $credentials = $request->all();

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Cadastra um usuário.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){ return response()->json($validator->errors()->toJson(), 400); }

        $user = User::create(array_merge(
                    $validator->validated(),
                    [
                        'password' => bcrypt($request->password),
                        'email_verified_at' => now(),
                    ]
                ));
        // $user->sendEmailVerificationNotification();
        $role = Role::where('role', '=', 'adm')->first();

        DB::table('user_roles')->insert([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $user = User::with(['role'])->where('id','=',$user->id)->first();

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Cria um usuário de certo \App\Role.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:adm,normal',
        ]);

        if($validator->fails()){ return response()->json($validator->errors()->toJson(), 400); }

        $user = User::create(array_merge(
                    $validator->validated(),
                    [
                        'password' => bcrypt($request->password),
                        'email_verified_at' => now(),
                    ]
                ));
        // $user->sendEmailVerificationNotification();

        $role = Role::where('role', '=', $request['role'])->first();

        DB::table('user_roles')->insert([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $user = User::with(['role'])->where('id','=',$user->id)->first();

        return response()->json([
            'message' => 'User successfully created',
            'user' => $user
        ], 201);
    }

    /**
     * Atualiza um usuário.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|between:2,100',
            'email' => 'sometimes|string|email|max:100|unique:users',
            'role' => 'sometimes|string|in:adm,normal',
        ]);

        if($validator->fails()){ return response()->json($validator->errors()->toJson(), 400); }

        $user = User::find($request->id);
        $user->update($request->all());
        $user->save();

        $role = Role::where('role', '=', $request['role'])->first();

        DB::table('user_roles')
            ->where('user_id', '=', $request->id)
            ->update([
                'role_id' => $role->id,
                'updated_at' => now(),
            ]);

        $user = User::with(['role'])->where('id','=',$user->id)->first();

        return response()->json([
            'message' => 'User successfully updated',
            'user' => $user
        ], 201);
    }

    /**
     * Desloga o usuário (Invalida o token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Recarrega um token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->respondWithToken(auth()->refresh());
    }

   /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token) {   
        $user = User::with(['role'])->where('id','=',auth()->user()->id)->first();

        return response()->json([
            'access_token' => $token,
            'user' => $user,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 600
        ]);
    }

}
