<?php
namespace app\index\controller;
use think\Request;
use think\Db;
class Msg
{
    use \traits\controller\Jump;
    public function index()
    {
        return view('index/msg');
    }

    public function getIndexPage(){
        $page = Request::instance()->param('page');
        $page = intval($page);
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        $msgData = $this->getAllMsg($page);
        $data = [
            'msgData'  => $msgData['data'],
            'num'      => $msgData['num'],
            'pagesize' => config('pagedata.msgpagesize'),
        ];
        echo json_encode($data);
    }

    /**获取所有留言 AJAX返回*/
    public function getAllMsg($page){
        $pagesize=config('pagedata.msgpagesize');
        $where = '';
        $order = 'pubtime asc';
        $tol = DB::table('bg_liuyan')->where($where)->count();
        $startIndex = $pagesize*($page-1);
        $Befdata=DB::table('bg_liuyan')->where($where)->order($order)->limit($startIndex,$pagesize)->select();
        $data = array();
        if(empty($Befdata)){
            $allData=array();
        }else{
            $data=$this->DealMsg($Befdata);
            $showKey = $startIndex;
            foreach ($data as $key=>$value){
                $showKey++;
                $id=$value['id'];
                $data[$key]['time']=$value['time'];
                $data[$key]['content']=$value['content'];
                $uid=$value['uid'];
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
                $data[$key]['key'] = '#'.$showKey;
                $data[$key]['iskey'] = true;
                $data[$key]['click'] = 'addComment('.$id.',1,0)';
                $res=DB::table('bg_liuyancomm')->where('lid = '.$id.' and level=1')->select();
                if($res){//若有二级回复
                    foreach($res as $k=>$v){
                        $id=$v['id'];
                        $res[$k]['time']=date("Y-m-d H:i",$v['pubtime']);
                        $res[$k]['content']=$v['content'];
                        $uid=$v['uid'];
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
                        $res[$k]['iskey'] = false;
                        $res[$k]['click'] = 'addComment('.$id.',2,1)';
                        $secData=$this->GetOtherMsg($id);
                        if ($secData) {
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
    //利用递归获取所有下级留言回复
    private function GetOtherMsg($id){
        $data=DB::table('bg_liuyancomm')->where('fid = '.$id.' and level !=1')->select();
        if($data){
            foreach($data as $key => $value){
                $id=$value['id'];
                $data[$key]['time']=date("Y-m-d H:i",$value['pubtime']);
                $data[$key]['content']=$value['content'];
                $uid=$value['uid'];
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
                $data[$key]['click'] = 'addComment('.$id.',2,1)';
                $secData=$this->GetOtherMsg($id);
                if ($secData) {
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


    /**处理评论格式*/
    private function dealMsg($data){
        if (empty($data)){
            return array();
        }else{
            foreach ($data as $k=>$v){
                $data[$k]['time']=date("Y-m-d H:i",$v['pubtime']);
            }
        }
        return $data;
    }
    
}
