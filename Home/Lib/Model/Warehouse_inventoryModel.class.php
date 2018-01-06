<?php
class Warehouse_inventoryModel extends RelationModel {
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
	public function enterConfirm($order_id){
	    $Model        = new Model();
	    $Enter_order  = new Model('Enter_order');	    
	    $sql = "INSERT IGNORE INTO tp_warehouse_inventory(warehouse_id,goods_id)SELECT d.ID,g.goods_id FROM tp_dmenu AS d,tp_goods AS g 
	        WHERE d.Sid = '63'";
	    $Model->execute($sql);
	    $order_info   = $Enter_order->where("order_id = $order_id")->find();
	    $warehouse_id = $order_info['warehouse_id'];
	    $goods_id     = $order_info['goods_id'];
	    $order_sn     = $order_info['order_sn'];
	    //写日志
	    $sql = "SELECT og.goods_id,og.number AS num
	    FROM tp_enter_order_goods AS og
	    WHERE og.order_id = '{$order_id}' ";
	    $skus2 = $Model->query($sql);
	    
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['front_num']     = $front['stock_number'];
	    }
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_enter_order AS eo , tp_enter_order_goods AS eog 
	        SET wi.stock_number = wi.stock_number + eog.number 
	        WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id 
	        AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['stock_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 1;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '入库单确认，增加可用现货库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	}
	public function enterunConfirm($order_id){
	    $Model = new Model();
	    $Enter_order  = new Model('Enter_order');
	    $sql = "INSERT IGNORE INTO tp_warehouse_inventory(warehouse_id,goods_id)SELECT d.ID,g.goods_id FROM tp_dmenu AS d,tp_goods AS g
	        WHERE d.Sid = '63'";
	    $Model->execute($sql);
	    $order_info   = $Enter_order->where("order_id = $order_id")->find();
	    $warehouse_id = $order_info['warehouse_id'];
	    $goods_id     = $order_info['goods_id'];
	    $order_sn     = $order_info['order_sn'];
	    //写日志
	    $sql = "SELECT og.goods_id,og.number AS num
	    FROM tp_enter_order_goods AS og
	    WHERE og.order_id = '{$order_id}' ";
	    $skus2 = $Model->query($sql);
	     
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['front_num']     = $front['stock_number'];
	    }
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_enter_order AS eo , tp_enter_order_goods AS eog
	    SET wi.stock_number = wi.stock_number - eog.number
	    WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['stock_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 1;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '入库单取消确认，扣减可用现货库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	}
	public function enterIn($order_id){
	    $Model = new Model();
	    $Enter_order  = new Model('Enter_order');
	    $order_info   = $Enter_order->where("order_id = $order_id")->find();
	    $warehouse_id = $order_info['warehouse_id'];
	    $goods_id     = $order_info['goods_id'];
	    $order_sn     = $order_info['order_sn'];
	    //写日志
	    $sql = "SELECT og.goods_id,og.number AS num
	    FROM tp_enter_order_goods AS og
	    WHERE og.order_id = '{$order_id}' ";
	    $skus2 = $Model->query($sql);
	    //实际库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['front_num']     = $front['actual_number'];
	    }
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_enter_order AS eo , tp_enter_order_goods AS eog
	    SET wi.actual_number = wi.actual_number + eog.number
	    WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //实际库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['actual_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 0;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '入库单入库，增加实际库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	}
	public function outerConfirm($order_id){
	    $Model = new Model();
		//判断可用现货库存
		if(false==$this->check_inventory_enough($order_id,'outer_out')) {
				return array(
					'status'=>'0',
					'message'=>'实际库存不足！'
		 		);
		}
		$Outer_order  = new Model('Outer_order');
		$order_info   = $Outer_order->where("order_id = $order_id")->find();
		$warehouse_id = $order_info['warehouse_id'];
		$goods_id     = $order_info['goods_id'];
		$order_sn     = $order_info['order_sn'];
		//写日志
		$sql = "SELECT og.goods_id,og.number AS num
		FROM tp_outer_order_goods AS og
		WHERE og.order_id = '{$order_id}' ";
		$skus2 = $Model->query($sql);
		 
		//可用库存库存帐
		foreach ($skus2 as $key=>$sku){
		    //修改库存后的数量
		    $array_front = array(
		        'warehouse_id'   => $warehouse_id,
		        'goods_id'       => $sku['goods_id'],
		    );
		    $front = $this->front_after_num2($array_front);
		    $skus[$key]['front_num']     = $front['stock_number'];
		}
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_outer_order AS eo , tp_outer_order_goods AS eog
	    SET wi.stock_number = wi.stock_number - eog.number
	    WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['stock_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 1;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '出库单确认，扣减可用现货库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	    return array(
	        'status'=>'1',
	        'message'=>'成功！'
	    );
	}
	public function outerunConfirm($order_id){
	    $Model = new Model();
	    $Outer_order  = new Model('Outer_order');
	    $order_info   = $Outer_order->where("order_id = $order_id")->find();
	    $warehouse_id = $order_info['warehouse_id'];
	    $goods_id     = $order_info['goods_id'];
	    $order_sn     = $order_info['order_sn'];
	    //写日志
	    $sql = "SELECT og.goods_id,og.number AS num
	    FROM tp_outer_order_goods AS og
	    WHERE og.order_id = '{$order_id}' ";
	    $skus2 = $Model->query($sql);
	    	
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['front_num']     = $front['stock_number'];
	    }
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_outer_order AS eo , tp_outer_order_goods AS eog
	    SET wi.stock_number = wi.stock_number + eog.number
	    WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['stock_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 1;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '出库单取消确认，增加可用现货库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	    return array(
	        'status'=>'1',
	        'message'=>'成功！'
	    );
	}
	public function outerOut($order_id){
	    $Model = new Model();
	    $Outer_order  = new Model('Outer_order');
	    $order_info   = $Outer_order->where("order_id = $order_id")->find();
	    $warehouse_id = $order_info['warehouse_id'];
	    $goods_id     = $order_info['goods_id'];
	    $order_sn     = $order_info['order_sn'];
	    //写日志
	    $sql = "SELECT og.goods_id,og.number AS num
	    FROM tp_outer_order_goods AS og
	    WHERE og.order_id = '{$order_id}' ";
	    $skus2 = $Model->query($sql);
	    //实际库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['front_num']     = $front['actual_number'];
	    }
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_outer_order AS eo , tp_outer_order_goods AS eog
	    SET wi.actual_number = wi.actual_number - eog.number
	    WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //实际库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['actual_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 0;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '出库单出库，扣减实际库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	}
	public function rectifyConfirm($order_id){
	    $Model        = new Model();
	    $Enter_order  = new Model('Rectify_order');
	    $sql = "INSERT IGNORE INTO tp_warehouse_inventory(warehouse_id,goods_id)SELECT d.ID,g.goods_id FROM tp_dmenu AS d,tp_goods AS g
	        WHERE d.Sid = '63'";
	    $Model->execute($sql);
	    $order_info   = $Enter_order->where("order_id = $order_id")->find();
	    $warehouse_id = $order_info['warehouse_id'];
	    $goods_id     = $order_info['goods_id'];
	    $order_sn     = $order_info['order_sn'];
	    //写日志
	    $sql = "SELECT og.goods_id,og.number AS num
	    FROM tp_rectify_order_goods AS og
	    WHERE og.order_id = '{$order_id}' ";
	    $skus2 = $Model->query($sql);
	     
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['front_num']     = $front['stock_number'];
	    }
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_rectify_order AS eo , tp_rectify_order_goods AS eog
	    SET wi.stock_number = wi.stock_number + eog.number
	    WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['stock_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 1;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '调整单确认，调整可用现货库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	}
	public function rectifyunConfirm($order_id){
	    $Model = new Model();
	    $Enter_order  = new Model('Rectify_order');
	    $sql = "INSERT IGNORE INTO tp_warehouse_inventory(warehouse_id,goods_id)SELECT d.ID,g.goods_id FROM tp_dmenu AS d,tp_goods AS g
	        WHERE d.Sid = '63'";
	    $Model->execute($sql);
	    $order_info   = $Enter_order->where("order_id = $order_id")->find();
	    $warehouse_id = $order_info['warehouse_id'];
	    $goods_id     = $order_info['goods_id'];
	    $order_sn     = $order_info['order_sn'];
	    //写日志
	    $sql = "SELECT og.goods_id,og.number AS num
	    FROM tp_rectify_order_goods AS og
	    WHERE og.order_id = '{$order_id}' ";
	    $skus2 = $Model->query($sql);
	
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['front_num']     = $front['stock_number'];
	    }
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_rectify_order AS eo , tp_rectify_order_goods AS eog
	    SET wi.stock_number = wi.stock_number - eog.number
	    WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //可用库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['stock_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 1;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '调整单取消确认，调整可用现货库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	}
	public function rectify($order_id){
	    $Model = new Model();
	    $Rectify_order  = new Model('Rectify_order');
	    $order_info   = $Rectify_order->where("order_id = $order_id")->find();
	    $warehouse_id = $order_info['warehouse_id'];
	    $goods_id     = $order_info['goods_id'];
	    $order_sn     = $order_info['order_sn'];
	    //写日志
	    $sql = "SELECT og.goods_id,og.number AS num
	    FROM tp_rectify_order_goods AS og
	    WHERE og.order_id = '{$order_id}' ";
	    $skus2 = $Model->query($sql);
	    //实际库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['front_num']     = $front['actual_number'];
	    }
	    $sql = "UPDATE tp_warehouse_inventory AS wi ,tp_rectify_order AS eo , tp_rectify_order_goods AS eog
	    SET wi.actual_number = wi.actual_number + eog.number
	    WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}'";
	    $Model->execute($sql);
	    //实际库存库存帐
	    foreach ($skus2 as $key=>$sku){
	        //修改库存后的数量
	        $array_front = array(
	            'warehouse_id'   => $warehouse_id,
	            'goods_id'       => $sku['goods_id'],
	        );
	        $front = $this->front_after_num2($array_front);
	        $skus[$key]['after_num']     = $front['actual_number'];
	        $skus[$key]['actual_number'] = $front['actual_number'];
	        $skus[$key]['goods_id']      = $sku['goods_id'];
	        $skus[$key]['warehouse_id']  = $warehouse_id;
	        $skus[$key]['num']           = $sku['num'];
	        $skus[$key]['sign']          = 0;
	        $skus[$key]['order_sn']      = $order_sn;		//单据编号
	        $skus[$key]['log_desc']      = '调整单入库，调整实际库存';	//日志描述
	        $skus[$key]['add_time']      = date("Y-m-d H:i:s");	//创建时间
	        $skus[$key]['action_user']   = $_SESSION['ThinkUser']['Username'];
	    }
	    $this->write_log($skus);
	}
	public function check_inventory_enough($order_id, $type){
	    if ($order_id == '')
	        return false;
	    switch ($type ){
	   		   case 'outer_out':
	   		       $sql = "SELECT 1 FROM tp_warehouse_inventory AS wi ,tp_outer_order AS eo , tp_outer_order_goods AS eog
	   		           WHERE wi.warehouse_id = eo.warehouse_id AND eo.order_id = eog.order_id AND wi.goods_id = eog.goods_id
	    AND eo.order_id ='{$order_id}' AND wi.stock_number < eog.number ";
	   		       break;
	   		   default:
	   		       return false;
	    };
	    $Model = new Model();
	    $data  = $Model->query($sql);
	    if (empty($data))
	        return true;
	    else
	        return false;
	}
	public function front_after_num2($params=array()){
	    $Model = new Model();
	    $sql = "SELECT ifnull(actual_number,0) AS actual_number,ifnull(stock_number,0) AS stock_number "
	        ." FROM tp_warehouse_inventory WHERE warehouse_id ='".$params['warehouse_id']."' AND goods_id='".$params['goods_id']."'";
	    $res = $Model->query($sql);
	    return $res[0];
	}
	/**
	 * 写库存流水帐
	 * 参数为空的可以不传入，相应字段将不会保存
	 *
	 * @param $para =
	 * array(
	 * 'log_id'		=> '自增',
	 * 'sku_sn'   	=> 'sku编码',
	 * 'num'        => '库存变动量',
	 * 'order_type' => '单据类型（直接中文）',
	 * 'op'         => '操作方法（直接中文）',
	 * 'log_desc'	=> '日志描述',
	 * 'add_time'  	=> '创建时间',
	 * )
	 * @return 日志ID
	 */
	public function write_log($params = array()){
	    $inventory_log = D('Inventory_log');;
	    foreach ($params as $log){
	        $inventory_log->create($log);
	        $result = $inventory_log->add();
	    }
	    return $result;
	}
}
?>