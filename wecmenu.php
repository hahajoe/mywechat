<?php
//菜单操作类
require_once("wechat.php");

class weCMenu extends weChat{//菜单类	
	
	public function __construct($aid,$asecrect){
		parent::__construct($aid,$asecrect);		
		$menutemp = array(
			"createmenu" => "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s",
			"getmenu" => "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=%s",
			"delmenu" => "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=%s",
			"createconditionmenu" => "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=%s",
			"delconditionmenu" => "https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token=%s",
			"matchconditionmenu" => "https://api.weixin.qq.com/cgi-bin/menu/trymatch?access_token=%s",
			"getmenuconfig" => "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=%s"
			);
		$this -> apptmp = array_merge($this -> apptmp, $menutemp);
	}
	
	private function createNode($ntype){
		$type = strtolower($ntype);
		$types = array("parentmenu","click","view","scancode_push","scancode_waitmsg","pic_sysphoto","pic_photo_or_album","pic_weixin","location_select","media_id","view_limited");
		if(!in_array($type , $types)){
			$this -> errmsg = sprintf($this -> apptmp["errmsg"] , "h_m_001" , "Menu Type is missmatch.");
			return false;
		}
		
		$keys = array(
			"scancode_waitmsg" => "rselfmenu_0_0",
			"scancode_push" => "rselfmenu_0_1",
			"pic_sysphoto" => "rselfmenu_1_0",
			"pic_photo_or_album" => "rselfmenu_1_1",
			"pic_weixin" => "rselfmenu_1_2",
			"location_select" => "rselfmenu_2_0"
		);
		
		if($type=="parentmenu"){
			return array(
				"name" => "",
				"sub_button" => array()
			);
		}
		
		if($type=="click"){
			return array(
				"type" => "click",
				"name" => "",
				"key" => ""
			);
		}
		
		if($type=="view"){
			return array(
				"type" => "view",
				"name" => "",
				"url" => ""
			);
		}
		
		if(isset($keys[$type])){
			return array(
				"type" => $type,
				"name" => "",
				"key" => $keys[$type],
				"sub_button" => array()
			);
		}
		
		if($type=="location_select"){
			return array(
				"type" => "location_select",
				"name" => "",
				"key" => $keys[$type]
			);
		}
		
		if($type=="media_id" || $type=="view_limited"){
			return array(
				"type" => $type,
				"name" => "",
				"media_id" => ""
			);
		}
	}
	
	//该方法如果能写完，它应该是私有的
	public function buildMenuData($menuarray){
		$cmenu = array(
			"button"=>array()
		);
		$node = $this -> createNode("");
		echo json_encode($node);
	}
	
	public function createMenu($mdata){//$mdata可以是字符串也可以是数组，如果字符串直接生成（最好写一个检查函数），如实是组通过buildMenuData()生成字符串再生成
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["createmenu"] , $accesstoken);
			if(is_array($mdata)){
				$appdate = $this -> buildMenuData($mdata);				
			}else{
				//此处最好能检查一下字符串的格式
				$appdate = $mdata;
			}
			$menuJ = $this->https_request($appurl , $appdate);
			$menuA = json_decode($menuJ,true);
			
			if(isset($menuA["errcode"])){
				$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $menuA["errcode"] , $menuA["errmsg"]);
				return false;
			}
			return true;
		}
		return false;		
	}
	
	public function getMenu(){
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["getmenu"] , $accesstoken);
			$menuJ = file_get_contents($appurl);
			$menuA = json_decode($menuJ,true);
			
			if(isset($menuA["errcode"])){
				$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $menuA["errcode"] , $menuA["errmsg"]);
				return false;
			}
			return $menuJ;
		}
		return false;	
	}
	
	public function delMenu(){
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["delmenu"] , $accesstoken);
			$menuJ = file_get_contents($appurl);
			$menuA = json_decode($menuJ,true);
			
			if(isset($menuA["errcode"])){
				$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $menuA["errcode"] , $menuA["errmsg"]);
				return false;
			}
			return true;
		}
		return false;	
	}
	
	public function createConditionMenu(){
		//do somthing.....
	}
	
	public function delConditionMenu(){
		//do somthing.....
	}
	
	public function matchConditionMenu(){
		//do somthing.....
	}
	
	public function getMenuConfig(){
		$accesstoken = $this -> getAccessToken();
		if(isset($accesstoken)){
			$appurl = sprintf($this -> apptmp["getmenuconfig"] , $accesstoken);
			$menuJ = file_get_contents($appurl);
			$menuA = json_decode($menuJ,true);
			
			if(isset($menuA["errcode"])){
				$this -> errmsg = sprintf($this -> apptmp["errmsg"] , $menuA["errcode"] , $menuA["errmsg"]);
				return false;
			}
			
			$comTypes = array("view","text","img","photo","video","voice");//公众平台上设置的菜单类型集合，API设置的更多
			return $menuJ;
		}
		return false;
	}
}
?>