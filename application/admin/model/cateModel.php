<?php
namespace app\admin\model;
use think\Model;
class CateModel extends Model
{
	protected $table              = 'bg_cate';
	protected $autoWriteTimestamp = true;
	protected $createTime         = 'createTime';
	protected $updateTime         = 'UpdataTime';
	protected function initialize()
    {
        parent::initialize();
    }
}
