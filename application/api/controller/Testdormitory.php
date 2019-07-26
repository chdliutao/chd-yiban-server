<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use fast\Http;
use think\Db;
use think\Hook;
use fast\Random;
use app\common\library\Token;

use app\api\model\Dormitory as DormitoryModel;
/**
 * 
 */
class Testdormitory extends Freshuser
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    private $loginInfo = null;
    private $token = null;
    private $userInfo = null;
    const LOCAL_URL = "http://localhost:8080/yibanbx/public/api/dormitory2019/";
    const SERVICE_URL = "https://yiban.chd.edu.cn/api/dormitory2019/";
	

	public function finish()
	{
        set_time_limit(0);
        $id = $this->request->get("id");
		// for ($i=0; $i < 100; $i++) { 
		// 	$stime=microtime(true);
        $this->test();
        echo '<script>window.location.href="http://localhost:8080/yibanbx/public/api/testdormitory/finish?id='.($id+1).'";</script>';
        
		// 	$etime=microtime(true); 
		// 	$total=$etime-$stime;
        //     echo $total;
        //     break;
		// }
	}
	

    public function test()
    {
        set_time_limit(0);
        $type = $this -> request -> get('type');
        // if ($type = "local"){
        $url_base = self::SERVICE_URL;
            // $url_base = self::LOCAL_URL;
        // }
        // if ($type = "service") {
        //     $url_base = self::SERVICE_URL;
        // }
		$stu_info = Db::name('fresh_info') -> where('QQ','') -> find();
        $stu_id   = $stu_info['XH'];
        $stu_name = $stu_info['XM'];
        $stu_sfzh = substr($stu_info['SFZH'], -6);
        $login_url = $url_base."user/login";
        $param = [
            "studentID" => $stu_id,
            "password"  => $stu_sfzh,
        ];
        $postData = [
            "key" => base64_encode(urlencode(json_encode($param))),
        ];
        $response_login = Http::post($login_url,$postData);
        $response_login = json_decode($response_login,true);
        $token = $response_login['data']["token"];
        $res = Db::name('fresh_info') -> where('XH', $stu_id) -> update(['QQ' => '282813637']);

        $questionnaireUrl = $url_base."dormitory/setinfo";
        $postData = [
            "key" => "JTdCJTIyZm9ybTElMjIlM0ElN0IlMjJtZW1iZXIlMjIlM0ElNUIlN0IlMjJuYW1lJTIyJTNBJTIyJUU2JUI1JThCJUU4JUFGJTk1JTIyJTJDJTIyYWdlJTIyJTNBJTIyMzUlMjIlMkMlMjJyZWxhdGlvbiUyMiUzQSUyMiVFNyU4OCVCNiVFNCVCQSVCMiUyMiUyQyUyMnVuaXQlMjIlM0ElMjIlRTYlQjUlOEIlRTglQUYlOTUlMjIlMkMlMjJqb2IlMjIlM0ElMjIlRTYlQjUlOEIlRTglQUYlOTUlMjIlMkMlMjJpbmNvbWUlMjIlM0ElMjIxMDAwMDAlMjIlMkMlMjJoZWFsdGglMjIlM0ElMjIlRTYlOTclQTAlMjIlMkMlMjJtb2JpbGUlMjIlM0ElMjIxODg5MDg3NjUzMiUyMiU3RCU1RCUyQyUyMlFRJTIyJTNBJTIyMjgyODEzNjM3NyUyMiUyQyUyMkJSREglMjIlM0ElMjIxNTUxNzc4OTk4OCUyMiUyQyUyMlJYUUhLJTIyJTNBJTIyJUU1JTlGJThFJUU5JTk1JTg3JTIyJTJDJTIyU0ZHQyUyMiUzQSUyMiVFNSU5MCVBNiUyMiUyQyUyMllaQk0lMjIlM0ElMjI0NTQ2NTAlMjIlMkMlMjJYWERaJTIyJTNBJTIyJUU2JUIyJUIzJUU1JThEJTk3JUU3JTlDJTgxJUU2JUI1JThFJUU2JUJBJTkwJUU1JUI4JTgyJUU1JTg1JThCJUU0JUJBJTk1JUU5JTk1JTg3JTIyJTJDJTIyU1pEUSUyMiUzQSU1QiUyMjExMDAwMCUyMiUyQyUyMjExMDEwMCUyMiUyQyUyMjExMDEwMSUyMiU1RCUyQyUyMlNaRFFfQ04lMjIlM0ElMjIlRTUlOEMlOTclRTQlQkElQUMlRTUlQjglODIrJUU1JUI4JTgyJUU4JUJFJTk2JUU1JThDJUJBKyVFNCVCOCU5QyVFNSU5RiU4RSVFNSU4QyVCQSUyMiUyQyUyMkpUUktTJTIyJTNBMiU3RCUyQyUyMmZvcm0yJTIyJTNBJTVCJTVCJTIyMyUyMiU1RCUyQyU1QiUyMjMlMjIlNUQlMkMlNUIlMjIyJTIyJTVEJTJDJTVCJTIyMSUyMiU1RCUyQyU1QiUyMjElMjIlNUQlMkMlNUIlMjIzJTIyJTVEJTJDJTVCJTIyMiUyMiU1RCUyQyU1QiUyMjMlMjIlMkMlMjIxJTIyJTVEJTVEJTdE",
        ];
        $params[CURLOPT_HTTPHEADER] = array("Authorization:$token");

        $response_setinfo = Http::post($questionnaireUrl,$postData,$params);
        $response_setinfo = json_decode($response_setinfo,true);
        $show_url = $url_base."dormitory/room";
        $response_show_building = Http::get($show_url,"",$params);
        $response_show_building = json_decode($response_show_building,true);
        $building = $response_show_building['data']["list"];
        $count = count($building);
        $building_choice = rand(0, $count-1);
        $building_choice = $building[$building_choice];
        $room = $building_choice["room"];
        // $show_dormitory_url = $url_base."dormitory/show?token=".$token."&type=dormitory&building=".$building_choice;
        // $response_show_dormitory = Http::get($show_dormitory_url);
        // $response_show_dormitory = json_decode($response_show_dormitory,true);
        // $dormitory = $response_show_dormitory['data'];
        $roomcount = count($room);
        $dormitory_choice = rand(0, $roomcount-1);
        while(!$room[$dormitory_choice]["free"]){
            $building_choice = rand(0, $count-1);
            $building_choice = $building[$building_choice];
            $room = $building_choice["room"];
            $roomcount = count($room);
            $dormitory_choice = rand(0, $roomcount-1);
        }
        $buildingSubmit = $building_choice["value"];
        $roomSubmit     = $room[$dormitory_choice]["value"];
        
        $postData = [
            "building"  =>  $buildingSubmit,
            "room"      =>  $roomSubmit,
        ];
        $postData = [
			"key" => base64_encode(urlencode(json_encode($postData))),
        ];

        $show_bed_url = $url_base."dormitory/bed";
        $response_show_bed = Http::post($show_bed_url,$postData,$params);
        $response_show_bed = json_decode($response_show_bed,true);
        // dump($params);
        // dump($postData);
        // dump($response_show_bed);
		// while (!empty($response_show_bed) ) {
        //     $response_show_bed = Http::post($show_bed_url,$postData,$params);
        //     $response_show_bed = json_decode($response_show_bed,true);
        // }
		$bed = $response_show_bed['data']["list"];
		$count = count($bed);
        $bed_choice = rand(0, $count-1);
        $bed_choice = $bed[$bed_choice];
        
		while ($bed_choice["disabled"]) {
			$building = $response_show_building['data']["list"];
            $count = count($building);
            $building_choice = rand(0, $count-1);
            $building_choice = $building[$building_choice];
            $room = $building_choice["room"];
            // $show_dormitory_url = $url_base."dormitory/show?token=".$token."&type=dormitory&building=".$building_choice;
            // $response_show_dormitory = Http::get($show_dormitory_url);
            // $response_show_dormitory = json_decode($response_show_dormitory,true);
            // $dormitory = $response_show_dormitory['data'];
            $roomcount = count($room);
            $dormitory_choice = rand(0, $roomcount-1);
            while(!$room[$dormitory_choice]["free"]){
                $building_choice = rand(0, $count-1);
                $building_choice = $building[$building_choice];
                $room = $building_choice["room"];
                $roomcount = count($room);
                $dormitory_choice = rand(0, $roomcount-1);
            }
            $buildingSubmit = $building_choice["value"];
            $roomSubmit     = $room[$dormitory_choice]["value"];
            
            $postData = [
                "building"  =>  $buildingSubmit,
                "room"      =>  $roomSubmit,
            ];
            $postData = [
                "key" => base64_encode(urlencode(json_encode($postData))),
            ];

            $show_bed_url = $url_base."dormitory/bed";
            $response_show_bed = Http::post($show_bed_url,$postData,$params);
            $response_show_bed = json_decode($response_show_bed,true);
            // dump($response_show_bed);
            $bed = $response_show_bed['data']["list"];
            $count = count($bed);
            $bed_choice = rand(0, $count-1);
            $bed_choice = $bed[$bed_choice];
        }
        
		$bedSubmit = $bed_choice["value"];
		$postData = [
			"building"  =>  $buildingSubmit,
			"room"      =>  $roomSubmit,
			"bed"       =>  $bedSubmit,
        ];
        
        dump($postData);
		$postMarkData = [
			"building"  =>  $buildingSubmit,
			"room"      =>  $roomSubmit,
			"bed"       =>  $bedSubmit,
			"action"    =>  "mark",
		];

		$postData = [
			"key" => base64_encode(urlencode(json_encode($postData))),
		];
		$postMarkData = [
			"key" => base64_encode(urlencode(json_encode($postMarkData))),
		];

        // while ($bed == "该宿舍陕西省人数过多，请更换！") {
        //     $show_dormitory_url = $url_base."dormitory/show?token=".$token."&type=dormitory&building=".$building_choice;
        //     $response_show_dormitory = Http::get($show_dormitory_url);
        //     $response_show_dormitory = json_decode($response_show_dormitory,true);
        //     $dormitory = $response_show_dormitory['data'];
        //     $count = count($dormitory);
        //     $dormitory_choice = rand(0, $count-1);
        //     $dormitory_choice = $dormitory[$dormitory_choice];
            
        //     $show_bed_url = $url_base."dormitory/show?token=".$token."&type=bed&building=".$building_choice."&dormitory=".$dormitory_choice;
        //     $response_show_bed = Http::get($show_bed_url);
        //     $response_show_bed = json_decode($response_show_bed,true);
        //     $bed = $response_show_bed['data']; 
        // }
        // $count = count($bed);
        // $bed_choice = rand(0, $count-1);
        // $bed_choice = $bed[$bed_choice];
		
		$mark_url   = $url_base."dormitory/mark";
        $response_submit = Http::post($mark_url,$postMarkData,$params);
        
        dump($response_submit);
        $submit_url = $url_base."dormitory/submit";
		$response_submit = Http::post($submit_url,$postData,$params);
		
        $response_submit = json_decode($response_submit,true);
        dump($response_submit);
 
		$postData = [
			"type" => "confirm",
		];
		$postData = [
			"key" => base64_encode(urlencode(json_encode($postData))),			
		];

        $confirm_url = $url_base."dormitory/confirm";
        $response_confirm= Http::post($confirm_url,$postData,$params);
        $response_confirm = json_decode($response_confirm,true);

        // if ($response_confirm["code"] != 0) {
        //     dump($response_confirm);
        //     exit;
        // }
        dump($response_confirm);
    	// dump($response_confirm);
        // $finish_url = $url_base."dormitory/finished?token=".$token."&type=confirm";
        // $finish_url= Http::get($finish_url);
        // $finish_url = json_decode($finish_url,true);
        
    }
    
    /**
     * 测试并发登录
     */
    public function testlogin()
    {
        header('Access-Control-Allow-Origin:*');
        $count = Db::name('fresh_info') -> count();
        $id = rand(1,$count);
        $info = Db::name('fresh_info') -> where('id',$id) ->field('XH, SFZH') -> find();
        $XH = $info['XH'];
        $SFZH = $info['SFZH'];
        $password = substr($SFZH, -6);
        $userid = $this -> loginself($XH, $password);
        if($userid){
            $this->_token = Random::uuid();
            Token::set($this->_token, $userid, $this->keeptime);
            Hook::listen("user_login_successed", $userid);
            $info = $this -> _token;
            $this->success('认证成功',$info);
        } else {
            $this->error('认证失败','请检查学号以及密码是否正确');
        } 
    }

    private function loginself($XH, $ZKZH)
    {
        $info = Db::name('fresh_info')
                    -> where('XH', $XH)
                    -> where('ZKZH', $ZKZH)
                    ->find(); 
        if (empty($info)) {
            return false;
        } else {
            $userid = $info['ID'];
            return $userid;
        }
    }
    /**
     * 测试返回剩余房间数和床位数
     */
    public function testshow(){
        header('Access-Control-Allow-Origin:*');
        $mem_p1 = memory_get_usage();
        $count = Db::name('fresh_info') -> count();
        $id = rand(1,$count);
        $data = Db::name('fresh_info') -> where('id',$id) ->field('XBDM,YXDM') -> find();
        $college_id = $data['YXDM'];
        $sex = $data['XBDM'];

        $data = Db::name('fresh_dormitory')
                    -> where('YXDM',$college_id)
                    -> where('XB', $sex)
                    -> group('LH')
                    -> select();
        echo Db::name('fresh_dormitory')->getLastSql();
        foreach ($data as $key => $value) {
            $build = $value['LH'];
            if ($build <= 6 && $build > 0) {
                $info = array(
                    'name' =>  $build."号楼（西区）",
                    'value' => $build,
                );   
            } elseif ($build <=15) {
                $info = array(
                    'name' =>  $build."号楼（东区）",
                    'value' => $build,
                );   
            } elseif ( $build <= 19) {
                $info = array(
                    'name' =>  $build."号楼（高层）",
                    'value' => $build,
                );   
            }
            $list[] = $info;
        }
        $dormitory_info = Db::name('fresh_dormitory') -> where('SYRS','>=','1') 
                                -> where('XB',$sex)
                                -> where('YXDM',$college_id)
                                -> field('SYRS')
                                -> select();
        echo Db::name('fresh_dormitory')->getLastSql();
        $dormitory_number = count($dormitory_info);
        $bed_number = 0;
        foreach ($dormitory_info as $key => $value) {
            $bed_number += $value['SYRS'];
        }
        $mem_p9 = memory_get_usage();

        $mem_cost = ($mem_p9 - $mem_p1) / 1024 / 1024 ;

        $this -> success('查询成功', ['memory'=> $mem_cost.'mb','list' => $list, 'dormitory_number' => $dormitory_number, 'bed_number' => $bed_number]);
    }

    public function testLoad(){
        $result = Db::name('fresh_info') -> find();
        $this->success('success',$result);
    }

    public function testPHP(){
        $this->success('success');
    }
    /**
     * 测试服务器环境
     */
    public function testinfo()
    {
        $key = json_decode(urldecode(base64_decode($this->request->post('key'))),true);
        $DormitoryModel = new DormitoryModel;
        $steps = parent::getSteps($this->loginInfo['user_id']);
        $result = $DormitoryModel -> setinfo($this->userInfo, $key, $steps);
        if (!$result['status']) {
            $this -> error($result['msg'], $result['data']);
        } else {
            $data = $result['data'];
            $info = $result['info'];
            $Userinfo = parent::validate($data,'Userinfo.user');
            $Family[0] = $Userinfo;
            if (empty($info)) {
                if (gettype($Userinfo) == 'string') {
                    $this->error($Userinfo);
                }
                $data['RJSR'] = $data['ZSR']/$data['JTRKS'];
                $data['RJSR'] = round($data['RJSR'], 2);
                $this -> success($data['RJSR']);
                $res = Db::name('fresh_info_add') -> insert($data);
                $res == 1 ? $this -> success('信息录入成功'): $this -> error('信息录入失败');
            } else {
                foreach ($info as $key => $value) {
                    $Familyinfo = parent::validate($value,'Userinfo.family');
                    $Family[] = $Familyinfo;
                }
                foreach ($Family as $key => $value) {
                    if (gettype($value) == "string") {
                        $this->error($value);
                    }
                }
                $res = Db::name('fresh_info_add') -> insert($data);
                foreach ($info as $key => $value) {
                   $res1 = Db::name('fresh_family_info') -> insert($value);
                }
                if ($res && $res1) {
                    $this -> success("信息录入成功");
                }else {
                    $this -> error("信息录入失败");
                }
            }
        }
    }
    /**
     * 查找数据库中的重复数据
     */
    public function searchsame(){
        $data = Db::name('fresh_dormitory') -> field('SSDM,CPXZ') -> select();
        foreach ($data as $key => $value) {
            $SSDM = $value['SSDM'];
            $RS = strlen($value['CPXZ']);
            $number = Db::name('fresh_list') -> where('SSDM',$SSDM) -> count();
            if ($number > $RS) {
                echo "宿舍有问题".$SSDM;
                echo '<br/>';
            } 
        }
        // $data = Db::name('fresh_info')-> field('XH') -> select();
        // $nomal = 0;
        // $no = 0;
        // $except = 0;
        // foreach ($data as $key => $value) {
        //     $stu_id = $value['XH'];
        //     $number = Db::name('fresh_list') -> where('XH', $stu_id) -> count();
        //     if ($number == 1) {
        //         $nomal += 1;
        //     } elseif ($number == 0) {
        //         $no += 1;
        //     } else {
        //         $except += 1;
        //     }
        // }
        // echo "正常人数".$nomal;
        // echo "未选择人数".$no;
        // echo "异常人数".$except;
    }

}
    