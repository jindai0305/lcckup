<?php
namespace app\indexweb\controller;
use app\indexweb\controller\PublicInc;
use think\Db;
use think\Request;
class Index extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        $this->getTopPic();
        $this->InCates();
        $this->GetTopBlog();
        $this->GetRandAss();
        return view('indexweb/index');
    }

    public function getIndexPage(){
        $page = Request::instance()->param('page');
        $page = intval($page);
        $pagesize=config('pagedata.indexpagesize');
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        if ($page == 1) {
            $artData = $this->getList($page,$pagesize);
            $data    =[
                'artData' => $artData['data'],
            ];
        }else{
            $pagesize = 3;
            $artData = $this->getList($page,$pagesize);
            $data = ['artData' => $artData['data']];
        }
        echo json_encode($data);
    }

    /**首页流动图片 AJAX返回*/
    public function getTopPic(){
        $where='type=2 and isshow=1';
        $limit='4';
        $order='isTop desc,id';
        $dataList=DB::table('bg_detail')->field('picture,title,moreinfo')->where($where)->order($order)->limit($limit)->select();
        foreach ($dataList as $key => $value) {
            if ($key == 0) {
                $dataList[$key]['class1'] = 'active';
                $dataList[$key]['class2'] = 'item active';
            }else{
                $dataList[$key]['class1'] = '';
                $dataList[$key]['class2'] = 'item';
            }
            $dataList[$key]['picture'] = '/static/uploads/pic/'.$value['picture'];
            $dataList[$key]['alt']     = ($key+1).'  slide';
        }
        \think\View::share('PicData',$dataList);
    }
}
