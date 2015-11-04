<?php

namespace App\Http\Controllers;

use DB;
use App\Exceptions\ValidationException;
use LucaDegasperi\OAuth2Server\Authorizer;

class ArticleController extends CommonController
{

    public function __construct(Authorizer $authorizer)
    {
        parent::__construct($authorizer);
        // $this->middleware('disconnect:sqlsrv', ['only' => ['report', 'index', 'show', 'search', 'moreArticle', 'myStar', 'team']]);
        // $this->middleware('disconnect:mongodb', ['only' => ['favour', 'show', 'commentList', 'myComment', 'myStar', 'myInformation']]);
        $this->middleware('oauth', ['except' => ['index', 'show', 'report']]);
        // $this->middleware('validation.required:content', ['only' => ['anonymousComment', 'anonymousReply', 'comment', 'reply']]);
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

        // article is favoured
        // todo

        $this->origin = $article->origin;
        $related_articles = $this->getReleatedArticles($id);

        // return
        return [
            'article' => $article,
            'related_articles' => $related_articles,
        ];
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
        return DB::connection('sqlsrv')->table('lanmu')
            ->select('lanmu_id as id', 'lanmu_name as name')
            ->where('lanmu_language', 'zh-cn')
            ->whereIn('lanmu_father', [113, 167, 168])
            ->get();
    }

    /**
     * [star description]
     * @param  string $id 文章id
     * @return [type]     [description]
     */
    public function star($id)
    {
        // need login user
    }

}
