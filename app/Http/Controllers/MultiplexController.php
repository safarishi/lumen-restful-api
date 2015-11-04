<?php

namespace App\Http\Controllers;

use Request;
use Intervention\Image\Facades\Image;

class MultiplexController extends Controller
{
    /**
     * 上传用户头像
     *
     * @param string  $uid 用户id
     * @return string      用户头像地址
     */
    public static function uploadAvatar($uid)
    {
        $ext = 'png';
        $subDir = substr($uid, -1);
        $storageDir = config('imagecache.paths.avatar_url').'/'.$subDir.'/';
        $storageName = $uid;
        $storagePath = $subDir.'/'.$storageName.'.'.$ext;

        if (!file_exists($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }

        Image::make(Request::file('avatar_url'))
            ->encode($ext)
            ->save($storageDir.$storageName.'.'.$ext);

        return config('imagecache.paths.avatar_url_prefix').'/'.$storagePath;
    }
}
