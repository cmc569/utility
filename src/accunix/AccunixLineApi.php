<?php
namespace Jasonchen\Accunix;

class AccunixLineApi extends AccunixApiCore
{
    protected $baseUrl = 'https://api-tf.accunix.net/api/LINEBot';
    protected $baseUrl_v2 = 'https://api-tf.accunix.net/api/line';

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

    //身分驗證
    public function authenticate(String $user_token, String $RoleId, Array $data=NULL)
    {
        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/authenticate";

        $post_data = [
            'userToken'    => $user_token,
            'roleId'          => $RoleId,
        ];

        if (!empty($data)) {
            $post_data['data'] = $data;
        }

        return $this->send($post_data);
    }
    ##

    //切換主選單
    public function switch(String $user_token, String $richmenu_guid)
    {
        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/richmenu/switch";

        $post_data = [
            'userToken'     => $user_token,
            'richmenuGuid'  => $richmenu_guid,
        ];
        
        return $this->send($post_data);
    }
    ##

    //剝除標籤
    public function removeTags(Array $user_token, Array $tags)
    {
        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/tag/remove";

        $post_data = [
            'userToken' => $user_token,
            'tags'      => $tags,
        ];
        
        return $this->send($post_data);
    }
    ##

    //寄送訊息
    public function sendMessages(String $user_token, Array $data=NULL, Int $messageId=NULL)
    {
        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/message/send";

        $post_data = [
            'userToken'    => $user_token,
        ];
        
        if (empty($data) && empty($messageId)) {
            $res = [
                'status'    => 400,
                'message'   => '請定義訊息格式內容',
            ];

            $this->throwException($res);
        }
        
        if (!empty($messageId)) {
            $post_data['messageId'] = $messageId;
        } else if (!empty($data)) {
            $post_data['messages'] = $data;
        } else {
            $res = [
                'status'    => 400,
                'message'   => '請輸入訊息格式內容',
            ];

            $this->throwException($res);
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
    public function createTags(String $tag, String $description=NULL, Int $days=NULL)
    {
        $days = $days ?? '-1';
        
        $this->url = "{$this->baseUrl_v2}/{$this->BotId}/tag/create";
        
        $post_data = [
            'name'  => $tag,
            'days'  => $days,
        ];
        
        if (!empty($description)) {
            $post_data['description'] = $description;
        }

        return $this->send($post_data);
    }
    ##

    //好友貼標（自動建立標籤）
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

    //取得好友推薦目標資訊
    public function referralInfo(String $user_token, Int $referral_id)
    {
        $this->url = "{$this->baseUrl}/{$this->BotId}/referral/info?user_token={$user_token}&referral_id={$referral_id}";

        return $this->send([], 'GET');
    }
    ##

    //取得User推薦好友數
    public function shareUser(String $user_token, Int $referral_id)
    {
        $this->url = "{$this->baseUrl}/{$this->BotId}/referral/share-user?user_token={$user_token}&referral_id={$referral_id}";

        return $this->send([], 'GET');
    }
    ##

    //取得好友分享連結
    public function getShareLink(String $user_token)
    {
        $this->url = "{$this->baseUrl}/{$this->BotId}/users/getShareLink";
        
        $post_data = [
            'sharer_token'  => $user_token,
        ];

        return $this->send($post_data);
    }
    ##

    //取得 User Profile api
    public function getUserProfile(String $user_token)
    {
        $this->url = "{$this->baseUrl}/{$this->BotId}/users/getUserProfile?user_token={$user_token}&options=tags,auth";

        return $this->send([], 'GET');
    }
    ##

    //確認用戶是否有該身份驗證
    public function is_role(Array $response, Int $role)
    {
        if (empty($response['data']['response']['user']['authentications'])) {
            return false;
        }
        
        foreach ($response['data']['response']['user']['authentications'] as $index => $value) {
            if (empty($value['roles'])) {
                continue;
            }
            
            foreach ($value['roles'] as $index2 => $value2) {
                if ($role == $value2['id']) {
                    return true;
                }
            }
        }
        
        return false;
    }
    ##

    //確認用戶是否有該標籤
    public function is_tag(Array $response, String $tag)
    {
        if (empty($response['data']['response']['user']['tags'])) {
            return false;
        }
        
        foreach ($response['data']['response']['user']['tags'] as $index => $value) {
            if (preg_match("/^\d+$/", $tag) && ($value['id'] == $tag)) {
                return true;
            } else if ($value['name'] == $tag) {
                return true;
            }
        }
        
        return false;
    }
    ##



    //取得User分享好友加入人數（棄用)
    private function getUserShareInfo(String $user_token)
    {
        $this->url = "{$this->baseUrl}/{$this->BotId}/users/getUserShareInfo?user_token={$user_token}";

        return $this->send([], 'GET');
    }
    ##
}
    
?>