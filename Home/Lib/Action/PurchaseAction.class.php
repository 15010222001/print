<?php
//客户管理类
class PurchaseAction extends CommonAction {
	public function index() {
		parent::userauth2(91);
		$this->display();
	}
	//客户资料Ajax请求
	public function indexajax() {
		$keyword = I('get.keyword','','htmlspecialchars');
		$purchase = D('Purchase_order');
		import('ORG.Util.Page');						// 导入分页类
		if($keyword!=""){
		    $where['order_sn'] = $keyword;
		}
		$count = $purchase->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $purchase->relation(true)->where($where)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['PurchaseType'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseType'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['PurchaseLevel'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseLevel'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['PurchaseSource'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseSource'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['warehouse_id'] == $dlist[$j]['ID']) {
						$volist[$i]['warehouse_id'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['supplier_id'] == $dlist[$j]['ID']) {
						$volist[$i]['supplier_id'] = $dlist[$j]['MenuName'];
					}
				}
			}
			//输出数据
			$i = 1;
			$purchase_order_status = C('purchase_order_status');
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
				if($vo['order_status'] !=3){
				    $str = "<a href='".$vo['order_id']."' class='edit'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/edit.png' border='0' title='查看/修改' /></a><a href='".$vo['order_id']."' class='del'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/delete.png' border='0' title='删除' /></a>";
				    $disabled  = "";
				}else{
				    $str = "";
				    $disabled  = "disabled='disabled'";
				}
				if(!parent::userauth3(100)){
				    $vo['order_amount'] = "未知";
				}
				$html .= "
					<tr class='tr ".$tr2."'>
						<td class='tc'><input type='checkbox' class='delid' {$disabled} value='".$vo['order_id']."' /></td>
						<td class='tc'><a href='".$vo['order_id']."' class='edit'>".$vo['order_sn']."</a></td>
						<td class='tc'>".$vo['supplier_id']."</td>
						<td class='tc'>".$vo['size']."</td>
						<td class='tc'>".$vo['quantity']."</td>
						<td class='tc'>".$vo['specification']."</td>
						<td class='tc'>".$purchase_order_status[$vo['order_status']]."</td>
						<td class='tc'>".$vo['add_time']."</td>
						<td class='tc'>".$vo['Username']."</td>
						<td class='tc'>".$vo['notes']."</td>
						<td class='tc fixed_w'>".$str."</td>
					</tr>
				";
				$i++;
			}
			$data = array('s'=>'ok','html'=>$html,'page'=>'<span class="page">'.$show.'</span>');
			echo json_encode($data);
		}else {
			$html = "<tr class='tr'><td class='tc' colspan='11'>暂无数据，等待添加～！</td></tr>";
			$data = array('s'=>'no','html'=>$html);
			echo json_encode($data);
		}
	}
	//打开或关闭共享请求
	public function opensharedo() {
		parent::userauth(72);
		$id = I('get.id','');
		$openshare = I('get.openshare','');
		$openshare = intval($openshare);
		$id = intval($id);
		$purchase = M('Purchase');
		if ($openshare==0) {
			$openshare = 1;
		}else {
			$openshare = 0;
		}
		if ($purchase->where("ID = $id")->save(array('OpenShare' => $openshare))) {
			parent::operating(__ACTION__,0,'共享客户编号为：'.$id.'的数据');
			R('Public/errjson',array('ok'));
		}else {
			parent::operating(__ACTION__,1,'共享失败：'.$purchase->getError());
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
		$purchase = D('Purchase');
		import('ORG.Util.Page');						// 导入分页类
		$where['Recycle'] = 0;
		$where['OpenShare'] = 1;
		if ($uid!='') {
			$where['Uid'] = intval($uid);
		}
		$where['CompanyName'] = $keyword;
		$count = $purchase->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $purchase->relation(true)->where($where)->order('FinalTime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['PurchaseType'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseType'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['Industry'] == $dlist[$j]['ID']) {
						$volist[$i]['Industry'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['PurchaseLevel'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseLevel'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['PurchaseSource'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseSource'] = $dlist[$j]['MenuName'];
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
						<td class='tc'>".$vo['PurchaseType']."</td>
						<td>".$vo['Industry']."</td>
						<td>".$vo['PurchaseLevel']."</td>
						<td class='tc'>".$vo['PurchaseSource']."</td>
						<td class='tc'>".$vo['FollowUp']."</td>
						<td class='tc'>".$vo['Intent']."</td>
						<td class='tc'><a class='uid' href='".C('TMPL_PARSE_STRING.__APP__')."/Purchase/openshareajax/?uid=".$vo['Uid']."'>".$vo['Username']."</a></td>
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
		$purchase = D('Purchase');
		$result = $purchase->relation(true)->where("ID = $id AND Recycle = 0 AND OpenShare = 1")->find();
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
							<td class='tc fixed_w'><a href='".C('TMPL_PARSE_STRING.__APP__')."/Purchase/openshare_contact_detail?id=".$vo['ID']."' class='edit'>查看详细</a></td>
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
	//新增客户
	public function purchaseadd() {
		parent::win_userauth(92);
		$dmenu=M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
		$this->assign('volist',$volist);
		$this->display('purchaseadd');
	}
	//导入客户
	public function purchaseimport() {
	    parent::win_userauth(66);
	    $dmenu=M('dmenu');
	    $volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
	    $this->assign('volist',$volist);
	    $this->display('purchaseimport');
	}
	//添加处理
	public function purchaseadd_do() {
		//验证用户权限
		parent::win_userauth(92);
		if ($this->isPost()) {
			$data=array();
			$cont=array();
			//echo "<pre>";print_r(M('base'));exit;
			$data['order_sn']      = D('Base')->create_fast_order_sn('purchase');
			$data['warehouse_id']  = I('post.warehouse_id','','htmlspecialchars');
			$data['supplier_id']   = I('post.supplier_id','','htmlspecialchars');
			$data['add_time']      = date('Y-m-d H:i:s');
			$data['add_user']      = $_SESSION['ThinkUser']['ID'];
			$data['notes']         = I('post.notes','','htmlspecialchars');

			//自动完成验证与新增
			$purchase=D('Purchase_order');
			if ($purchase->create($data)) {
			    $purchase->add();
			    parent::purchase($purchase->getLastInsID(),0,'新增采购单');
				parent::operating(__ACTION__,0,'新增采购单：'.$data['order_sn']);
				$this->success('添加成功',__APP__.'/Purchase/purchaseadd',3);
			}else {
			    echo $purchase->getError();exit;
				parent::operating(__ACTION__,1,'新增失败：'.$purchase->getError());
				$this->error($purchase->getError());
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//添加处理
	public function purchaseimport_do() {
	    //验证用户权限
	    parent::win_userauth(86);
	    if ($this->isPost()) {
	        $cont=array();
	        vendor('PHPExcel.PHPExcel');
	        $PHPExcel  = new PHPExcel();
	        $PHPReader = new PHPExcel_Reader_Excel2007();

	        $name      = $_FILES['Purchase_info']['tmp_name'];
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
	            $data['PurchaseType']    = trim($currentSheet->getCellByColumnAndRow(5, $row)->getValue());
	            $data['PurchaseLevel']   = trim($currentSheet->getCellByColumnAndRow(6, $row)->getValue());
	            $data['PurchaseSource']  = trim($currentSheet->getCellByColumnAndRow(7, $row)->getValue());
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
	            $purchase=D('Purchase');
	            $cli['CompanyName']    = $v['CompanyName'];
	            $cli['Address']        = $v['Address'];
	            $cli['ZipCode']        = $v['ZipCode'];
	            $cli['Industry']       = $v['Industry'];
	            $cli['PurchaseType']     = $v['PurchaseType'];
	            $cli['PurchaseLevel']    = $v['PurchaseLevel'];
	            $cli['PurchaseSource']   = $v['PurchaseSource'];
	            $cli['FollowUp']       = $v['FollowUp'];
	            $cli['Wast']           = $v['Wast'];
	            $cli['Intent']         = $v['Intent'];
	            $cli['MainItems']      = $v['MainItems'];
	            $cli['Message']        = $v['Message'];

	            if ($purchase->create($cli)) {
	                //添加联系人
	                $cid = $purchase->order('ID desc')->getField('ID')+1;
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
	                    $id = $purchase->add();
	                    $cid = $contact->add();
	                    $purchase->where("ID=$id")->Save(array('Cid' => $cid));
	                }else {
	                    $this->error($contact->getError());
	                }
	                parent::operating(__ACTION__,0,'导入客户：'.$data['CompanyName']);
	            }else {
	                parent::operating(__ACTION__,1,'新增失败：'.$purchase->getError());
	                $this->error($purchase->getError());
	            }
	        }
	        $this->success('导入成功',__APP__.'/Purchase/Purchaseimport',3);
	    }else {
	        parent::operating(__ACTION__,1,'非法请求');
	        $this->error('非法请求');
	    }
	}
	//修改客户资料
	public function purchaseedit() {
		parent::win_userauth(101);
		$id = I('get.id','');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$purchase_order_status = C('purchase_order_status');
		$uid = $_SESSION['ThinkUser']['ID'];
		//下拉菜单数据
		$dmenu = M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();
		//查出相应数据
		$purchase = D('Purchase_order');
		$result = $purchase->join(" tp_user ON tp_purchase_order.add_user = tp_user.ID ")->where("order_id = $id ")->find();
		if (!$result) {
			parent::operating(__ACTION__,1,'没有找到数据：'.$id);
			$this->content='不存在你要修改的数据，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$purchase_order_goods = D('Purchase_order_goods');
		$goods_list = $purchase_order_goods->join(' tp_goods ON tp_purchase_order_goods.goods_id = tp_goods.goods_id')->where("tp_purchase_order_goods.order_id = $id ")->field('tp_purchase_order_goods.*,tp_goods.goods_name , tp_purchase_order_goods.price*tp_purchase_order_goods.number AS sum_price')->select();
		//echo "<pre>";print_r($purchase_order_goods->getLastSql());exit;
		$purchase_order_status = C('purchase_order_status');
		$purchase_order_action = D('Purchase_order_action');
		$action_list = $purchase_order_action->where("order_id = $id ")->order('action_id DESC')->select();
		if(!parent::userauth3(100)){
		    $result['is_price'] = 0;
		}else{
		    $result['is_price'] = 1;
		}
		$this->assign('volist',$volist);
		$this->assign('goods_list',$goods_list);
		$this->assign('action_list',$action_list);
		$this->assign('purchase_order_status',$purchase_order_status);
		$this->assign('result',$result);
		$this->assign('purchase_order_status',$purchase_order_status);
		$this->display('purchaseedit');
	}
	//修改 保存 采购单处理
	public function purchaseedit_do() {
		//验证用户权限
		parent::win_userauth(93);
		if ($this->isPost()) {
			$data=array();
			//客户资料信息
			$data['order_id']      = I('post.orderID','');
			$data['supplier_id']   = I('post.supplier_id','','htmlspecialchars');
			$data['warehouse_id']  = I('post.warehouse_id','','htmlspecialchars');
			$data['notes']         = I('post.notes','','htmlspecialchars');
			$data['size']   = I('post.size','','htmlspecialchars');
			$data['quantity']  = I('post.quantity','','htmlspecialchars');
			$data['specification']  = I('post.quantity','','htmlspecialchars');
			$data['amount']  = I('post.quantity','','htmlspecialchars');
			$data['order_status'] = 2;
			$purchase = M('Purchase_order');
			if ($purchase->create($data)) {
				$purchase->save();
				parent::operating(__ACTION__,0,'更新采购单：'.$data['order_sn']);
				$this->success('采购单更新成功',__APP__.'/Purchase/Purchaseedit?id='.$data['order_id']);
			}else {
				parent::operating(__ACTION__,1,'更新失败：'.$data['order_sn']);
				$this->error($purchase->getError());
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//删除客户资料到回收站
	public function purchase_del() {
		parent::userauth(94);
		//判断是否是ajax请求
		if ($this->isAjax()) {
			$id=I('post.id','');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(__ACTION__,1,'参数错误');
				R('Public/errjson',array('参数ID类型错误'));
			}else {
				$id=intval($id);
				$purchase=M('Purchase_order');
				$where=array('order_id'=>$id);
				if ($purchase->where($where)->delete()) {
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
	public function purchase_indel() {
		//验证用户权限
		parent::userauth(94);
		if ($this->isAjax()) {
			if (!$delid=explode(',',I('post.delid',''))) {
				R('Public/errjson',array('请选中后再删除'));
			}
			//将最后一个元素弹出栈
			array_pop($delid);
			$id=join(',',$delid);
			$purchase=M('Purchase_order');
			$contact=M('contact');
			$map['ID'] = array('in',$id);
			$co['Cid'] = array('in',$id);
			if ($purchase->where($map)->save(array('Recycle' => 1))) {
				$contact->where($co)->save(array('Recycle' => 1));
				parent::operating(__ACTION__,0,'删除成功：'.$delid);
				R('Public/errjson',array('ok'));
			}else {
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
							<td><a title='点击只显示此公司的联系人' class='compyname' href='".C('TMPL_PARSE_STRING.__APP__')."/Purchase/contactajax?Cid=".$vo['Cid']."'>".$vo['CompanyName']."</a><img class='add' src='".C('TMPL_PARSE_STRING.__IMAGE__')."/icon.png' alt='".C('TMPL_PARSE_STRING.__APP__')."/With/withadd?Cid=".$vo['Cid']."' border='0' title='新增跟单' /></td>
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
	public function getgoodslist() {
		parent::win_userauth(92);
		$this->display();
	}
	//窗口客户资料Ajax请求
	public function wingoodsajax() {
	    $Cid = I('get.Cid','','htmlspecialchars');
	    $keyword = I('get.keyword','','htmlspecialchars');
	    $goods = D('Goods');
	    import('ORG.Util.Page');						// 导入分页类
	    if ($Cid!='' && is_numeric($Cid)) {
	        //$where['Cid']  = intval($Cid);
	    }
	    if($keyword!=""){
	        $where['goods_sn|goods_name'] = array('LIKE',"%$keyword%");
	    }
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
	    //echo "<pre>";print_r($goods->getLastSql());exit;
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
							<td class='tc'><input type='checkbox' class='delid' value='".$vo['goods_id']."' /></td>
							<td class='tc'>".$vo['goods_sn']."</td>
							<td class='tc'>".$vo['goods_name']."</td>
							<td class='tc'><input name='price' type='text' class='ctext' size='10'  value=''/></td>
							<td class='tc'><input name='number' type='text' class='ctext' size='10'  value=''/></td>
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
	//窗口联系人管理
	public function getpurchasegoodslist() {
	    parent::win_userauth(92);
	    $order_id = I('get.order_id','','htmlspecialchars');
	    $this->assign('order_id',$order_id);
	    $this->display();
	}
	//窗口客户资料Ajax请求
	public function wincontactajax() {
		$order_id = I('get.order_id','','htmlspecialchars');
		$goods = D('Purchase_order_goods');
		import('ORG.Util.Page');						// 导入分页类
		if ($Cid!='' && is_numeric($Cid)) {
			//$where['Cid']  = intval($Cid);
		}
		$where['order_id'] = $order_id;
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
		$volist = $goods->relation(true)->where($where)->order('order_goods_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		//echo "<pre>";print_r($goods->getLastSql());exit;
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
							<td class='tc'><input type='checkbox' class='delid' value='".$vo['goods_id']."' /></td>
							<td class='tc'>".$vo['goods_sn']."</td>
							<td class='tc'>".$vo['goods_name']."</td>
							<td class='tc'>".$vo['number']."</td>
							<td class='tc'>".$vo['enter_number']."</td>
							<td class='tc'>".($vo['number']-$vo['enter_number'])."</td>
							<td class='tc'><input name='number' type='text' class='ctext' size='10'  value=''/><input type='hidden' name='price' value=".$vo['price']." ></td>
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
		parent::win_userauth(69);
		$Cid = I('get.Cid','');
		if ($Cid=='' || !is_numeric($Cid)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		//下拉菜单数据
		$dmenu = M('dmenu');
		$volist=$dmenu->where('Sid <> 0')->order('Sortid asc')->select();	
		$purchase = M('Purchase');
		$result = $purchase->where("ID = $Cid AND Uid=".$_SESSION['ThinkUser']['ID'])->find();
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
				$this->success('添加成功',__APP__.'/Purchase/contactadd?Cid='.$cont['Cid'],3);
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
	public function goodsedit() {
		parent::win_userauth(93);
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
		$contact = D('Purchase_order_goods');
		$result = $contact->join(" tp_goods ON tp_purchase_order_goods.goods_id = tp_goods.goods_id ")->where("order_goods_id = $id")->field("tp_purchase_order_goods.*,tp_goods.goods_name")->find();
		if (!$result) {
			parent::operating(__ACTION__,1,'没有找到数据：'.$id);
			$this->content='不存在你要修改的数据，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$this->assign('volist',$volist);
		$this->assign('result',$result);
		$this->display('goodsedit');
	}
	//修改联系人信息处理
	public function goodsedit_do() {
		parent::userauth2(93);
		if ($this->isPost()) {
			$cont=array();		
			$cont['order_goods_id']  = I('post.ID','','htmlspecialchars');	
			$cont['price']           = I('post.price','','htmlspecialchars');	
			$cont['number']          = I('post.number','','htmlspecialchars');
			//自动完成验证与新增
			$purchase_goods=D('Purchase_order_goods');
			$purchase      =D('Purchase_order');
			$_data = $purchase_goods->where(array('order_goods_id'=>$cont['order_goods_id']))->find();
			if ($purchase_goods->create($cont)) {
				$purchase_goods->save();
				$purchase->updateOrderinfo($_data['order_id']);
				parent::operating(__ACTION__,0,'更新成功：'.$cont['order_goods_id']);
				$this->success('修改成功',__APP__.'/Purchase/goodsedit?id='.$cont['order_goods_id'],3);
			}else {
				parent::operating(__ACTION__,1,'更新失败：'.$purchase_goods->getError());
				$this->error($purchase_goods->getError());
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//删除联系人到
	public function goods_del() {
		parent::userauth(94);
		//判断是否是ajax请求
		if ($this->isAjax()) {
			$id=I('post.id','');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(__ACTION__,1,'参数错误：'.$id);
				R('Public/errjson',array('参数ID类型错误'));
			}else {
				$id=intval($id);
				$purchase_goods = M('Purchase_order_goods');
				$where = array('order_goods_id'=>$id);
				$_data = $purchase_goods->where($where)->find();
				if ($purchase_goods->where($where)->delete()) {
				    //echo $purchase->getLastSql();exit;
				    $purchase = D('Purchase_order');
				    $purchase->updateOrderinfo($_data['order_id']);
					parent::operating(__ACTION__,0,'删除成功：'.$id);
					R('Public/errjson',array('ok'));
				}else {
					parent::operating(__ACTION__,1,'删除失败：'.$purchase_goods->getError());
					R('Public/errjson',array($purchase_goods->getError()));
				}
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			$this->error('非法请求');
		}
	}
	//批量添加商品
	public function addgoods() {
		//验证用户权限
		parent::userauth(92);
		if ($this->isAjax()) {
		    //echo "<pre>";print_r($_REQUEST);exit;
			if (empty($_REQUEST['item'])) {
				R('Public/errjson',array('请选中后再添加'));
			}
			$purchase = D('Purchase_order');
			$purchase_goods = M('Purchase_order_goods');
			foreach($_REQUEST['item'] as $k=>$v){
			    $v['order_id'] = $_REQUEST['order_id'];
			    $result = $purchase_goods->add($v,array(),true);
			}
			$purchase->updateOrderinfo($_REQUEST['order_id']);
			//echo "<pre>";print_r($v);exit;
			if ($result) {
				parent::operating(__ACTION__,0,'添加成功');
				R('Public/errjson',array('ok'));
			}else {
				parent::operating(__ACTION__,1,'添加失败');
				R('Public/errjson',array('添加失败'));
			}
		}else {
			parent::operating(__ACTION__,1,'非法请求');
			R('Public/errjson',array('非法请求'));
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
		$purchase = D('Purchase');
		import('ORG.Util.Page');						// 导入分页类
		$where['Recycle'] = 1;
		$where['Uid'] = $_SESSION['ThinkUser']['ID'];
		$where['CompanyName'] = $keyword;
		$count = $purchase->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dmenu = M('dmenu');
		$dlist = $dmenu->order('Sortid asc')->select();
		$volist = $purchase->relation(true)->where($where)->order('FinalTime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		//判断有无数据
		if (count($volist) > 0) {
			//循环取出对应下拉菜单的数据
			for($i=0; $i<count($volist); $i++) {
				for($j=0; $j<count($dlist); $j++) {
					if ($volist[$i]['PurchaseType'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseType'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['Industry'] == $dlist[$j]['ID']) {
						$volist[$i]['Industry'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['PurchaseLevel'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseLevel'] = $dlist[$j]['MenuName'];
					}
					if ($volist[$i]['PurchaseSource'] == $dlist[$j]['ID']) {
						$volist[$i]['PurchaseSource'] = $dlist[$j]['MenuName'];
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
						<td class='tc'>".$vo['PurchaseType']."</td>
						<td>".$vo['Industry']."</td>
						<td>".$vo['PurchaseLevel']."</td>
						<td class='tc'>".$vo['PurchaseSource']."</td>
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
		parent::userauth(75);
		//判断是否是ajax请求
		if ($this->isAjax()) {
			$id=I('post.id','');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(__ACTION__,1,'参数错误');
				R('Public/errjson',array('参数ID类型错误'));
			}else {
				$id=intval($id);
				$purchase = M('Purchase');
				$contact=M('contact');
				$where=array('ID'=>$id);
				if ($purchase->where($where)->getField('ID')) {
					$purchase->where($where)->save(array('Recycle' => 0));
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
		parent::userauth(75);
		if ($this->isAjax()) {
			if (!$inid=explode(',',I('post.inid',''))) {
				R('Public/errjson',array('请选中后再删除'));
			}
			//将最后一个元素弹出栈
			array_pop($inid);
			$purchase = M('Purchase');
			$contact=M('contact');
			foreach($inid as $val) {
				$where=array('ID'=>$val);
				$purchase->where($where)->save(array('Recycle' => 0));
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
	public function Purchase_c_indel() {
		//验证用户权限
		parent::userauth(94);
		if ($this->isAjax()) {
			if (!$delid=explode(',',I('post.delid',''))) {
				R('Public/errjson',array('请选中后再删除'));
			}
			//将最后一个元素弹出栈
			array_pop($delid);
			$id=join(',',$delid);
			$purchase=M('Purchase');
			$contact=M('contact');
			$co['Cid'] = array('in',$id);
			if ($purchase->delete("$id")) {
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
	//确认订单
	public function confirm() {
	    parent::userauth(103);
	    //判断是否是ajax请求
	    if ($this->isAjax()) {
	        $order_id = I('post.orderID','');
	        if ($order_id=='' || !is_numeric($order_id)) {
	            parent::operating(__ACTION__,1,'参数错误：'.$id);
	            R('Public/errjson',array('参数ID类型错误'));
	        }else {
	            $purchase = M('Purchase_order');
	            $data['supplier_id']   = I('post.supplier_id','','htmlspecialchars');
	            $data['warehouse_id']  = I('post.warehouse_id','','htmlspecialchars');
	            $data['notes']         = I('post.notes','','htmlspecialchars');
	            $data['size']   = I('post.size','','htmlspecialchars');
	            $data['quantity']  = I('post.quantity','','htmlspecialchars');
	            $data['specification']  = I('post.quantity','','htmlspecialchars');
	            $data['amount']  = I('post.quantity','','htmlspecialchars');
	            $data['order_status'] = 1;
	            $where['order_id'] = $order_id;
	            
	            if ($purchase->where($where)->save($data)) {
	                //D('Warehouse_inventory')->purchaseConfirm($order_id);
	                parent::purchase($order_id,1,'确认采购单');
	                parent::operating(__ACTION__,0,'确认成功：'.$id);
	                R('Public/errjson',array('ok'));
	            }else {
	                parent::operating(__ACTION__,1,'确认失败：'.$purchase_goods->getError());
	                R('Public/errjson',array($purchase_goods->getError()));
	            }
	        }
	    }else {
	        parent::operating(__ACTION__,1,'非法请求');
	        $this->error('非法请求');
	    }
	}
	//确认订单
	public function unconfirm() {
	    parent::userauth(103);
	    //判断是否是ajax请求
	    if ($this->isAjax()) {
	        $order_id = I('post.order_id','');
	        if ($order_id=='' || !is_numeric($order_id)) {
	            parent::operating(__ACTION__,1,'参数错误：'.$id);
	            R('Public/errjson',array('参数ID类型错误'));
	        }else {
	            $purchase = M('Purchase_order');
	            $where['order_id'] = $order_id;
	            $data['order_status'] = 0;
	            if ($purchase->where($where)->save($data)) {
	                //D('Warehouse_inventory')->purchaseunConfirm($order_id);
	                parent::purchase($order_id,0,'取消确认采购单');
	                parent::operating(__ACTION__,0,'取消确认成功：'.$id);
	                R('Public/errjson',array('ok'));
	            }else {
	                parent::operating(__ACTION__,1,'取消确认失败：'.$purchase_goods->getError());
	                R('Public/errjson',array($purchase_goods->getError()));
	            }
	        }
	    }else {
	        parent::operating(__ACTION__,1,'非法请求');
	        $this->error('非法请求');
	    }
	}
	//入库订单
	public function purchase() {
	    parent::userauth(104);
	    //判断是否是ajax请求
	    if ($this->isAjax()) {
	        $order_id = I('post.order_id','');
	        if ($order_id=='' || !is_numeric($order_id)) {
	            parent::operating(__ACTION__,1,'参数错误：'.$id);
	            R('Public/errjson',array('参数ID类型错误'));
	        }else {
	            $purchase = M('Purchase_order');
	            $where['order_id'] = $order_id;
	            $data['order_status'] = 2;
	            if ($purchase->where($where)->save($data)) {
	                D('Warehouse_inventory')->purchaseIn($order_id);
	                parent::purchase($order_id,2,'采购单入库');
	                parent::operating(__ACTION__,0,'入库成功：'.$id);
	                R('Public/errjson',array('ok'));
	            }else {
	                parent::operating(__ACTION__,1,'入库失败：'.$purchase_goods->getError());
	                R('Public/errjson',array($purchase_goods->getError()));
	            }
	        }
	    }else {
	        parent::operating(__ACTION__,1,'非法请求');
	        $this->error('非法请求');
	    }
	}
	//完成订单
	public function complete() {
	    parent::userauth(105);
	    //判断是否是ajax请求
	    if ($this->isAjax()) {
	        $order_id = I('post.order_id','');
	        if ($order_id=='' || !is_numeric($order_id)) {
	            parent::operating(__ACTION__,1,'参数错误：'.$id);
	            R('Public/errjson',array('参数ID类型错误'));
	        }else {
	            $purchase = M('Purchase_order');
	            $where['order_id'] = $order_id;
	            $data['order_status'] = 4;
	            if ($purchase->where($where)->save($data)) {
	                parent::purchase($order_id,4,'采购单完成');
	                parent::operating(__ACTION__,0,'完成成功：'.$id);
	                R('Public/errjson',array('ok'));
	            }else {
	                parent::operating(__ACTION__,1,'完成失败：'.$purchase_goods->getError());
	                R('Public/errjson',array($purchase_goods->getError()));
	            }
	        }
	    }else {
	        parent::operating(__ACTION__,1,'非法请求');
	        $this->error('非法请求');
	    }
	}
}
?>