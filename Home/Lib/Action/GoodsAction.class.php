<?php
//客户管理类
class GoodsAction extends CommonAction {
	public function index() {
		parent::userauth2(87);
		$this->display();
	}
	//客户资料Ajax请求
	public function indexajax() {
		$keyword = I('get.keyword','','htmlspecialchars');
		$goods = D('Goods');
		import('ORG.Util.Page');						// 导入分页类
		if($keyword!=""){
		    $where['goods_sn|goods_name'] = $keyword;  //2017-06-02 商品搜索
		}
		$where['Recycle'] = 0;
		$count = $goods->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $goods->where($where)->order('goods_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['GoodsType'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsType'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['GoodsLevel'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsLevel'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['GoodsSource'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsSource'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['FollowUp'] == $dlist[$j]['ID']) {
						$volist[$i]['FollowUp'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['Intent'] == $dlist[$j]['ID']) {
						$volist[$i]['Intent'] = $dlist[$j]['MenuName'];
					}
				}
			}
			//输出数据
			$i = 1;
			foreach($volist as $vo) {
				//将搜索的标为红色
				$company = str_replace($keyword,'<font>'.$keyword.'</font>',$vo['CompanyName']);		//公司名称
				if ($b=$i % 2 == 0) { 
					$tr2 = 'tr2';
				}else {
					$tr2 = '';
				}
				if ($vo['OpenShare']==0) {
					$OpenShare = '<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/no.png" border="0" title="点击可共享客户" alt="'.$vo['ID'].'" data-title="'.$vo['OpenShare'].'" class="openshare" />';
				}else {
					$OpenShare = '<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/yes.png" border="0" title="点击关闭共享" alt="'.$vo['ID'].'" data-title="'.$vo['OpenShare'].'" class="openshare" />';
				}
				if(!parent::userauth3(100)){
				    $vo['price'] = "未知";
				}
				$html .= "
					<tr class='tr ".$tr2."'>
						<td class='tc'><input type='checkbox' class='delid' value='".$vo['goods_id']."' /></td>
						<td class='tc'>".$vo['goods_name']."</td>
						<td class='tc'>".$vo['goods_sn']."</td>
						<td class='tc'>".$vo['price']."</td>
						<td class='tc'>".$vo['barcode']."</td>
						<td class='tc'>".date('Y-m-d H:i:s',$vo['delivery_time'])."</td>
						<td class='tc'>".date('Y-m-d H:i:s',$vo['create_time'])."</td>
						<td class='tc fixed_w'><a href='".$vo['goods_id']."' class='edit'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/edit.png' border='0' title='查看/修改' /></a><a href='".$vo['goods_id']."' class='del'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/delete.png' border='0' title='删除' /></a></td>
					</tr>
				";
				$i++;  //2017-06-02 添加 <td class='tc'>".$vo['vender_name']."</td>
			}
			$data = array('s'=>'ok','html'=>$html,'page'=>'<span class="page">'.$show.'</span>');
			echo json_encode($data);
		}else {
			$html = "<tr class='tr'><td class='tc' colspan='11'>暂无数据，等待添加～！</td></tr>";
			$data = array('s'=>'no','html'=>$html);
			echo json_encode($data);
		}
	}
	/*
	//打开或关闭共享请求
	public function opensharedo() {
		parent::userauth(72);
		$id = I('get.id','');
		$openshare = I('get.openshare','');
		$openshare = intval($openshare);
		$id = intval($id);
		$goods = M('Goods');
		if ($openshare==0) {
			$openshare = 1;
		}else {
			$openshare = 0;
		}
		if ($goods->where("ID = $id")->save(array('OpenShare' => $openshare))) {
			parent::operating(__ACTION__,0,'共享客户编号为：'.$id.'的数据');
			R('Public/errjson',array('ok'));
		}else {
			parent::operating(__ACTION__,1,'共享失败：'.$goods->getError());
			R('Public/errjson',array('共享操作失败'));
		}
	}
	//查看共享客户
	public function openshare() {
		//验证用户权限
		parent::userauth2(73);
		$this->display();
	}
	
	//共享数据请求
	public function openshareajax() {
		parent::userauth(73);
		$keyword = I('get.keyword','','htmlspecialchars');
		$uid = I('get.uid','','htmlspecialchars');
		$goods = D('Goods');
		import('ORG.Util.Page');						// 导入分页类
		$where['Recycle'] = 0;
		$where['OpenShare'] = 1;
		if ($uid!='') {
			$where['Uid'] = intval($uid);
		}
		$where['CompanyName'] = $keyword;
		$count = $goods->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $goods->relation(true)->where($where)->order('FinalTime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['GoodsType'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsType'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['Industry'] == $dlist[$j]['ID']) {
						$volist[$i]['Industry'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['GoodsLevel'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsLevel'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['GoodsSource'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsSource'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['FollowUp'] == $dlist[$j]['ID']) {
						$volist[$i]['FollowUp'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['Intent'] == $dlist[$j]['ID']) {
						$volist[$i]['Intent'] = $dlist[$j]['MenuName'];
					}
				}
			}
			//输出数据
			$i = 1;
			foreach($volist as $vo) {
				//将搜索的标为红色
				$company = str_replace($keyword,'<font>'.$keyword.'</font>',$vo['CompanyName']);		//公司名称
				if ($b=$i % 2 == 0) { 
					$tr2 = 'tr2';
				}else {
					$tr2 = '';
				}
				$html .= "
					<tr class='tr ".$tr2."'>
						<td class='tc'><input type='checkbox' class='delid' value='".$vo['ID']."' /></td>
						<td class='tc'>".$vo['ID']."</td>
						<td><a href='".$vo['ID']."' class='edit'>".$company."</a></td>
						<td class='tc'>".$vo['ContactName']."</td>
						<td class='tc'>".$vo['GoodsType']."</td>
						<td>".$vo['Industry']."</td>
						<td>".$vo['GoodsLevel']."</td>
						<td class='tc'>".$vo['GoodsSource']."</td>
						<td class='tc'>".$vo['FollowUp']."</td>
						<td class='tc'>".$vo['Intent']."</td>
						<td class='tc'><a class='uid' href='".C('TMPL_PARSE_STRING.__APP__')."/Goods/openshareajax/?uid=".$vo['Uid']."'>".$vo['Username']."</a></td>
						<td class='tc'>".$this->Beautifytime($vo['FinalTime'])."</td>
						<td class='tc fixed_w'><a href='".$vo['ID']."' class='edit'>查看详细</a></td>
					</tr>
				";
				$i++;
			}
			$data = array('s'=>'ok','html'=>$html,'page'=>'<span class="page">'.$show.'</span>');
			echo json_encode($data);
		}else {
			$html = "<tr class='tr'><td class='tc' colspan='13'>暂无数据，等待添加～！</td></tr>";
			$data = array('s'=>'no','html'=>$html);
			echo json_encode($data);
		}
	}
	//共享资料详细，基本资料
	public function openshare_company_detail() {
		//验证用户权限
		parent::win_userauth(73);
		$id = I('get.id','');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		//下拉菜单数据
		$dmenu = M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
		//查出相应数据
		$goods = D('Goods');
		$result = $goods->relation(true)->where("ID = $id AND Recycle = 0 AND OpenShare = 1")->find();
		if (!$result) {
			parent::operating(__ACTION__,1,'数据不存在');
			$this->content='不存在你要修改的数据，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$this->assign('volist',$volist);
		$this->assign('result',$result);
		$this->display('opensharecompanydetail');
	}
	//共享资料详细，联系人资料
	public function openshare_contact_list() {
		//验证用户权限
		parent::userauth2(73);
		$Cid = I('get.Cid','');
		if ($Cid=='' || !is_numeric($Cid)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->error('参数不正确');
		}
		$this->assign('Cid',$Cid);
		$this->display('opensharecontactlist');
	}
	//共享客户联系信息ajax请求
	public function openshare_contact_ajax() {
		parent::userauth(73);
		$Cid = I('get.Cid','','htmlspecialchars');
		$contact = D('Contact');
		import('ORG.Util.Page');						// 导入分页类
		$where['Recycle'] = 0;
		$where['OpenShare'] = 1;
		if ($Cid!='' && is_numeric($Cid)) {
			$where['Cid']  = intval($Cid);
		}
		$count = $contact->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $contact->relation(true)->where($where)->order('FinalTime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['Post'] == $dlist[$j]['ID']) {
						$volist[$i]['Post'] = $dlist[$j]['MenuName'];
					}
				}
			}
			//输出数据
			$i=1;
			foreach($volist as $vo) {
				//判断该客户资料是否已经删除到回收站
				if ($vo['Recycle']==0) {
					//将搜索的标为红色
					if ($b=$i % 2 == 0) { 
						$tr2 = 'tr2';
					}else {
						$tr2 = '';
					}
					$html .= "
						<tr class='tr ".$tr2."'>
							<td class='tc'><input type='checkbox' class='delid' value='".$vo['ID']."' /></td>
							<td class='tc'>".$vo['ContactName']."</td>
							<td class='tc'>".$vo['Sex']."</td>
							<td class='tc'>".$vo['Phone']."</td>
							<td class='tc'>".$vo['Tel']."</td>
							<td class='tc'>".$vo['Qq']."</td>
							<td class='tc'>".$vo['Post']."</td>
							<td class='tc'>".$this->Beautifytime($vo['FinalTime'])."</td>
							<td class='tc fixed_w'><a href='".C('TMPL_PARSE_STRING.__APP__')."/Goods/openshare_contact_detail?id=".$vo['ID']."' class='edit'>查看详细</a></td>
						</tr>
					";
					$i++;
				}
			}
			$data = array('s'=>'ok','html'=>$html,'page'=>'<span class="page">'.$show.'</span>');
			echo json_encode($data);
		}else {
			$html = "<tr class='tr'><td class='tc' colspan='9'>暂无数据，等待添加～！</td></tr>";
			$data = array('s'=>'no','html'=>$html);
			echo json_encode($data);
		}
	}
	//共享查看联系人的详细信息
	public function openshare_contact_detail() {
		//验证用户权限
		parent::win_userauth(73);
		$id = I('get.id','');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		//下拉菜单数据
		$dmenu = M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
		//查出相应数据
		$contact = D('Contact');
		$result = $contact->relation(true)->where("ID = $id AND Recycle = 0")->find();
		if (!$result) {
			parent::operating(__ACTION__,1,'数据不存在');
			$this->content='不存在你要修改的数据，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$this->assign('volist',$volist);
		$this->assign('result',$result);
		$this->display('opensharecontactdetail');
	}
	*/
	//新增客户
	public function goodsadd() {
		parent::userauth2(88);
		$dmenu=M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
		$this->assign('volist',$volist);
		$this->display('goodsadd');
	}
	/*
	//导入客户
	public function goodsimport() {
	    parent::win_userauth(66);
	    $dmenu=M('dmenu');
	    $volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
	    $this->assign('volist',$volist);
	    $this->display('goodsimport');
	}
	*/
	//添加处理
	public function goodsadd_do() {
		//验证用户权限
		parent::win_userauth(88);
		if ($this->isPost()) {
			$data=array();
			$cont=array();
			$data['goods_name']  = I('post.goods_name','','htmlspecialchars');
			$data['goods_sn']    = I('post.goods_sn','','htmlspecialchars');
			$data['price']       = I('post.price','','htmlspecialchars');
			$data['barcode']     = I('post.barcode','','htmlspecialchars');
			$data['introducer_name']     = I('post.introducer_name','','htmlspecialchars');  
			$data['Introducer_tel']     = I('post.Introducer_tel','','htmlspecialchars');
			$data['consignee_name']     = I('post.consignee_name','','htmlspecialchars');
			$data['consignee_tel']     = I('post.consignee_tel','','htmlspecialchars');
			$delivery_time    = I('post.delivery_time','','htmlspecialchars');
			$data['delivery_time'] = $delivery_time ? strtotime($delivery_time) : time();
			$data['create_time']     = time();
			//自动完成验证与新增
			$goods=D('Goods');
			if ($goods->create($data)) {
			    $goods->add();
				parent::operating(__ACTION__,0,'新增商品：'.$data['goods_sn']);
				$this->success('添加成功',__APP__.'/Goods/Goodsadd',3);
			}else {
				parent::operating(__ACTION__,1,'新增失败：'.$goods->getError());
				$this->error($goods->getError());
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	/*
	//添加处理
	public function goodsimport_do() {
	    //验证用户权限
	    parent::win_userauth(86);
	    if ($this->isPost()) {
	        $cont=array();
	        vendor('PHPExcel.PHPExcel');
	        $PHPExcel  = new PHPExcel();
	        $PHPReader = new PHPExcel_Reader_Excel2007();

	        $name      = $_FILES['goods_info']['tmp_name'];
	        if(!$PHPReader->canRead($name)){
	            $PHPReader = new PHPExcel_Reader_Excel5();
	            if(!$PHPReader->canRead($name)){
	                return array('status'=>0,'message'=>'导入失败','data'=>'');
	            }
	        }
	        $PHPExcel     = $PHPReader->load($name);
	        $currentSheet = $PHPExcel->getSheet(0);
	        $highestRow   = $currentSheet->getHighestRow();
	        $contents     = array();
	        for($row=2;$row<=$highestRow;$row++){
	            $data = array();
	            $data['CompanyName']   = trim($currentSheet->getCellByColumnAndRow(0, $row)->getValue());
	            $data['Address']       = trim($currentSheet->getCellByColumnAndRow(1, $row)->getValue());
	            $data['ZipCode']       = trim($currentSheet->getCellByColumnAndRow(2, $row)->getValue());
	            $data['WebUrl']        = trim($currentSheet->getCellByColumnAndRow(3, $row)->getValue());
	            $data['Industry']      = trim($currentSheet->getCellByColumnAndRow(4, $row)->getValue());
	            $data['GoodsType']    = trim($currentSheet->getCellByColumnAndRow(5, $row)->getValue());
	            $data['GoodsLevel']   = trim($currentSheet->getCellByColumnAndRow(6, $row)->getValue());
	            $data['GoodsSource']  = trim($currentSheet->getCellByColumnAndRow(7, $row)->getValue());
	            $data['FollowUp']      = trim($currentSheet->getCellByColumnAndRow(8, $row)->getValue());
	            $data['Wast']          = trim($currentSheet->getCellByColumnAndRow(9, $row)->getValue());
	            $data['Intent']        = trim($currentSheet->getCellByColumnAndRow(10, $row)->getValue());
	            $data['MainItems']     = trim($currentSheet->getCellByColumnAndRow(11, $row)->getValue());
	            $data['Message']       = trim($currentSheet->getCellByColumnAndRow(12, $row)->getValue());

	            $data['ContactName']   = trim($currentSheet->getCellByColumnAndRow(13, $row)->getValue());
	            $data['Sex']           = trim($currentSheet->getCellByColumnAndRow(14, $row)->getValue());
	            $data['Post']          = trim($currentSheet->getCellByColumnAndRow(16, $row)->getValue());
	            $data['Qq']            = trim($currentSheet->getCellByColumnAndRow(17, $row)->getValue());
	            $data['Skype']         = trim($currentSheet->getCellByColumnAndRow(22, $row)->getValue());
	            $data['Alww']          = trim($currentSheet->getCellByColumnAndRow(23, $row)->getValue());
	            $data['Phone']         = trim($currentSheet->getCellByColumnAndRow(18, $row)->getValue());
	            $data['Tel']           = trim($currentSheet->getCellByColumnAndRow(19, $row)->getValue());
	            $data['Fax']           = trim($currentSheet->getCellByColumnAndRow(21, $row)->getValue());
	            $data['Email']         = trim($currentSheet->getCellByColumnAndRow(20, $row)->getValue());
	            $data['Birthday']      = trim($currentSheet->getCellByColumnAndRow(15, $row)->getValue());
	            
	            if($data['CompanyName'] =="")continue;
	            $contents[] = $data;
	        }
	        //echo "<pre>";print_r($contents);exit;
	        foreach ($contents as $k=>$v){
	            $cont = $cli = array();
	            //自动完成验证与新增
	            $goods=D('Goods');
	            $cli['CompanyName']    = $v['CompanyName'];
	            $cli['Address']        = $v['Address'];
	            $cli['ZipCode']        = $v['ZipCode'];
	            $cli['Industry']       = $v['Industry'];
	            $cli['GoodsType']     = $v['GoodsType'];
	            $cli['GoodsLevel']    = $v['GoodsLevel'];
	            $cli['GoodsSource']   = $v['GoodsSource'];
	            $cli['FollowUp']       = $v['FollowUp'];
	            $cli['Wast']           = $v['Wast'];
	            $cli['Intent']         = $v['Intent'];
	            $cli['MainItems']      = $v['MainItems'];
	            $cli['Message']        = $v['Message'];

	            if ($goods->create($cli)) {
	                //添加联系人
	                $cid = $goods->order('ID desc')->getField('ID')+1;
	                $cont['Cid'] = $cid++;
	                $cont['ContactName']   = $v['ContactName'];
	                $cont['Sex']           = $v['Sex'];
	                $cont['Post']          = $v['Post'];
	                $cont['Qq']            = $v['Qq'];
	                $cont['Skype']         = $v['Skype'];
	                $cont['Alww']          = $v['Alww'];
	                $cont['Phone']         = $v['Phone'];
	                $cont['Tel']           = $v['Tel'];
	                $cont['Fax']           = $v['Fax'];
	                $cont['Email']         = $v['Email'];
	                $cont['Birthday']      = $v['Birthday'];
	                 
	                $contact=D('Contact');
	                if ($contact->create($cont)) {
	                    $id = $goods->add();
	                    $cid = $contact->add();
	                    $goods->where("ID=$id")->Save(array('Cid' => $cid));
	                }else {
	                    $this->error($contact->getError());
	                }
	                parent::operating(__ACTION__,0,'导入客户：'.$data['CompanyName']);
	            }else {
	                parent::operating(__ACTION__,1,'新增失败：'.$goods->getError());
	                $this->error($goods->getError());
	            }
	        }
	        $this->success('导入成功',__APP__.'/Goods/goodsimport',3);
	    }else {
	        parent::operating(__ACTION__,1,'非法请求');
	        $this->error('非法请求');
	    }
	}
	*/
	
	//修改订单信息
	public function goodsedit() {
		parent::win_userauth(89);
		$id = I('get.id','');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$uid = $_SESSION['ThinkUser']['ID'];
		//下拉菜单数据
		$dmenu = M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
		//查出相应数据
		$goods = D('Goods');
		$result = $goods->where("goods_id = $id ")->find();
		if (!$result) {
			parent::operating(__ACTION__,1,'没有找到数据：'.$id);
			$this->content='不存在你要修改的数据，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$this->assign('volist',$volist);
		$this->assign('result',$result);
		$this->display('goodsedit');
	}
	//修改客户资料处理
	public function goodsedit_do() {
		//验证用户权限
		parent::win_userauth(89);
		if ($this->isPost()) {
			$data=array();
			//客户资料信息
			$data['goods_id']   = I('post.ID','');
			$data['goods_name']  = I('post.goods_name','','htmlspecialchars');
			$data['goods_sn']    = I('post.goods_sn','','htmlspecialchars');
			$data['price']       = I('post.price','','htmlspecialchars');
			$data['barcode']     = I('post.barcode','','htmlspecialchars');
			$data['introducer_name']     = I('post.introducer_name','','htmlspecialchars');  
			$data['Introducer_tel']     = I('post.Introducer_tel','','htmlspecialchars');
			$data['consignee_name']     = I('post.consignee_name','','htmlspecialchars');
			$data['consignee_tel']     = I('post.consignee_tel','','htmlspecialchars');
			$delivery_time    = I('post.delivery_time','','htmlspecialchars');
			$data['delivery_time'] = strtotime($delivery_time);
			$data['create_time']     = time();
			$goods = M('Goods');
			if ($goods->create($data)) {
				$goods->save();
				parent::operating(__ACTION__,0,'更新商品：'.$data['goods_sn']);
				$this->success('商品更新成功',__APP__.'/Goods/goodsedit?id='.$data['goods_id']);
			}else {
				parent::operating(__ACTION__,1,'更新失败：'.$data['CompanyName']);
				$this->error($goods->getError());
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//删除客户资料到回收站
	public function goods_del() {
		parent::userauth(90);
		//判断是否是ajax请求
		if ($this->isAjax()) {
			$id=I('post.id','');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(__ACTION__,1,'参数错误');
				R('Public/errjson',array('参数ID类型错误'));
			}else {
				$id=intval($id);
				$goods=M('Goods');
				$where=array('goods_id'=>$id);
				if ($goods->where($where)->save(array('Recycle' => 1))) {
					parent::operating(__ACTION__,0,'删除成功：'.$id);
					R('Public/errjson',array('ok'));
				}else {
					parent::operating(__ACTION__,1,'删除失败：'.$this->getError());
					R('Public/errjson',array($this->getError()));
				}
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//批量删除客户资料到回收站
	public function goods_indel() {
		//验证用户权限
		parent::userauth(90);
		if ($this->isAjax()) {
			if (!$delid=explode(',',I('post.delid',''))) {
				R('Public/errjson',array('请选中后再删除'));
			}
			//将最后一个元素弹出栈
			array_pop($delid);
			$id=join(',',$delid);
			$goods=M('Goods');
			$map['goods_id'] = array('in',$id);
			if ($goods->where($map)->save(array('Recycle' => 1))) {
				parent::operating(__ACTION__,0,'删除成功：'.$delid);
				R('Public/errjson',array('ok'));
			}else {
				echo $goods->getLastSql();die;
				parent::operating(__ACTION__,1,'删除失败：'.$delid);
				R('Public/errjson',array('删除失败'));
			}
			
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	
	//联系人管理
	public function contact() {
		parent::userauth2(85);
		$this->display();
	}
	//时间美化函数
	protected function Beautifytime($dtime) {
		$dtime = strtotime($dtime);
		$betime = time()-$dtime;
		$timename='';
		switch($betime) {
			case ($betime < 60):
				$timename = floor($betime).'秒前';
				break;
			case ($betime < 3600 && $betime > 60):
				$timename = floor(($betime/60)).'分钟前';
				break;
			case ($betime < 86400 && $betime > 3600):
				$timename = floor(($betime/60/60)).'小时前';
				break;
			case ($betime < 2592000 && $betime > 86400):
				$timename = floor(($betime/60/60/30)).'天前';
				break;
			case ($betime < 31536000 && $betime > 2592000):
				$timename = floor(($betime/60/60/30/12)).'个月前';
				break;
			case ($betime < 3153600000 && $betime > 31536000):
				$timename = floor(($betime/60/60/30/12/100)).'年前';
				break;
		}
		return $timename;
	}
	//客户资料Ajax请求
	public function contactajax() {
		$keyword = I('get.keyword','','htmlspecialchars');
		$Cid = I('get.Cid','','htmlspecialchars');
		$contact = D('Contact');
		import('ORG.Util.Page');						// 导入分页类
		$where['Recycle'] = 0;
		$where['Uid'] = $_SESSION['ThinkUser']['ID'];
		if (is_numeric($keyword)) {
			$where['Phone']  = array('LIKE',"%$keyword%");
		}else {
			$where['ContactName']  = $keyword;
		}
		if ($Cid!='' && is_numeric($Cid)) {
			$where['Cid']  = intval($Cid);
		}
		$count = $contact->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $contact->relation(true)->where($where)->order('FinalTime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['Post'] == $dlist[$j]['ID']) {
						$volist[$i]['Post'] = $dlist[$j]['MenuName'];
					}
				}
			}
			//输出数据
			$i=1;
			foreach($volist as $vo) {
				//判断该客户资料是否已经删除到回收站
				if ($vo['Recycle']==0) {
					//将搜索的标为红色
					$ContactName = str_replace($keyword,'<font>'.$keyword.'</font>',$vo['ContactName']);		//客户名称
					$phone = str_replace($keyword,'<font>'.$keyword.'</font>',$vo['Phone']);
					if ($b=$i % 2 == 0) { 
						$tr2 = 'tr2';
					}else {
						$tr2 = '';
					}
					$html .= "
						<tr class='tr ".$tr2."'>
							<td class='tc'><input type='checkbox' class='delid' value='".$vo['ID']."' /></td>
							<td class='tc'>".$vo['ID']."</td>
							<td><a title='点击只显示此公司的联系人' class='compyname' href='".C('TMPL_PARSE_STRING.__APP__')."/Goods/contactajax?Cid=".$vo['Cid']."'>".$vo['CompanyName']."</a><img class='add' src='".C('TMPL_PARSE_STRING.__IMAGE__')."/icon.png' alt='".C('TMPL_PARSE_STRING.__APP__')."/With/withadd?Cid=".$vo['Cid']."' border='0' title='新增跟单' /></td>
							<td class='tc'>".$ContactName."</td>
							<td class='tc'>".$vo['Sex']."</td>
							<td class='tc'>".$phone."</td>
							<td class='tc'>".$vo['Tel']."</td>
							<td class='tc'>".$vo['Qq']."</td>
							<td>".$vo['Email']."</td>
							<td class='tc'>".$vo['Post']."</td>
							<td class='tc'>".$this->Beautifytime($vo['FinalTime'])."</td>
							<td class='tc fixed_w'><a href='".$vo['ID']."' class='edit'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/edit.png' border='0' title='查看详细/修改' /></a><a href='".$vo['ID']."' class='del'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/delete.png' border='0' title='删除' /></a></td>
						</tr>
					";
					$i++;
				}
			}
			$data = array('s'=>'ok','html'=>$html,'page'=>'<span class="page">'.$show.'</span>');
			echo json_encode($data);
		}else {
			$html = "<tr class='tr'><td class='tc' colspan='12'>暂无数据，等待添加～！</td></tr>";
			$data = array('s'=>'no','html'=>$html);
			echo json_encode($data);
		}
	}
	//窗口联系人管理
	public function wincontact() {
		parent::win_userauth2(85);
		$Cid = I('get.Cid','');
		if ($Cid=='' || !is_numeric($Cid)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->error('参数不正确');
		}
		$this->assign('Cid',$Cid);
		$this->display();
	}
	//窗口客户资料Ajax请求
	public function wincontactajax() {
		$Cid = I('get.Cid','','htmlspecialchars');
		$contact = D('Contact');
		import('ORG.Util.Page');						// 导入分页类
		$where['Recycle'] = 0;
		$where['Uid'] = $_SESSION['ThinkUser']['ID'];
		if ($Cid!='' && is_numeric($Cid)) {
			$where['Cid']  = intval($Cid);
		}
		$count = $contact->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $contact->relation(true)->where($where)->order('FinalTime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['Post'] == $dlist[$j]['ID']) {
						$volist[$i]['Post'] = $dlist[$j]['MenuName'];
					}
				}
			}
			//输出数据
			$i=1;
			foreach($volist as $vo) {
				//判断该客户资料是否已经删除到回收站
				if ($vo['Recycle']==0) {
					//将搜索的标为红色
					if ($b=$i % 2 == 0) { 
						$tr2 = 'tr2';
					}else {
						$tr2 = '';
					}
					$html .= "
						<tr class='tr ".$tr2."'>
							<td class='tc'><input type='checkbox' class='delid' value='".$vo['ID']."' /></td>
							<td class='tc'>".$vo['ContactName']."</td>
							<td class='tc'>".$vo['Sex']."</td>
							<td class='tc'>".$vo['Phone']."</td>
							<td class='tc'>".$vo['Tel']."</td>
							<td class='tc'>".$vo['Qq']."</td>
							<td class='tc'>".$vo['Post']."</td>
							<td class='tc'>".$this->Beautifytime($vo['FinalTime'])."</td>
							<td class='tc fixed_w'><a href='".$vo['ID']."' class='edit'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/edit.png' border='0' title='查看详细/修改' /></a><a href='".$vo['ID']."' class='del'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/delete.png' border='0' title='删除' /></a></td>
						</tr>
					";
					$i++;
				}
			}
			$data = array('s'=>'ok','html'=>$html,'page'=>'<span class="page">'.$show.'</span>');
			echo json_encode($data);
		}else {
			$html = "<tr class='tr'><td class='tc' colspan='9'>暂无数据，等待添加～！</td></tr>";
			$data = array('s'=>'no','html'=>$html);
			echo json_encode($data);
		}
	}
	//新增联系人
	public function contactadd() {
		parent::userauth2(69);
		$Cid = I('get.Cid','');
		if ($Cid=='' || !is_numeric($Cid)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		//下拉菜单数据
		$dmenu = M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();	
		$goods = M('Goods');
		$result = $goods->where("ID = $Cid AND Uid=".$_SESSION['ThinkUser']['ID'])->find();
		$this->assign('volist',$volist);
		$this->assign('result',$result);
		$this->display('contactadd');
	}
	//新增联系人处理
	public function contactadd_do() {
		parent::userauth2(69);
		if ($this->isPost()) {
			$cont=array();		
			$cont['Cid'] = I('post.Cid','','htmlspecialchars');	
			$cont['ContactName'] = I('post.ContactName','','htmlspecialchars');
			$cont['Sex'] = I('post.Sex','','htmlspecialchars');
			$cont['Birthday'] = I('post.Birthday','','htmlspecialchars');
			$cont['Post'] = I('post.Post','','htmlspecialchars');
			$cont['Qq'] = I('post.Qq','','htmlspecialchars');
			$cont['Phone'] = I('post.Phone','','htmlspecialchars');
			$cont['Tel'] = I('post.Tel','','htmlspecialchars');
			$cont['Email'] = I('post.Email','','htmlspecialchars');
			$cont['Fax'] = I('post.Fax','','htmlspecialchars');
			$cont['Skype'] = I('post.Skype','','htmlspecialchars');
			$cont['Alww'] = I('post.Alww','','htmlspecialchars');
			$cont['Content'] = I('post.Content','','htmlspecialchars');
			//自动完成验证与新增
			$contact=D('Contact');
			if ($contact->create($cont)) {
				$contact->add();
				parent::operating(__ACTION__,0,'新增成功：'.$cont['ContactName']);
				$this->success('添加成功',__APP__.'/Goods/contactadd?Cid='.$cont['Cid'],3);
			}else {
				parent::operating(__ACTION__,1,'新增失败：'.$contact->getError());
				$this->error($contact->getError());
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//修改联系人信息
	public function contactedit() {
		parent::userauth2(70);
		$id = I('get.id','');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$uid = $_SESSION['ThinkUser']['ID'];
		//下拉菜单数据
		$dmenu = M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
		//查出相应数据
		$contact = D('Contact');
		$result = $contact->relation(true)->where("ID = $id AND Recycle = 0 AND Uid = $uid")->find();
		if (!$result) {
			parent::operating(__ACTION__,1,'没有找到数据：'.$id);
			$this->content='不存在你要修改的数据，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$this->assign('volist',$volist);
		$this->assign('result',$result);
		$this->display('contactedit');
	}
	//修改联系人信息处理
	public function contactedit_do() {
		parent::userauth2(70);
		if ($this->isPost()) {
			$cont=array();		
			$cont['ID'] = I('post.ID','','htmlspecialchars');	
			$cont['ContactName'] = I('post.ContactName','','htmlspecialchars');
			$cont['Sex'] = I('post.Sex','','htmlspecialchars');
			$cont['Birthday'] = I('post.Birthday','','htmlspecialchars');
			$cont['Post'] = I('post.Post','','htmlspecialchars');
			$cont['Qq'] = I('post.Qq','','htmlspecialchars');
			$cont['Phone'] = I('post.Phone','','htmlspecialchars');
			$cont['Tel'] = I('post.Tel','','htmlspecialchars');
			$cont['Email'] = I('post.Email','','htmlspecialchars');
			$cont['Fax'] = I('post.Fax','','htmlspecialchars');
			$cont['Skype'] = I('post.Skype','','htmlspecialchars');
			$cont['Alww'] = I('post.Alww','','htmlspecialchars');
			$cont['Content'] = I('post.Content','','htmlspecialchars');
			//自动完成验证与新增
			$contact=D('Contact');
			if ($contact->create($cont)) {
				$contact->save();
				parent::operating(__ACTION__,0,'更新成功：'.$cont['ContactName']);
				$this->success('修改成功',__APP__.'/Goods/contactedit?id='.$cont['ID'],3);
			}else {
				parent::operating(__ACTION__,1,'更新失败：'.$contact->getError());
				$this->error($contact->getError());
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//删除联系人到
	public function contact_del() {
		parent::userauth2(71);
		//判断是否是ajax请求
		if ($this->isAjax()) {
			$id=I('post.id','');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(__ACTION__,1,'参数错误：'.$id);
				R('Public/errjson',array('参数ID类型错误'));
			}else {
				$id=intval($id);
				$goods = M('Goods');
				if ($goods->where("Cid=$id")->find()) {
					parent::operating(__ACTION__,1,'不能删除该公司/客户的最后一个联系人：'.$id);
					R('Public/errjson',array('不能删除该公司/客户的最后一个联系人'));
				}
				$contact=M('contact');
				$where=array('ID'=>$id);
				if ($contact->where($where)->getField('ID')) {
					$contact->where($where)->delete();
					parent::operating(__ACTION__,0,'删除成功：'.$id);
					R('Public/errjson',array('ok'));
				}else {
					parent::operating(__ACTION__,1,'删除失败：'.$contact->getError());
					R('Public/errjson',array($contact->getError()));
				}
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//批量删除联系人
	public function contact_indel() {
		//验证用户权限
		parent::userauth2(71);
		if ($this->isAjax()) {
			if (!$delid=explode(',',I('post.delid',''))) {
				R('Public/errjson',array('请选中后再删除'));
			}
			//将最后一个元素弹出栈
			array_pop($delid);
			$goods = M('Goods');
			$contact=M('contact');
			foreach($delid as $val) {
				$where=array('ID'=>$val);
				if ($goods->where("Cid=$val")->find()) {
					parent::operating(__ACTION__,1,'不能删除一个公司/客户的初始联系人：');
					R('Public/errjson',array('不能删除一个公司/客户的初始联系人'));
				}else {	
					$contact->where($where)->delete();
				}
			}
			parent::operating(__ACTION__,0,'删除成功：'.join(',',$delid));
			R('Public/errjson',array('ok'));
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//回收站
	public function recycle() {
		parent::userauth2(74);
		$this->display();
	}
	//已经删除数据请求
	public function recycleajax() {
		$keyword = I('get.keyword','','htmlspecialchars');
		$goods = D('Goods');
		import('ORG.Util.Page');						// 导入分页类
		$where['Recycle'] = 1;
		$where['Uid'] = $_SESSION['ThinkUser']['ID'];
		$where['CompanyName'] = $keyword;
		$count = $goods->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $goods->relation(true)->where($where)->order('FinalTime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['GoodsType'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsType'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['Industry'] == $dlist[$j]['ID']) {
						$volist[$i]['Industry'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['GoodsLevel'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsLevel'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['GoodsSource'] == $dlist[$j]['ID']) {
						$volist[$i]['GoodsSource'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['FollowUp'] == $dlist[$j]['ID']) {
						$volist[$i]['FollowUp'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['Intent'] == $dlist[$j]['ID']) {
						$volist[$i]['Intent'] = $dlist[$j]['MenuName'];
					}
				}
			}
			//输出数据
			$i = 1;
			foreach($volist as $vo) {
				//将搜索的标为红色
				$company = str_replace($keyword,'<font>'.$keyword.'</font>',$vo['CompanyName']);		//公司名称
				if ($b=$i % 2 == 0) { 
					$tr2 = 'tr2';
				}else {
					$tr2 = '';
				}
				$html .= "
					<tr class='tr ".$tr2."'>
						<td class='tc'><input type='checkbox' class='delid' value='".$vo['ID']."' /></td>
						<td class='tc'>".$vo['ID']."</td>
						<td>".$company."</td>
						<td class='tc'>".$vo['ContactName']."</td>
						<td class='tc'>".$vo['GoodsType']."</td>
						<td>".$vo['Industry']."</td>
						<td>".$vo['GoodsLevel']."</td>
						<td class='tc'>".$vo['GoodsSource']."</td>
						<td class='tc'>".$vo['FollowUp']."</td>
						<td class='tc'>".$vo['Intent']."</td>
						<td class='tc'>".$vo['Username']."</td>
						<td class='tc'>".$this->Beautifytime($vo['FinalTime'])."</td>
						<td class='tc fixed_w'><a href='".$vo['ID']."' class='recy'>还原</a></td>
					</tr>
				";
				$i++;
			}
			$data = array('s'=>'ok','html'=>$html,'page'=>'<span class="page">'.$show.'</span>');
			echo json_encode($data);
		}else {
			$html = "<tr class='tr'><td class='tc' colspan='13'>暂无数据，等待添加～！</td></tr>";
			$data = array('s'=>'no','html'=>$html);
			echo json_encode($data);
		}
	}
	//还原操作
	public function reduction() {
		parent::userauth2(75);
		//判断是否是ajax请求
		if ($this->isAjax()) {
			$id=I('post.id','');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(__ACTION__,1,'参数错误');
				R('Public/errjson',array('参数ID类型错误'));
			}else {
				$id=intval($id);
				$goods = M('Goods');
				$contact=M('contact');
				$where=array('ID'=>$id);
				if ($goods->where($where)->getField('ID')) {
					$goods->where($where)->save(array('Recycle' => 0));
					$contact->where("Cid=$id")->save(array('Recycle' => 0));
					parent::operating(__ACTION__,0,'还原成功');
					R('Public/errjson',array('ok'));
				}else {
					parent::operating(__ACTION__,1,'还原失败');
					R('Public/errjson',array('数据不存在'));
				}
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//批量还原
	public function reduction_in() {
		//验证用户权限
		parent::userauth2(75);
		if ($this->isAjax()) {
			if (!$inid=explode(',',I('post.inid',''))) {
				R('Public/errjson',array('请选中后再删除'));
			}
			//将最后一个元素弹出栈
			array_pop($inid);
			$goods = M('Goods');
			$contact=M('contact');
			foreach($inid as $val) {
				$where=array('ID'=>$val);
				$goods->where($where)->save(array('Recycle' => 0));
				$contact->where("Cid=$val")->save(array('Recycle' => 0));
			}
			parent::operating(__ACTION__,0,'还原成功');
			R('Public/errjson',array('ok'));
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//批量删除
	public function goods_c_indel() {
		//验证用户权限
		parent::userauth2(76);
		if ($this->isAjax()) {
			if (!$delid=explode(',',I('post.delid',''))) {
				R('Public/errjson',array('请选中后再删除'));
			}
			//将最后一个元素弹出栈
			array_pop($delid);
			$id=join(',',$delid);
			$goods=M('Goods');
			$contact=M('contact');
			$co['Cid'] = array('in',$id);
			if ($goods->delete("$id")) {
				$contact->where($co)->delete();
				parent::operating(__ACTION__,0,'删除成功');
				R('Public/errjson',array('ok'));
			}else {
				parent::operating(__ACTION__,1,'删除失败');
				R('Public/errjson',array('删除失败'));
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			R('Public/errjson',array('非法请求'));
		}
	}
}
?>