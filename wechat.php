<?php
//基类
class weChat {
	protected $ary = array();
	protected $appid;
	protected $appsecrect;
	protected $apptmp;
	protected $errmsg;
	
	public function __construct($aid,$asecrect){
		$this -> appid = $aid;
		$this -> appsecrect = $asecrect;
		
		$this->apptmp = array(
		"getaccesstoken" => "https://api.weixin.qq.com/cgi-bin/token?grant_type=%s&appid=%s&secret=%s",
		"gethostiplist" => "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=%s",
		"errmsg" => "ErrorCode:%s; ErrorMessage:%s"
		);
	}
	
	public function __set($key,$val){
		$this -> ary[$key] = $val;
	}
	
	public function __get($key){
		if(isset($this -> ary[$key])){
			return $this -> ary[$key];
		}
		return null;
	}
	
	public function __isset($key){
		if(isset($this -> ary[$key])){
			return true;
		}
		return false;
	}
	
	public function __unset($key){
		unset($this -> ary[$key]);
	}
	
	public function setAppTmp($aurl=array()){
		//$this -> apptmp = $aurl;
		//考虑一下，该结构是用来修改，还是追加，或者覆盖
	}
	
	public function getErrMsg(){
		return $this -> errmsg;
	}
	
	public function getAccessToken(){//成功返回一个字符串，失败返回false
		//先检查数据库或者memcache中有没有可用的accesstoken
		if(isset($this -> appid) && isset($this -> appsecrect)){
			$appurl = sprintf($this -> apptmp["getaccesstoken"] , "client_credential" , $this -> appid , $this -> appsecrect);

			$accessTokenJ = file_get_contents($appurl); 
			$accessTokenA = json_decode($accessTokenJ,true);
			
			if(isset($accessTokenA["errcode"])){
				$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $accessTokenA["errcode"] , $accessTokenA["errmsg"]);
				return false;
			}
			
			$access_token = $accessTokenA["access_token"];
			$expires_in = $accessTokenA["expires_in"];
			//将信息写入数据库或者memcache
			return $access_token;
		}
		return false;
	}
	
	public function getHostIPList(){//成功返回一个ip地址数组，失败返回false
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["gethostiplist"] , $accesstoken);
			$iplistJ = file_get_contents($appurl);
			$iplistA = json_decode($iplistJ,true);
			
			if(isset($iplistA["errcode"])){
				$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $iplistA["errcode"] , $iplistA["errmsg"]);
				return false;
			}
			
			return $iplistA["ip_list"];
		}
		return false;
	}
	
	//这个业务逻辑方法不应该出现在这个类里面，暂时先放着，以后在调出来吧
	public function https_request($url,$data=null)
	{
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
		if(!empty($data)){
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
		}
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
}

?>