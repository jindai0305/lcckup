<?php
namespace app\admin\controller;
use app\admin\controller\PublicInc;
use think\Db;
use think\Request;
use app\admin\model\artModel;
class Art extends PublicInc
{
    use \traits\controller\Jump;
    public function index()
    {
        $this->getArtList();
        return view('admin/art');
    }

    public function picShow(){
        $type = 2;
        $role = 1;
        $this->getArtList($type,$role);
        return view('admin/picshow');
    }
    public function textShow(){
        $type = 1;
        $role = 1;
        $this->getArtList($type,$role);
        return view('admin/textshow');
    }

    public function getArtListByCate(){
        $cate_id = Request::instance()->param('cate');
        if (empty($cate_id) || !is_numeric(intval($cate_id))) {
            return view('404');
            exit;
        }
        $type = 1;
        $role = 1;
        $cate = $cate_id;
        $this->getArtList($type,$role,$cate);
        return view('admin/art');
    }

    public function SeeArtInfo(){
        $id = Request::instance()->param('id');
        if (empty($id)) {
            $cates =  DB::name('cate')->where('state = 1')->select();
            \think\View::share('cates',$cates);
            return view('admin/artadd');
        }else if(!is_numeric(intval($id))){
            return "无效页面";
            exit;
        }else{
            $Info = DB::name('detail')->where('id = '.$id)->find();
            if (!empty($Info['keywords'])) {
                $keywords = json_decode($Info['keywords']);
                $Info['keywords'] = implode(',', $keywords);
            }else{
                $Info['keywords'] = "";
            }
            $cates =  DB::name('cate')->where('state = 1')->select();
            \think\View::share('Info',$Info);
            \think\View::share('cates',$cates);
            return view('admin/artadd');
        }
    }

    public function editArt(){
        $data = Request::instance()->post();
        $new_data = array();
        if ($data['type'] == 2) {
            //图文信息
            if (isset($_FILES['myFile'])) {
                $uploadPath = './uploads/pic';
                $upload = new \custom\Upload($uploadPath);
                $picInfo = $upload->uploadFile();
                if ($picInfo['success']) {
                    $new_data['picture'] = $picInfo['picName'];
                }else{
                    if ($picInfo['error'] != "没有选择上传文件") {
                        $this->error($picInfo['error']);
                    }
                }
            }
        }
        if (!empty($data['keywords'])) {
            $keywords = explode(',', $data['keywords']);
            foreach ($keywords as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                $new_keywords[] = $value;
            }
            $new_data['keywords'] = json_encode($new_keywords);
        }
        $new_data['title']    = $data['title'];
        $new_data['cate']     = $data['cate'];
        $new_data['type']     = $data['type'];
        $new_data['moreinfo'] = $data['editorValue'];
        $new_data['uid']      = session('login.id');
        $art = new artModel;
        if (!empty($data['id'])) {
            $result = $art->isUpdate(true)->save($new_data,['id'=>$data['id']]);
            if ($result !== false) {
                $this->success('修改成功','/admin.php/Art');
            }else{
                $this->error('修改失败');
            }
        }else{
            $art->save($new_data);
            $result = $art->id;
            if ($result) {
                $this->success('发布成功','/admin.php/Art');
            }else{
                $this->error('发布失败');
            }
        }
    }

    private function getArtList($type = "",$role = "",$cate = ""){
        if (empty($type)) {
            if (Request::instance()->param('type')) {
                $type = Request::instance()->param('type');
            }else{
                $type = 1;
            }
        }
        \think\View::share('type',$type);
        $where = $this->getKeyWords();
	$where_new = array();
	if(isset($where['title'])){
	  $where_new['title'] = $where['title'];
	} 
        $where_new['type'] = array('EQ',$type);
        if (!empty($role)) {
            $where_new['isshow'] = array('EQ',1);
        }
        if (!empty($cate)) {
            $where_new['cate'] = array('EQ',$cate);
        }
        $page_size = config('admin_page.page_size');
        $list_counts = DB::name('detail')->where($where_new)->count();
        $page = new \custom\Page($list_counts,$page_size);
        $list = DB::name('detail')->where($where_new)->limit($page->firstRow,$page->listRows)->order('id asc')->select();
        $p = $page->show();
        $list = $this->dealArtList($list);
        \think\View::share('page',$p);
        \think\View::share('list',$list);
        \think\View::share('ct',$list_counts);
    }

    private function getKeyWords(){
        if (Request::instance()->param('keywords')) {
            $keywords = Request::instance()->param('keywords');
        }else{
            $keywords = "";
        }
	$where = array();
        if (!empty($keywords)) {
            $where['title'] = array('like','%'.$keywords.'%');
        }else{
            $where = '';
        }
        \think\View::share('keywords',$keywords);
        return $where;
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
