<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use app\admin\model\loginInfo;
use app\admin\model\adminUser;
class Login
{
    use \traits\controller\Jump;
    public function index()
    {
        return view('admin/login');
    }
    public function loginOut(){
    	session('login',null);
    	$this->redirect('/');
    }

    public function chechLogin(){
    	if (Request::instance()->post()){
    		$userName = Request::instance()->post('username');
    		$pwd      = Request::instance()->post('password');
			$pwd      =md5(md5($pwd).'pass');
			$result=DB::table('bg_adminuser')->where("name='".$userName."'")->find();
			if ($result) {
				if ($result['pass'] === $pwd) {
					unset($result['pass']);
					session('login',$result);
					$this->add_login_num(1,$result['id'],'');
					return (['status'=>1]);
				}else{
					$this->add_login_num(2,$result['id'],'密码错误');
					return (['status'=>0,'msg'=>'密码错误']);
				}
			}else{
				return (['status'=>0,'msg'=>'用户名不存在']);
			}
    	}else{
    		return view('admin/login');
    	}		
    }

    private function add_login_num($state=1,$uid,$info=''){
    	$request = Request::instance()->ip();
    	$user_ip = $request;
    	$login = new loginInfo();
    	$login->uid        = $uid;
    	$login->state      = $state;
    	$login->login_ip   = $user_ip;
    	$login->reason     = $info;
    	$login->save();
		if ($state == 1) {
			$adminUser = new adminUser();
			$data      = [
				'lasttime' => time(),
				'lastip'   => $user_ip,
				'counts'   =>['exp','counts+1']
			];
			$adminUser->save($data,['id'=>$uid]);
		}
	}

	public function loginLog(){
		$uid = session('login.id');
		$page_size = config('admin_page.page_size');
		// $login = new loginInfo();
		$where = array('uid'=>$uid);
		// $login_list = $login->where($where)->select();
		$list_counts = DB::name('logininfo')->where($where)->count();
		$page = new \custom\Page($list_counts,$page_size);
		$login_list = DB::name('logininfo')->where($where)->limit($page->firstRow,$page->listRows)->order('id desc')->select();
		$p = $page->show();
		\think\View::share('page',$p);
		\think\View::share('list',$login_list);
		\think\View::share('ct',$list_counts);
		return view('admin/loginlog');
	}
}
