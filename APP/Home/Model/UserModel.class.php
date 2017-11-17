<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
	protected $trueTableName="user";
	protected $_validate = array(
		array('phone','/^1[3|4|5|8][0-9]\d{4,8}$/','请输入正确的手机号',0,'regex',3),
		array('password','/^\w{6,18}$/','密码格式错误',0,'regex',3),
		array('phone','','手机号已被注册',0,'unique',3),
	);

	protected $_auto = array (
		array('password','md5',3,'function'),
	);
}
?>