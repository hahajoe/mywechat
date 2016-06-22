<?php
//消息接收与反应类
class wechatCallbackapi{
	private $token;
	private $appid;
	private $appsecret;
	private $encodingaeskey;
	private $wxid;
	private $originalid;
	
	public function __construct($pparams)
	{
		$this -> token = $pparams["TOKEN"];
		$this -> appid = $pparams["APPID"];
		$this -> appsecret = $pparams["APPSECRET"];
		$this -> encodingaeskey = $pparams["ENCODINGAESKEY"];
		$this -> wxid = $pparams["WXID"];
		$this -> originalid = $pparams["ORIGINALID"];
	}

	public function valid($timestamp,$nonce,$signature,$echoStr)
    {
        if($this->checkSignature($timestamp,$nonce,$signature)){
        	echo $echoStr;
			$this -> responseMsg();
        	exit;
        }
    }
	
	private function checkSignature($timestamp,$nonce,$signature)
	{
        // you must define TOKEN by yourself
        if (!isset($this -> token)){
            throw new Exception('TOKEN is not assigned!');
        }        
        		
		$tmpArr = array($this -> token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	//消息加密
	private function generateEncryptMsg($replyMsg, $timeStamp, $nonce, &$reMsg)
	{		
		$pc = new WXBizMsgCrypt($this -> token, $this -> encodingaeskey, $this -> appid);
		
		$errCode = $pc->encryptMsg($replyMsg, $timeStamp, $nonce, $reMsg);
		// echo $aaa;
		//return;
		return $errCode;
	}
	
	//消息解密
	private function getDecryptMsg($msgSign, $timeStamp, $nonce, $from_xml)
	{
		$msg= "";
		$pc = new WXBizMsgCrypt($this -> token, $this -> encodingaeskey, $this -> appid);
		
		$errCode = $pc->decryptMsg($msgSign, $timeStamp, $nonce, $from_xml, $msg);
		if ($errCode == 0) {
			return $msg;
		} else {
			return false;
		}
		//return $errCode;
	}

     public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){                
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
				$encryptType = $_GET["encrypt_type"];
				//解密加密数据（在兼容模式和安全模式下起作用）
				if(isset($encryptType) && strtolower($encryptType)=="aes"){
					include_once "wxBizMsgCrypt.php";
					$msg_sign = $_GET["msg_signature"];
					$timestamp = $_GET["timestamp"];
					$nonce = $_GET["nonce"];
					$xmlStr = $this -> getDecryptMsg($msg_sign, $timestamp, $nonce, $postStr);
					if($xmlStr){
						$postObj = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);
					}
				}				
				//解密加End
				
                $fromUsername = (string) $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
				$msgType = $postObj->MsgType;
				$msgId = $postObj->MsgId;
				
				$simpleMsgTypes = array("text","image","voice","video","shortvideo","location","link");
				
				
                $time = time();
				//回复用户消息模板
				$tpls = array(
					"textTpl" => "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							</xml>",
					"imageTpl" => "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[image]]></MsgType>
							<Image>
							<MediaId><![CDATA[%s]]></MediaId>
							</Image>
							</xml>",
					"voiceTpl" => "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[voice]]></MsgType>
							<Voice>
							<MediaId><![CDATA[%s]]></MediaId>
							</Voice>
							</xml>",
					"videoTpl" => "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[video]]></MsgType>
							<Video>
							<MediaId><![CDATA[%s]]></MediaId>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							</Video> 
							</xml>",
					"musicTpl" => "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[music]]></MsgType>
							<Music>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							<MusicUrl><![CDATA[%s]]></MusicUrl>
							<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
							<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
							</Music>
							</xml>",
					"newsTpl" => array(
							"newsMainTpl" => "<xml>
									<ToUserName><![CDATA[%s]]></ToUserName>
									<FromUserName><![CDATA[%s]]></FromUserName>
									<CreateTime>%s</CreateTime>
									<MsgType><![CDATA[news]]></MsgType>
									<ArticleCount>%s</ArticleCount>
									<Articles>%s</Articles>
									</xml>",
							"newsItemTpl" => "<item>
									<Title><![CDATA[%s]]></Title> 
									<Description><![CDATA[%s]]></Description>
									<PicUrl><![CDATA[%s]]></PicUrl>
									<Url><![CDATA[%s]]></Url>
									</item>")
				);

				if(isset($msgType) && $msgType=="event"){//处理推送事件
					$event = $postObj->Event;
					//接收消息，接收时间集
					$rEvents = array("subscribe","unsubscribe","SCAN","LOCATION");
					//菜单事件集
					$mEvents = array("CLICK","VIEW","scancode_push","scancode_waitmsg","pic_sysphoto","pic_photo_or_album","pic_weixin","location_select");
					if(in_array($event,$mEvents)){
						if($event=="CLICK"){
							$eventkey = $postObj->EventKey;
							//根据$eventkey关键字，来进行相应的操作
						}
						if($event=="VIEW"){
							$eventkey = $postObj->EventKey;//事件KEY值，设置的跳转URL
							$menuid = $postObj->MenuID;//如果是个性化菜单可以根据该值来判断是哪个个性化菜单被点击
							//do somthing..........
						}
					}
					
					if(in_array($event,$rEvents)){
						if($event == "LOCATION"){
							//服务号功能-上报地理位置
						}
						if($event == "SCAN"){		
							//服务号功能-扫描带参数二维码事件
						}
						if($event == "subscribe"){		
							//关注事件，可以随后向用户发送欢迎消息
						}
						if($event == "unsubscribe"){		
							//取消关注事件，可以随后在自己的数据库里对该用户进行记录
						}
					}
				}
				
 				if(isset($msgType) && in_array($msgType,$simpleMsgTypes)){//常规消息事件处理					
              		$msgType = "text";
					$arr = is_object($postObj) ? get_object_vars($postObj) : $postObj;
					foreach($arr as $key=>$value){
						$contentStr .= $key."=>".$value."\n";
					}
					
					require_once("wecustomservice.php");//引用客服类文件
					
					if(!empty($keyword) && $keyword=="创建客服"){
						$custservice = new weCustomService($this -> appid, $this -> appsecret);
						$apron = $custservice -> addAccount("zta@wbxjzxh", "July", "123456");
						if($apron){
							$contentStr .= "客服招聘成功！\n";
						}else{
							$contentStr .= "客服招聘失败！\n".$custservice -> getErrMsg();
						}
						unset($custservice);
					}
					
					if(!empty($keyword) && $keyword=="修改客服"){
						$custservice = new weCustomService($this -> appid, $this -> appsecret);
						$apron = $custservice -> updateAccount("nini2@wbxjzxh", "Baby", "123456");
						if($apron){
							$contentStr .= "客服任命成功！\n";
						}else{
							$contentStr .= "客服任命失败！\n".$custservice -> getErrMsg();
						}
						unset($custservice);
					}
					
					if(!empty($keyword) && $keyword=="删除客服"){
						$custservice = new weCustomService($this -> appid, $this -> appsecret);
						$apron = $custservice -> delAccount("nini2@wbxjzxh");
						if($apron){
							$contentStr .= "客服删除成功！\n";
						}else{
							$contentStr .= "客服删除失败！\n".$custservice -> getErrMsg();
						}
						unset($custservice);
					}
					
					if(!empty($keyword) && $keyword=="获取客服"){
						$custservice = new weCustomService($this -> appid, $this -> appsecret);
						$apron = $custservice -> getAccountList();
						if($apron){
							$contentStr .= "客服获取成功！\n";
							$arr = json_decode($apron, true);
							
							$i = 1;
							foreach($arr["kf_list"] as $v){
								$contentStr .= $i."=> \n";
								if(is_array($v)){									
									foreach($v as $key=>$value){
										$contentStr .= $key."=>".$value."\n";
									}
								}
								$i++;
							}
						}else{
							$contentStr .= "客服获取失败！\n".$custservice -> getErrMsg();
						}
						unset($custservice);
					}
					
					$custservice = new weCustomService($this -> appid, $this -> appsecret);
					$rems = $custservice -> sendMessage($fromUsername, "text", array("content" => "客服消息！"));
					if($rems){
						$contentStr .= "客服消息：\n";
					}else{
						$contentStr .= "客服消息发送失败：\n".$custservice -> getErrMsg();
					}
					
                	$resultStr = sprintf($tpls["textTpl"], $fromUsername, $toUsername, $time, $msgType, $contentStr);
					
					//加密数据（在兼容模式和安全模式下起作用）
					if(isset($encryptType) && strtolower($encryptType)=="aes"){
						$reMsg = "";
						$errCode = $this -> generateEncryptMsg($resultStr, $timestamp, $nonce, $reMsg);
				
						if($errCode == 0){
							$resultStr = $reMsg;
						}
					}
					//加密数据End
                	echo $resultStr;
					return;
				}
				
				if(!empty( $keyword ))
                {
              		$msgType = "text";
                	$contentStr = "Welcome to wechat world!";
                	$resultStr = sprintf($tpls["textTpl"], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;
                }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
    } 
}

?>