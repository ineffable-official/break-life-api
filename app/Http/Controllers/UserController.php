<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = User::all();

            return response()->json(["message" => $data], 200);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "name" => "required|string",
                "email" => "required|email",
                "password" => "required|string",
            ]);

            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }

            $check = User::all()->where("email", $request->email);

            if (count($check) > 0) {
                return response()->json(["status" => 0, "message" => "Account already exists"], Response::HTTP_FOUND);
            }

            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = md5($request->password);
            $user->save();

            $token = $user->createToken($request->name)->plainTextToken;

            return response()->json(["message" => "Account successfully created", "data" => $user, "token" => $token, "token_type" => "bearer"], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "email" => "required|email",
                "password" => "required|string",
            ]);

            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }


            $user = User::all()->where("email", $request->email)->where("password", md5($request->password));

            if (count($user) > 0) {
                $token = $user->first()->createToken($user->first()->name)->plainTextToken;

                return response()->json(["message" => "Login successfully", "token" => $token, "token_type" => "bearer"], Response::HTTP_CREATED);
            }

            return response()->json(["status" => 0, "message" => "Email or password wrong"], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
