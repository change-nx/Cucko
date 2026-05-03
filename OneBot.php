<?php
define("adapter",config["id"]);

$Signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
if (!empty($Signature)) {
    $Sign = 'sha1=' . hash_hmac('sha1',json_encode(raw,320),config["token"]);
    if (!hash_equals($Sign,$Signature)) {
        wlog(adapter,json_encode(["type"=>"system","msg"=>"收到未知推送","time"=>time()],320));
        exit;
    }
}

require("function/OneBot/main.php");

$event = event_type(raw);
define("time",raw["time"]);
define("uin",raw["self_id"]);
define("event",$event);

switch ($event) {
    case "群聊":
        define("消息",CQ_message(raw["raw_message"]));
        define("消息ID",raw["message_id"]);
        define("QQ",raw["user_id"]);
        define("昵称",raw["sender"]["nickname"]);
        switch (raw["sender"]["role"]) {
            case "owner":
                define("身份","群主");
            break;
            case "admin":
                define("身份","管理员");
            break;
            case "member":
                define("身份","群员");
            break;
        }
        define("群号",raw["group_id"]);
        define("群名",raw["group_name"]);
        break;
    case "私聊":
        define("消息",CQ_message(raw["raw_message"]));
        define("消息ID",raw["message_id"]);
        define("QQ",raw["user_id"]);
        define("昵称",raw["sender"]["nickname"]);
        break;
    case "群聊-文件上传":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        define("文件ID",raw["file"]["id"]);
        define("文件名",raw["file"]["name"]);
        define("文件大小",raw["file"]["size"]);
        break;
    case "群聊-管理员-设置":
    case "群聊-管理员-取消":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        break;
    case "群聊-退群":
    case "群聊-进群":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        define("操作者",raw["operator_id"]);
        break;
    case "群聊-禁言":
    case "群聊-解禁":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        define("操作者",raw["operator_id"]);
        define("禁言时长",raw["duration"]);
        break;
    case "好友-添加":
        define("QQ",raw["user_id"]);
        break;
    case "群聊-撤回":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        define("操作者",raw["operator_id"]);
        define("消息ID",raw["message_id"]);
        break;
    case "好友-撤回":
        define("QQ",raw["user_id"]);
        define("消息ID",raw["message_id"]);
        break;
    case "群聊-戳一戳":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        define("被戳者",raw["target_id"]);
        break;
    case "群聊-昵称变更":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        define("旧昵称",raw["card_old"]);
        define("新昵称",raw["card_new"]);
        break;
    case "群聊-头衔变更":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        define("头衔",raw["title"]);
        break;
    case "请求-好友":
        define("QQ",raw["user_id"]);
        define("验证信息",raw["comment"]);
        define("验证ID",raw["flag"]);
        break;
    case "请求-群聊":
    case "请求-被邀":
        define("群号",raw["group_id"]);
        define("QQ",raw["user_id"]);
        define("验证信息",raw["comment"]);
        define("验证ID",raw["flag"]);
        break;
}


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