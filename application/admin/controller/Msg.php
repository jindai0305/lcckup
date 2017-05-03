<?php
namespace app\admin\controller;
use app\admin\controller\PublicInc;
use think\Db;
use think\Request;
class Art extends PublicInc
{
    use \traits\controller\Jump;
    public function index()
    {
        $this->getArtList();
        return view('admin/art');
    }

    private function getArtList(){
        if (Request::instance()->param('type')) {
            $type = Request::instance()->param('type');
        }else{
            $type = 1;
        }
        \think\View::share('type',$type);
        $where = array(
            'type' => $type,
        );
        $page_size = config('admin_page.page_size');
        $list_counts = DB::name('detail')->where($where)->count();
        $page = new \custom\Page($list_counts,$page_size);
        $list = DB::name('detail')->where($where)->limit($page->firstRow,$page->listRows)->order('id asc')->select();
        $p = $page->show();
        $list = $this->dealArtList($list);
        \think\View::share('page',$p);
        \think\View::share('list',$list);
        \think\View::share('ct',$list_counts);
    }

    private function dealArtList($data){
        $where = array();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $where['id'] = array('EQ',$value['uid']);
                $user_info = DB::name('adminuser')->where($where)->field('truename')->find();
                $data[$key]['truename'] = $user_info['truename'];
            }
        }
        return $data;
    }
    
}
