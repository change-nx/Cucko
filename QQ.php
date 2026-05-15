<?php
define("adapter",config["id"]);
$raw = file_get_contents("php://input");

$op = raw["op"];

// 处理解密
$sign = function ($payload,$seed){
    while (strlen($seed) < SODIUM_CRYPTO_SIGN_SEEDBYTES) {
        $seed .= $seed;
    }
    $privateKey = sodium_crypto_sign_secretkey(
        sodium_crypto_sign_seed_keypair(substr($seed, 0, SODIUM_CRYPTO_SIGN_SEEDBYTES))
    );
    $signature = bin2hex(
        sodium_crypto_sign_detached(
            $payload['d']['event_ts'] . $payload['d']['plain_token'], 
            $privateKey
        )
    );
    echo json_encode([
        'plain_token' => $payload['d']['plain_token'],
        'signature' => $signature
    ]);
};

if ($op == 13) {
    $sign(raw,config["secret"]);
    exit;
}

if ($op == 0) {
    $event_id = raw["id"];
    $event = 读(".QQBOT/".adapter."/".date("Y-m-d"),$event_id,false);
    if($event) {
        wlog(adapter,json_encode([
            "type" => "system",
            "msg" => "事件重复",
            "time" => time()
        ],320));
        die("error");
     }
    写(".QQBOT/".adapter."/".date("Y-m-d"),$event_id,true);
    wlog(adapter,json_encode(raw,320));
    Plugin_Run(raw);
}


function Plugin_Run($raw){
$event = $raw["t"];
switch($event) {
    case "GROUP_AT_MESSAGE_CREATE":
    case "GROUP_MESSAGE_CREATE":
        define("事件", "群聊");
        define("消息ID", $raw["d"]["id"]);
        define("消息", trim($raw["d"]["content"], "/ "));
        define("群号", $raw["d"]["group_id"]);
        define("QQ", $raw["d"]["author"]["id"]);
        break;

    case "C2C_MESSAGE_CREATE":
        define("事件", "私聊");
        define("消息ID", $raw["d"]["id"]);
        define("消息", trim($raw["d"]["content"], "/ "));
        define("QQ", $raw["d"]["author"]["id"]);
        break;
        
    case "GROUP_ADD_ROBOT":
        define("事件", "加群");
        define("事件ID", $raw["id"]);
        define("群号", $raw["d"]["group_openid"]);
        define("QQ", $raw["d"]["op_member_openid"]);
        break;
        
    case "GROUP_DEL_ROBOT":
        define("事件", "退群");
        define("事件ID", $raw["id"]);
        define("群号", $raw["d"]["group_openid"]);
        define("QQ", $raw["d"]["op_member_openid"]);
        break;
        
    case "INTERACTION_CREATE":
        define("事件", "回调");
        define("事件ID", $raw["id"]);
        define("群号", $raw["d"]["group_openid"]);
        define("消息",$raw["d"]["data"]["resolved"]["button_data"]);
        define("QQ",$raw["d"]["group_member_openid"]);
        break;
}
  require("function/QQ/main.php");
  load_plugin();
  exit;
}

function load_plugin() {
    $plugin_path = __DIR__ . "/plugin/".adapter;
    if (!is_dir($plugin_path)) {
        mkdir($plugin_path, 0777, true);
        wlog(adapter,json_encode([
            "type" => "system",
            "msg" => "已为适配器[". adapter . "]创建插件目录",
            "time" => time()
        ],320));
    }
    
    $plugin = glob($plugin_path . "/*/main.php");
    foreach ($plugin as $name) {
        $info = dirname($name) . "/info.json";
        if (!is_file($info)) {
            wlog(adapter,json_encode([
                "type" => "system",
                "msg" => "目录[{$name}]无插件配置信息",
                "time" => time()
            ],320));
            continue;
        }
        try {
            (function($__file) {
                require_once($__file);
            })($name);
        } catch (Throwable $e) {
            $error = json_encode([
                "type" => "system",
                "msg" => "[{$name}]运行出错: ".$e->getMessage()." 行数:".$e->getLine(),
                "time" => time()
            ],320);
            wlog(adapter,$error);
            continue;
        }        
    }
}