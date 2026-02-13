<?php
define("onebot",require("config/onebot.php"));
define("QQBOT",require("config/QQBOT.php"));
define("key",require("key.php"));


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

#开启插件
{
    "type": "plugin_open",
    "data": {
        "name": "插件名"
    }
}

#关闭插件
{
    "type": "plugin_lock",
    "data": {
        "name": "插件名"
    }
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


#QQBOT 部分
plat=QQBOT

#获取配置信息
{
    "type": "get_info"
}

#添加/修改配置
{
    "type": "set",
    "data": {
        "appid": "机器人appid",
        "secret": "机器人secret"
    }
}

#插件列表
{
    "type": "plugin_list"
}

#开启插件
{
    "type": "plugin_open",
    "data": {
        "name": "插件名"
    }
}

#关闭插件
{
    "type": "plugin_lock",
    "data": {
        "name": "插件名"
    }
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
            
            if ($key != key) {
            
                echo json_encode([
                    "code" => -1,
                    "msg" => "key错误"
                ],480);
                
            } else {
            
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
                
            }
            
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
            $code = file_put_contents($configFile,$content);
            
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
            
            $All = glob(__DIR__."/Onebot/*.php");
            $list = [];
            
            foreach ($All as $name) {
            
                $fileName = basename($name);
                $pluginName = basename($fileName,".php");
                
                $lockedPath = __DIR__ . "/Onebot/" . $pluginName;
                $isLocked = file_exists($lockedPath) && !is_dir($lockedPath);
                
                $list[$pluginName] = !$isLocked;
                
            }
            
            $lockedFiles = glob(__DIR__."/Onebot/*");
            foreach ($lockedFiles as $file) {
                if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                    $pluginName = basename($file);
                    if (!isset($list[$pluginName])) {
                        $list[$pluginName] = false;
                    }
                }
            }
            
            echo json_encode([
                "code" => 200,
                "data" => $list
            ], 480);
            
            break;
            
         // 开启插件
        case "plugin_open":
            
            $name = $POST["data"]["name"];
            $path = __DIR__ . "/Onebot/{$name}";
            
            if (rename($path,$path.".php")) {
                
                echo json_encode([
                    "code" => 200,
                    "msg" => "开启成功"
                ],480);
                
            } else {
                
                echo json_encode([
                    "code" => -1,
                    "msg" => "开启失败"
                ],480);
                
            }
            
            break;
            
        // 关闭插件
        case "plugin_lock":
            
            $name = $POST["data"]["name"];
            $path = __DIR__ . "/Onebot/{$name}";
            
            if (rename($path.".php",$path)) {
                
                echo json_encode([
                    "code" => 200,
                    "msg" => "关闭成功"
                ],480);
                
            } else {
                
                echo json_encode([
                    "code" => -1,
                    "msg" => "关闭失败"
                ],480);
                
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
    
} elseif ($plat == "QQBOT") {
    
    switch ($type) {
        
        // 获取配置信息
        case "get_info":
            
            $json = [
                "code" => 200,
                "data" => QQBOT
            ];
            $json = json_encode($json,480);
            
            echo $json;
            
            break;
        
        // 修改配置信息
        case "set":
            
            $json = $POST["data"];
            $config = QQBOT;
            
            $config["appid"] = $json["appid"];
            $config["secret"] = $json["secret"];
            
            $content = "<?php\nreturn ".var_export($config, true).";\n";
            $configFile = __DIR__."/config/QQBOT.php";
            $code = file_put_contents($configFile,$content);
            
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
            
            $All = glob(__DIR__."/QQBOT/*.php");
            $list = [];
            
            foreach ($All as $name) {
            
                $fileName = basename($name);
                $pluginName = basename($fileName,".php");
                
                $lockedPath = __DIR__ . "/QQBOT/" . $pluginName;
                $isLocked = file_exists($lockedPath) && !is_dir($lockedPath);
                
                $list[$pluginName] = !$isLocked;
                
            }
            
            $lockedFiles = glob(__DIR__."/QQBOT/*");
            foreach ($lockedFiles as $file) {
                if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                    $pluginName = basename($file);
                    if (!isset($list[$pluginName])) {
                        $list[$pluginName] = false;
                    }
                }
            }
            
            echo json_encode([
                "code" => 200,
                "data" => $list
            ], 480);
            
            break;
            
         // 开启插件
        case "plugin_open":
            
            $name = $POST["data"]["name"];
            $path = __DIR__ . "/QQBOT/{$name}";
            
            if (rename($path,$path.".php")) {
                
                echo json_encode([
                    "code" => 200,
                    "msg" => "开启成功"
                ],480);
                
            } else {
                
                echo json_encode([
                    "code" => -1,
                    "msg" => "开启失败"
                ],480);
                
            }
            
            break;
            
        // 关闭插件
        case "plugin_lock":
            
            $name = $POST["data"]["name"];
            $path = __DIR__ . "/QQBOT/{$name}";
            
            if (rename($path.".php",$path)) {
                
                echo json_encode([
                    "code" => 200,
                    "msg" => "关闭成功"
                ],480);
                
            } else {
                
                echo json_encode([
                    "code" => -1,
                    "msg" => "关闭失败"
                ],480);
                
            }
            
            break;
        
        // 日志列表
        case "log_list":
            
            $All = glob(__DIR__."/log/QQBOT/*.log");
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
            $content = file(__DIR__."/log/QQBOT/".$name);
            
            $json = [];
            foreach ($content as $value) {
                
                $value = json_decode($value,true);
                
                if (isset($value["t"])) {
                    switch($value["t"]) {
                        case "GROUP_AT_MESSAGE_CREATE":
                            $json["group"][$value["d"]["group_id"]][] = $value;
                            break;
                            
                        case "C2C_MESSAGE_CREATE":
                            $json["friend"][$value["d"]["author"]["id"]][] = $value;
                            break;
                            
                        case "GROUP_ADD_ROBOT":
                        case "GROUP_DEL_ROBOT":
                        case "INTERACTION_CREATE":
                            $json["system"][] = $value;
                            break;
                            
                        default:
                            $json["system"][] = $value;
                            break;
                    }
                } else {
                    $json["system"][] = $value;
                }
            }
            
            echo json_encode($json,480);
            
            break;
            
        // 删除日志
        case "log_delete":
             
            $name = $POST["data"]["name"];
             
            if (unlink(__DIR__."/log/QQBOT/{$name}")) {
             
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