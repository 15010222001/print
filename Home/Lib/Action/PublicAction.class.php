<?php
class PublicAction extends Action {
	public function verify() {
		import('ORG.Util.Image');
	    ob_clean();
		Image::buildImageVerify('4','3','png','84','38','verify');
	}
	public function err($content) {
		$this->display();
	}
	public function errjson($content) {
		$err=array('s'=>$content);
		exit(json_encode($err));
	}
	public function sha1pow($pow) {
		return sha1(md5($pow));
	}
	public function location($title,$url) {
		header('Content-Type: text/html; charset=utf-8');	//输出头，防止中文乱码
		echo '<script>alert("'.$title.'"); window.top.location="'.$url.'"</script>';
		exit;
	}
	//跟单提醒
	public function remind() {
		$with = D('With');
		$Uid = $_SESSION['ThinkUser']['ID'];
		$date = date('Y-m-d H:i:s');
		//总跟单数
		$withlist = $with->relation(true)->where("Uid = $Uid AND Status = 0 AND RemindTime <= '$date'")->select();
		//已完成跟单数
		$withcomplete = $with->where("Uid = $Uid AND Status = 0 AND Remind = 1 AND RemindTime <= '$date'")->select();
		$this->assign('withcount',count($withlist));
		$this->assign('withcomplete',count($withcomplete));
		$this->assign('withlist',$withlist);
		$this->display('Public/info');
	}
	//时间美化函数
	public function Beautifytime($dtime) {
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
}
?>