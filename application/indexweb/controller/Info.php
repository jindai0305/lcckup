<?php
namespace app\indexweb\controller;
use app\indexweb\controller\PublicInc;
use think\Db;
use think\Request;
class Info extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        $page_size = config('pagedata.msgpagesize');
        $id        = Request::instance()->param('id');
        $type      = Request::instance()->param('type');
        \think\View::share('pagesize',$page_size);
        $id        = empty($id)   ? 1  : intval($id);
        $type      = empty($type) ? "" : $type;
        if(!empty($type)){
            $this->RetDiv($type,$id);//构建邮箱链接过来的前台js参数
        }else{//不是由邮件过来的
            \think\View::share('pageindex',1);
            \think\View::share('flag',0);
            \think\View::share('rootCmdId',0);
            \think\View::share('newId',0);
            \think\View::share('CmdId',0);
        }
        if( $id < 1){
            $this->errorPg();
        }else{
            $data=DB::name('detail')->where('id = '.$id)->find();
            $this->InCates();
            $this->GetRandAss();
            $this->GetTopBlog();
            if($data){
                if($data['isshow'] == 1){
                    DB::name('detail')->where('id = '.$id)->setInc('liulan_num', 1);
                    $data = $this->DealArtData($data);
                    if(!session('?user')){
                        $isWrite=1;
                        $uInfo="";
                    }else{
                        $isWrite=0;
                        $uInfo=session('user');
                    }
                    $nums=DB::name('pinlun')->where('tid='.$id)->count();
                    $host = "http://".$_SERVER['HTTP_HOST'];
                    $url =$host.$_SERVER['PHP_SELF'];
                    $shortMsg = $this->getShortInfo($data['moreinfo']);//分享的简述
                    $NewId = $this->GetNextId($id);
                    $data['url']      = $url;
                    $data['nexturl']  = $url;
                    $data['showMsg'] = $shortMsg;
                    $data['msgNum'] = $nums;
                    \think\View::share('Info',$data);
                    \think\View::share('uInfo',$uInfo);
                }else{
                    $this->errorPg();
                }
            }else{
                $this->errorPg();
            }
        }
        return view('indexweb/artinfo');
    }

    public function GetNextId($id){
        $where['id'] = array('GT',$id);
        $where['type'] = array('EQ',1);
        $where['isshow'] = array('EQ',1);
        $NewId = DB::name('detail')->field('id')->where($where)->find();
        if ($NewId) {
            return $NewId['id'];
        }else{
            return null;
        }
    }

    /**获取当前博文的所有评论**/
    public function GetAllAssById($fid){
        if(!empty($fid)){
            $where['fid'] = array('EQ',$fid);
            $result=DB::name('pinluncomm')->where($where)->select();
            $arr=array();
            if($result){
                foreach($result as $key => $value){
                    $value['list']=$this->GetAllAssById($value['id']);
                    $arr[]=$value;
                }
                return $arr;
            }
        }else{
            return array();
        }
    }

    /**获取评论列表*/
    public function CommList(){
        $tId          = Request::instance()->param('id');
        $tId          = empty($tId) ? 1 : intval($tId);
        $pagesize     = Request::instance()->param('pageSize');
        $pagesize     = empty($pagesize) ? 5 : intval($pagesize);
        $page         = Request::instance()->param('pageIndex');
        $page         = empty($page) ? 1 : intval($page);
        $tId          = ($tId<1)  ? 1 : $tId;
        $page         = ($page<1) ? 1 : $page;
        $where['tid'] = array('EQ',$tId);
        $order='top desc,pubtime asc';
        $tol          = DB::name('pinlun')->where($where)->count();
        $total_pages  = ceil($tol / $pagesize);//获取总页数
        $page = $page > $total_pages ? $total_pages : $page;//判断当前页码是否合法
        if($page > 0){
            $startIndex = ($page - 1) * $pagesize;
        }else{
            $startIndex = 0;
        }
        $Befdata     =  DB::name('pinlun')->where($where)->order($order)->limit($startIndex,$pagesize)->select();
        if($tol == 0){
            $html = "";
            $PageNavStr = "";
        }else{
            $PageNavStr = $this->GetCommPage($total_pages, $page,$tId,$pagesize);
            $data       = $this->DealComm($Befdata,$page,$pagesize);
            $html='<ol class="commentlist">';
            foreach ($data as $key=>$value){
                $id      = $value['id'];
                $time    = $value['time'];
                $content = $value['content'];
                $uid     = $value['uid'];
                $tid     = $value['tid'];
                if ($uid == -1) {
                    $uInfo['uname'] = "博主";
                    $uInfo['uface'] = "ceshi.jpg";
                }else{
                    $uInfo = DB::name('youke')->where('id='.$uid)->field('uname,uface')->find();
                }
                $html .= $this->createHtml($id,$tid,$uInfo['uface'],$uInfo['uname'],$time,$content,0,$key);
                $res   = DB::name('pinluncomm')->where('pid = '.$id.' and level=1')->select();
                if($res){//若有二级回复
                    $html.='<ol class="children">';
                    foreach($res as $k=>$v){
                        $id      = $v['id'];
                        $time    = date("Y-m-d H:i",$v['pubtime']);
                        $content = $v['content'];
                        $uid     = $v['uid'];
                        $tid     = $v['tid'];
                        if ($uid == -1) {
                            $uInfo['uname'] = "博主";
                            $uInfo['uface'] = "ceshi.jpg";
                        }else{
                            $uInfo = DB::name('youke')->where('id='.$uid)->field('uname,uface')->find();
                        }
                        $html     .= $this->createHtml($id,$tid,$uInfo['uface'],$uInfo['uname'],$time,$content,1);
                        $SecHtml   = $this->GetOtherAss($id);
                        if($SecHtml){
                            $html .= $SecHtml;
                        }
                        $html     .= '</li>';
                    }
                    $html.='</ol>';
                }
                $html.='</li>';
            }
            $html.='</ol>';
        }
        $result['success']    = true;
        $result['CoreData']   = $html;
        $result['PageNavStr'] = $PageNavStr;
        echo json_encode($result);
        exit;
    }

    /**构建html**/
    private function createHtml($id,$tid,$uface,$uname,$time,$content,$type,$key=0){
        if($type==0){
            $html='<li id="comment-'.$id.'" class="comment even thread-even depth-1 parent">';
            $html.='<article id="div-comment-'.$id.'" class="comment-body">';
        }else{
            $html='<li id="second-comment-'.$id.'" class="comment even thread-even depth-1 parent">';
            $html.='<article id="sdiv-comment-'.$id.'" class="comment-body">';
        }
        
        $html.='<footer class="comment-meta">';
        $html.='<div class="comment-author vcard">';
        $html.='<img src="/uploads/uface/'.$uface.'" class="avatar avatar-70 photo" height="60" width="60">';
        $html.='<b class="fn"><a href rel="external nofollow" class="floor">'.$uname.'</a></b>';
        $html.='<span class="says">say:</span>';
        $html.='</div>';
        $html.='<div class="comment-metadata">';
        $html.='<a href><time datatime="'.$time.'">'.$time.'&nbsp&nbsp&nbsp';
        if($type==0){
            $html.='<font color="#D2322D">'.$key.'楼</font>';
        }
        $html.='</time></a></div></footer>';
        $html.='<div class="comment-content">';
        $html.='<p>'.$content.'</p></div>';
        $html.='<div class="reply">';
        if($type==0){
            $html.='<a class="comment-reply-link" href="javascript:void(0)" onclick="addComment('.$id.','.$tid.',1,0)">回复</a>';
        }else{
            $html.='<a class="comment-reply-link" href="javascript:void(0)" onclick="addComment('.$id.','.$tid.',2,1)">回复</a>';
        }
        $html.='</div>';
        $html.='</article>';
        return $html;
    }

    //利用递归获取所有下级评论
    private function GetOtherAss($id){
        $html='';
        $data=DB::name('pinluncomm')->where('fid = '.$id.' and level !=1')->select();
        if($data){
            $html.='<ol class="children">';
            foreach($data as $key => $value){
                $id=$value['id'];
                $time=date("Y-m-d H:i",$value['pubtime']);
                $content=$value['content'];
                $uid=$value['uid'];
                $tid=$value['tid'];
                if ($uid==-1) {
                    $uInfo['uname']="博主";
                    $uInfo['uface']="ceshi.jpg";
                }else{
                    $uInfo = DB::name('youke')->where('id='.$uid)->field('uname,uface')->find();
                }
                $html.=$this->createHtml($id,$tid,$uInfo['uface'],$uInfo['uname'],$time,$content,1);
                $SecHtml=$this->GetOtherAss($id);
                if($SecHtml){
                    $html.=$SecHtml;
                }
                $html.='</li>';
            }
            $html.='</ol>';
            return $html;
        }else{//没有下级评论
            return false;
        }
    }

    /**获取博文评论分页*/
    private function GetCommPage($total_pages,$page,$tid,$pagesize){
        $showpage=3;
        $pageList='<div class="pagination">';
        $pageList.='<ul>';
        $pageList.='<li id="start-page"><a href="/Info/CommList/id/'.$tid.'/pageIndex/1/pageSize/'.$pagesize.'">首页</a></li>';
        if($page-1>0){
            $BefPage=$page-1;
            $pageList.='<li class="prev-page"><a href="/Info/CommList/id/'.$tid.'/pageIndex/'.$BefPage.'/pageSize/'.$pagesize.'">上一页</a></li>';
        }
        $pageoffset = ($showpage - 1) / 2;
        /*if ($total_pages > $showpage) {
         if ($page > $pageoffset + 1) {
         $pageList.= "...";
         }
         }*/
        for($i=1;$i<=$total_pages;$i++){
            if($i==$page){
                $pageList.='<li class="active show-page"><span>'.$i.'</span></li>';
            }else{
                $pageList.='<li class="show-page"><a href="/Info/CommList/id/'.$tid.'/pageIndex/'.$i.'/pageSize/'.$pagesize.'">'.$i.'</a></li>';
            }
        }
        
        if($page+1<=$total_pages){
            $NexPage=$page+1;
            $pageList.='<li class="next-page"><a href="/Info/CommList/id/'.$tid.'/pageIndex/'.$NexPage.'/pageSize/'.$pagesize.'">下一页</a></li></li>';
        }
        $pageList.='<li id="end-page"><a href="/Info/CommList/id/'.$tid.'/pageIndex/'.$total_pages.'/pageSize/'.$pagesize.'">末页</a></li>';
        $pageList.='<li id="total-page"><span>共-'.$total_pages.'-页</span></li>';
        $pageList.='</ul>';
        $pageList.='</div>';
        return $pageList;
    }

    /**处理评论格式*/
    private function DealComm($data,$page,$pagesize){
        if (empty($data)){
            return array();
        }else{
            foreach ($data as $k=>$v){
                $data[$k]['time']=date("Y-m-d H:i",$v['pubtime']);
            }
            $endData=array();
            if ($page==1){
                $start=1;
            }else{
                $start=($page-1)*$pagesize+1;
            }
            foreach ($data as $key=>$value){
                $endData[$start]=$value;
                $start++;
            }
        }
        return $endData;
    }
    
}
