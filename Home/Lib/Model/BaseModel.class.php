<?php
//公共模型类
class BaseModel extends RelationModel {
    
	public function create_scm_order_sn($str_type, $prefix='', $length=4)
	{
		static    $arr_type  =  array('purchase','purchase_return','arrival', 'enter', 'outer', 'loss', 'rectify', 'return', 'move','returnorder','ycd','payment','ocean_plan','price');
		if(!in_array($str_type, $arr_type)) return false;
		$tb = sprintf("wh_%s_order", $str_type);
		
		if ($str_type == 'arrival') 
		    $tb = "ebay_iostore";
		elseif ($str_type == 'purchase')
			$tb = "ebay_iostore";
		elseif ($str_type == 'move')
			$tb = "ebay_iostore";
		elseif ($str_type == 'purchase_return')
			$tb = "sanvn_pur_return_order";
		elseif ($str_type == 'payment')
			$tb = "sanvn_payment_order";
		elseif ($str_type == 'returnorder')
			$tb = "sanvn_order_return";
		elseif ($str_type == 'rectify')
			$tb = "tp_rectify_order";
		elseif ($str_type == 'ycd')
			$tb = "sanvn_wh_move_order";
		elseif ($str_type == 'ocean_plan')
			$tb = "sanvn_ocean_plan_order";
		elseif ($str_type == 'outer')
			$tb = "tp_outer_order";
		elseif ($str_type == 'enter')
			$tb = "tp_enter_order";
		elseif ($str_type == 'return')
			$tb = sprintf("pur_%s_order", $str_type);
	    $sn = '';

	    while (empty($sn))
	    {
	        $sn     = empty($prefix) ? $this->generate_rand($length) : ($prefix . $this->generate_rand($length));
	        $data   = M($tb)->where("order_sn='{$sn}'")->select();
	        if (!empty($data))
	        {
	            $sn = '';
	        }
	    }
	    return $sn;	
	}
	//创建单据号
	public function create_fast_order_sn($str_type, $length=4){
		static    $arr_type  =  array('purchase', 'purchase_return', 'arrival', 'enter', 'return', 'outer', 'loss', 'rectify', 'move','returnorder','ycd','payment','ocean_plan','price');
		if(!in_array($str_type, $arr_type)) return false;
		switch ($str_type) {
			default:
				return false;
				break;	
			case 'purchase':
				$prefix = 'CG';
				break;
			case 'arrival':
				$prefix = 'DH';
				break;
			case 'enter':
				$prefix = 'RK';
				break;	
			case 'return':		
				$prefix = 'TH';
	            break;
			case 'outer':		
				$prefix = 'CK';
	            break;   
			case 'loss':		
				$prefix = 'YK';
	            break;    
	 		case 'rectify':		
				$prefix = 'TZ';
	            break;           
			case 'move':		
				$prefix = 'MV';
	            break;       
	        case 'purchase_return':		
				$prefix = 'TH';
	            break; 
	        case 'returnorder':		
				$prefix = 'THD';
	            break;
	        case 'ycd':		
				$prefix = 'YC';
	            break;    
	        case 'payment':		
				$prefix = 'FK';
	            break;
	        case 'ocean_plan':		
				$prefix = 'HYD';
	            break;
	        case 'price':		
				$prefix = 'JG';
	            break;            
		}
		return $this->create_scm_order_sn($str_type, $prefix.date('Ymd'), $length);	
	}
	public function generate_rand($length = 4)
	{
	    $chars = '0123456789';
	
	    for ($i = 0, $count = strlen($chars); $i < $count; $i++)
	    {
	        $arr[$i] = $chars[$i];
	    }
	
	    mt_srand((double) microtime() * 1000000);
	    shuffle($arr);
	    return substr(implode('', $arr), 0, $length);
	}
}
?>