<?php
class Inventory_logModel extends RelationModel {
	//自动验证
	protected $_validate = array(

	);
	//自动完成
	protected $_auto = array ( 

	);
	//添加当前时间
	protected function Dtime() {

	}
	//关联查询
	protected $_link = array(
		'Goods' => array(
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'Goods',
			'foreign_key'=>'goods_id',
			'mapping_name'=>'goods_name',
			'mapping_fields'=>'goods_name',
			'as_fields'=>'goods_name'
		),
	);
}
?>