<?php 
/****
 * 
 * 上机印制
 */

class PrintAction extends CommonAction{
	
	public function  index(){
		parent::userauth2(121);
		$this->assign('title', C('common_title')['print_title']);
		$this->display();
	}
	
	public function printadd(){
		parent::userauth2(122);
		$order_result = $this->getOrderInfo();
		$action_user   = $_SESSION['ThinkUser']['Username'];
		$this->assign('action_user', $action_user);
		$this->assign('volist', $order_result);
		$this->display();
	}
	
	//添加
	public function print_do(){
		parent::userauth2(122);
		$print = D('print');
		$data['number'] = I('post.number', '', 'htmlspecialchars');
		$data['start_time'] = strtotime(I('post.start_time'));
		$data['end_time'] = strtotime(I('post.end_time'));
		$data['order_id'] = I('post.order_id');
		$data['notes'] = I('post.notes', '', 'htmlspecialchars');
		$data['Recycle'] = 0;
		
		if($print->create($data)){
			$print->add();
			parent::operating(__ACTION__,0,'新增成功：制版流程');
			$this->success('添加成功',__APP__.'/Platemak/platemakadd',3);
		}else {
			parent::operating(__ACTION__,1,'新增失败：'.$print->getError());
			$this->error($print->getError());
		}
	}
	
	public function indexajax(){
		$keyword = I('get.keyword','','htmlspecialchars');
		$print = D('print');
		import('ORG.Util.Page');						// 导入分页类
		if($keyword!=""){
			$where['order_sn'] = $keyword;
		}
		$where['Recycle'] = 0;
		$count = $print->where($where)->count();			//总记录数
		$Page = new Page($count,15);					//实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('header','条记录');
		$Page->setConfig('prev','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/prev.gif" border="0" title="上一页" />');
		$Page->setConfig('next','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/next.gif" border="0" title="下一页" />');
		$Page->setConfig('first','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/first.gif" border="0" title="第一页" />');
		$Page->setConfig('last','<img src="'.C('TMPL_PARSE_STRING.__IMAGE__').'/last.gif" border="0" title="最后一页" />');
		$show = $Page->show();							//分页显示输出
		$dlist = $print->where($where)->order('ID desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$html = '';
		$i = 1;
		if($count > 0){
			foreach($dlist as $vo) {
				//将搜索的标为红色
				$company = str_replace($keyword,'<font>'.$keyword.'</font>',$vo['CompanyName']);		//公司名称
				if ($i % 2 == 0) {
					$tr2 = 'tr2';
				}else {
					$tr2 = '';
				}
				$str = "<a href='".$vo['ID']."' class='edit'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/edit.png' border='0' title='查看/修改' /></a>
						<a href='".$vo['ID']."' class='del'><img src='".C('TMPL_PARSE_STRING.__IMAGE__')."/delete.png' border='0' title='删除' /></a>";
					
				$html .= "
						<tr class='tr ".$tr2."'>
							<td class='tc'><input type='checkbox' class='delid' value='".$vo['ID']."' /></td>
							<td class='tc'>".$vo['ID']."</td>
							<td class='tc'>".$this->getAuthInfo('Username', $vo['auth_id'])['Username']."</td>
							<td class='tc'>".$this->get_order_info($vo['order_id'])."</td>
							<td class='tc'>".$vo['number']."</td>
							<td class='tc'>".date('Y-m-d H:i:s', $vo['start_time'])."</td>
							<td class='tc'>".date('Y-m-d H:i:s', $vo['end_time'])."</td>
							<td class='tc'>".date('Y-m-d H:i:s', $vo['add_time'])."</td>
							<td class='tc'>".$vo['notes']."</td>
							<td class='tc fixed_w'>".$str."</td>
						</tr>
					";
				$i++;
			}
			$data = array('s'=>'ok','html'=>$html,'page'=>'<span class="page">'.$show.'</span>');
			echo json_encode($data);
		}else{
			$html = "<tr class='tr'><td class='tc' colspan='11'>暂无数据，等待添加～！</td></tr>";
			$data = array('s'=>'no','html'=>$html);
			echo json_encode($data);
		}
	}
	
	
	
	public function printedit(){
		parent::userauth2(123);
		$id = I('get.id');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$print = D('print');
		$where = array('ID' => $id);
		$info = $print->where($where)->find();
		$action_user   = $_SESSION['ThinkUser']['Username'];
		$order_result = $this->getOrderInfo();
		$this->assign('action_user', $action_user);
		$this->assign('order_result', $order_result);
		$this->assign('info', $info);
		$this->display();
	}
	
	//更新
	public function printedit_do(){
		parent::userauth2(123);
		$id = I('post.id');
		if ($id=='' || !is_numeric($id)) {
			parent::operating(__ACTION__,1,'参数错误');
			$this->content='参数ID类型错误，请关闭本窗口';
			exit($this->display('Public:err'));
		}
		$print = D('print');
		$data['number'] = I('post.number', '', 'htmlspecialchars');
		$data['start_time'] = strtotime(I('post.start_time'));
		$data['end_time'] = strtotime(I('post.end_time'));
		$data['order_id'] = I('post.order_id');
		$data['notes'] = I('post.notes', '', 'htmlspecialchars');
		$data['Recycle'] = 0;
		$data['ID'] = $id;
		if($print->create($data)){
			$print->save();
			parent::operating(__ACTION__,0,'更新成功');
			$this->success('添加成功',__APP__.'/Platemak/platemakadd',3);
		}else {
			parent::operating(__ACTION__,1,'更新失败：'.$print->getError());
			$this->error($print->getError());
		}
	}
	
	public function print_del(){
		parent::userauth(124);
		//判断是否是ajax请求
		if ($this->isAjax()) {
			$id=I('post.id','');
			if ($id=='' || !is_numeric($id)) {
				parent::operating(__ACTION__,1,'参数错误');
				R('Public/errjson',array('参数ID类型错误'));
			}else {
				$id=intval($id);
				$print = D('print');
				$where=array('ID'=>$id);
				if ($print->where($where)->save(array('Recycle' => 1))) {
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
	public function print_indel() {
		//验证用户权限
		parent::userauth(124);
		if ($this->isAjax()) {
			if (!$delid=explode(',',I('post.delid',''))) {
				R('Public/errjson',array('请选中后再删除'));
			}
			//将最后一个元素弹出栈
			array_pop($delid);
			$id=join(',',$delid);
			$print = D('print');
			$where['ID'] = array('in', $id);
			if ($print->where($where)->save(array('Recycle' => 1))) {
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
	
}
