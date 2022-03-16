<?php
namespace Jasonchen\Accunix;

class AccunixFacebookApi extends AccunixApiCore
{
    protected $AuthId;
    protected $RoleId;
    protected $baseUrl = 'https://api-tf.accunix.net/api/FBMessengerBot';
    protected $baseUrl_v2 = 'https://api-tf.accunix.net/api/fb';

    //建構
    public function __construct(String $bot_id, String $log=NULL)
    {
        return parent::__construct($bot_id, $log);
    }
    ##
    
    //解構
    public function __destruct()
    {
        return parent::__destruct();
    }
    ##

    //身分驗證 api
    public function authenticate(String $user_token, Int $RoleId, Array $data=NULL)
    {

        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/authenticate";

        $post_data = [
            'userToken'     => $user_token,
            'roleId'        => $RoleId,
        ];

        if (!empty($data)) {
            $post_data['data'] = $data;
        }

        return $this->send($post_data);
    }
    ##
    
    //寫入好友資訊
    public function setUserData(String $user_token, Array $data)
    {
        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/users/data";
        
        $post_data = [
            'userToken'    => $user_token,
        ];
        
        if (!empty($data)) {
            $post_data['data'] = $data;
        }

        return $this->send($post_data, 'PATCH');
    }
    ##
    
    //新增標籤
    public function createTags(String $tag, Int $days=NULL)
    {
        $days = $days ?? '-1';
        
        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/tag/create";
        
        $post_data = [
            'name'  => $tag,
            'days'  => $days,
        ];

        return $this->send($post_data);
    }
    ##

    //好友貼標 api
    public function addUsersTag($user_token, $data)
    {
        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/tag/add";
        
        if (!is_array($user_token)) {
            $user_token = [$user_token];
        }

        if (count($user_token) > 10) {
            $res = [
                'status'    => 400,
                'message'   => 'user token 總數不可超過 10 組'
            ];

            $this->throwException($res);
        }

        $post_data = [
            'userTokens'    => $user_token,
        ];

        if (count($data) > 3) {
            $res = [
                'status'    => 400,
                'message'   => '標簽數不可超過 3 筆',
            ];

            $this->throwException($res);
        } else {
            $post_data['tags'] = $data;
        }

        return $this->send($post_data);
    }
    ##

    //好友貼標
    public function addTag($user_token, $data, $days=-1)
    {
        $total = count($data);
        if (($total > 3) || ($total <= 0)) {
            $res = [
                'status'    => 400,
                'message'   => '標簽數量請限定於 1~3 筆內',
            ];

            $this->throwException($res);
        }
        
        foreach ($data as $tag) {
            $res = $this->createTags($tag, $days);
            switch ($res['status']) {
                case 200:
                        //成功
                        break;
                case 422:
                        //標籤重複
                        break;
                default:
                        $res = [
                            'status'    => 400,
                            'message'   => '標簽建立失敗',
                        ];
                        
                        $this->throwException($res);
                        
                        break;
            }
            unset($res);
        }
        
        return $this->addUsersTag($user_token, $data);
    }
    ##


}
    
?>