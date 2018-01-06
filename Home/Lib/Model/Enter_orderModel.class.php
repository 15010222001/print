<?php
//客户管理
class Enter_orderModel extends RelationModel {
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
	);
	public function updateOrderinfo($order_id){
	    $where = array();
	    $where['order_id'] = $order_id;
	    $enter_order_goods = M('enter_order_goods');
	    $order_amount      = $enter_order_goods->where($where)->sum('price*number');
	    $this->where($where)->save(array('order_amount' => $order_amount));
	}
}
?>