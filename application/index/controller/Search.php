<?php
namespace app\index\controller;
use app\index\controller\PublicInc;
use think\Db;
use think\Request;
class Search extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        return view('index/search');
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
            $cateData=$this->InCates();
            $deilData=$this->GetTopBlog();
            $yglsData=$this->GetRandAss();
            $data    =[
                'artData'  => $artData['data'],
                'num'      => $artData['num'],
                'cateData' => $cateData,
                'deilData' => $deilData,
                'yglsData' => $yglsData
            ];
        }else{
            $artData = $this->getList($page,$pagesize,$kw);
            $data = ['artData' => $artData['data']];
        }
        echo json_encode($data);
    }
}
