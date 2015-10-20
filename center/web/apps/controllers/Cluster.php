<?php
namespace App\Controller;

use Swoole;

require_once '/data/www/public/sdk/StatsCenter.php';
require_once '/data/www/public/sdk/CloudConfig.php';

class Cluster extends \App\LoginController
{
    static $envs = [
        'product' => '生产环境',
        'pre' => '预发布环境',
        'test' => '测试环境',
        'dev' => '开发环境',
        'local' => '本机',
    ];

    static $projects = [
        'chelun' => ['name' => '车轮社区', 'namespace' => 'CheLun',],
        'kaojiazhao' => ['name' => '考驾照', 'namespace' => 'KJZ',],
    ];

    protected function getProject()
    {
        $project = empty($_GET['p']) ? 'chelun' : trim($_GET['p']);
        if (!isset(self::$projects[$project]))
        {
            $project = 'chelun';
        }
        $this->assign('c_projs', self::$projects);
        $this->assign('c_proj', $project);
        return $project;
    }

    function index()
    {
        $project = $this->getProject();
        $list = [];
        foreach(self::$envs as $k => $v)
        {
            $key = 'aopnet:'.$k.':service:config:'.$project;
            $res = $this->redis('cluster')->get($key);
            if ($res === false)
            {
                $list[$k] = array('namespace' => self::$projects[$project]['namespace'], 'servers' => []);
            }
            else
            {
                $list[$k] = json_decode($res, true);
            }
            $list[$k]['env.name'] = $v;
        }
        $this->assign('list', $list);
        $this->display();
    }

    function node()
    {
        if (empty($_GET['env']))
        {
            return "缺少参数";
        }

        $project = $this->getProject();
        $env = $_GET['env'];
        $key = 'aopnet:'.$env.':service:config:'.$project;

        $res = $this->redis('cluster')->get($key);
        if ($res)
        {
            $config = json_decode($res, true);
        }
        else
        {
            $config = array('namespace' => self::$projects[$project]['namespace'], 'servers' => []);
        }

        if (!empty($_POST['ip']) and !empty($_POST['port']))
        {
            $ip = trim($_POST['ip']);
            if (!Swoole\Validate::ip($ip))
            {
                return Swoole\JS::js_back("IP格式错误");
            }
            $port = intval(trim($_POST['port']));
            if ($port < 1 or $port > 65535)
            {
                return Swoole\JS::js_back("PORT格式错误");
            }
            $newServer = "$ip:$port";
            if (in_array($newServer, $config['servers']))
            {
                return Swoole\JS::js_back("Server已存在");
            }
            $config['servers'][] = $newServer;
            if ($this->redis('cluster')->set($key, json_encode($config)) === false)
            {
                return Swoole\JS::js_back("写入Redis失败");
            }
        }
        elseif (!empty($_GET['del']))
        {
            if (count($config['servers']) == 1)
            {
                return Swoole\JS::js_back("仅剩1个节点无法再删除");
            }
            $del = trim($_GET['del']);
            $id = array_search($del, $config['servers']);
            if ($id !== false)
            {
                unset($config['servers'][$id]);
                if ($this->redis('cluster')->set($key, json_encode($config)) === false)
                {
                    return Swoole\JS::js_back("写入Redis失败");
                }
            }
        }
        $this->assign('config', $config);
        $this->display();
    }

    function config_list()
    {
        $conf = \CloudConfig::getFromCloud('config:category', 'system');
        $this->display();
    }

    function config_detail()
    {

    }
}