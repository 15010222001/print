<?php
//客户管理
class Purchase_order_goodsModel extends RelationModel {
	//自动验证
	protected $_validate = array(

	);
	//自动完成
	protected $_auto = array ( 

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
	    'User' => array(
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'User',
			'foreign_key'=>'add_user',
			'mapping_name'=>'Username',
			'mapping_fields'=>'Username',
			'as_fields' => 'Username'
	    ),
	    'Goods' => array(
	        'mapping_type'=>BELONGS_TO,
	        'class_name'=>'Goods',
	        'foreign_key'=>'goods_id',
	        'mapping_name'=>'goods_sn,goods_name',
	        'mapping_fields'=>'goods_sn,goods_name',
	        'as_fields' => 'goods_sn,goods_name'
	    ),
	);
}
?>