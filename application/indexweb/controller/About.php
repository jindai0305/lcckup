<?php
namespace app\indexweb\controller;
use app\indexweb\controller\PublicInc;
use think\Db;
use think\Request;
class About extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        $this->GetRandAss();
        $this->getAboutMe();
        $this->getAboutMe();
        return view('indexweb/about');
    }
    protected function getAboutMe(){
        $where='isShow = 1';
        $limit='1';
        $order='id desc';
        $detail=DB::table('bg_myinfo')->field('moreinfo')->where('isShow=1')->order('id desc')->find();
        \think\View::share('Info',$detail['moreinfo']);
    }
}
