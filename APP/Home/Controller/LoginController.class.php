<?php
namespace Home\Controller;
use Think\Controller;
header("content-type:text/html;charset=utf8");
class LoginController extends Controller
{
	public function test() {
		$user = M("user");
		$invite = "DLWYZ0";
		if ( $super = $user->where("invite_code = '$invite'")->find() ) {
			echo var_dump($super) . "<br/>";
			$data['superior'] = (int)$super['id'];
			exit(var_dump($data));
		}
		else {
			echo json_encode(array("status"=>4));     // 4. 邀请码错误
			exit();
		}
	}

	public function login()	{
		$mobile=I("post.mobile"); 
		$password=I("post.password");
		$password=md5($password);
		$user = M("user");
		if( $one = $user->where("mobile = $mobile")->find() ) {
			if($one["password"]==$password){
				$super_id = $one['superior'];
				$super = $user->where("id = $super_id")->find();
				echo json_encode(array(
					"status"=>1, 
					"mobile" => $mobile,
					"invite_code"=>$super['invite_code'],
					"vip_date" => $one['vip_date'],
					"id" => $one['id'],
				));     // 登录成功
			} else {
				echo json_encode(array("status"=>2));    // 密码错误
			}
		} else {
		 	echo json_encode(array("status"=>3));    //未注册
		}
	}

	public function regis() {
		$mobile = I("post.mobile");
		$mobile_verify = I("mobile_verify");
		$verify_code = I("post.verify");
		$invite = I("post.invite");

   		// 短信验证码验证
		$obj=new \Org\Phone\Phone();
		$flag= json_decode( $obj->Verify($mobile, $mobile_verify) );
		if ( isset($flag->code)) {
			echo json_encode(array("status"=>2));
			exit();                                   // 2 短信验证码输入错误
		}

		$user = M("user");
		$datetime = new \DateTime;
		$data = array(
			'mobile' => $mobile, 
			'password' => md5(I("post.password")),
			"vip_date" => $datetime->format('Y-m-d H:i:s'),
			"superior" => 0,
		);
		// 添加上级
		
		if ( $super = $user->where("invite_code = '$invite'")->find() )
			$data['superior'] = (int)$super['id'];
		else {
			echo json_encode(array("status"=>4));     // 4. 邀请码错误
			exit();
		}
		// 注册
		if ( $user->where("mobile = $mobile")->find() ) {
			echo json_encode(array("status"=>2));      // 用户已存在
		} else {
			if ( $user->create($data) ) {
				$user->add();
				echo json_encode(array("status"=>1));      // 1 注册成功
			} else {
				echo json_encode(array("status"=>0));      // 0 注册失败
			}
		}	
	}

	public function modify() {
		$mobile = I("post.mobile");
		$old_password = md5(I("post.old_password"));

		$user = M("user");
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
		$user = M("user");
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
		// $mobile = I("post.mobile");
		$mobile = "18434755428";

		// $user = M("user");
		// if ( $user->where("mobile = $mobile")->find() ) {
		// 	echo json_encode(array("status"=>2));          // 2 用户已存在
		// 	exit();
		// }

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