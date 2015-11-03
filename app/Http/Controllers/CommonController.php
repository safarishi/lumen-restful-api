<?php

namespace App\Http\Controllers;

use DB;

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

}