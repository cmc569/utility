<?php
namespace Jasonchen\Kafka;

class KafkaRest
{
    private $base_url;
    private $topic_name;
    private $group_name = 'my_group';
    private $instance = 'my_instance';
    private $headers;
    private $url;

    //建構
    public function __construct(String $base_url, String $log=null)
    {
        $this->log = $log ?? dirname(dirname(__DIR__)).'/log/kafka';
        
        if (!is_dir($this->log)) {
            mkdir($this->log, 0777, true);
        }

        if (empty($base_url) || !preg_match("/^http[s]{0,1}\:\/\//i", $base_url)) {
            $res = [
                'status'    => 400,
                'message'   => '未指定 access token'
            ];

            $this->throwException($res);
        }
        
        $this->setBaseUrl($base_url);
        
        return $this;
    }
    ##
    
    //解構
    public function __destruct()
    {
    }
    ##

    //取得 topic 列表
    public function getTopics():Array
    {
        $this->setHeaders('application/vnd.kafka.v2+json');
        $this->url = "{$this->base_url}/topics";
        
        return $this->send([], 'GET');
    }
    ##
    
    //訊息寫入 Kafka (Produce)
    public function push(String $record, String $key=null):Array
    {
        // $this->setHeaders('application/vnd.kafka.binary.v2+json');
        $this->setHeaders('application/vnd.kafka.json.v2+json');
        $this->url = "{$this->base_url}/topics/{$this->topic_name}";
        echo $this->url."\n";
        $record = ['value' => $record];        
        if (!empty($key)) {
            $record['key'] = $key;
        }
        
        $payload = [
            "records"   => [ $record ]
        ];
        
        return $this->send($payload, 'POST', 'produce');
    }
    ##
    
    //建立 instance (Consumer)
    public function joinGroup(String $group_name=null, String $instance=null):Array
    {
        $this->setGroupName($group_name ?? $this->group_name);
        $this->setInstance($instance ?? $this->instance);
        
        $this->setHeaders('application/vnd.kafka.v2+json');
        $this->url = "{$this->base_url}/consumers/{$this->group_name}";
        
        $payload = [
            "name"                  => $this->instance,
            "format"                => "binary",
            "auto.offset.reset"     => "earliest",
        ];
        
        return $this->send($payload);
    }
    ##
    
    //instance 訂閱 topic(Consumer)
    public function subscriptionTopic():Array
    {
        $this->setHeaders('application/vnd.kafka.v2+json');
        $this->url = "{$this->base_url}/consumers/{$this->group_name}/instances/{$this->instance}/subscription";
        
        $payload = [
            "topics" => [
                $this->topic_name
            ],
        ];
        
        return $this->send($payload);
    }
    ##
    
    //解除訂閱 topic(Consumer)
    public function deleteSubscriptionTopic():Array
    {
        $this->setHeaders('application/vnd.kafka.v2+json');
        $this->url = "{$this->base_url}/consumers/{$this->group_name}/instances/{$this->instance}/subscription";
        
        $payload = [];
        
        return $this->send($payload, 'DELETE');
    }
    ##
    
    //檢查 instance 訂閱是否存在(Consumer)
    public function subscriptionTopicCheck():Array
    {
        $this->setHeaders('application/vnd.kafka.v2+json');
        $this->url = "{$this->base_url}/consumers/{$this->group_name}/instances/{$this->instance}/subscription";
        
        $payload = [];
        
        return $this->send($payload, 'GET');
    }
    ##
    
    //刪除 instance(Consumer)
    public function deleteSubscriptionInstance():Array
    {
        $this->setHeaders('application/vnd.kafka.v2+json');
        $this->url = "{$this->base_url}/consumers/{$this->group_name}/instances/{$this->instance}";
        
        $payload = [];
        
        return $this->send($payload, 'DELETE');
    }
    ##
    
    //讀取 kafka message (Consumer)
    public function pop(Int $timeout=null, Int $max_bytes=null):Array
    {
        $this->setHeaders('application/vnd.kafka.v2+json');
        $this->url = "{$this->base_url}/consumers/{$this->group_name}/instances/{$this->instance}/records";
        
        $query_string = [];
        if (preg_match("/^\d+$/", $timeout)) {
            $query_string[] = 'timeout='.$timeout;
        }
        
        if (preg_match("/^\d+$/", $max_bytes)) {
            $query_string[] = 'max_bytes='.$max_bytes;
        }
        
        if (!empty($query_string)) {
            $this->url .= '?'.implode('&', $query_string);
        }
        
        return $this->send([], 'GET', 'consume');
    }
    ##

    //設定 base url
    public function setBaseUrl(String $base_url)
    {
        if (empty($base_url) || !preg_match("/^http[s]{0,1}\:\/\//i", $base_url)) {
            $res = [
                'status'    => 400,
                'message'   => '未指定 access token'
            ];

            $this->throwException($res);
        }
        
        $this->base_url = $base_url;
        
        return $this;
    }
    ##
    
    //設定 topic 名稱
    public function setTopicName(String $topic_name)
    {
        $this->topic_name = $topic_name;
        return $this;
    }
    ##
    
    //設定 group name
    public function setGroupName(String $group_name)
    {
        $this->group_name = $group_name;
        return $this;
    }
    ##
    
    //設定 Instance
    public function setInstance(String $instance)
    {
        $this->instance = $instance;
        return $this;
    }
    ##
    
    //設定 Header Content-Type
    private function setHeaders(String $content_type)
    {
        $this->headers = ["Content-Type: {$content_type}"];
        return $this;
    }
    ##

    //發送 request
    private function send(Array $post_data, String $method=null, String $log_type=null):Array
    {
        $method = empty($method) ? 'POST' : strtoupper($method);

        $this->envCheck();

        //發出
        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        }

        if (!empty($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }
        $this->headers = '';
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        try {
            $json = curl_exec($ch);
            $response = json_decode($json, true);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
        } catch (Exception $e) {
            $this->logs($e->getMessage(), 'error', 'exception');
        }
        
        curl_close($ch);
        ##
        
        //log
        $log = "End-Point: {$this->url}({$method})\n";
        $log .= 'Request: '.json_encode($post_data, JSON_UNESCAPED_UNICODE)."\n";
        $log .= 'Response: '.json_encode($response, JSON_UNESCAPED_UNICODE)."\n";
        $log .= 'Response Code: '.$status."\n";

        $this->logs($log, $log_type ?? 'request');
        ##

        $return = [
            'status'    => $status,
        ];

        if (($status >= 200) && ($status < 300))  {
            $return['message'] = 'success';
            
            if (!empty($response)) {
                $return['data'] = $response;
            }
        } else {
            $return['message'] = $response['message'] ?? $curl_error;
        }
        
        return $return;
    }
    ##

    //檢查環境參數
    protected function envCheck():Bool
    {
        $this->existsUrl();
        $this->existsTopic();

        return true;
    }
    ##

    //檢查 url
    private function existsUrl()
    {
        if (empty($this->url)) {
            $res = [
                'status'    => 400,
                'message'   => '未指定 end-point url'
            ];
            
            $this->throwException($res);
        }
    }
    ##
    
    //檢查 topic
    private function existsTopic()
    {
        if (empty($this->topic_name)) {
            $res = [
                'status'    => 400,
                'message'   => '未指定 topic'
            ];
            
            $this->throwException($res);
        }
    }
    ##

    //例外處理
    private function throwException(Array $res)
    {
        $this->logs($res, 'error');
        throw new Exception(json_encode($res, JSON_UNESCAPED_UNICODE)); //丟出例外
    }
    ##

    //資訊紀錄
    private function logs($response, $path, $filename='')
    {
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