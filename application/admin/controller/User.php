<?php
namespace app\admin\controller;
use app\admin\controller\PublicInc;
use think\Db;
use think\Request;
class User extends PublicInc
{
    use \traits\controller\Jump;
    public function index()
    {
        $this->getUserList();
        return view('admin/user');
    }

    private function getUserList(){
        $where = $this->getKeyWords();
        $page_size = config('admin_page.page_size');
        $list_counts = DB::name('youke')->where($where)->count();
        $page = new \custom\Page($list_counts,$page_size);
        $list = DB::name('youke')->where($where)->limit($page->firstRow,$page->listRows)->order('id desc')->select();
        $list = $this->dealUserList($list);
        $p = $page->show();
        \think\View::share('page',$p);
        \think\View::share('ct',$list_counts);
        \think\View::share('list',$list);
    }

    private function getKeyWords(){
        if (Request::instance()->param('keywords')) {
            $keywords = Request::instance()->param('keywords');
        }else{
            $keywords = "";
        }
        if (!empty($keywords)) {
            $where['uname'] = array('like','%'.$keywords.'%');
        }else{
            $where = '';
        }
        \think\View::share('keywords',$keywords);
        return $where;
    }

    private function dealUserList($data){
        $where = array();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $data[$key]['pubtime'] = empty($value['reptime'])?$value['pubtime']:$value['reptime'];
            }
        }
        return $data;
    }

}
