<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use Request;
use Validator;
use App\User;
use App\Exceptions\ValidationException;
use LucaDegasperi\OAuth2Server\Authorizer;

class UserController extends CommonController
{

    public function __construct(Authorizer $authorizer)
    {
        parent::__construct($authorizer);
        $this->middleware('oauth', ['except' => 'store']);
        $this->middleware('disconnect:mongodb', ['only' => ['show', 'myComment']]);
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

    /**
     * 修改用户信息前的校验
     *
     * @param  string $uid 用户id
     * @return void
     *
     * @throws \App\Exceptions\ValidationException
     */
    protected function prepareModify($uid)
    {
        // validator
        $validator = Validator::make(Request::all(), [
            'email'  => 'email',
            'gender' => 'in:男,女',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator->messages()->all());
        }

        if (Request::has('email')) {
            $this->validateModifyEmail($uid);
        }
    }

    /**
     * 修改用户信息的时候校验邮箱唯一性
     *
     * @param  string $uid 用户id
     * @return void
     *
     * @throws \App\Exceptions\ValidationException
     */
    protected function validateModifyEmail($uid)
    {
        $outcome = $this->dbRepository('mongodb', 'user')
            ->where('_id', '<>', $uid)
            ->where('email', Request::input('email'))
            ->first();

        if ($outcome) {
            throw new ValidationException('邮箱已被占用');
        }
    }

    public function modify()
    {
        $uid = $this->authorizer->getResourceOwnerId();

        $this->prepareModify($uid);

        $user = User::find($uid);

        // modify avatar_url todo
        $allowedFields = ['display_name', 'gender', 'email', 'company'];

        array_walk($allowedFields, function($item) use ($user, $uid) {
            $v = Request::input($item);
            if ($v && $item !== 'avatar_url') {
                $user->$item = $v;
            }
        });

        $user->save();

        return $this->dbRepository('mongodb', 'user')->find($uid);
    }

    public function logout()
    {
        $oauthAccessToken = DB::table('oauth_access_tokens');

        $oauthAccessToken->where('id', $this->accessToken)->delete();

        return response('', 204);
    }

    public function show()
    {
        $uid = $this->authorizer->getResourceOwnerId();

        return $this->dbRepository('mongodb', 'user')->find($uid);
    }

    public function myComment()
    {
        $uid = $this->authorizer->getResourceOwnerId();

        $this->models['article_comment'] = $this->dbRepository('mongodb', 'article_comment');

        $commentModel = $this->models['article_comment']
            ->where('user._id', $uid)
            ->orderBy('created_at', 'desc');

        $this->addPagination($commentModel);

        $data = $commentModel->get();

        return $this->handleCommentResponse($data);
    }

    public function myStar()
    {
        $uid = $this->authorizer->getResourceOwnerId();

        $user = $this->dbRepository('mongodb', 'user')->find($uid);

        if (!array_key_exists('starred_articles', $user)) {
            return [];
        }

        $articleModel = $this->article()
            ->whereIn('article_id', $user['starred_articles']);

        $this->addPagination($articleModel);

        return $articleModel->get();
    }
}