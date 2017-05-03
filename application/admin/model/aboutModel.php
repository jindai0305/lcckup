<?php
namespace app\admin\model;
use think\Model;
class AboutModel extends Model
{
	protected $table              = 'bg_myinfo';
	protected $autoWriteTimestamp = true;
	protected $createTime         = 'pubtime';
	protected $updateTime         = false;
	protected function initialize()
    {
        parent::initialize();
    }
}
