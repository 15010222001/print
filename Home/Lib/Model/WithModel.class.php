<?php
//联系人表
class WithModel extends RelationModel {
	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
		array('ID','number','ID号参数获取失败',0,'',2),									//在更新数据时验证ID是否正确
		array('Cid','number','客户资料ID获取失败',0),	
		array('ContactName','require','请选择跟单对像'),
		array('Wast','require','请选择跟单类型'),
		array('NextTime','require','请选择下次联系日期'),
		array('RemindTime','require','请选择提醒时间'),
		array('Content','0,1000','备注请在1000个字符以内！',0,'length'),
	);
	//自动完成
	protected $_auto = array ( 
		array('Dtime','Dtime',1,'callback'),
		array('Uid','Uid',1,'callback'),
	);
	//添加当前时间
	protected function Dtime() {
		return date('Y-m-d H:i:s');
	}
	//添加用户ID
	protected function Uid() {
		return $_SESSION['ThinkUser']['ID'];
	}
	//关联查询
	protected $_link = array(
		'Client' => array(
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'Client',
			'foreign_key'=>'Cid',
			'mapping_name'=>'CompanyName',
			'mapping_fields'=>'CompanyName',
			'as_fields'=>'CompanyName'
		),
		'Contact' => array(
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'Contact',
			'foreign_key'=>'NameId',
			'mapping_name'=>'ContactName',
			'mapping_fields'=>'ContactName',
			'as_fields'=>'ContactName'
		),
	);
}
?>