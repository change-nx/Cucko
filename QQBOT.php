<?php
ob_start();
// 引入函数/信息文件
require "function/function.php";
define("config",require("config/QQBOT.php"));

// 接收元数据
$raw = file_get_contents("php://input");

// 判断是否为空
if (empty($raw)) {
    wlog("QQBOT",'{"plat_error":"收到未知请求,元数据为空已阻拦"}');
    ob_end_clean();
    die("Request error");
}

// 判断是否来自腾讯
$appid = $_SERVER["HTTP_X_BOT_APPID"] ?? "";
if ($appid != config["appid"]) {
    wlog("QQBOT",'{"plat_error":"收到非官方请求,已阻拦"}');
    ob_end_clean();
    die("Appid error");
}

// 定义相关变量
define("appid",config["appid"]);
define("secret",config["secret"]);

// 检查拓展
if (!function_exists('sodium_crypto_sign_seed_keypair')) {
    wlog("QQBOT",'{"plat_error":"未安装sodium拓展"}');
    ob_end_clean();
    die("sodium error");
}
if (!extension_loaded('sodium')) {
    wlog("QQBOT",'{"plat_error":"未加载sodium拓展"}');
    ob_end_clean();
    die("sodium error");
}

// 开始正式处理
$raw = json_decode($raw,true);
define("raw",$raw);

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
    $sign(raw,secret);
    exit;
}

if ($op == 0) {
    $event_id = raw["id"];
    $event = 读("QQBOT/事件判断/".appid."/".date("Y-m-d"),$event_id,false);
    if($event) {
        wlog("QQBOT",'{"plat_error":"元数据重复上传"}');
        die("error");
     }
    写("QQBOT/事件判断/".appid."/".date("Y-m-d"),$event_id,true);
    wlog("QQBOT",json_encode(raw,JSON_UNESCAPED_UNICODE));
    Main(raw);
}


function Main($raw){
$event = $raw["t"];
switch($event) {
    case "GROUP_AT_MESSAGE_CREATE":
        define("消息来源", "群聊");
        define("消息ID", $raw["d"]["id"]);
        define("消息", trim($raw["d"]["content"], "/ "));
        define("来源", $raw["d"]["group_id"]);
        define("用户", $raw["d"]["author"]["id"]);
        break;

    case "C2C_MESSAGE_CREATE":
        define("消息来源", "私聊");
        define("消息ID", $raw["d"]["id"]);
        define("消息", trim($raw["d"]["content"], "/ "));
        define("来源", $raw["d"]["author"]["id"]);
        define("用户", $raw["d"]["author"]["id"]);
        break;
        
    case "GROUP_ADD_ROBOT":
        define("消息来源", "加群");
        define("事件ID", $raw["id"]);
        define("消息", "[加群]");
        define("来源", $raw["d"]["group_openid"]);
        define("用户", $raw["d"]["op_member_openid"]);
        break;
        
    case "GROUP_DEL_ROBOT":
        define("消息来源", "退群");
        define("事件ID", $raw["id"]);
        define("消息", "[退群]");
        define("来源", $raw["d"]["group_openid"]);
        define("用户", $raw["d"]["op_member_openid"]);
        break;
        
    case "INTERACTION_CREATE":
        define("消息来源", "互动");
        define("事件ID", $raw["id"]);
        define("来源", $raw["d"]["group_openid"]);
        break;
}
  require("function/QQBOT.php");
  load_plugin();
  exit;
}

function load_plugin() {
    $All = glob(__DIR__."/QQBOT/*.php");
    foreach($All as $name) {
        try {
            require_once($name);
        } catch (Throwable $e) {
            $error = json_encode([
                 "plat_error" => "[{$name}]运行出错: ".$e->getMessage()." 行数:".$e->getLine()
            ],JSON_UNESCAPED_UNICODE);
            wlog("QQBOT",$error);
            continue;
        }
    }
}