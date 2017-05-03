<?php
namespace app\index\controller;
use app\index\controller\PublicInc;
use think\Db;
use think\Request;
class Info extends PublicInc {
    use \traits\controller\Jump;
    public function index()
    {
        $id = Request::instance()->param('id');
        \think\View::share('id',$id);
        return view('index/artinfo');
    }

    public function getIndexPage(){
        $page = Request::instance()->param('page');
        $id = Request::instance()->param('id');
        $id   = intval($id);
        $page = intval($page);
        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }
        if ($page == 1) {
            $artInfo     = $this->getInfo($id);
            $commentData = $this->getCommentList($id,$page);
            $cateData=$this->InCates();
            $deilData=$this->GetTopBlog();
            $yglsData=$this->GetRandAss();
            $data    =[
                'artInfo'     => $artInfo,
                'commentData' => $commentData['data'],
                'cateData' => $cateData,
                'deilData' => $deilData,
                'yglsData' => $yglsData
            ];
        }else{
            $commentData = $this->getCommentList($id,$page);
            $data = ['commentData' => $commentData['data']];
        }
        echo json_encode($data);
    }

    private function getInfo($id){
        if(!is_numeric($id) || $id < 1){
            $data['code'] = "false";
        }else{
            $data=DB::table('bg_detail')->where('id = '.$id.' and isshow = 1')->find();
            if($data){
                DB::table('bg_detail')->where('id = '.$id)->setInc('liulan_num', 1);
                $data=$this->DealArtData($data);
                $num=DB::table('bg_pinlun')->where('tid='.$id)->count();
                $data['msgNum'] = $num;
                $data['code']   = true;
                $nextInfo = DB::table('bg_detail')->where('id >'.$id.' and isshow = 1')->field('id')->find();
                if ($nextInfo) {
                    $data['next'] = true;
                    $data['nexturl'] = '/Info/Index/id/'.$nextInfo['id'];
                }else{
                    $data['next'] = false;
                }
            }else{
                $data['code']   = "false"; 
            }
        }
        return $data;
    }

    private function getCommentList($id,$page){
        $pagesize=config('pagedata.commentpagesize');
        $Model=DB::table('bg_pinlun');
        $where='tid='.$id;
        $order='top desc,pubtime asc';
        $tol=$Model->where($where)->count();
        $startIndex = $pagesize*($page-1);
        $Befdata=DB::table('bg_pinlun')->where($where)->order($order)->limit($startIndex,$pagesize)->select();
        if(empty($Befdata)){
            $data = array();
        }else{
            $data=$this->DealComm($Befdata);
            $showKey = $startIndex;
            foreach ($data as $key=>$value){
                $showKey++;
                $id=$value['id'];
                $data[$key]['time']=$value['time'];
                $data[$key]['content']=$value['content'];
                $data[$key]['fid'] = '-1';
                $uid=$value['uid'];
                $tid=$value['tid'];
                if ($uid == -1) {
                    $data[$key]['imgurl'] = '/static/uploads/uface/ceshi.jpg';
                    $data[$key]['uname'] = '博主';
                }else{
                    $uInfo=DB::table('bg_youke')->where('id='.$uid)->field('uname,uface')->find();
                    $data[$key]['imgurl'] = '/static/uploads/uface/'.$uInfo['uface'];
                    $data[$key]['uname'] = $uInfo['uname'];
                }
                $data[$key]['liId'] = "comment-".$id;
                $data[$key]['divId'] = "div-comment-".$id;
                $data[$key]['key'] = $showKey."楼";
                $data[$key]['iskey'] = true;
                $data[$key]['click'] = 'addComment('.$id.','.$tid.',1,0)';
                $res=DB::table('bg_pinluncomm')->where('pid = '.$id.' and level=1')->select();
                if($res){//若有二级回复
                    foreach($res as $k=>$v){
                        $id=$v['id'];
                        $res[$k]['time']=date("Y-m-d H:i",$v['pubtime']);
                        $res[$k]['content']=$v['content'];
                        $uid=$v['uid'];
                        $tid=$v['tid'];
                        if ($uid == -1) {
                            $res[$k]['imgurl'] = '/static/uploads/uface/ceshi.jpg';
                            $res[$k]['uname'] = '博主';
                        }else{
                            $uInfo=DB::table('bg_youke')->where('id='.$uid)->field('uname,uface')->find();
                            $res[$k]['imgurl'] = '/static/uploads/uface/'.$uInfo['uface'];
                            $res[$k]['uname'] = $uInfo['uname'];
                        }
                        $res[$k]['liId'] = "second-comment--".$id;
                        $res[$k]['divId'] = "sdiv-comment-".$id;
                        $res[$k]['fid'] = $v['fid'];
                        $res[$k]['iskey'] = false;
                        $res[$k]['click'] = 'addComment('.$id.','.$tid.',2,1)';
                        $secData=$this->GetOtherAss($id);
                        if($secData){
                            $res[$k]['laterData'] = $secData;
                            $res[$k]['isData'] = true;
                        }else{
                            $res[$k]['laterData'] = array();
                            $res[$k]['isData'] = false;
                        }
                    }
                    $data[$key]['laterData'] = $res;
                    $data[$key]['isData'] = true;
                }else{
                    $data[$key]['laterData'] = array();
                    $data[$key]['isData'] = false;
                }
            }
        }
        $allData['data'] = $data;
        $allData['num']  = $tol;
        return $allData;
    }


    private function DealComm($data){
        if (!empty($data)){
            foreach ($data as $k=>$v){
                $data[$k]['time']=date("Y-m-d H:i",$v['pubtime']);
            }
        }
        return $data;
    }

    //利用递归获取所有下级留言回复
    private function GetOtherAss($id){
        $data=DB::table('bg_pinluncomm')->where('fid = '.$id.' and level !=1')->select();
        if($data){
            foreach($data as $key => $value){
                $id=$value['id'];
                $data[$key]['time']=date("Y-m-d H:i",$value['pubtime']);
                $data[$key]['content']=$value['content'];
                $uid=$value['uid'];
                $tid=$value['tid'];
                if ($uid == -1) {
                    $data[$key]['imgurl'] = '/static/uploads/uface/ceshi.jpg';
                    $data[$key]['uname'] = '博主';
                }else{
                    $uInfo=DB::table('bg_youke')->where('id='.$uid)->field('uname,uface')->find();
                    $data[$key]['imgurl'] = '/static/uploads/uface/'.$uInfo['uface'];
                    $data[$key]['uname'] = $uInfo['uname'];
                }
                $data[$key]['liId'] = "second-comment--".$id;
                $data[$key]['divId'] = "sdiv-comment-".$id;
                $data[$key]['iskey'] = false;
                $data[$key]['fid'] = $value['fid'];
                $secData=$this->GetOtherAss($id);
                if($secData){
                    $data[$key]['laterData'] = $secData;
                    $data[$key]['isData'] = true;
                }else{
                    $data[$key]['laterData'] = array();
                    $data[$key]['isData'] = false;
                }
            }
            return $data;
        }else{//没有下级评论
            return false;
        }
    }
}
