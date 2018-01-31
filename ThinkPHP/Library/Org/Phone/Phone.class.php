<?php
namespace Org\Phone;
class Phone{
	public function Send($phone, $operation ){
		$url = "https://fg5pcyhk.api.lncld.net/1.1/requestSmsCode";
		$post_data = array(
			"mobilePhoneNumber" => $phone,
			"ttl" => 10,       // 鏈夋晥鏃堕棿锛屽崟浣嶅垎閽燂紝榛樿10鍒嗛挓
			"name" => "V客云盒",    // 搴旂敤鍚�
			"op" => $operation
		);
		$headers = array();
	        $headers[] = "X-LC-Id: ";
	        $headers[] = "X-LC-Key: ";
	        $headers[] = "Content-Type: application/json";
			
		$res = $this->curlPost($url,$post_data,$headers);
		return $res;
	}

	public function Verify($phone, $code) {
		$url = "https://fg5pcyhk.api.lncld.net/1.1/verifySmsCode/".$code;
		$post_data = array(
			"mobilePhoneNumber" => $phone,
			);
		$headers = array();
	        $headers[] = "X-LC-Id: ";
	        $headers[] = "X-LC-Key: ";
	        $headers[] = "Content-Type: application/json";
	       $res = $this->curlPost($url,$post_data,$headers);
	       return $res;
	}

	private   function curlPost($url,$data,$header){
	             //初始化
	            $ch = curl_init();
	            
	            //$header = ["Accept-Charset: utf-8"];
			curl_setopt($ch, CURLOPT_URL, $url);
	                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	                curl_setopt($ch, CURLOPT_POST, 1);
	                curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($data));
	                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$tmpInfo = curl_exec($ch);
	                if (curl_errno($ch)) {
	                  return false;
	                }else{
	                  return $tmpInfo;
	                }
	        }
}
?>