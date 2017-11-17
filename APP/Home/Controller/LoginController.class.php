<?php
namespace Home\Controller;
use Think\Controller;
header("content-type:text/html;charset=utf8");
class LoginController extends Controller
{
	public function test() {
		$Live = M("main_live");
		$lives = $Live->select();
		$this->ajaxReturn($lives);
	}

	public function getAnnouncement() {
		$announcement = M("main_announcement");
		$text = $announcement->order('-create_time')->find();
		echo $text['text'];
	}

	public function recharge() {
		$code = I("post.code");
		$mobile = I("post.mobile");

		$Carmel = M("agent_carmel");
		$carmel = $Carmel->where("code = '$code'")->find();
		if (! $carmel )
			echo json_encode(array("status"=>2));     // 无此卡密
		else if ($carmel['status'] == 1) 
			echo json_encode(array("status"=>3));
		else if ($carmel['status'] == 0) {
			$add_month = 0;
			if ($carmel['type_class'] == "月卡")
				$add_month = 1;
			else if ($carmel['type_class'] == "季卡" )
				$add_month = 3;
			else if ($carmel['type_class'] == "年卡" )
				$add_month = 12;

			$User = M("agent_user");
			$user = $User->where("mobile = $mobile")->find();
			$vip_date = $user['vip_date'];
			$date = date_create($vip_date);
			date_add($date, date_interval_create_from_date_string("$add_month months"));
			$vip_date = date_format($date, "Y-m-d H:m:s");
			$User->where("mobile = $mobile")->save(array("vip_date"=>$vip_date));   // 更新用户VIP时间
			$Carmel->where("code = '$code'")->save(array("status"=>1));    // 卡密已使用
			echo json_encode(array("status"=>1, "vip_date"=>$vip_date));     // 充值成功
		}
	}

	public function get_lives() {
		$Live = M("main_live");
		$lives = $Live->select();
		$this->ajaxReturn($lives);
	}

	public function login()	{
		$mobile=I("post.mobile"); 
		$password=I("post.password");
		$password=md5($password);
		$user = M("agent_user");
		if( $one = $user->where("mobile = $mobile")->find() ) {
			if($one["password"]==$password){
				$agent = M("agent_agent");
				$superior_id = $one['superior_id'];
				$invite_code = $agent->where("id = $superior_id")->find();
				$invite_code = $invite_code['invite_code'];
				echo json_encode(array(
					"status"=>1, 
					"mobile" => $mobile,
					"vip_date" => $one['vip_date'],
					"invite_code" => $invite_code,
					"id" => $one['id'],
				));     // 登录成功
			} else {
				echo json_encode(array("status"=>2));    // 密码错误
			}
		} else {
		 	echo json_encode(array("status"=>3));    //未注册
		}
	}


	public function modify() {
		$mobile = I("post.mobile");
		$old_password = md5(I("post.old_password"));

		$user = M("agent_user");
		$data = array(
			'password' => md5( I("post.new_password") ),
		);
		if ( $one = $user->where("mobile = $mobile")->find() ) {
			if ($one['password'] == $old_password) {
				if ( $user->where("mobile = $mobile")->save($data) ) {
					echo json_encode(array("status"=>1));     // 1 修改成功
				} else {
					echo json_encode(array("status"=>0));     // 修改失败
				}
			}
		} else {
			echo json_encode(array("status"=>2));         // 2 原密码错误
		}
	}

	public function find() {
		$user = M("agent_user");
		$mobile = I("post.mobile");
		$mobile_verify = I("post.mobile_verify");

		if ($user->where("mobile = $mobile")->find() == NULL) {
			exit(json_encode(array("status"=>3)));
		}

		$obj=new \Org\Phone\Phone();
		$flag=json_decode( $obj->Verify($mobile, $mobile_verify) );
		if (isset($flag->code)) {
			echo json_encode(array("status"=>2));
			exit();                                   // 2 短信验证码输入错误
		}

		$data = array(
			'password' => md5( I("post.password"))
		);
		if ( $user->where("mobile = $mobile")->save($data) ) {
			echo json_encode(array("status"=>1));     // 修改成功
		} else {
			echo json_encode(array("status"=>0));     // 修改失败
		}
	}

	public function refresh_verify() {
		ob_clean();
		$Verify = new \Think\Verify();
    	$Verify->entry();
	}

	public function send_verify() {
		$obj=new \Org\Phone\Phone();
		$mobile = I("post.mobile");
		$user = M("agent_user");
		if ( $user->where("mobile = $mobile")->find() ) {
			echo json_encode(array("status"=>2));          // 2 用户已存在
			exit();
		}

		$operation = I("post.operation");     // 请求类型
		$status=json_decode( $obj->send($mobile, $operation) );
		if (!isset($status->error) )
			echo json_encode(array("status"=>1));
		else
			exit( var_dump($status) );
	}

	public function get_string($length) {
				// 生成邀请码
		$chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$invite_code_list = array_rand($chars, 6);
		$invite_code = "";
		for($i=0; $i<6; $i++) {
			$invite_code .= $chars[$invite_code_list[$i]];
		}

		$charsUpper = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$charsCase = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$charsAll = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$chars = $charsUpper;
		$invite_code_list = array_rand($chars, $length);
		$invite_code = "";
		for($i=0; $i<$length; $i++) {
			$invite_code .= $chars[$invite_code_list[$i]];
		}
		echo $invite_code;
	}
}
?>