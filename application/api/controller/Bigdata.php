<?php

namespace app\api\controller;

use app\common\controller\Api;
use wechat\wxBizDataCrypt;
use think\Db;
use fast\Http;
use think\Cache;
/**
 * 更新接口
 */
class Bigdata extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    //获取token的url
    const GET_TOKEN_URL = "http://bigdata.chd.edu.cn:3003/open_api/authentication/get_access_token";
    //获取成绩的url
    const GET_SCORE_URL = "http://bigdata.chd.edu.cn:3003/open_api/customization/tgxxsbkscj/list";
    const APPKEY = "201906132614147905";
    const APPSECRET = "83004580acbae7bfbae62235c983e5842bf9dbf5";
    
    public function getAccessToken()
    {
        $access_token = Cache::get('bigdata_access_token');
        if (empty($access_token)) {
            $token_url = self::GET_TOKEN_URL;
            $post_data = ["key" => self::APPKEY,"secret" => self::APPSECRET];
            $data = Http::post($token_url,$post_data);
            $res_hash = json_decode($data, true);
            if ($res_hash['message'] != 'ok') {
                //错误处理
                return ["status" => false,"msg" => $res_hash["message"]];
            } else {
                $access_token = $res_hash['result']['access_token'];
                Cache::set('bigdata_access_token',$access_token,$res_hash['result']["expires_in"]);
                return ["status" => true,"access_token" => $access_token];
            }
        } else {
            // dump($access_token);
            return ["status" => true,"access_token" => $access_token];            
        }
    }

    /**
     * 获取成绩接口
     * @param int XH 
     * @param string access_token  
     * @return array 
     */
    public function getScore($params)
    {
        $request_url = self::GET_SCORE_URL."?access_token=".$params["access_token"];

        $data = Http::post($request_url,$params);
        $data = json_decode($data,true);
        $result = [];
        for ($i = 1; $i <= $data["result"]["max_page"]; $i++) { 
            $returnData = [];
            $params["page"] = $i;
            sleep(0.1);
            $returnData = Http::post($request_url,$params);
            $returnData = json_decode($returnData,true);   
            foreach ($returnData["result"]["data"] as $key => $value) {
                $mykey = $value["XN"]." ".$value["XQ"];
                $result[$mykey][] = $value;
            }
        }
        $arrayKeys = array_keys($result);
        $list = [];
        foreach ($arrayKeys as $key => $value) {
            $temp = [
                "XNXQ" => $value,
                "XN"   => explode(" ",$value)[0],
                "XQ"   => explode(" ",$value)[1],
                "list" => $result[$value],
            ];
            $list[] = $temp;
        }
        // dump($list);
        return $list;
    }
}