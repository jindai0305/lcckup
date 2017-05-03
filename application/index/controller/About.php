<?php
namespace app\index\controller;
use app\index\controller\PublicInc;
use think\Db;
use think\Request;
class About extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        return view('index/about');
    }

    public function getIndexPage(){
        $yglsData  = $this->GetRandAss();
        $aboutData = $this->getAboutMe();
        $data    =[
            'yglsData'  => $yglsData,
            'aboutData' => $aboutData
        ];
        echo json_encode($data);
    }

    protected function getAboutMe(){
        $where='isShow = 1';
        $limit='1';
        $order='id desc';
        $detail=DB::table('bg_myinfo')->field('moreinfo')->where('isShow=1')->order('id desc')->find();
        return $detail['moreinfo'];
    }
}
