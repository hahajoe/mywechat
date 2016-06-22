<?php
//客服类
require_once("wechat.php");

class weCustomService extends weChat{//客服类
	
	public function __construct($aid,$asecrect){
		parent::__construct($aid,$asecrect);		
		$menutemp = array(
			"addaccount" => "https://api.weixin.qq.com/customservice/kfaccount/add?access_token=%s",
			"updateaccount" => "https://api.weixin.qq.com/customservice/kfaccount/update?access_token=%s",
			"delaccount" => "https://api.weixin.qq.com/customservice/kfaccount/del?access_token=%s&kf_account=%s",
			"uploadheader" => "http://api.weixin.qq.com/customservice/kfaccount/uploadheadimg?access_token=%s&kf_account=%s",
			"getaccountlist" => "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=%s",
			"sendmessage" => "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s"
			);
		$this -> apptmp = array_merge($this -> apptmp, $menutemp);
	}
	
	public function addAccount($csaccount, $nickname, $password){
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["addaccount"] , $accesstoken);
			$accountdata = array(
				"kf_account" => $csaccount,
				"nickname" => $nickname,
				"password" => md5($password)
			);
			$accountdata = json_encode($accountdata);
			$accountJ = $this->https_request($appurl , $accountdata);
			$accountA = json_decode($accountJ,true);
			
			if(empty($accountA["errcode"])){
				return true;
			}
			$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $accountA["errcode"] , $accountA["errmsg"]);
		}
		return false;
	}
	
	public function updateAccount($csaccount, $nickname, $password){
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["updateaccount"] , $accesstoken);
			$accountdata = array(
				"kf_account" => $csaccount,
				"nickname" => $nickname,
				"password" => md5($password)
			);
			$accountdata = json_encode($accountdata);
			$accountJ = $this->https_request($appurl , $accountdata);
			$accountA = json_decode($accountJ,true);
			
			if(empty($accountA["errcode"])){
				return true;
			}
			$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $accountA["errcode"] , $accountA["errmsg"]);
		}
		return false;
	}
	
	public function delAccount($csaccount){
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["delaccount"] , $accesstoken, $csaccount);
			$accountJ = file_get_contents($appurl);
			$accountA = json_decode($accountJ,true);
			
			if(empty($accountA["errcode"])){
				return true;
			}
			$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $accountA["errcode"] , $accountA["errmsg"]);
		}
		return false;
	}
	
	public function uploadHeader(){
		return "Function is developing......";
	}
	
	public function getAccountList(){
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["getaccountlist"] , $accesstoken);
			$accountJ = file_get_contents($appurl , $accountdata);
			$accountA = json_decode($accountJ,true);
			
			if(!isset($accountA["errcode"])){
				return $accountJ;
			}
			$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $accountA["errcode"] , $accountA["errmsg"]);			
		}
		return false;
	}
	
	public function sendMessage($touser, $msgtype, $mdata){
		$msgtypes = array("text","image","voice","video","music","news","mpnews","wxcard");
		if(!in_array($msgtype,$msgtypes)){
			$this -> errmsg = sprintf($this -> apptmp["errmsg"] , "250" , "Message type is mismatching!");
			return false;
		}
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["sendmessage"] , $accesstoken);
			$messagedata = array(
				"touser" => $touser,
				"msgtype" => $msgtype,
				$msgtype => $mdata
			);
			// if(substr(PHP_VERSION, 0, 3)  >= "5.4"){
				// $messagedata = json_encode($messagedata, JSON_UNESCAPED_UNICODE);				
			// }else{
				//处理中文在json_encode后转换为unicode编码的问题
				foreach($messagedata[$msgtype] as $k => $v){
					$messagedata[$msgtype][$k] = urlencode($v);
				}
				$messagedata = json_encode($messagedata);
				$messagedata = urldecode($messagedata);			
			//}
			$messageJ = $this->https_request($appurl , $messagedata);
			$messageA = json_decode($messageJ,true);
			
			if(empty($messageA["errcode"])){
				return true;
			}
			$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $messageA["errcode"] , $messageA["errmsg"]);
		}
		return false;
	}
}
?>