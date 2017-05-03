<?php
namespace app\admin\controller;
use app\admin\controller\PublicInc;
use think\Db;
use think\Request;
class Program extends PublicInc
{
    use \traits\controller\Jump;
    public function index()
    {
        $this->getFileList();
        return view('admin/program');
    }

    private function getPath(){
        $url = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        $arr = explode('path/', $url);
        $path =end($arr);
        $path2 = encrypt($path,'D');
        if (empty($path2)) {
            return view('404');
            exit;
        }else{
            return $path2;
        }
    }

    public function addNewFile(){
        $name = Request::instance()->post('name');
        $path = Request::instance()->post('path');
        $file = new \custom\File;
        $result = $file->createFile($path,$name);
        echo json_encode($result);
    }

    public function addNewFolder(){
        $name = Request::instance()->post('name');
        $path = Request::instance()->post('path');
        $file = new \custom\File;
        $result = $file->createFolder($path,$name);
        echo json_encode($result);
    }

    public function seeFile(){
        $path = $this->getPath();
        if (file_exists($path)) {
            if (filetype($path) == 'dir') {
                $this->getFileList($path);
                return view('admin/program');
            }else{
                $this->getFileContent($path,1);
                return view('admin/fileview');
            }
        }else{
            $this->error('文件已被删除','/admin.php/Program/index');
        }
        
    }

    public function editFile(){
        $path = $this->getPath();
        if (file_exists($path)) {
            if (filetype($path) == 'dir') {
                $this->error('文件夹不可修改');
            }else{
                $this->getFileContent($path,2);
                return view('admin/fileview');
            }
        }else{
            $this->error('文件已被删除','/admin.php/Program/index');
        }
    }

    //获取单个文件内容
    private function getFileContent($path,$type){
        $content = file_get_contents($path);
        if(strlen($content)){
            if ($type == 1) {
                $new_content = highlight_string($content,true);
                \think\View::share('type',1);
            }else{
                $new_content = $content;
                \think\View::share('type',2);
            }
        }else{
            $new_content = "//这是一个空文件哦,保存时请删除该提示行";
            \think\View::share('type',2);
        }
        $file_name = basename($path);
        \think\View::share('content',$new_content);
        \think\View::share('file_name',$file_name);
        \think\View::share('path',$path);
    }

    public function FileEdit(){
        $content = Request::instance()->post('moreinfo');
        $file_name = Request::instance()->post('fileName');
        if(file_put_contents($file_name,$content)){
            $this->success('更新成功');
        }else{
            $this->error('更新失败');
        }
    }

    public function downloadFile(){
        $path = $this->getPath();
        if (filetype($path) == 'dir') {
            $this->error('暂不支持文件夹下载哦');
        }else{
            $file = new \custom\File;
            $file->downFile($path);
        }
    }

    public function delFile(){
        $path = $this->getPath();
        if (file_exists($path)) {
            if (filetype($path) == 'dir') {
                $file = new \custom\File;
                $result = $file->delFolder($path);
                // $this->error('暂不支持文件夹删除哦');
                if ($result) {
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }else{
                $file = new \custom\File;
                $result = $file->delFile($path);
                if ($result) {
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }
        }else{
            $this->error('文件已被删除','/admin.php/Program/index');
        }
    }

    public function getFileOldName(){
        $path = $this->getPath();
        if (filetype($path) == 'dir') {
            $path_array = explode('\\', $path);
            $fileName = end($path_array);
        }else{
            $fileName = basename($path);
        }
        echo json_encode(array('oldname'=>$fileName,'path'=>$path));
    }

    public function reNameFile(){
        $new_name = Request::instance()->post('newname');
        $old_name = Request::instance()->post('oldname');
        $path = Request::instance()->post('path');
        if (empty($new_name)||empty($old_name)||empty($path)) {
            echo json_encode(array('success'=>false,'msg'=>'Parameter is empty'));
            exit;
        }
        $file = new \custom\File;
        $result = $file->renameFile($old_name,$new_name,$path);
        echo json_encode($result);
    }

    //获取文件目录
    private function getFileList($path=""){
        if (empty($path)) {
            $path = ROOT_PATH;
        }
        $file = new \custom\File;
        $list = $file->readDirectory($path);
        $list = $this->dealFileList($list,$path,$file);
        $list_counts = count($list);
        if (empty($list)) {
            $is_empty = 1;
        }else{
            $is_empty = 0;
        }
        \think\View::share('is_empty',$is_empty);
        \think\View::share('ct',$list_counts);
        \think\View::share('list',$list);
        \think\View::share('path',$path);
    }
    //对文件路径的\进行转义
    private function dealPath($path){
	if($path){
	   $path = str_replace('\\','/',$path);
	}
	return $path;
    }

    //对文件目录数组进行处理
    private function dealFileList($data,$path,$fileObj){
        if (!empty($data)) {
            if (!empty($data['dir'])) {
                $dir = $data['dir'];
            }else{
                $dir = array();
            }
            if (!empty($data['file'])) {
                $file = $data['file'];
            }else{
                $file = array();
            }
            unset($data);
            $i = 0;
            if (!empty($dir)) {
                foreach ($dir as $key => $value) {
		    if($value == ".git"){
		      continue;
  		    }
                    $finlpath = $path.'/'.$value;
                    $data[$i]['ext'] = -1;
                    $data[$i]['name'] = $value;
                    $data[$i]['path'] = encrypt($finlpath,'E');
                    $data[$i]['type'] = 1;
                    $data[$i]['src'] = 'folder_ico.png';
                    $data[$i]['Byte'] = $fileObj->transByte($fileObj->dirSize($finlpath));
                    global $sum;
                    $sum = 0 ;
                    $data[$i]['is_readable'] = is_readable($finlpath) ? 'correct.png' : 'error.png';
                    $data[$i]['is_writable'] = is_writable($finlpath) ? 'correct.png' : 'error.png';
                    $data[$i]['is_executable'] = is_executable($finlpath) ? 'correct.png' : 'error.png';
                    $data[$i]['filectime'] = filectime($finlpath);
                    $data[$i]['filemtime'] = filemtime($finlpath);
                    $data[$i]['fileatime'] = fileatime($finlpath);
                    $i++;
                }
            }
            $image_ext = config('image_ext');
            if (!empty($file)) {
                foreach ($file as $key => $value) {
                    //过滤掉处理文件的类 以防系统崩溃
                    if ($value == "File.php" || $value == "Program.php") {
                        continue;
                    }
                    $finlpath = $path.'/'.$value;
                    $ext = explode(".",$value);
                    $ext = strtolower(end($ext));
                    if (in_array($ext,$image_ext)) {
                        $data[$i]['ext'] = 1;
                        $file_arr = explode('public', $finlpath);
                        $newfile = end($file_arr);
                        $newfile = str_replace("\\","/",$newfile);
                        $data[$i]['file'] = $newfile;
                    }else{
                        $data[$i]['ext'] = 0;
                    }
                    $data[$i]['name'] = $value;
                    $data[$i]['path'] = encrypt($finlpath,'E');
                    $data[$i]['type'] = 2;
                    $data[$i]['src'] = 'file_ico.png';
                    $data[$i]['Byte'] = $fileObj->transByte(filesize($finlpath));
                    $data[$i]['is_readable'] = is_readable($finlpath) ? 'correct.png' : 'error.png';
                    $data[$i]['is_writable'] = is_writable($finlpath) ? 'correct.png' : 'error.png';
                    $data[$i]['is_executable'] = is_executable($finlpath) ? 'correct.png' : 'error.png';
                    $data[$i]['filectime'] = filectime($finlpath);
                    $data[$i]['filemtime'] = filemtime($finlpath);
                    $data[$i]['fileatime'] = fileatime($finlpath);
                    $i++;
                }
            }
        }
        return $data;
    }
}
