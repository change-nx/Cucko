<?php
ob_start();
// 引入函数/信息文件
require "function/function.php";
define("config",require("config/onebot.php"));

// 接收元数据
$raw = file_get_contents("php://input");

// 判断是否为空
if (empty($raw)) {
    wlog("onebot",'{"plat_error":"收到未知请求,元数据为空已阻拦"}');
    ob_end_clean();
    die("Request error");
}

// 判断Http Token
$Signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
if (!empty($Signature)) {
    $Sign = 'sha1=' . hash_hmac('sha1',$raw,config["http_token"]);
    if (!hash_equals($Sign,$Signature)) {
        wlog("onebot",'{"plat_error":"收到未知事件,token错误已阻拦}');
        ob_end_clean();
        die("token error");
    }
}


// 开始正式处理
wlog("onebot",$raw);
$raw = json_decode($raw,true);

// 设置相关变量
define("raw",$raw);
define("登录账号",config["qq"]);
require("function/onebot.php");

// 判断自触
if(raw["user_id"]==config["qq"]&&!config["touch"])exit;

// 进行事件判断
$post_type = raw["post_type"];
$other_type = raw[$post_type."_type"];
$sub_type = raw["sub_type"];

switch ($post_type) {
    case "message":
    case "message_sent":
        switch ($other_type) {
            case "group":
            case "self":
                define("消息来源","群聊");
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
            case "private":
                define("消息来源","私聊");
                define("消息",CQ_message(raw["raw_message"]));
                define("消息ID",raw["message_id"]);
                define("QQ",raw["user_id"]);
                define("昵称",raw["sender"]["nickname"]);
            break;
        }
    break;
    case "notice":
        switch ($other_type) {
             case "group_increase":
                 define("消息来源","群聊-入群");
                 define("QQ",$data["user_id"]);
                 define("群号",$data["group_id"]);
             break;
             case "group_decrease":
                 define("消息来源","群聊-退群");
                 define("QQ",$data["user_id"]);
                 define("群号",$data["group_id"]);
             break;
             case "group_recall":
                 define("消息来源","群聊-消息撤回");
                 define("QQ",$data["user_id"]);
                 define("群号",$data["group_id"]);
                 define("消息ID",$data["message_id"]);
             break;
             case "friend_recall":
                 define("消息来源","私聊-消息撤回");
                 define("QQ",$data["user_id"]);
                 define("消息ID",$data["message_id"]);
             break;
             case "group_ban":
                 if ($sub_type == "ban") {
                     define("消息来源","群聊-群成员禁言");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                     define("操作者",$data["operator_id"]);
                     define("禁言时长",$data["duration"]);
                 } elseif ($sub_type == "lift_ban") {
                     define("消息来源","群聊-群成员解禁");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                     define("操作者",$data["operator_id"]);
                 }
             break;
             case "group_admin":
                 $sub_type = $data["sub_type"];
                 if ($sub_type == "set") {
                     define("消息来源","群聊-管理员添加");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                 } elseif ($sub_type == "unset") {
                     define("消息来源","群聊-管理员减少");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                 }
             break;
        }
    break;
    case "meta_event":
        switch ($other_type) {
            case "heartbeat":
                define("消息来源","心跳");
            break;
        }
    break;
    case "request":
        if ($other_type == "group") {
            switch ($sub_type) {
                case "add":
                     define("消息来源","群聊-申请入群");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                     define("申请ID",$data["flag"]);
                break;
            }
        }
    break;
}

// 加载插件
load_plugin();
ob_end_clean();
$json = [
    "status" => true
];
$json = json_encode($json,480);
echo $json;
exit;

function load_plugin() {
    $All = glob(__DIR__."/Onebot/*.php");
    foreach($All as $name) {
        try {
            require_once($name);
        } catch (Throwable $e) {
            $error = json_encode([
                 "plat_error" => "[{$name}]运行出错: ".$e->getMessage()." 行数:".$e->getLine()
            ],JSON_UNESCAPED_UNICODE);
            wlog("onebot",$error);
            continue;
        }
    }
}