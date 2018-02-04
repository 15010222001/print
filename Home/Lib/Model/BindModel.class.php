<?php
class BindModel extends RelationModel {
  
	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
		array('ID','number','ID号参数获取失败',0,'',2),		
		array('order_id','require','订单ID获取失败'),								
		array('order_id','number','订单ID获取失败'),
		//array('Title','require','请填写标题'),
		//array('Title','1,80','标题请在80个字符以内！',0,'length'),
		//array('Sortid','require','请填写排序ID'),								
		//array('Sortid','number','排序ID必须是数字'),
		array('notes','0,200','描述请在200个字符以内！',0,'length'),		
	);
	//自动完成
	protected $_auto = array ( 
		array('add_time','Dtime',1,'callback'),
		array('edit_time','Dtime',1,'callback'),
		array('auth_id','userid',3,'callback'),
	);
	//添加当前时间
	protected function Dtime() {
		return time();
	}
	//添加当前管理员
	protected function userid() {
		return (int)$_SESSION['ThinkUser']['ID'];
	}
  
}
