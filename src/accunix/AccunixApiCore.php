<?php
namespace Jasonchen\Accunix;

class AccunixApiCore
{
    protected $BotId;
    protected $AccessToken;
    protected $url;
    protected $headers;
    protected $log;
    protected $version = '2.0';

    //建構
    public function __construct(String $bot_id, String $log=NULL)
    {
        $this->log = $log ?? dirname(dirname(__DIR__)).'/log/accunix';
        
        if (!is_dir($this->log)) {
            mkdir($this->log, 0777, true);
        }

        $this->BotId = $bot_id;
        $this->headers = [
            'Content-Type: application/json;'
        ];

        $this->exists_bot_id();
    }
    ##
    
    //解構
    public function __destruct()
    {
    }
    ##

    //設定access token
    public function setAccessToken(String $AccessToken)
    {
        $this->AccessToken = $AccessToken;
        $this->setHeaders(["Authorization: Bearer {$AccessToken}"]);

        return $this;
    }
    ##

    //設定額外檔頭
    protected function setHeaders(Array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }
    ##

    //發送 request
    protected function send(Array $post_data, String $method=NULL)
    {
        $method = empty($method) ? 'POST' : strtoupper($method);

        $this->envCheck();

        //發出
        $ch = curl_init($this->url);

        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        }

        if ($method == 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        } 

        if (!empty($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        try {
            $json = curl_exec($ch);
            $response = json_decode($json, true);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } catch (Exception $e) {
            $this->Logs($e->getMessage(), 'error', 'exception');

            return [
                'status'    => 402,
                'message'   => '其他錯誤',
            ];
        }
        curl_close($ch);
        ##

        //log
        $log = "End-Point: {$this->url}({$method})\n";
        $log .= 'Request: '.json_encode($post_data, JSON_UNESCAPED_UNICODE)."\n";
        $log .= 'Response: '.json_encode($response, JSON_UNESCAPED_UNICODE)."\n";
        $log .= 'Response Code: '.$status."\n";

        $this->Logs($log, 'request');
        ##

        // $status = ($status == 200) ? 200 : 400;
        $return = [
            'status'    => $status,
            'message'   => $response['message'] ?? 'success',
        ];

        if ($status == 200) {
            if (isset($response['message'])) unset($response['message']);
            if (!empty($response)) {
                $return['data'] = $response;
            }
        }
        
        return $return;
    }
    ##

    //檢查環境參數
    protected function envCheck()
    {
        $this->exists_bot_id();
        $this->exists_access_token();
        $this->exists_url();
        $this->exists_headers();

        return true;
    }
    ##

    //檢查 bot id
    private function exists_bot_id() {
        if (empty($this->BotId)) {
            $res = [
                'status'    => 400,
                'message'   => '未輸入bot_id'
            ];
            
            $this->throwException($res);
        }
    }
    ##

    //檢查 url
    private function exists_url() {
        if (empty($this->url)) {
            $res = [
                'status'    => 400,
                'message'   => '未指定 end-point url'
            ];
            
            $this->throwException($res);
        }
    }
    ##

    //檢查 access token
    private function exists_access_token()
    {
        if (empty($this->AccessToken)) {
            $res = [
                'status'    => 400,
                'message'   => '未指定 access token'
            ];

            $this->throwException($res);
        }
    }
    ##

    //檢查 headers
    private function exists_headers() {
        if (empty($this->headers)) {
            $res = [
                'status'    => 400,
                'message'   => '未指定 header 資訊'
            ];
            
            $this->throwException($res);
        }
    }
    ##

    //例外處理
    protected function throwException(Array $res)
    {
        $this->Logs($res, 'error');
        throw new Exception(json_encode($res, JSON_UNESCAPED_UNICODE)); //丟出例外
    }
    ##

    //顯示目前版本
    public function version()
    {
        return $this->version;
    }
    ##

    //資訊紀錄
    protected function Logs($response, $path, $filename='') {
        // set log
        $filename = $path ?? "";
        $path = $path ?? $this->log;

        $path = empty($filename) ? $this->log : $this->log.'/'.$filename;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        ##

        file_put_contents(
            $path.'/'.$filename.'_'.date("Ymd").'.log',
            date("Y-m-d H:i:s")."\n".print_r($response, true)."\n\n",
            FILE_APPEND
        );
    }
    ##
}
    
?>
