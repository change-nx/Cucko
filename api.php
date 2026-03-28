<?php
define("onebot",require("config/onebot.php"));
define("key",require("key.php"));
require("function/other/Parsedown.php");


$raw = file_get_contents("php://input");
$plat = $_REQUEST["plat"] ?? "";

if (empty($raw) || empty($plat)) {
    die("你的请求被吃了哦饱饱,原因嘛,自己猜咯๑•́₃•̀๑");
}

$POST = json_decode($raw,true);
$type = $POST["type"];

/*
#登录后台
plat=admin
请求体:
{
    "type": "login",
    "data": {
        "key": "用户输入的key"
    }
}

#退出后台
plat=admin
请求体:
{
    "type": "logout",
    "data": {
        "key": "用户输入的key"
    }
}

#检查登录状态
plat=admin
请求体:
{
    "type": "check",
    "data": {
        "key": "用户输入的key"
    }
}


#Onebot 部分
plat=onebot

#获取配置信息
{
    "type": "get_info"
}

#添加/修改账号
{
    "type": "add",
    "data": {
        "qq": "机器人账号",
        "api": "HTTP服务器",
        "api_token": "HTTP服务器token",
        "http_token": "HTTP客户端token",
        "touch": "自触",
        "owner": [
            "主人列表"
        ]
    }
}

#插件列表
{
    "type": "plugin_list"
}

#日志列表
{
    "type": "log_list"
}

#日志内容
{
    "type": "log_content",
    "data": {
        "name":"通过list获取到的日志名"
    }
}

#删除日志
{
    "type": "log_delete",
    "data": {
        "name":"通过list获取到的日志名"
    }
}

*/

if ($plat == "admin") {

    $key = $POST["data"]["key"];
    switch ($type) {
        
        // 登录
        case "login":
            if ($key != key) {
            
                echo json_encode([
                    "code" => -1,
                    "msg" => "key错误"
                ],480);
                
            } else {
            
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
                    "code" => 200,
                    "msg" => "登录成功"
                ],480);
                
            }
            
        break;
        
        // 退出登录
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
                    "code" => 200,
                    "msg" => "退出成功"
                ],480);
                
        break;
        
        // 检查登录状态
        case "check":
        
            $cookie = $_COOKIE['Cucko'];
            if (!isset($cookie)) {
            
                echo json_encode([
                    "code" => -1,
                    "msg" => "未登录"
                ],480);
                
            } elseif (isset($cookie)) {
            
                echo json_encode([
                    "code" => 200,
                    "msg" => "已登录"
                ],480);

            }
            
        break;
        
    }
    exit;
}

if (!isset($_COOKIE['Cucko'])) {
    echo json_encode([
        "code" => false,
        "msg" => "未登录"
    ],480);
    exit;
}


if ($plat == "onebot") {

    switch ($type) {
        
        // 获取配置信息
        case "get_info":
            
            $json = [
                "code" => 200,
                "data" => onebot
            ];
            $json = json_encode($json,480);
            
            echo $json;
            
            break;
        
        // 修改配置信息
        case "add":
        
            $json = $POST["data"];
            $config = onebot;
            
            $config["qq"] = $json["qq"];
            $config["api"] = $json["api"];
            $config["api_token"] = $json["api_token"];
            $config["http_token"] = $json["http_token"];
            $config["touch"] = $json["touch"];
            $config["owner"] = $json["owner"];
            
            $content = "<?php\nreturn ".var_export($config, true).";\n";
            $code = file_put_contents(__DIR__."/config/onebot.php",$content);
            
            if ($code) {
            
                echo json_encode([
                    "code" => 200,
                    "msg" => "修改成功"
                ],480);
                
            } else {
            
                echo json_encode([
                    "code" => -1,
                    "msg" => "修改失败"
                ],480);
                
            }
            
            break;
        
        // 获取插件列表
        case "plugin_list":
            
            $All = glob(__DIR__."/Onebot/*/info.json");
            $list = [];
            foreach ($All as $value) {
                $info = file_get_contents($value);
                $json = json_decode($info,true);
                $dir = dirname($value);
                if (is_file($dir."/backend.json")) {
                    $backed = file_get_contents($dir."/backend.json");
                    $backend = json_decode($backed,true);
                } else {
                    $backend = null;
                }
                $json["backend"] = $backend;
                
                if (is_file($dir."/README.md")) {
                    $README = file_get_contents($dir."/README.md");
                } else {
                    $README = "该插件没有提供任何文档哦";
                }
                
                $Parsedown = new Parsedown();
                $README = $Parsedown->text($README);
                $json["README"] = $README;
                
                
                $list[] = $json;
            }
            
            echo json_encode([
                "code" => 200,
                "data" => $list
            ], 480);
            
            break;
        
        // 修改插件配置
    case "plugin_backend":
        
        $pluginName = $POST["data"]["name"];
        $backendData = $POST["data"]["backend"];
        
        // 遍历 Onebot 目录查找匹配的插件
        $allPlugins = glob(__DIR__."/Onebot/*/info.json");
        $targetPath = null;
        
        foreach ($allPlugins as $infoPath) {
            $info = json_decode(file_get_contents($infoPath), true);
            if ($info && isset($info['name']) && $info['name'] === $pluginName) {
                $targetPath = dirname($infoPath);
                break;
            }
        }
        
        if ($targetPath) {
            $pluginPath = $targetPath . "/backend.json";
            
            // 如果 backend.json 不存在，创建空数组
            if (!file_exists($pluginPath)) {
                file_put_contents($pluginPath, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
            
            $content = json_encode($backendData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $code = file_put_contents($pluginPath, $content);
            
            if ($code !== false) {
                echo json_encode([
                    "code" => 200,
                    "msg" => "保存成功"
                ], 480);
            } else {
                echo json_encode([
                    "code" => -1,
                    "msg" => "保存失败"
                ], 480);
            }
        } else {
            echo json_encode([
                "code" => -1,
                "msg" => "插件不存在: " . $pluginName
            ], 480);
        }
        
        break;
        
        // 日志列表
        case "log_list":
            
            $All = glob(__DIR__."/log/onebot/*.log");
            $list = [];
            
            foreach ($All as $name) {
            
                $name = basename($name);
                $list[] = $name;
                
            }
            
            echo json_encode([
                "code" => 200,
                "data" => $list
            ],480);
            
            break;
            
        // 日志内容
        case "log_content":
            
            $name = $POST["data"]["name"];
            $content = file(__DIR__."/log/onebot/".$name);
            
            $json = [];
            foreach ($content as $value) {
                
                $value = json_decode($value,true);
                
                if (isset($value["plat_error"])) {
                
                    $json["error"][] = $value["plat_error"];
                    
                } elseif (isset($value["group_id"])) {
                
                    $json["group"][$value["group_id"]][] = $value;
                    
                } elseif ($value["message_type"] == "private") {
                
                    $json["friend"][$value["user_id"]][] = $value;
                  
                } else {
                
                    $json["system"][] = $value;
                }
            }
            echo json_encode($json,480);
            
            break;
            
        // 删除日志
        case "log_delete":
             
            $name = $POST["data"]["name"];
             
            if (unlink(__DIR__."/log/onebot/{$name}")) {
             
               echo json_encode([
                   "code" => 200,
                   "msg" => "删除成功"
               ],480);
                
            } else {
                
                echo json_encode([
                   "code" => 200,
                   "msg" => "删除失败"
               ],480);
               
            }
            
            break;
    } 
    
} else {
    echo json_encode([
        "code" => -1,
        "msg" => "平台错误"
    ],480);
}