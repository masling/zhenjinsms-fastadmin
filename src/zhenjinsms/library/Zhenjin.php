<?php

namespace addons\zhenjinsms\library;
use think\Log;
use think\App;
class Zhenjin
{
    private $_params = [];
    protected $error = '';
    protected $config = [];
    protected static $instance = null;
    protected static $ACCESS_TOKEN=  "/risk/data/api/getAccessToken";
    protected static $SMS_SEND =  "/risk/data/sms/send";
    protected static $HOST_URL="https://api.zhenjinfengkong.com";

    public function __construct($options = [])
    {
        if ($config = get_addon_config('zhenjinsms')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->config = array_merge($this->config, is_array($options) ? $options : []);
    }

    /**
     * 单例
     * @param array $options 参数
     * @return Zhenjin
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 立即发送短信
     *
     * @return boolean
     */
    public function send()
    {
        $this->error = '';
        $params = $this->_params;
        $postArr = array(
            'mobile' => $params['mobile'],
            'msg' => $params['msg']
        );
        $options = [
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8'
            )
        ];
         $result = $this->postZJ(self::$SMS_SEND,$postArr);
         if($result)
         {
            return true;
         }else{
            Log::record('[ ERROR ] Run Zhenjin->send '.$this->error, 'info');
            return false;
         }

    }

    private function postZJ($url,$data){
        $accessToken= $this->getAccessToken($this->config["uname"],$this->config["secret"]);
        if (!$accessToken){
            $this->error="授权失败";
            return false;
        }
        $header[]="access-token: ".$accessToken;
        $result =  $this->sendCurlPost(self::$HOST_URL.$url, $data, $header);
        if ($result["r"]==1) {
                return TRUE;
        }else{
            $this->error = $result['msg'];
        }
        return false;
    }
    public function getAccessToken($uname,$secret){
        $data=["uname"=>$uname,"secret"=>$secret];
        $result = $this->sendCurlPost(self::$HOST_URL.self::$ACCESS_TOKEN, $data);
        if ($result["r"]) {
                return $result['data']['accessToken'];
        }else{
            $this->error = $result['msg'];
        }
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 接收手机
     * @param   string $mobile 手机号码
     * @return Zhenjin
     */
    public function mobile($mobile = '')
    {
        $this->_params['mobile'] = $mobile;
        return $this;
    }

    /**
     * 短信内容
     * @param   string $msg 短信内容
     * @return Zhenjin
     */
    public function msg($msg = '')
    {
        $this->_params['msg'] = $msg.$this->config['sign'] ;
        return $this;
    }
    /**
     * 发送请求
     *
     * @param string $url      请求地址
     * @param array  $dataObj  请求内容
     * @return array 应答json字符串
     */
    public function sendCurlPost($url, $dataObj,$header=[])
    {
        $baseHeader  =array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen(json_encode($dataObj)));
        if(count($header)>0){
            $baseHeader =array_merge($header,$baseHeader);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $baseHeader);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $ret = curl_exec($curl);
        if (false == $ret) {
            // curl_exec failed
            $result = "{ \"r\":" . -2 . ",\"msg\":\"" . curl_error($curl) . "\"}";
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "{ \"r\":" . -1 . ",\"msg\":\"". $rsp
                        . " " . curl_error($curl) ."\"}";
            } else {
                $result = $ret;
            }
        }
        if (App::$debug) {
            Log::record('[ INFO ] Run Zhenjin->sendCurlPost '.$url." header=".var_export($baseHeader,true)." data=".var_export($dataObj,true)." res=".$result, 'info');
        }
        curl_close($curl);
        return  (array)json_decode($result, true);
    }
}