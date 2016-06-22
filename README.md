# mywechat
封装微信的api借口以待后用
2016-6-22 第一次上传版本
1、接收反馈类（wechatCallbackapi）封装了微信验证、消息接收、消息反馈接口，包含消息加密操作-----wechatcallbackapi.php；
2、定义类基础类（weChat）存储appid、appsecret等信息，apptmp存储微信api的接口地址模板和返回的错误代码和错误消息模板这些模板可以在子类总继续增加，errmsg存储接口调用返回的错误信息，getAccessToken、getHostIPList、https_request等方法-----wechat.php；
3、定义菜单操作类（weCMenu，weChat的子类），构造时在apptmp增加了与菜单操作有关的api几口地址模板，createMenu、getMenu、delMenu、getMenuConfig等方法，createConditionMenu、delConditionMenu、matchConditionMenu未编写，createNode、buildMenuData有待完善----wecmenu.php；
4、客服类（weCustomService，weChat的子类），构造时在apptmp增加了与客服有关的api几口地址模板，addAccount、updateAccount、delAccount、getAccountList、sendMessage等方法，uploadHeader未编写----wecustomservice.php。
