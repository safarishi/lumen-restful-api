<?php

namespace App\Http\Controllers;

use Hash;
use Request;
use Validator;
use App\Exceptions\ValidationException;

class UserController extends CommonController
{

    public function __construct()
    {
        // $this->middleware('oauth', ['except' => 'store']);
        // $this->middleware('disconnect:mongodb', ['only' => ['modify']]);
        // before middleware
        $this->middleware('oauth.checkClient', ['only' => 'store']);
    }

    /**
     * 用户注册校验邮箱唯一性
     *
     * @throws \App\Exceptions\ValidationException
     */
    protected function validateStoreEmail()
    {
        if (!Request::has('email')) {
            throw new ValidationException('邮箱不可为空');
        }

        $this->email = Request::input('email');

        $this->models['user'] = $this->dbRepository('mongodb', 'user');

        $outcome = $this->models['user']->where('email', $this->email)->first();

        if ($outcome) {
            throw new ValidationException('邮箱已被注册');
        }
    }

    public function store()
    {
        $this->validateStoreEmail();

        // validator
        $validator = Validator::make(Request::all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator->messages()->all());
        }

        $password = Request::input('password');

        $avatarUrl = '/uploads/images/avatar/default.png';
        $insertData = [
            'password'   => Hash::make($password),
            'avatar_url' => $avatarUrl,
            'email'      => $this->email,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $insertId = $this->models['user']->insertGetId($insertData);

        return $this->models['user']->find($insertId);
    }
}