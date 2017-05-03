<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

	/**
	 * 得到文件扩展名
	 * @param string $filename
	 * @return string
	 */
	function getExt($filename){
		return strtolower(pathinfo($filename,PATHINFO_EXTENSION));
	}

	function trimall($str){
		$search = array("&mdash;","&nbsp;"," ","　","\n","\r","\t");
		$replace = array("","","","","","","");
		return str_replace($search, $replace, $str);
	}

	function trimnbsp($str){
		$search = array("&nbsp;"," ","\n");
		$replace = array("","","");
		return str_replace($search, $replace, $str);
	}
	function getShortInfo($moreinfo){
		$moreinfo = trimall($moreinfo);
		if(mb_strwidth($moreinfo, 'utf8')>220){
			// 此处设定从0开始截取，取10个追加...，使用utf8编码
			// 注意追加的...也会被计算到长度之内
			$moreinfo = mb_strimwidth($moreinfo, 0, 220, '...', 'utf8');
		}
		return $moreinfo;
	}
	function encrypt($string,$operation,$key=''){ 
		$key = 'lcckup';
	    $key=md5($key); 
	    $key_length=strlen($key); 
	    $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string; 
	    $string_length=strlen($string); 
	    $rndkey=$box=array(); 
	    $result=''; 
	    for($i=0;$i<=255;$i++){ 
	        $rndkey[$i]=ord($key[$i%$key_length]); 
	        $box[$i]=$i; 
	    } 
	    for($j=$i=0;$i<256;$i++){ 
	        $j=($j+$box[$i]+$rndkey[$i])%256; 
	        $tmp=$box[$i]; 
	        $box[$i]=$box[$j]; 
	        $box[$j]=$tmp; 
	    } 
	    for($a=$j=$i=0;$i<$string_length;$i++){ 
	        $a=($a+1)%256; 
	        $j=($j+$box[$a])%256; 
	        $tmp=$box[$a]; 
	        $box[$a]=$box[$j]; 
	        $box[$j]=$tmp; 
	        $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256])); 
	    } 
	    if($operation=='D'){ 
	        if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){ 
	            return substr($result,8); 
	        }else{ 
	            return''; 
	        } 
	    }else{ 
	        return str_replace('=','',base64_encode($result)); 
	    } 
	} 