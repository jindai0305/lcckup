<?php
namespace app\admin\controller;
use app\admin\controller\PublicInc;
use think\Db;
use think\Request;
use app\admin\model\aboutModel;
class About extends PublicInc
{
    use \traits\controller\Jump;
    public function index()
    {
        $this->getAboutList();
        return view('admin/about');
    }

    public function SeeAboutMe(){
        $id = Request::instance()->param('id');
        if (empty($id)) {
            return view('admin/aboutadd');
        }else if(!is_numeric(intval($id))){
            return "无效页面";
            exit;
        }else{
            $Info = DB::name('myinfo')->where('id = '.$id)->find();
            \think\View::share('Info',$Info);
            return view('admin/aboutadd');
        }
    }

    public function aboutEdit(){
        $data = Request::instance()->post();
        $new_data = array();
        $new_data['moreinfo'] = $data['editorValue'];
        $about = new aboutModel;
        if (!empty($data['id'])) {
            $result = $about->save($new_data,['id'=>$data['id']]);
            if ($result !== false) {
                $this->success('修改成功','/admin.php/About');
            }else{
                $this->error('修改失败');
            }
        }else{
            $about->save();
            $result = $about->id;
            if ($result) {
                $this->success('发布成功','/admin.php/About');
            }else{
                $this->error('发布失败');
            }
        }
    }

    private function getAboutList(){
        $where = array();
        $page_size = config('admin_page.page_size');
        $list_counts = DB::name('myinfo')->where($where)->count();
        $page = new \custom\Page($list_counts,$page_size);
        $list = DB::name('myinfo')->where($where)->limit($page->firstRow,$page->listRows)->order('id asc')->select();
        $p = $page->show();
        $list = $this->dealAboutList($list);
        \think\View::share('page',$p);
        \think\View::share('list',$list);
        \think\View::share('ct',$list_counts);
    }

    private function dealAboutList($data){
        $where = array();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $data[$key]['moreinfo'] =getShortInfo(strip_tags($value['moreinfo']));
            }
        }
        return $data;
    }
}
