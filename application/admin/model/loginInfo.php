<?php
namespace app\admin\model;
use think\Model;
class LoginInfo extends Model
{
	protected $table              = 'bg_logininfo';
	protected $autoWriteTimestamp = true;
	protected $createTime         = 'login_time';
	protected $updateTime         = false;
	protected function initialize()
    {
        parent::initialize();
    }
}
