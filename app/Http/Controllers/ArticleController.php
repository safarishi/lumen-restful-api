<?php

namespace App\Http\Controllers;

use DB;

class ArticleController extends Controller
{
    public function report()
    {
        return DB::connection('sqlsrv')->table('lanmu')
            ->select('lanmu_id as id', 'lanmu_name as name')
            ->where('lanmu_language', 'zh-cn')
            ->whereIn('lanmu_father', [113, 167, 168])
            ->get();
    }
}
