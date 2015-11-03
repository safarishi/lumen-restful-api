<?php

namespace App\Http\Controllers;

use DB;
use App\Exceptions\ValidationException;

class ArticleController extends CommonController
{
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
    public function report()
    {
        return DB::connection('sqlsrv')->table('lanmu')
            ->select('lanmu_id as id', 'lanmu_name as name')
            ->where('lanmu_language', 'zh-cn')
            ->whereIn('lanmu_father', [113, 167, 168])
            ->get();
    }
}
