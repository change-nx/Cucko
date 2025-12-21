<?php
define("info",require("info.php"));
define("owner",require("owner.php"));
require("function.php");

$type = $_REQUEST["type"];
$action = $_REQUEST["action"];

switch ($type) {
    case "login":
        $key = $_REQUEST["key"];
        if (empty($key)) {
            echo json_encode([
                "code" => false,
                "msg" => "未提供密钥"
            ],480);
            exit;
        } elseif ($key != info["admin"]) {
            echo json_encode([
                "code" => false,
                "msg" => "密钥错误"
            ],480);
            exit;
        } elseif ($key == info["admin"]) {
            $token = bin2hex(random_bytes(16));
            setcookie("Cucko", $token, [
                'expires' => time() + 864000,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            echo json_encode([
                "code" => true,
                "msg" => "登录成功"
            ],480);
            exit;
        } else {
            echo json_encode([
                "code" => false,
                "msg" => "未知错误"
            ],480);
            exit;
        }
        break;
    case "check":
        $cookie = $_COOKIE['Cucko'];
        if (!isset($cookie)) {
            echo json_encode([
                "code" => false,
                "msg" => "未登录"
            ],480);
            exit;
        } elseif (isset($cookie)) {
            echo json_encode([
                "code" => true,
                "msg" => "已登录"
            ],480);
            exit;
        }
        break;
    case "logout":
        setcookie("Cucko", "", [
            'expires' => time() - 864000,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        echo json_encode([
            "code" => true,
            "msg" => "已退出"
        ],480);
        exit;
        break;
}

if (!isset($_COOKIE['Cucko'])) {
    echo json_encode([
        "code" => false,
        "msg" => "未登录"
    ],480);
    exit;
}

switch ($type) {
    case "main":
        //日志总数
        $log_total = function () {
            $list = glob(__DIR__ . "/log/*.log");
            $count = 0;
            foreach ($list as $name) {
                $count = $count + count(file($name));
            }
            return $count;
        };
        //今日日志数
        $log_today = count(file(__DIR__ . "/log/" . date("Y-m-d") . ".log"));
        //插件数
        $plugin = function () {
            $list = glob(__DIR__ . "/plugin/*", GLOB_ONLYDIR);
            $total = 0;//总数
            $true = 0;//开启
            $false = 0;//关闭
            foreach ($list as $name) {
                if (!file_exists($name . "/info.prop")) {
                    continue;
                }
                $status = prop($name . "/info.prop","status");
                $total++;
                if ($status == "true") {
                    $true++;
                } elseif ($status == "false") {
                    $false++;
                }
            }
            return [
                "total" => $total,
                "true" => $true,
                "false" => $false
            ];
        };
        $plugin_count = $plugin();
        //系统信息
        $system = function () {
            $cpu_usage = trim(trim(shell_exec("top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\\([0-9.]*\\)%* id.*/\\1/' | awk '{print 100 - $1}'")),"%");
            $cpu_cores = trim(shell_exec('nproc'));
            $memory_info = shell_exec("free -k | awk 'NR==2{print $2,$3,$4}'");
            list($total_kb, $used_kb, $free_kb) = explode(' ', trim($memory_info));
            $memory_total = round($total_kb / 1024 / 1024, 1);
            $memory_used = round($used_kb / 1024 / 1024, 1);
            $memory_usage = round(($used_kb / $total_kb) * 100, 1);
            $storage_total = trim(shell_exec("df -h / | awk 'NR==2{print $2}'"));
            $storage_used = trim(shell_exec("df -h / | awk 'NR==2{print $3}'"));
            $storage_percent = trim(shell_exec("df -h / | awk 'NR==2{print $5}'"));
            $storage_usage = intval(trim($storage_percent, '%'));
            $system_name = trim(shell_exec("uname -s"));
            $system_version = trim(shell_exec("uname -r"));
            $system_arch = trim(shell_exec("uname -m"));
            return [
                'CPU' => $cpu_usage . '%',
                'CPUCount' => $cpu_cores,
                'MemoryUsed' => $memory_used . 'GB',
                'MemoryTotal' => $memory_total . 'GB',
                'StorageUsed' => $storage_used,
                'StorageTotal' => $storage_total, 
                'system' => $system_name . ' ' . $system_version . ' ' . $system_arch
            ];
        };
        echo json_encode([
            "QQ" => info["登录账号"],
            "avatar" => "http://q1.qlogo.cn/g?b=qq&nk=" . info["登录账号"] . "&s=640",
            "Framework" => [
                "LogTotal" => $log_total(),
                "LogToday" => $log_today,
                "PluginTotal" => $plugin_count["total"],
                "PluginTrue" => $plugin_count["true"],
                "PluginFalse" => $plugin_count["false"]
            ],
            "Owner" => owner,
            "Server" => $system()
        ],480);
        break;
    case "log":
        $name = $_REQUEST["name"];
        switch ($action) {
            case "list":
                $list = glob(__DIR__ . "/log/*.log");
                $echo = [];
                foreach ($list as $dir) {
                    $echo[] = basename($dir);
                }
                echo json_encode($echo,480);
                break;
            case "content":
                echo file_get_contents(__DIR__ . "/log/" . $name);
                break;
            case "del":
                unlink(__DIR__ . "/log/" . $name);
                echo "true";
                break;
        }
        break;
    case "plugin":
        $name = $_REQUEST["name"];
        switch ($action) {
            case "list":
                $list = glob(__DIR__ . "/plugin/*",GLOB_ONLYDIR);
                $echo = [];
                foreach ($list as $dir) {
                    $Plugin = $dir . "/info.prop";
                    $echo[] = [
                        "name" => prop($Plugin,"name"),
                        "author" => prop($Plugin,"author"),
                        "version" => prop($Plugin,"version"),
                        "status" => prop($Plugin,"status"),
                        "desc" => file_get_contents($dir . "/desc.txt")
                    ];
                }
                echo json_encode($echo,480);
                break;
            case "true":
               $list = glob(__DIR__ . "/plugin/*",GLOB_ONLYDIR);
                foreach ($list as $dir) {
                    $Plugin = $dir . "/info.prop";
                    $pluginname = prop($Plugin,"name");
                    if ($pluginname != $name) {
                        continue;
                    } else {
                        $content = "name=".prop($Plugin,"name")."\n";
                        $content .= "author=".prop($Plugin,"author")."\n";
                        $content .= "version=".prop($Plugin,"version")."\n";
                        $content .= "status=true";
                        file_put_contents($Plugin,$content);
                        echo "true";
                    }
                }
            break;
            case "false":
               $list = glob(__DIR__ . "/plugin/*",GLOB_ONLYDIR);
                foreach ($list as $dir) {
                    $Plugin = $dir . "/info.prop";
                    $pluginname = prop($Plugin,"name");
                    if ($pluginname != $name) {
                        continue;
                    } else {
                        $content = "name=".prop($Plugin,"name")."\n";
                        $content .= "author=".prop($Plugin,"author")."\n";
                        $content .= "version=".prop($Plugin,"version")."\n";
                        $content .= "status=false";
                        file_put_contents($Plugin,$content);
                        echo "true";
                    }
                }
            break;
            case "create":
                $author = $_REQUEST["author"];
                $version = $_REQUEST["version"];
                $desc = $_REQUEST["desc"];
                $path = __DIR__ . "/plugin/" . $name;
                mkdir($path,0755,true);
                $content = "name=".$name."\n";
                $content .= "author=".$author."\n";
                $content .= "version=".$version."\n";
                $content .= "status=true";
                file_put_contents($path."/info.prop",$content);
                file_put_contents($path."/desc.txt",$desc);
                file_put_contents($path."/main.php","<?php\n\n?>");
                echo "true";
            break;
        }
        break;
        case "info":
            $url = $_REQUEST["url"] ?? info["url"] ?? "";
            $url_token = $_REQUEST["url_token"] ?? info["url_token"] ?? "";
            $http_token = $_REQUEST["http_token"] ?? info["http_token"] ?? "";
            $loginQQ = $_REQUEST["loginQQ"] ?? info["登录账号"] ?? "";
            $zc = $_REQUEST["zc"] ?? info["自触"] ?? "";
            $admin = $_REQUEST["admin"] ?? info["admin"] ?? "";
            $content = '<?php' . PHP_EOL;
            $content .= 'return [' . PHP_EOL;
            $content .= '    "url" => "' . addslashes($url) . '",' . PHP_EOL;
            $content .= '    "url_token" => "' . addslashes($url_token) . '",' . PHP_EOL;
            $content .= '    "http_token" => "' . addslashes($http_token) . '",' . PHP_EOL;
            $content .= '    "登录账号" => "' . addslashes($loginQQ) . '",' . PHP_EOL;
            $content .= '    "自触" => "' . addslashes($zc) . '",' . PHP_EOL;
            $content .= '    "admin" => "' . addslashes($admin) . '"' . PHP_EOL;
            $content .= '];' . PHP_EOL;
            file_put_contents(__DIR__ . "/info.php", $content);
            echo "true";
        break;
        case "getinfo":
            echo json_encode(info,480);
        break;
}