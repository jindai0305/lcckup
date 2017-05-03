<?php
namespace app\admin\controller;
use app\admin\controller\PublicInc;
use think\Db;
use think\Request;
use app\admin\model\cateModel;
class Cate extends PublicInc
{
    use \traits\controller\Jump;
    public function index()
    {
        $this->getCateList();
        return view('admin/cate');
    }

    public function addCate(){
        $cateName = Request::instance()->post('cateName');
        if (empty($cateName)) {
            echo json_encode(array('success'=>false,'msg'=>'Parameter is empty'));
            exit;
        }else{
            $new_cateName = trimall($cateName);
            $result = DB::name('cate')->where('cateName = '."'".$new_cateName."'")->find();
            if ($result) {
                echo json_encode(array('success'=>false,'msg'=>'已存在该分类名'));
                exit;
            }else{
                $uid = session('login.id');
                $cate = new cateModel;
                $cate->uid = $uid;
                $cate->cateName = $new_cateName;
                $cate->save();
                $new_id = $cate->id;
                if ($new_id) {
                    echo json_encode(array('success'=>true,'msg'=>'success instert'));
                }else{
                    echo json_encode(array('success'=>false,'msg'=>'mysql is error'));
                }
            }
        }
    }

    public function editCate(){
        $cateName = Request::instance()->post('cateName');
        $id = Request::instance()->post('id');
        if (empty($cateName) || empty($id)) {
            echo json_encode(array('success'=>false,'msg'=>'Parameter is empty'));
            exit;
        }else{
            $new_cateName = trimall($cateName);
            $result = DB::name('cate')->where('cateName = '."'".$new_cateName."'")->find();
            if ($result) {
                echo json_encode(array('success'=>false,'msg'=>'已存在该分类名'));
                exit;
            }else{
                $cate = new cateModel;
                $result = $cate->isUpdate(true)->save(['cateName'=>$new_cateName],['id'=>$id]);
                if ($result) {
                    echo json_encode(array('success'=>true,'msg'=>'success instert'));
                }else{
                    echo json_encode(array('success'=>false,'msg'=>'mysql is error'));
                }
            }
        }
    }

    private function getCateList(){
        $where = array('state'=>1);
        $page_size = config('admin_page.page_size');
        $list_counts = DB::name('cate')->where($where)->count();
        $page = new \custom\Page($list_counts,$page_size);
        $list = DB::name('cate')->where($where)->limit($page->firstRow,$page->listRows)->order('id desc')->select();
        $list = $this->dealCateList($list);
        $p = $page->show();
        \think\View::share('page',$p);
        \think\View::share('ct',$list_counts);
        \think\View::share('cates',$list);
    }

    private function dealCateList($data){
        $where = array();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $where['cate'] = array('EQ',$value['id']);
                $num = DB::name('detail')->where($where)->count();
                $data[$key]['num'] = $num;
                $data[$key]['createTime'] = empty($value['UpdataTime'])?$value['createTime']:$value['UpdataTime'];
            }
        }
        return $data;
    }

}
