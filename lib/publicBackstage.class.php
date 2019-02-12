<?php
/**
 *
 * @authors fisher (i@qnmlgb.trade)
 * @date    2019-02-12
 * @version 1.0
 */

class publicBackstage
{
    protected $config  = '';
    protected $cookies = '';
    protected $data    = '';
    const INDEX        = 'http://tieba.baidu.com/bawu2/platform/index?word=';
    const POST         = 'http://tieba.baidu.com/bawu2/platform/listPostLog?word=';
    const USER         = 'http://tieba.baidu.com/bawu2/platform/listUserLog?word=';
    const DATA         = 'http://tieba.baidu.com/bawu2/platform/data?word=';
    const BAWU         = 'http://tieba.baidu.com/bawu2/platform/listBawuLog?word=';
    const EXCEL        = 'http://tieba.baidu.com/bawu2/platform/dataExcel?word=';

    public function __construct($config)
    {
        $this->config  = $config;
        $this->cookies = 'BDUSS=' . $config['bduss'];
    }
    protected function verify()
    {
        //�˴����������˺ŵ���˽�����Լ��˺���Ȩ��������ʱ��������Ȩ
    }
    protected function showPages()
    {
        $r = $this->data;
        header("Content-type:text/html;charset=GBK");
        if (empty($r)) {
            die('COOKIEʧЧ����Ȩ��');
        }
        //��������
        $replace = [
            '/<div class="user_info">.+?<\/div><nav/' => '<div class="user_info"><div class="user_info"><h2 style="color:white;">���񹫿���̨</h2><p style="color:white;">���ڰɣ�' . $this->config['kw'] . '��<br> Powered By Ͷ������</p></div></div><nav', //�ɱ�����
            '/href="\/(p|home)/'                      => 'href="http://tieba.baidu.com/\1', //��ת��������ҳ
            '/<img src="\/([^\/])/'                   => '<img src="http://tieba.baidu.com/\1', //ͼƬ����
        ];
        //$r = str_replace('<div class="bazhu">', '<div class="bazhu" style="display:none">', $r); //����app������������辻��
        $r = str_replace('/bawu2/platform/', './', $r);
        $r = str_replace('/bawu2/postappeal/', './', $r);
        //·�ɹ���
        if ($this->config['showpic']) {
            $replace['/(http:\/\/imgsrc\.baidu\.com\/.*?\.jpg)/'] = './getpic?url=\1';
        }
        if ($this->config['hideopt']) {
            // var_dump("���������Ч");
            $replace['/<label><input.*?value="op_uname"\/>\s*������<\/label>/'] = '';
            // �����˰�ť
            $replace['/<a href="#" class="ui_text_normal">[^<]+<\/a>/'] = '<span class="ui_text_normal"><strong>Hidden</strong></span>';
            //�������б�
            $replace['/������<\/strong><\/div><div class="menu_options_list"><div class="options_up_btn j_up disabled">[\w\W]+?<\/div>[\w\W]+?<\/div>[\w\W]+?<\/div>/'] = '������</strong></div>';
            //���������
        }
        foreach ($replace as $k => $v) {
            $r = preg_replace($k, $v, $r);
        }
        echo $r;
    }
    public function urlRoute($path = 'index')
    {
        $data = ['index', 'data', 'listBawuLog', 'listPostLog', 'listUserLog', 'getpic', 'dataExcel'];
        if (!in_array($path, $data)) {
            $this->showerr();
            return;
        }
        $this->$path();
    }
    protected function showerr()
    {
        header("Content-type:text/html;charset=GBK");
        echo "<h1>��������Ӧ�����ߴ��� &nbsp; :)</h1><h3>���ȥ�������԰�~</h3>";
    }
    protected function index()
    {
        $this->cget($this::INDEX . $this->config['kw'], $this->cookies);
        $this->showPages();
    }
    protected function dataExcel()
    {
        $this->cget($this::EXCEL . $this->config['kw'], $this->cookies);
        header("Content-Type:application/vnd.ms-excel; charset=GBK");
        echo $this->data;
    }
    protected function listBawuLog()
    {
        $this->cget($this::BAWU . $this->config['kw'] . '&' . parse_url($_SERVER['REQUEST_URI'])['query'], $this->cookies);
        $this->showPages();
    }
    protected function listPostLog()
    {
        $this->cget($this::POST . $this->config['kw'] . '&' . parse_url($_SERVER['REQUEST_URI'])['query'], $this->cookies);
        $this->showPages();
    }
    protected function listUserLog()
    {
        $this->cget($this::USER . $this->config['kw'] . '&' . parse_url($_SERVER['REQUEST_URI'])['query'], $this->cookies);
        $this->showPages();
    }
    protected function data()
    {
        $this->cget($this::DATA . $this->config['kw'], $this->cookies);
        $this->showPages();
    }
    protected function getpic()
    {
        header("Content-type: image/png");
        preg_match('/url=(.*)/', $_SERVER['REQUEST_URI'], $url);
        $url = empty($url[1]) ? 'http://img.gifhome.com/gif/emoji/2018/f346e45b00d74642a2ce2c775cba7cc6.jpg' : $url[1];
        $this->cget($url, $this->cookies);
        echo $this->data;
    }
    protected function cget($url, $cookie)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko/20100101 Firefox/65.0', 'Connection:keep-alive', 'Referer:http://wapp.baidu.com/'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $r = curl_exec($ch);
        curl_close($ch);
        $this->data = $r;
    }
    public function __call($name, $args)
    {
        die('Sorry~');
    }
}