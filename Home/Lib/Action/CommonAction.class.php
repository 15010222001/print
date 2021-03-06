<?php
class CommonAction extends Action {
	public function _initialize() {
		$this->checkAdminSession();
		$this->configcache();
	}
	
	//根据id获取用户信息
	public function getAuthInfo($field, $id){
		$user = D('user');
		if(empty($field)){
			$field = '*';
		}
		$info = $user->field($field)->where()->find();
		return $info ? $info : null;
	}
	
	//获取订单名称
	public function get_order_info($order_id){
		$order = M('goods');
		$where = array('goods_id' => $order_id);
		$order_info = $order->field('goods_id, goods_name, Recycle')->where($where)->find();
		if(1 != $order_info['Recycle']){
			return $order_info['goods_name'];
		}else{
			return '';
		}
	}
	
	
	//获取订单信息
	public function getOrderInfo(){
		$where = array('Recycle' => 0);
		$order = $goods = D('Goods');
		return  $order->field('goods_id, goods_name')->where($where)->select();
	}
	
	//判断用户是否登录的方法
	public function checkAdminSession() {
		$nowtime = time();
		$end_time=C('USER_AUTH_SESSION');		//读取配置文件中session的过期时间
		if (!isset($_SESSION['ThinkUser'])) {
			R('Public/location',array('您还没有登录',__APP__.'/Index/index'));
		}else {
			if (($nowtime - $_SESSION['ThinkUser']['Logintime']) > $end_time) {
				unset($_SESSION['ThinkUser']);
				R('Public/location',array('登录超时',__APP__.'/Index/index'));
			}else {
				$user = M('user');
				$uid = $_SESSION['ThinkUser']['ID'];
				$result=$user->where("ID = $uid")->count();
				if ($result != 1) {
					unset($_SESSION['ThinkUser']);
					R('Public/location',array('非法用户登录',__APP__.'/Index/index'));
				}else {
					$_SESSION['ThinkUser']['Logintime'] = $nowtime;
					$this->statis($uid);
				}
			}
		}
	}
	//在线人数统计
	public function statis($uid) {
		$statis = M('statis');
		$where['Dtime'] = array('LT', time()-120);
		if ($statis->where("Uid = $uid")->count()) {
			$statis->where("Uid = $uid")->save(array('Dtime' => time()));
			$statis->where($where)->delete();
		}else {
			$statis->where($where)->delete();
			$time = time();
			$data = array('Uid' => $uid, 'Dtime' => time());
			$statis->add($data);
		}
	}	
	//基本配置信息缓存
	public function configcache() {
		if (!S('configcache')) {
			$configcache = require(CONF_PATH.'webconfig.php');
			S('configcache', $configcache, $configcache['DataCache']*3600);
		}
		$this->assign('configcache',S('configcache'));
	}
	//操作记录
	public function operating($url,$status,$description) {
		$data = array();
		$data['Uid'] = $_SESSION['ThinkUser']['ID'];
		$data['Url'] = $url;
		$data['Description'] = $description;
		$data['Ip'] = get_client_ip();
		$data['Status'] = $status;
		$data['Dtime'] = date('Y-m-d H:i:s');
		$operating = M('operating');
		$operating->add($data);
	}
	//入库单操作记录
	public function enter($order_id,$status,$description) {
	    $data = array();
	    $data['action_user']   = $_SESSION['ThinkUser']['Username'];
	    $data['action_note']   = $description;
	    $data['order_status']  = $status;
	    $data['order_id']      = $order_id;
	    $data['log_time']      = date('Y-m-d H:i:s');
	    $operating = M('Enter_order_action');
	    $operating->add($data);
	}
	//入库单操作记录
	public function outer($order_id,$status,$description) {
	    $data = array();
	    $data['action_user']   = $_SESSION['ThinkUser']['Username'];
	    $data['action_note']   = $description;
	    $data['order_status']  = $status;
	    $data['order_id']      = $order_id;
	    $data['log_time']      = date('Y-m-d H:i:s');
	    $operating = M('Outer_order_action');
	    $operating->add($data);
	}
	//入库单操作记录
	public function rectify($order_id,$status,$description) {
	    $data = array();
	    $data['action_user']   = $_SESSION['ThinkUser']['Username'];
	    $data['action_note']   = $description;
	    $data['order_status']  = $status;
	    $data['order_id']      = $order_id;
	    $data['log_time']      = date('Y-m-d H:i:s');
	    $operating = M('Rectify_order_action');
	    $operating->add($data);
	}
	//入库单操作记录
	public function purchase($order_id,$status,$description) {
	    $data = array();
	    $data['action_user']   = $_SESSION['ThinkUser']['Username'];
	    $data['action_note']   = $description;
	    $data['order_status']  = $status;
	    $data['order_id']      = $order_id;
	    $data['log_time']      = date('Y-m-d H:i:s');
	    $operating = M('Purchase_order_action');
	    $operating->add($data);
	}
	//用户权限验证1(ajax发送返回验证)
	public function userauth($auth) {
		$comp=explode(',',$_SESSION['ThinkUser']['Competence']);			//当前用户权限获取
		array_pop($comp);
		if (!in_array($auth,$comp)) {
			$err=array('s'=>'抱歉，您没有此操作权限');
			exit(json_encode($err));
		}
	}
	//用户权限验证2(页面)
	public function userauth2($auth) {
		$comp=explode(',' , $_SESSION['ThinkUser']['Competence']);			//当前用户权限获取
		array_pop($comp);
		if (!in_array($auth,$comp)) {
			$this->content='抱歉，您没有此操作权限';
			//exit($this->display('Public:location'));
			header('Content-Type: text/html; charset=utf-8');	//输出头，防止中文乱码
			echo '<script>alert("'.$this->content.'"); window.top.location="'.__APP__.'"</script>';
			exit;
		}
	}
	//用户权限验证3(函数)
	public function userauth3($auth) {
	    $comp=explode(',',$_SESSION['ThinkUser']['Competence']);			//当前用户权限获取
	    array_pop($comp);
	    if (!in_array($auth,$comp)) {
	        return false ;
	    }else{
	        return true ;
	    }
	}
	//用户权限验证3(窗口验证)
	public function win_userauth($auth) {
		$comp=explode(',',$_SESSION['ThinkUser']['Competence']);			//当前用户权限获取
		array_pop($comp);
		if (!in_array($auth,$comp)) {
			$this->content='抱歉，您没有此操作权限';
			exit($this->display('Public:err'));
		}
	}
	//删除缓存文件
	public function delDir($dirName) {
		 $dh = opendir($dirName);
		 //循环读取文件
		 while ($file = readdir($dh)) {
			 if($file != '.' && $file != '..') {
				 $fullpath = $dirName . '/' . $file;
				 //判断是否为目录
				 if(!is_dir($fullpath)) {
					 //如果不是,删除该文件
					 if(!unlink($fullpath)) {
						 echo $fullpath . '无法删除,可能是没有权限!<br>';
					 }
				 } else {
					 //如果是目录,递归本身删除下级目录
					 $this->delDir($fullpath);
				 }
			 }
		 }
		 //关闭目录
		 closedir($dh);
		 //删除目录
		 if(!rmdir($dirName)) {
			 R('Public/errjson',array($dirName.'__目录删除失败'));
		 }
	}
	//一键清空缓存
	public function clearcache() {
		//验证用户权限
		$this->userauth(24);
		if ($this->isAjax()) {
			if (I('post.clear','')=='ok') {
				$fileDel = 'Home/Runtime';
				if (file_exists($fileDel)) {
					$this->delDir($fileDel);
					$this->operating(__ACTION__,0,'清空站点缓存');
					R('Public/errjson',array('ok'));
				}else {
					R('Public/errjson',array('缓存目录不存在'));
				}
			}else {
				R('Public/errjson',array('非法请求'));
			}
		}else {
			R('Public/errjson',array('非法请求'));
		}
	}
	//链接请求
	public function link() {
		$with = D('With');
		$Uid = $_SESSION['ThinkUser']['ID'];
		$date = date('Y-m-d H:i:s');
		//总跟单数
		$withlist = $with->relation(true)->where("Uid = $Uid AND Status = 0 AND Remind = 0 AND RemindTime <= '$date'")->count();
		R('Public/errjson',array($withlist));
	}
}
?>