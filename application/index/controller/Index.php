<?php
namespace app\index\controller;
use app\index\controller\PublicInc;
use think\Db;
use think\Request;
class Index extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        return view('index/index');
    }

    public function getIndexPage(){
        $page = Request::instance()->param('page');
        $page = intval($page);
        $pagesize=config('pagedata.indexpagesize');
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        if ($page == 1) {
            $picData = $this->getTopPic();
            $artData = $this->getList($page,$pagesize);
            $cateData= $this->InCates();
            $deilData= $this->GetTopBlog();
            $yglsData= $this->GetRandAss();
            $data    =[
                'picData' => $picData,
                'artData' => $artData['data'],
                'cateData' => $cateData,
                'deilData' => $deilData,
                'yglsData' => $yglsData
            ];
        }else{
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
        return $dataList;
    }
}
