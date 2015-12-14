<?php
require_once('./WeixinGather.class.php');
$weixinGatherObj = new WeixinGather(array(
	'username' => '****',  #微信公众平台-账号
	'pwd'      => '****' #微信公众平台-密码
));
$weixinGatherObj->set_article(array('start' => 0, 'count' => 15));
$weixinGatherObj->get_contents();
//var_dump($weixinGatherObj->results);
if (is_array($weixinGatherObj->results)){
	foreach ($weixinGatherObj->results as $key => $item){
		echo 
		'seq:' . $item->seq . '<br>' . 
		'app_id:' . $item->app_id . '<br>' . 
		'file_id:' . $item->file_id . '<br>' .
		'title:' . $item->title . '<br>' . 
		'digest:' . $item->digest . '<br>' . 
		'create_time:' . $item->create_time . '<br>' . 
		'content_url:' . $item->content_url . '<br>' . 
		'img_url:' . $item->img_url . '<br>=======================<br>';
	}
}
?>