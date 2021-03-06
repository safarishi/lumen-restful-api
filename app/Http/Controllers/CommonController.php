<?php

namespace App\Http\Controllers;

use DB;
use Request;

class CommonController extends ApiController
{

    /**
     * [dbRepository description]
     *
     * @param  string $connection 数据库连接名
     * @param  string $name       数据库表名（或集合名）
     * @return object
     */
    protected function dbRepository($connection, $name)
    {
        return DB::connection($connection)->table($name);
    }

    protected function article()
    {
        return $this->dbRepository('sqlsrv', 'articles')
            ->select('article_id as id', 'article_title as title', 'article_logo as thumbnail_url', 'article_writer as origin', 'article_whoadd as author', 'article_addtime as created_at')
            ->where('article_active', 1);

    }

    /**
     * 栏目信息固定返回
     *
     * @return object
     */
    protected function column()
    {
        return $this->dbRepository('sqlsrv', 'lanmu')
            ->select('lanmu_id as column_id', 'lanmu_name as column_name')
            ->where('lanmu_language', 'zh-cn')
            ->where('lanmu_active', 1);
    }

    /**
     * 增加图片前缀 url
     *
     * @param [type] $thumbnailUrl [description]
     */
    protected function addImagePrefixUrl($thumbnailUrl)
    {
        if (!empty($thumbnailUrl)) {
            return 'http://sisi-smu.org'.str_replace('\\', '/', $thumbnailUrl);
        }

        return '';
    }

    /**
     * 检查用户是否收藏文章
     *
     * @param  string $uid       用户id
     * @param  string $articleId 文章id
     * @return boolean
     */
    protected function checkUserStar($uid, $articleId)
    {
        $this->models['user'] = $this->dbRepository('mongodb', 'user');

        $user = $this->models['user']->find($uid);

        if ($user === null) {
            return false;
        }

        $starred = array();
        if (array_key_exists('starred_articles', $user)) {
            $starred = $user['starred_articles'];
        }

        return in_array($articleId, $starred, true);
    }

    /**
     * 获取用户id
     * 用户未登录返回空字符串 ''
     * 登录用户返回用户id
     *
     * @return string
     */
    protected function getUid()
    {
        $uid = '';

        if ($this->accessToken) {
            // 获取用户id
            $this->authorizer->validateAccessToken();
            $uid = $this->authorizer->getResourceOwnerId();
        }

        return $uid;
    }

    /**
     * 处理评论返回数据
     *
     * @param  array $response [description]
     * @return array           [description]
     */
    protected function handleCommentResponse($response)
    {
        $uid = $this->getUid();

        foreach ($response as &$value) {
            $nums = 0;
            $isFavoured = false;
            if (array_key_exists('favoured_user', $value)) {
                $favouredUser = $value['favoured_user'];
                $nums = count($favouredUser);
                $isFavoured = in_array($uid, $favouredUser, true);
            }
            $value['favours'] = $nums;
            $value['is_favoured'] = $isFavoured;

            $replyId = $value['_id']->{'$id'};
            $replies = $this->getReply($replyId);
            if ($replies) {
                $value['replies'] = $replies;
            }
        }
        unset($value);

        return $response;
    }

    protected function getReply($id)
    {
        return $this->dbRepository('mongodb', 'reply')
            ->select('created_at', 'content', 'user')
            ->where('comment_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();
    }

    /**
     * 判断用户是否点赞评论
     *
     * @param  string $uid       用户id
     * @param  string $commentId 文章评论id
     * @return boolean
     */
    protected function checkUserFavour($uid, $commentId)
    {
        $this->models['article_comment'] = $this->dbRepository('mongodb', 'article_comment');

        $comment = $this->models['article_comment']
            ->select('favoured_user')
            ->find($commentId);

        if ($comment === null) {
            return false;
        }

        $favouredUser = array();
        if (array_key_exists('favoured_user', $comment)) {
            $favouredUser = $comment['favoured_user'];
        }

        return in_array($uid, $favouredUser, true);
    }

    /**
     * 增加数据分页
     *
     * @param  object $model 需要分页的数据模型
     * @return void
     */
    protected function addPagination($model)
    {
        // 第几页数据，默认为第一页
        $page    = Request::input('page', 1);
        // 每页显示数据条目，默认为每页20条
        $perPage = Request::input('per_page', 20);
        $page    = intval($page);
        $perPage = intval($perPage);

        if ($page <= 0 || !is_int($page)) {
            $page = 1;
        }
        if (!is_int($perPage) || $perPage < 1 || $perPage > 100) {
            $perPage = 20;
        }
        // skip -- offset , take -- limit
        $model->skip(($page - 1) * $perPage)->take($perPage);
    }

}