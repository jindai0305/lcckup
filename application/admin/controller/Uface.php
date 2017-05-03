<?php
namespace app\admin\controller;
use app\admin\controller\PublicInc;
use think\Db;
use think\Request;
class Uface extends PublicInc
{
    use \traits\controller\Jump;
    public function index()
    {
        $this->getUfaceList();
        return view('admin/uface');
    }

    private function getUfaceList(){
        $where = "";
        $list = DB::name('uface')->where($where)->order('id desc')->select();
        $list_counts = count($list);
        \think\View::share('ct',$list_counts);
        \think\View::share('list',$list);
    }

}
