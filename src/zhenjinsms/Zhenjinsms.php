<?php

namespace addons\zhenjinsms;

use \app\common\model\Sms;
use think\Addons;

/**
 * rryunsms插件
 */
class Zhenjinsms extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {

        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {

        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {

        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {

        return true;
    }

    /**
     * 短信发送
     * @param Sms $params
     * @return mixed
     */
    public function smsSend(&$params)
    {
        $zhenjin = new library\Zhenjin();
        $result = $zhenjin->mobile($params->mobile)->msg("您的验证码为:" . $params->code . ", 5分钟内有效")->send();
        return $result;
    }

    /**
     * 短信发送通知（msg参数直接构建实际短信内容即可）
     * @param   array $params
     * @return  boolean
     */
    public function smsNotice(&$params)
    {
        $zhenjin = new library\Zhenjin();
        $result = $zhenjin->mobile($params['mobile'])->msg($params['msg'])->send();
        return $result;
    }

    /**
     * 检测验证是否正确
     * @param   Sms $params
     * @return  boolean
     */
    public function smsCheck(&$params)
    {
        return TRUE;
    }
}
