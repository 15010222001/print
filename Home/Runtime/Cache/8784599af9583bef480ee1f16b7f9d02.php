<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>系统配置信息|<?php echo ($configcache['Title']); ?></title>
<link rel="stylesheet" type="text/css" href="__CSS__/content.css"  />
<link rel="stylesheet" type="text/css" href="__CSS__/public.css"  />
<script type="text/javascript" src="__JS__/jquery.js"></script>
<script type="text/javascript" src="__JS__/Public.js"></script>
</head>
<body>
<div id="content">
	<h1>首页 > 系统管理 > 系统配置</h1>
    <h2>
    	<div class="h2_left">
        	<a href="javascript:;" class="f5" onclick="f5();">刷新</a>
            <a href="javascript:history.back();" class="Retreat">后退</a>
            <a href="javascript:history.go(1);" class="Advance">前进</a>
        </div>
    </h2>
    <h3>
    	<a href="__APP__/System/systemwebsite">网站设置</a>
    	<a href="__APP__/System/systemconfig" class="h3a">系统配置</a>
        <a href="__APP__/System/systemcore">核心配置</a>
    </h3>
    <form action="__APP__/System/systemconfig_do" method="post" id="form">
    <dl id="cdl">
    	<dd>
        	<span class="dd_left">Tarce信息：</span>
            <span class="dd_right">
            	<label><input type="radio" value="true" name="trace" <?php if($config['tarce'] == true): ?>checked<?php endif; ?> /> 开启</label>
                <label><input type="radio" value="false" name="trace" <?php if($config['tarce'] == false): ?>checked<?php endif; ?> /> 关闭</label>
                <font>显示页面Tarce信息，包括（请求时间、当前页面、请求协议等等...）</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">调试模式：</span>
            <span class="dd_right">
            	<label><input type="radio" value="true" name="debug" <?php if($config['debug'] == true): ?>checked<?php endif; ?> /> 开启</label>
                <label><input type="radio" value="false" name="debug" <?php if($config['debug'] == false): ?>checked<?php endif; ?> /> 关闭</label>
                <font>应用调试模式</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">会话过期时间：</span>
            <span class="dd_right">
            	<select name="sessionuser" class="select">
                    <option value="600" <?php if($config['sessionuser'] == '600'): ?>selected<?php endif; ?>>10分钟</option>
                    <option value="1800" <?php if($config['sessionuser'] == '1800'): ?>selected<?php endif; ?>>30分钟</option>
                    <option value="3600" <?php if($config['sessionuser'] == '3600'): ?>selected<?php endif; ?>>1小时</option>
                </select><font>设置用户SESSION过期时间</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">日志记录：</span>
            <span class="dd_right">
            	<label><input type="radio" value="true" name="log" <?php if($config['log'] == true): ?>checked<?php endif; ?> /> 开启</label>
                <label><input type="radio" value="false" name="log" <?php if($config['log'] == false): ?>checked<?php endif; ?> /> 关闭</label>
                <font>开启日志记录</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">日志类型：</span>
            <span class="dd_right">
            	<label><input type="checkbox" value="EMERG" name="typelog[]" <?php if(in_array(($config['typelog']['EMERG']), is_array($config['typelog'])?$config['typelog']:explode(',',$config['typelog']))): ?>checked<?php endif; ?>/> EMERG</label>
                <label><input type="checkbox" value="ALERT" name="typelog[]" <?php if(in_array(($config['typelog']['ALERT']), is_array($config['typelog'])?$config['typelog']:explode(',',$config['typelog']))): ?>checked<?php endif; ?> /> ALERT</label>
                <label><input type="checkbox" value="CRIT" name="typelog[]" <?php if(in_array(($config['typelog']['CRIT']), is_array($config['typelog'])?$config['typelog']:explode(',',$config['typelog']))): ?>checked<?php endif; ?> /> CRIT</label>
                <label><input type="checkbox" value="ERR" name="typelog[]" <?php if(in_array(($config['typelog']['ERR']), is_array($config['typelog'])?$config['typelog']:explode(',',$config['typelog']))): ?>checked<?php endif; ?> /> ERR</label>
                <font>如果没有开启日志记录，选择将无效</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">URL模式：</span>
            <span class="dd_right">
            	<select name="url" class="select">
                    <option value="0" <?php if($config['url'] == '0'): ?>selected<?php endif; ?>>普通模式</option>
                    <option value="1" <?php if($config['url'] == '1'): ?>selected<?php endif; ?>>PATHINFO模式</option>
                    <option value="2" <?php if($config['url'] == '2'): ?>selected<?php endif; ?>>REWRITE模式</option>
                    <option value="3" <?php if($config['url'] == '3'): ?>selected<?php endif; ?>>兼容模式</option>
                </select><font>默认为PATHINFO模式</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">URL大小写：</span>
            <span class="dd_right">
            	<label><input type="radio" value="false" name="urlcase" <?php if($config['urlcase'] == false): ?>checked<?php endif; ?> /> 开启</label>
                <label><input type="radio" value="true" name="urlcase" <?php if($config['urlcase'] == true): ?>checked<?php endif; ?> /> 关闭</label>
                <font>URL大小写</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">令牌验证：</span>
            <span class="dd_right">
            	<label><input type="radio" value="true" name="tokenon" <?php if($config['tokenon'] == true): ?>checked<?php endif; ?> /> 开启</label>
                <label><input type="radio" value="false" name="tokenon" <?php if($config['tokenon'] == false): ?>checked<?php endif; ?> /> 关闭</label>
                <font>提交表单时是否开启令牌验证</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">重置令牌：</span>
            <span class="dd_right">
            	<label><input type="radio" value="true" name="tokenreset" <?php if($config['tokenreset'] == true): ?>checked<?php endif; ?> /> 是</label>
                <label><input type="radio" value="false" name="tokenreset" <?php if($config['tokenreset'] == false): ?>checked<?php endif; ?> /> 否</label>
                <font>令牌验证出错后是否重置令牌</font>
            </span>
        </dd>
        <dd>
        	<span class="dd_left">字段验证：</span>
            <span class="dd_right">
            	<label><input type="radio" value="true" name="dbfieldcheck" <?php if($config['dbfieldcheck'] == true): ?>checked<?php endif; ?> /> 开启</label>
                <label><input type="radio" value="false" name="dbfieldcheck" <?php if($config['dbfieldcheck'] == false): ?>checked<?php endif; ?> /> 关闭</label>
                <font>是否开启字段类型验证</font>
            </span>
        </dd>
        <dd><input type="submit" name="submit" value="提交" class="submit" /></dd>
    </dl>
    </form>
</div>
</body>
</html>