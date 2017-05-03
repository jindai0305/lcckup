<?php
namespace app\admin\controller;
use app\admin\controller\PublicInc;
use think\Db;
// use think\Request;
class Index extends PublicInc
{
    use \traits\controller\Jump;
    public function index()
    {
        return view('admin/index');
    }
    public function header(){
        $uid = session('login.id');
        $user = DB::table('bg_adminuser')->where("id = ".$uid)->find();
        \think\View::share('user',$user);
        return view('admin/Inc/header');
    }
    /** 动态生成导航树菜单 */
    public function menu(){
        $menulist=DB::table('bg_menu')->where("static=1 and fid=0")->order("id asc")->select();
        $menucount=count($menulist);
        for ($i = 0; $i< $menucount; $i++) {
            $mid=$menulist[$i]['id'];
            $seclist=DB::table('bg_menu')->where("fid=$mid and static=1")->order("sorts asc,id asc")->select();
            $menulist[$i]['seclist']=$seclist;
        }
        \think\View::share('menulist',$menulist);
        return view('admin/Inc/menu');
    }
    /** 当前时间显示 */
    public function main() {
        date_default_timezone_set('PRC');
        $nowtime=date("Y-m-d H:i:s");
        \think\View::share('nowtime',$nowtime);
        $uid = session('login.id');
        $type = session('login.type');
        $user = DB::table('bg_adminuser')->where("id = ".$uid)->find();
        $user['lasttime']=date("Y-m-d H:i:s",$user['lasttime']);
        \think\View::share('user',$user);
        return view('admin/Inc/main');
    }
}
