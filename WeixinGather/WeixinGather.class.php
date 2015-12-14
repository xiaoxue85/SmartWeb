<?php
class WeixinGather{
	private $account_config, $article_config, $weixin_config;
    public $results;
	
	function __construct($account = array()){
		$this->set_account($account);
        $this->set_article();
        $this->set_weixin();	
	}
	
	public function set_account($account = array()){
		if (!is_array($account) || empty($account['username']) || empty($account['pwd'])){
			$this->show_msg(array('code' => 0, 'msg' => 'Account set error'));
			return;
		}
        $this->account_config            = $account;
        $this->account_config['pwd']     = md5($this->account_config['pwd']);
        $this->account_config['imgcode'] = '';
        $this->account_config['f']       = 'json';
	}
	public function set_article($article_config){
        if (!empty($article_config)){
            $this->article_config = $article_config;
        }
        if (empty($this->article_config['start']) || !is_integer($this->article_config['start'])) $this->article_config['start'] = 0;
        if (empty($this->article_config['count']) || !is_integer($this->article_config['count'])) $this->article_config['count'] = 10;
    }
    public function set_weixin($weixin_config){
        $this->weixin_config['domain_url']  = 'https://mp.weixin.qq.com';
        $this->weixin_config['login_url']   = $this->weixin_config['domain_url'] . '/cgi-bin/login';
        $this->weixin_config['article_url'] = $this->weixin_config['domain_url'] . '/cgi-bin/appmsg';
        $this->weixin_config['verify_url']  = $this->weixin_config['domain_url'] . '/cgi-bin/verifycode?username=' . $this->account_config['username'] . '&r=1449728211251';
    }

    public function get_contents(){
        //$verify = $this->get_verify();
        //exit;
        //$this->account_config['imgcode'] = 'dddd';
        // 获取登录Token
        $result = $this->get_url_contents($this->weixin_config['login_url'], 'Referer: ' . $this->weixin_config['domain_url'] . '/', http_build_query($this->account_config), 'POST');
        $data   = json_decode(str_replace("\\", '', $result['data']));
        $cookie = $result['cookie'];
        $tmp    = explode('token=', $data->redirect_url);
        $token  = $tmp[1];
        
        // 获取文章列表
        $article_url = $this->weixin_config['article_url'] . "?begin=" . $this->article_config['start'] . "&count=" . $this->article_config['count'] . "&t=media/appmsg_list2&type=10&action=list_card&token=" . $token . "&lang=zh_CN";
        $header = "GET /cgi-bin/appmsg?begin=" . $this->article_config['start'] . "&count=" . $this->article_config['count'] . "&t=media/appmsg_list&type=10&action=list_card&lang=zh_CN&token=" . $token . " HTTP/1.1
Host: mp.weixin.qq.com
Connection: keep-alive
Cache-Control: max-age=0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36
Referer: https://mp.weixin.qq.com/cgi-bin/home?t=home/index&lang=zh_CN&token=" . $token . "
Cookie: " . $cookie;
        $result = $this->get_url_contents($article_url, $header);
        $data = $result['data'];

        $page_rules = preg_quote('wx.cgiData = {content};', '/');
        $page_rules = '/' . str_replace('content', '(.*)', $page_rules) . '/';
        preg_match_all($page_rules, $data, $result_arr);
        $tmp = array('wx.cgiData = ', ';');
        $json_str = str_replace($tmp, '', $result_arr[0][0]);
        $data = json_decode($json_str);
        $this->results = $data->item;
    }

    private function get_verify(){
        // 获取验证码
        $result = $this->get_url_contents($this->weixin_config['verify_url']);
        var_dump($result);exit;
    }
	private function show_msg($msg){
		var_dump($msg);
	}

    private function get_url_contents($gather_url, $header = '', $content = '', $method = 'GET'){
        $opts = array('http' => array(
            'method'  => $method, 
            'header'  => $header, 
            'content' => $content
        ));
        $msg_stream = @stream_context_create($opts);
        $data = @file_get_contents($gather_url, false, $msg_stream);
        // 获取Cookie
        if (is_array($http_response_header)){
            foreach ($http_response_header as $item){
                if (strstr($item, 'Set-Cookie:')){
                    $pageRules = preg_quote('Set-Cookie: content Path', '/');
                    $pageRules = '/' . str_replace('content', '(.*)', $pageRules) . '/';
                    preg_match_all($pageRules, $item, $resultArray);
                    $cookie .= $resultArray[1][0] . ' ';
                }
            }
            $cookie = substr($cookie, 0, strlen($cookie) - 2);
        }
        return array('data' => $data, 'cookie' => $cookie);
    }
}
?>