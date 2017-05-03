<?php
namespace app\indexweb\controller;
use think\Db;
class PublicInc
{
    use \traits\controller\Jump;
    // public function __construct()
    // {
        // config('template.view_path',ROOT_PATH.'template/');
        // parent::__construct();
    // }

    /**右侧小版块标签云*/
	public function InCates(){
		$where='state = 1';
		$limit='12';
		$order="id asc";
		$data=DB::table('bg_cate')->field('id,cateName')->where($where)->limit($limit)->order($order)->select();
		foreach($data as $k=>$v){
			$id=$v['id'];
			$BlogNum=DB::table('bg_detail')->where('cate ='.$id.' and type = 1 and isShow = 1')->count();
			$data[$k]['num'] = $BlogNum;
			$data[$k]['cateurl'] = '/?cate='.$id; 
		}
        \think\View::share('cates',$data);
	}
	
	/**首页博客列表 AJAX返回*/
    public function getList($page,$pagesize,$kw=""){
        $where="type=1 and isshow=1";
        if (!empty($kw)) {
        	$where.=" and title like '%".$kw."%'";
        }
        $order="isTop desc,id";
        $startIndex = $pagesize*($page-1);
        $tol=DB::name('detail')->where($where)->count();
        $dataList=DB::name('detail')->where($where)->order($order)->limit($startIndex,$pagesize)->select();
        $data=$this->DealArtData($dataList);
        $allData['data'] = $data;
        $allData['num']  = $tol;
        return $allData;
    } 
	
	/**右侧小版块 浏览排行*/
	public function GetTopBlog(){
		$where='isShow = 1 and type = 1';
		$order='liulan_num desc,id asc';
		$limit='8';
		$data=DB::table('bg_detail')->field('id,title,liulan_num')->where($where)->limit($limit)->order($order)->select();
		foreach ($data as $key => $value) {
			$data[$key]['infourl'] = '/Info/index/id/'.$value['id'];
		}
        \think\View::share('hotArray',$data);
	}
	
	/**右侧小版块 雁过留声*/
	public function GetRandAss(){
		$Model=DB::table('bg_pinlun');
		$num=$Model->count();
		$arr=array();
		$RandArray=array();
		if($num<=6){
			while($num>0){
				$arr[]=$num;
				$num--;
			}
		}else{
			$count=0;
			while($count < 6){
				$arr[]=mt_rand(1,$num);
				$arr=array_flip(array_flip($arr));
				$count=count($arr);
			}
		}
		if(!empty($arr)){
			foreach($arr as $key => $val){
				$data=DB::table('bg_pinlun')->field('uid,tid,content')->where('id = '.$val)->find();
				$uid=$data['uid'];
				if($uid){
					if ($uid==-1) {
						$data['imgurl'] = '/static/uploads/uface/ceshi.jpg';
					}else{
						$uInfo=DB::table('bg_youke')->where('id = '.$uid)->find();
						$data['imgurl'] = '/static/uploads/uface/'.$uInfo['uface'];
					}
				}else{
					$data['imgurl'] = '/static/uploads/uface/ceshi.jpg';
				}
				$RandArray[$key]=$data;
				$RandArray[$key]['infourl'] = '/Info/index/id/'.$data['tid'];
			}
		}
        \think\View::share('RandArray',$RandArray);
	}


	/**处理博客文章格式*/
	public function DealArtData($data,$kw=""){
		if(!empty($data)){
			if(count($data)==count($data,1)){//如果是一维数组
				$cId=$data['cate'];
				$cateName=DB::table('bg_cate')->where('id='.$cId)->field('catename')->find();
				if($cateName){
					$data['cateName']=$cateName['catename'];
				}else{
					$data['cateName']="";
				}
				if($data['keywords']){
					$data['keywords']=json_decode($data['keywords'],true);
				}
				$tId=$data['id'];
				$data['cateUrl'] = '/?cate='.$cId;
				$data['pinlunNum']=DB::table('bg_pinlun')->where('tid = '.$tId)->count();
				$data['pubtime']=date("Y-m-d H:i",$data['pubtime']);
				// \think\View::share('cate',$cateName['catename']);
				// \think\View::share('cid',$cId);
			}else{
				foreach($data as $key => $value){
					$cId=$value['cate'];
					$cateName=DB::table('bg_cate')->where('id='.$cId)->field('catename')->find();
					if($cateName){
						$data[$key]['cateName']=$cateName['catename'];
					}else{
						$data[$key]['cateName']="";
					}
					if($value['keywords']){
						$data[$key]['keywords']=json_decode($value['keywords'],true);
					}
					$tId=$value['id'];
					$data[$key]['pinlunNum']=DB::table('bg_pinlun')->where('tid = '.$tId)->count();
					$data[$key]['pubtime']=date("Y-m-d H:i",$value['pubtime']);
					$moreinfo=strip_tags($value['moreinfo']);
					if(!empty($kw)){$moreinfo=trimall($moreinfo);}
					if(mb_strwidth($moreinfo, 'utf8')>220){
						// 此处设定从0开始截取，取10个追加...，使用utf8编码
						// 注意追加的...也会被计算到长度之内
						$moreinfo = mb_strimwidth($moreinfo, 0, 220, '...', 'utf8');
					}
					if(!empty($kw)){
						$html = "<font style='color:red;'>{$kw}</font>";
						$moreinfo=str_ireplace($kw,$html,$moreinfo);
						$data[$key]['title']=str_ireplace($kw,$html,$value['title']);
					}
					if ($key == 0) {
						$data[$key]['class'] = "excerpt excerpt-first";
					}else if($key == count($data)-1){
						$data[$key]['class'] = "excerpt excerpt-end";
					}else{
						$data[$key]['class'] = "excerpt";
					}
					$data[$key]['moreinfo']=$moreinfo;
					$data[$key]['infourl']='/Info/index/id/'.$tId;
				}
			}
		}
		return $data;
	}

	public function getShortInfo($moreinfo){
		$moreinfo = trimall($moreinfo);
		if(mb_strwidth($moreinfo, 'utf8')>220){
			// 此处设定从0开始截取，取10个追加...，使用utf8编码
			// 注意追加的...也会被计算到长度之内
			$moreinfo = mb_strimwidth($moreinfo, 0, 220, '...', 'utf8');
		}
		return $moreinfo;
	}
	/**
	 *发送回复邮件
	 */
	public function send_message($email,$replyName,$oldmsg,$url){
		$mail = new \Common\Lib\Mail();
		return $mail->send_reply($email,$replyName,$oldmsg,$url);//发送邮件
	}

	/**
	 *判断用户名是否重复
	 */
	public function chechUserName(){
		$name=$_GET['uName'];
		$model=DB::table('youke');
		$res=array();
		$name=trimall($name);
		$res=$model->where("uname = '$name'")->find();
		if ($res) {
			// $res['success']=false;
			$res=1;
			echo $res;
			exit;
		}
	}
	//更新关键词
	public function createCheckWords(){
		$_path=SITE_DIR.'/Data';
	    $path=$_path.'/ViolationWords.json';
	    $newWords=C('VIOLATION_WORDS');
	    if (!is_dir($_path)){
	        mkdir($_path, 0777); // 使用最大权限0777创建文件
	    }
	    if (file_exists($path)){
		    file_put_contents($path,json_encode($newWords)) or die("error");
	        exit;
	    }else{
		    $aa = fopen($path, "w") or die("Unable to open file!");
		    fwrite($aa, json_encode($newWords)) or die("write error");
		    fclose($aa);
		    exit;
	    }
	}
}
