<?php
namespace app\indexweb\controller;
use think\Request;
use think\Db;
class Msg
{
    use \traits\controller\Jump;
    /**留言页面首页*/
    public function index(){
        $page_size = config('pagedata.msgpagesize');
        $type      = Request::instance()->param('type');
        $type      = empty($type) ? $type:"";
        if(!empty($type)){
            $this->RetDiv($type,$id);//构建邮箱链接过来的前台js参数
        }else{//不是由邮件过来的
            \think\View::share('pageindex',1);
            \think\View::share('flag',0);
            \think\View::share('rootCmdId',0);
            \think\View::share('newId',0);
            \think\View::share('CmdId',0);
        }
        if(session('?user')){
            $login = true;
            $uInfo=session('user');
        }else{
            $login = false;
            $uInfo=array();
        }
        $num=DB::name('liuyan')->count();
        \think\View::share('num',$num);
        \think\View::share('login',$login);
        \think\View::share('uInfo',$uInfo);
        \think\View::share('pagesize',$page_size);
        return view('indexweb/msg');
    }

    /**获取所有留言 AJAX返回*/
    public function GetAllMsg(){
        $pagesize = Request::instance()->param('pageSize');
        $page     = Request::instance()->param('pageIndex');
        $pagesize = empty($pagesize) ? config('pagedata.msgpagesize') : intval($pagesize);
        $page     = empty($page) ? 1 : intval($page);
        if (!is_numeric($page)) {
            $page    = 1;
        }
        $pagesize    = ($pagesize < 1) ? 10 : $pagesize;
        $page        = ($page < 1) ? 1 : $page;
        $where       = '';
        $order       = 'pubtime asc';
        $tol         = DB::name('liuyan')->where($where)->count();
        $total_pages = ceil($tol / $pagesize);//获取总页数
        $page        = $page > $total_pages ? $total_pages : $page;//判断当前页码是否合法
        if ($page > 0) {
            $startIndex = ($page - 1) * $pagesize;
        }else{
            $startIndex = 0;
        }
        $Befdata     = DB::name('liuyan')->where($where)->order($order)->limit($startIndex,$pagesize)->select();
        if($tol == 0){
            $html      = "";
            $PageNavSt = "";
        }else{
            $PageNavStr  = $this->GetMsgPage($total_pages, $page,$pagesize);
            $data        = $this->DealMsg($Befdata,$page,$pagesize);
            $html        = '<ol class="commentlist">';
            foreach ($data as $key=>$value){
                $id      = $value['id'];
                $time    = $value['time'];
                $content = $value['content'];
                $uid     = $value['uid'];
                if ($uid == -1) {
                    $uInfo['uname'] = "博主";
                    $uInfo['uface'] = "ceshi.jpg";
                }else{
                    $uInfo = DB::name('youke')->where('id='.$uid)->field('uname,uface')->find();
                }
                $html .= $this->createHtml($id,$uInfo['uface'],$uInfo['uname'],$time,$content,0,$key);
                $res   = DB::name('liuyancomm')->where('lid = '.$id.' and level=1')->select();
                if($res){//若有二级回复
                    $html .= '<ol class="children">';
                    foreach($res as $k=>$v){
                        $id      = $v['id'];
                        $time    = date("Y-m-d H:i",$v['pubtime']);
                        $content = $v['content'];
                        $uid     = $v['uid'];
                        if ($uid == -1) {
                            $uInfo['uname'] = "博主";
                            $uInfo['uface'] = "ceshi.jpg";
                        }else{
                            $uInfo = DB::name('youke')->where('id='.$uid)->field('uname,uface')->find();
                        }
                        $html     .= $this->createHtml($id,$uInfo['uface'],$uInfo['uname'],$time,$content,1);
                        $SecHtml   = $this->GetOtherMsg($id);
                        if($SecHtml){
                            $html .= $SecHtml;
                        }
                        $html.= '</li>';
                    }
                    $html.= '</ol>';
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

    /**获取留言区分页*/
    private function GetMsgPage($total_pages,$page,$pagesize){
        $showpage = 3;
        $pageList = '<div class="pagination">';
        $pageList.='<ul>';
        $pageList.='<li id="start-page"><a href="/Msg/GetAllMsg/pageIndex/1/pageSize/'.$pagesize.'">首页</a></li>';
        if($page-1>0){
            $BefPage  = $page-1;
            $pageList.='<li class="prev-page"><a href="/Msg/GetAllMsg/pageIndex/'.$BefPage.'/pageSize/'.$pagesize.'">上一页</a></li>';
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
                $pageList.='<li class="show-page"><a href="/Msg/GetAllMsg/pageIndex/'.$i.'/pageSize/'.$pagesize.'">'.$i.'</a></li>';
            }
        }
        
        if($page+1<=$total_pages){
            $NexPage=$page+1;
            $pageList.='<li class="next-page"><a href="/Msg/GetAllMsg/pageIndex/'.$NexPage.'/pageSize/'.$pagesize.'">下一页</a></li></li>';
        }
        $pageList.='<li id="end-page"><a href="/Msg/GetAllMsg/pageIndex/'.$total_pages.'/pageSize/'.$pagesize.'">末页</a></li>';
        $pageList.='<li id="total-page"><span>共-'.$total_pages.'-页</span></li>';
        $pageList.='</ul>';
        $pageList.='</div>';
        return $pageList;
    }

        /**构建html**/
    private function createHtml($id,$uface,$uname,$time,$content,$type,$key=0){
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
        $html.='<b class="fn"><a href rel="external nofollow" class="floor">'.$uname.'&nbsp&nbsp&nbsp</a></b>';
        $html.='<a style="color:rgba(0, 39, 59, 0.35);" href="javascript:;"><time datatime="'.$time.'">'.$time.'&nbsp&nbsp&nbsp</time></a>';
        $html.='</div>';
        if($type==0){
            $html.='<div class="comment-metadata" style="color:#D2322D">#'.$key.'</div>';
        }
        $html.='</footer>';
        $html.='<div class="comment-content">';
        $html.='<p>'.$content.'</p></div>';
        $html.='<div class="reply">';
        if($type==0){
            $html.='<a class="comment-reply-link" href="javascript:void(0)" onclick="addComment('.$id.',1,0)">回复</a>';
        }else{
            $html.='<a class="comment-reply-link" href="javascript:void(0)" onclick="addComment('.$id.',2,1)">回复</a>';
        }
        $html.='</div>';
        $html.='</article>';
        return $html;
    }

        //利用递归获取所有下级留言回复
    private function GetOtherMsg($id){
        $html='';
        $data=DB::name('liuyancomm')->where('fid = '.$id.' and level !=1')->select();
        if($data){
            $html.='<ol class="children">';
            foreach($data as $key => $value){
                $id=$value['id'];
                $time=date("Y-m-d H:i",$value['pubtime']);
                $content=$value['content'];
                $uid=$value['uid'];
                if ($uid==-1) {
                    $uInfo['uname'] = "博主";
                    $uInfo['uface'] = "ceshi.jpg";
                }else{
                    $uInfo = DB::name('youke')->where('id='.$uid)->field('uname,uface')->find();
                }
                $html.=$this->createHtml($id,$uInfo['uface'],$uInfo['uname'],$time,$content,1);
                $SecHtml=$this->GetOtherMsg($id);
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

    /**处理评论格式*/
    private function DealMsg($data,$page,$pagesize){
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
