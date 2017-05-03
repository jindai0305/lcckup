<?php
namespace app\indexweb\controller;
use app\indexweb\controller\PublicInc;
use think\Db;
use think\Request;
class Art extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        $this->InCates();
        $this->GetTopBlog();
        $this->GetRandAss();
        $this->getData();
        return view('indexweb/artlist');
    }

    private function getData(){
        $page = Request::instance()->param('p');
        $page = intval($page);
        if (empty($page) || !is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        $pagesize=config('pagedata.artpagesize');
        $artData = $this->getList($page,$pagesize);

        $total_pages =ceil($artData['num']/$pagesize);

        $page = $this->GetPageList($total_pages,$page);
        \think\View::share('artData',$artData);
        \think\View::share('page',$page);
    }

    /**获取博客分页表*/
    private function GetPageList($total_pages,$page){
        $showpage=3;
        $pageList='';
        $pageList.='<li id="start-page"><a href="/Art/index/p/1.html">首页</a></li>';
        if($page-1>0){
            $BefPage=$page-1;
            $pageList.='<li class="prev-page"><a href="/Art/index/p/'.$BefPage.'.html">上一页</a></li>';
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
                $pageList.='<li class="show-page"><a href="/Art/index/p/'.$i.'.html">'.$i.'</a></li>';
            }
        }
        if($page+1<=$total_pages){
            $NexPage=$page+1;
            $pageList.='<li class="next-page"><a href="/Art/index/p/'.$NexPage.'.html">下一页</a></li></li>';
        }
        $pageList.='<li id="end-page"><a href="/Art/index/p/'.$total_pages.'.html">末页</a></li>';
        $pageList.='<li id="total-page"><span>共-'.$total_pages.'-页</span></li>';
        return $pageList;
    }

}
