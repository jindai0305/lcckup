<?php
namespace app\admin\model;
use think\Model;
class ArtModel extends Model
{
	protected $table              = 'bg_detail';
	protected $autoWriteTimestamp = true;
	protected $createTime         = 'pubtime';
	protected $updateTime         = false;
	protected function initialize()
    {
        parent::initialize();
    }
}
