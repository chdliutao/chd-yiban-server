<?php

namespace app\api\controller\miniapp;

use app\common\controller\Api;
use think\Config;
use fast\Http;
use wechat\wxBizDataCrypt;
use app\api\model\Wxuser as WxuserModel;
use app\api\model\Ykt as YktModel;
use app\api\model\Books as BooksModel;
use app\api\model\Score as ScoreModel;
use app\api\controller\Bigdata as BigdataController;
/**
 * 获取课表
 */
class Portal extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    const LOGIN_URL = 'https://api.weixin.qq.com/sns/jscode2session';
    const PORTAL_URL = 'http://ids.chd.edu.cn/authserver/login';

    public function yikatong(){
        //解析后应对签名参数进行验证
        $key = json_decode(base64_decode($this->request->post('key')),true);
        $Ykt = new YktModel;
        $data = $Ykt -> get_yikatong_data($key);
        // $info = [
        //     'status' => 200,
        //     'message' => 'success',
        //     'data' => $data,
        // ];
        $this->success("success",$data);
        return json($info);
    }
    //查询当前借阅信息
    public function books(){
        
        /*$info示例
        [
            'book_list' => [
                ['book' => "c",'jsrq' => '2017-08-02','yhrq' => '2018-08-02'],
                ['book' => "c",'jsrq' => '2017-08-02','yhrq' => '2018-08-02'],
                ['book' => "c",'jsrq' => '2017-08-02','yhrq' => '2018-08-02'],
            ],  //当前借阅列表
            'books_num' => 3,   //当前借阅量
            'history'   => 10,     //历史借阅量
            'dbet'      => 0,        //欠费
            'nothing'   =>  true   //当前是否有借阅
        ] */
        //解析后应对签名参数进行验证
        $key = json_decode(base64_decode($this->request->post('key')),true);
        $Books = new BooksModel;
        $data = $Books -> get_books_data($key);
        if ($data['status']) {     
            // $info = [
            //     'status' => 200,
            //     'message' => 'success',
            //     'data' => $data['data'],
            // ];
            $this->success("success",$data["data"]);
        } else {
            // $info = [
            //     'status' => 500,
            //     'message' => 'param error',
            //     'data' => $data['data'],
            // ];
            $this->error("params error",$data["data"]);

        }
        // return json($info);
    }
    //查询历史借阅信息

    public function history_books(){
        //解析后应对签名参数进行验证
        $key = json_decode(base64_decode($this->request->post('key')),true);
        $Books = new BooksModel;
        $data = $Books -> get_history_data($key);
        $nowResult = $Books -> get_books_data($key);
        $result = [];
        $result['nothing'] = $data['data']['history_count'] == 0 ? false : true ;
        $result['book_list'] = $data['data']['history_list'];
        $result['books_num'] = $nowResult['data']['books_num'];
        $result['history'] = $data['data']['history_count'];
        $dbet_data = $Books -> get_dbet_data($key);
        $result['dbet'] = $dbet_data['data']['dbet'];
        if ($data['status']) {     
            // $info = [
            //     'status' => 200,
            //     'message' => 'success',
            //     'data' => $result,

            // ];
            $this->success("success",$result);
        } else {
            // $info = [
            //     'status' => 500,
            //     'message' => 'param error',
            //     'data' => $result,
            // ];
            $this->error("params error",$result);
            
        }
        // return json($info);
    }

    //续借
    public function renew()
    {
        //解析后应对签名参数进行验证
        $key = json_decode(base64_decode($this->request->post('key')),true);
        $Books = new BooksModel;
        $data = $Books -> renew_books($key);
        if ($data['status']) {
            // $info = [
            //     'status' => 200,
            //     'message' => 'success',
            //     'data' => $data['data'],
            // ];
            $this->success("success",$data["data"]);
        } else {
            // $info = [
            //     'status' => 500,
            //     'message' => 'param error',
            //     'data' => $data['data'],
            // ];
            $this->error("params error",$data["data"]);
        }
        // return json($info);
    }

    //获取考试成绩
    public function score(){
        $key = json_decode(base64_decode($this->request->post('key')),true);
        // $score = new ScoreModel;
        // $data = $score -> get_score($key);
        // $info = [
        //     'status' => 200,
        //     'message' => 'success',
        //     'data' => $data,
        // ];
        // return json($info);
        $score = new BigdataController;
        $Wxuser = new WxuserModel;
        $XH = $key["id"];
        $access_token = $score->getAccessToken();
        $access_token = $access_token["access_token"];
        $params = ["access_token" => $access_token,"XH" => $XH];
        $data = array_reverse($score->getScore($params));
        // $info = [
        //     'status' => 200,
        //     'message' => 'success',
        //     'data' => $data,
        // ];
        $this->success("success",$data);
        // return json($info);
    }

    /**
     * 获取学生体测成绩
     */

    public function tcscore()
    {
        $key = json_decode(base64_decode($this->request->post('key')),true);
        $score = new BigdataController;
        $XH = $key["id"];
        $access_token = $score->getAccessToken();
        $access_token = $access_token["access_token"];
        $params = ["access_token" => $access_token,"XH" => $XH];
        $returnData = $score->getTcScore($params);
        if ($returnData["status"] == true) {
            // $info = [
            //     'status' => 200,
            //     'message' => 'success',
            //     'data' => $returnData["data"],
            // ];
            $this->success("success",$returnData["data"]);
        } else {
            // $info = [
            //     'status' => 200,
            //     'message' => $returnData["msg"],
            //     'data' => $returnData["data"],
            // ];
            $this->error($returnData["msg"],$returnData["data"]);
        }
        // return json($info);
    }
    /**
     * 获取学生四六级成绩
     */

    public function slscore()
    {
        $key = json_decode(base64_decode($this->request->post('key')),true);
        $score = new BigdataController;
        $XH = $key["id"];
        $access_token = $score->getAccessToken();
        $access_token = $access_token["access_token"];
        $params = ["access_token" => $access_token,"XH" => $XH];
        $data = array_reverse($score->getSlScore($params));
        // $info = [
        //     'status' => 200,
        //     'message' => 'success',
        //     'data' => $data,
        // ];
        $this->success('success',$data);
        return json($info);
    }

    //获取空闲教室
    public function empty_room(){
        $key = json_decode(base64_decode($this->request->post('key')),true);
        /**
         * [
         *  weekNo:第几周
         *  weekDay:周几（周一:1 周二:2 ……）
         *  classNo:第几节课 1@2:一二节课 1@2@3@4 一二三四节课
         *  buildingNo: 1:宏远 2:明远 3:修远
         *  openid:微信openid
         *  timestamp:时间戳
         *  sign:签名验证字符串
         * ]
         */
        $info = [
            'status' => 200,
            'message' => 'success',
            'data' => [
                ['room' => ['WM1211']],
                ['room' => ['WM2501']]
            ]
        ];
        
        return json($info);
    }
}