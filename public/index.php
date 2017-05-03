<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
if(!isset($_SESSION)){ session_start(); }
// [ 应用入口文件 ]
	function is_mobile_request(){
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';  
        $mobile_browser = '0';  
        if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))  
            {$mobile_browser++; }
        if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))  
            {$mobile_browser++;}  
        if(isset($_SERVER['HTTP_X_WAP_PROFILE']))  
        {$mobile_browser++;} 
        if(isset($_SERVER['HTTP_PROFILE']))  
        {$mobile_browser++;}  
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));  
        $mobile_agents = array(  
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
        'wapr','webc','winw','winw','xda','xda-'
        );  
        if(in_array($mobile_ua, $mobile_agents))  
        {$mobile_browser++; } 
        if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)  
        {$mobile_browser++; } 
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)  
        {$mobile_browser=0;}  
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)  
        {$mobile_browser++;}  
        if($mobile_browser>0){  
            return true;  
        }else{
            return false;
        }
    }
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
//定义文件根目录
define('ROOT_PATH',__DIR__ . '/../');
//定义默认模块
if (!isset($_SESSION['ISPHONE'])) {
	if(is_mobile_request()){
		$_SESSION['ISPHONE'] = true;
		define('BIND_MODULE','index');
	}else{
		$_SESSION['ISPHONE'] = false;
		define('BIND_MODULE','indexweb');
	}
}else{
	if ($_SESSION['ISPHONE']) {
		define('BIND_MODULE','index');
	}else{
		define('BIND_MODULE','indexweb');
	}
}

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
