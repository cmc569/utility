<?php
namespace Jasonchen\Util;

class Util {
    /**
     * 紀錄log
     * {{root}}private/storage/{{git 專案名稱}}/log/{{method}}/{{Y-m-d}}.log
    */
    public static function logs(String $repository, String $message, String $method=null, String $root=null) {
        $log = $root ?? __DIR__;
        $log .= "/private/storage/{$repository}/log";
        
        if (!empty($method)) {
            $log .= "/{$method}";
        }

        if (!is_dir($log)) {
            mkdir($log, 0777, true);
        }
        
        $log .= '/'.date("Y-m-d") . '.log';
        $timeNow = "[".date("Y-m-d H:i:s")."]";
        file_put_contents($log, $timeNow." ".$message . "\n", FILE_APPEND);
    }
}
