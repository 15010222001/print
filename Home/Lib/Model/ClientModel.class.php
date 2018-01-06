<?php
//客户管理
class ClientModel extends RelationModel {
	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
		array('ID','number','ID号参数获取失败',0,'',2),									//在更新数据时验证ID是否正确
		array('CompanyName','require','请输入公司名称'),
		array('CompanyName','1,60','公司名称请在60个字符以内！',0,'length'),
		array('Address','0,100','详细地址请在100个字符以内！',0,'length'),
		array('ZipCode','0,10','邮编请在10个字符以内！',0,'length'),
		array('WebUrl','0,100','网站地址请在100个字符以内！',0,'length'),
		array('MainItems','0,200','主营项目请在200个字符以内！',0,'length'),
		array('Address','0,100','详细地址请在100个字符以内！',0,'length'),
		array('Message','0,1000','备注请在1000个字符以内！',0,'length'),
		array('Industry','number','行业必须是数字'),
		array('ClientType','number','客户类型必须是数字'),
		array('ClientLevel','number','客户级别必须是数字'),
		array('ClientSource','number','客户来源必须是数字'),
		array('FollowUp','number','跟进情况必须是数字'),
		array('Wast','number','跟单类型必须是数字'),
		array('Intent','number','客户意向必须是数字'),
	);
	//自动完成
	protected $_auto = array ( 
		array('FinalTime','Dtime',3,'callback'),
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
		'Contact' => array(
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'Contact',
			'foreign_key'=>'Cid',
			'mapping_name'=>'Contact',
			'mapping_fields'=>'ID,Cid,ContactName',
			'as_fields' => 'ContactName'
		),
		'User' => array(
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'User',
			'foreign_key'=>'Uid',
			'mapping_name'=>'Username',
			'mapping_fields'=>'Username',
			'as_fields' => 'Username'
		),
	);
}
?>