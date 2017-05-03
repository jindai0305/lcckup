<?php
namespace app\admin\model;
use think\model;
class AdminUser extends Model
{
	protected $table    = 'bg_adminuser';
	protected $readonly = ['name'];       //设置只读字段
	protected function initialize()
    {
        parent::initialize();
    }
}
