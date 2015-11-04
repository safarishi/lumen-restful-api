<?php

namespace App\Http\Controllers;

use DB;
use Request;
use App\Exceptions\ValidationException;
use LucaDegasperi\OAuth2Server\Authorizer;
use App\Exceptions\DuplicateOperationException;

class ArticleController extends CommonController
{

    public function __construct(Authorizer $authorizer)
    {
        parent::__construct($authorizer);
        $this->middleware('disconnect:sqlsrv', ['only' => ['comment', 'index', 'show', 'report']]);
        $this->middleware('disconnect:mongodb', ['only' => ['comment', 'index', 'show', 'commentList', 'reply']]);
        $this->middleware('oauth', ['except' => ['index', 'show', 'report', 'commentList']]);
        $this->middleware('validation.required:content', ['only' => ['comment', 'reply']]);
    }

    public function index()
    {
        $pictureNews = $this->article()
            ->where('article_havelogo', 1)
            ->orderBy('article_addtime', 'desc')
            ->take(3)
            ->get();

        foreach ($pictureNews as $value) {
            $value->thumbnail_url = $this->addImagePrefixUrl($value->thumbnail_url);
        }

        // $columns = $this->getColumns();

        // foreach ($columns as $column) {
        //     // 获取栏目下的文章列表
        //     $column->articles = $this->getColumnArticle($column->column_id);
        // }

        return ['picture_news' => $pictureNews];
        // return ['picture_news' => $pictureNews, 'article_list' => $columns];
    }

    /**
     * [show description]
     * @param  string $id 文章id
     * @return [type]     [description]
     */
    public function show($id)
    {
        $article = $this->article()
            ->addSelect('article_body as content')
            ->where('article_id', $id)
            ->first();

        if ($article === null) {
            throw new ValidationException('文章 id 参数传递错误');
        }

        $article->thumbnail_url = $this->addImagePrefixUrl($article->thumbnail_url);

        $article->is_starred = $this->checkUserArticleStar($id);

        $this->origin = $article->origin;
        $related_articles = $this->getReleatedArticles($id);

        // return
        return [
            'article' => $article,
            'related_articles' => $related_articles,
        ];
    }

    protected function checkUserArticleStar($id)
    {
        $uid = $this->getUid();

        return $this->checkUserStar($uid, $id);
    }

    /**
     * [getReleatedArticles description]
     * @param  string $id 文章id
     * @return [type]     [description]
     */
    protected function getReleatedArticles($id)
    {
        return $this->article()
            ->where('article_writer', $this->origin)
            ->where('article_id', '<>', $id)
            ->orderBy('article_addtime', 'desc')
            ->take(2)
            ->get();
    }

    public function report()
    {
        return $this->dbRepository('sqlsrv', 'lanmu')
            ->select('lanmu_id as id', 'lanmu_name as name')
            ->where('lanmu_language', 'zh-cn')
            ->whereIn('lanmu_father', [113, 167, 168])
            ->get();
    }

    /**
     * [star description]
     * @param  string $id 文章id
     * @return array
     *
     * @throws \App\Exceptions\DuplicateOperationException
     */
    public function star($id)
    {
        $uid = $this->authorizer->getResourceOwnerId();

        if ($this->checkUserStar($uid, $id)) {
            throw new DuplicateOperationException('您已收藏！');
        }

        $this->models['user']
            ->where('_id', $uid)
            ->push('starred_articles', [$id], true);

        return $this->models['user']->find($uid);
    }

    public function unstar($id)
    {
        $uid = $this->authorizer->getResourceOwnerId();

        $this->dbRepository('mongodb', 'user')
            ->where('_id', $uid)
            ->pull('starred_articles', [$id]);

        return response('', 204);
    }

    /**
     * [comment description]
     * @param  string $id 文章id
     * @return array
     */
    public function comment($id)
    {
        $uid = $this->authorizer->getResourceOwnerId();

        $this->user = $this->dbRepository('mongodb', 'user')
            ->select('avatar_url', 'display_name')
            ->find($uid);

        return $this->commentResponse($id);
    }

    /**
     * 文章评论返回数据
     *
     * @param  string $id 文章id
     * @return array
     */
    protected function commentResponse($id)
    {
        $article = (array) $this->article()->where('article_id', $id)
            ->select('article_id as id', 'article_writer as origin')
            ->first();

        $insertData = [
            'content'    => Request::input('content'),
            'created_at' => date('Y-m-d H:i:s'),
            'article'    => $article,
            'user'       => $this->user,
        ];

        $comment = $this->dbRepository('mongodb', 'article_comment');

        $insertId = $comment->insertGetId($insertData);

        return $comment->find($insertId);
    }

    /**
     * [commentList description]
     * @param  string $id 文章id
     * @return todo
     */
    public function commentList($id)
    {
        // mongodb disconnect
        $this->models['article_comment'] = $this->dbRepository('mongodb', 'article_comment');

        $list = $this->models['article_comment']
            ->where('article.id', $id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return $list;
    }

    /**
     * [reply description]
     * @param  string $id        文章id
     * @param  string $commentId 评论id
     * @return array
     */
    public function reply($id, $commentId)
    {
        $uid = $this->authorizer->getResourceOwnerId();

        $this->user = $this->dbRepository('mongodb', 'user')
            ->select('avatar_url', 'display_name')
            ->find($uid);

        return $this->replyResponse($commentId);
    }

    /**
     * [replyResponse description]
     * @param  string $commentId 评论id
     * @return array
     */
    protected function replyResponse($commentId)
    {
        $content = Request::input('content');

        $insertData = [
            'content'    => $content,
            'created_at' => date('Y-m-d H:i:s'),
            'comment_id' => $commentId,
            'user'       => $this->user,
        ];

        $reply = $this->dbRepository('mongodb', 'reply');

        $insertId = $reply->insertGetId($insertData);

        return $reply->find($insertId);
    }

}
