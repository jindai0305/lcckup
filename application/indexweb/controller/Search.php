<?php
namespace app\indexweb\controller;
use app\indexweb\controller\PublicInc;
use think\Db;
use think\Request;
class Search extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        $this->InCates();
        $this->GetTopBlog();
        $this->GetRandAss();
        return view('indexweb/search');
    }

    public function getIndexPage(){
        $kw = Request::instance()->param('kw');
        $page = Request::instance()->param('page');
        $page = intval($page);
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        $pagesize=config('pagedata.artpagesize');
        if ($page == 1) {
            $artData = $this->getList($page,$pagesize,$kw);
            $data    =[
                'artData'  => $artData['data'],
                'num'      => $artData['num'],
            ];
        }else{
            $pagesize = 3;
            $artData = $this->getList($page,$pagesize,$kw);
            $data = ['artData' => $artData['data']];
        }
        echo json_encode($data);
    }
}
