<?php
namespace Wechat\API;

use Wechat\Utils\JSON;

/**
 * 微信JSSDK相关接口.
 *
 * @author Tian.
 */
class JSSDKApi extends BaseApi
{

     /**
     * 获取JSSDK的配置数组
     *
     * @param array $APIs
     * @param bool  $debug
     * @param bool  $json
     *
     * @return string|array
     */
    public function config(array $APIs, $debug = false, $json = true)
    {
        $signPackage = $this->getSignature();

        $base = array(
                 'debug' => $debug
                );

        $config = array_merge($base, $signPackage, array('jsApiList' => $APIs));

        return $json ? JSON::encode($config) : $config;
    }

    /**
     * 获取JSSDK接口认证.
     *
     * @author Tian
     *
     * @date   2015-12-08
     *
     * @return string 认证签名.
     */
    public function getTicket()
    {
        //$token = $this->AccessToken;

        $key = 'JSAPI_TICKET'.$this->getAppId();
        $ticket = S($key);
        if (!$ticket) {
            $this->module = 'ticket';
            $queryStr = array(
                'type'  =>  'jsapi'
            );
            $res = $this->_get('getticket', $queryStr, false);
            if (!$res) {
                E($this->getError());
            }

            $ticket = $res['ticket'];
            $expires = $res['expires_in'];

            S($key, $ticket, $expires - 300);
        }

        return $ticket;
    }

    /**
     * 获取JSSDK签名.
     *
     * @author Tian
     *
     * @date   2015-12-08
     *
     * @return string
     */
    public function getSignature()
    {
        $timestamp = $this->getTimeStamp();
        $nonceStr = $this->getnonceStr();
        $ticket = $this->getTicket();

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $signature_array = array();
        $signature_array['appId']     = $this->getAppId();
        $signature_array['nonceStr']  = $nonceStr;
        $signature_array['timestamp'] = $timestamp;
        $signature_array['signature'] = $this->getSignatures($ticket, $nonceStr, $timestamp, $url);

        return $signature_array;
    }

    /**
     * 生成签名
     *
     * @param string $ticket
     * @param string $nonce
     * @param int    $timestamp
     * @param string $url
     *
     * @return string
     */
    public function getSignatures($ticket, $nonce, $timestamp, $url)
    {
        return sha1("jsapi_ticket={$ticket}&noncestr={$nonce}&timestamp={$timestamp}&url={$url}");
    }

    /**
     * 获取唯一时间戳.
     *
     * @author Tian
     *
     * @date   2015-12-08
     *
     * @return timestamp
     */
    public function getTimeStamp()
    {
        static $timestamp;
        if (!$timestamp) {
            $timestamp = time();
        }

        return $timestamp;
    }

    /**
     * 获取唯一随机串.
     *
     * @author Tian
     *
     * @date   2015-12-08
     *
     * @return string
     */
    public function getnonceStr()
    {
        static $nonceStr;
        if (!$nonceStr) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $nonceStr = '';
            for ($i = 0; $i < 16; $i++) {
                $nonceStr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            }
        }

        return $nonceStr;
    }
}