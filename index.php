<?php
ob_start();
require("function.php");
define("info",require("info.php"));

$raw = file_get_contents("php://input");
if (empty($raw)) {
    ob_end_clean();
    die("Cucko运行成功");
}


$Signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
if (!empty($Signature)) {
    $Sign = 'sha1=' . hash_hmac('sha1',$raw,info["http_token"]);
    if (!hash_equals($Sign,$Signature)) {
        http_response_code(401);
        wlog("HTTP_Token验证失败");
        exit;
    }
}

$data = json_decode($raw,true);
wlog($raw);
define("raw",$raw);
$post_type = $data["post_type"];


switch ($post_type) {
    case "message":
        $message_type = $data["message_type"];
        switch ($message_type) {
            case "group":
                define("消息来源","群聊");
                define("消息",CQ_message($data["raw_message"]));
                define("群号",$data["group_id"]);
                define("消息ID",$data["message_id"]);
                define("QQ",$data["user_id"]);
                define("昵称",$data["sender"]["nickname"]);
                define("群昵称",$data["sender"]["card"]);
                define("群身份",$data["sender"]["role"]);
                define("头衔",$data["sender"]["title"]);
            break;
            case "private":
                define("消息来源","私聊");
                define("消息",CQ_message($data["raw_message"]));
                define("消息ID",$data["message_id"]);
                define("QQ",$data["user_id"]);
                define("昵称",$data["sender"]["nickname"]);
            break;
        }
    break;
    case "notice":
        $notice_type = $data["notice_type"];
        switch ($notice_type) {
             case "group_increase":
                 define("消息来源","有人入群");
                 define("QQ",$data["user_id"]);
                 define("群号",$data["group_id"]);
             break;
             case "group_decrease":
                 define("消息来源","有人退群");
                 define("QQ",$data["user_id"]);
                 define("群号",$data["group_id"]);
             break;
             case "group_recall":
                 define("消息来源","群消息撤回");
                 define("QQ",$data["user_id"]);
                 define("群号",$data["group_id"]);
                 define("消息ID",$data["message_id"]);
             break;
             case "friend_recall":
                 define("消息来源","好友消息撤回");
                 define("QQ",$data["user_id"]);
                 define("消息ID",$data["message_id"]);
             break;
             case "group_ban":
                 $sub_type = $data["sub_type"];
                 if ($sub_type == "ban") {
                     define("消息来源","群成员禁言");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                     define("操作者",$data["operator_id"]);
                     define("禁言时长",$data["duration"]);
                 } elseif ($sub_type == "lift_ban") {
                     define("消息来源","群成员解禁");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                     define("操作者",$data["operator_id"]);
                 }
             break;
             case "group_admin":
                 $sub_type = $data["sub_type"];
                 if ($sub_type == "set") {
                     define("消息来源","管理员添加");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                 } elseif ($sub_type == "unset") {
                     define("消息来源","管理员减少");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                 }
             break;
        }
    break;
    case "meta_event":
        $meta_event_type = $data["meta_event_type"];
        switch ($meta_event_type) {
            case "heartbeat":
                define("消息来源","心跳");
            break;
        }
    break;
    case "request":
        $request_type = $data["request_type"];
        $sub_type = $data["sub_type"];
        if ($request_type == "group") {
            switch ($sub_type) {
                case "add":
                     define("消息来源","有人申请入群");
                     define("QQ",$data["user_id"]);
                     define("群号",$data["group_id"]);
                     define("申请ID",$data["flag"]);
                break;
            }
        }
    break;
    
}
require("bot.php");
load();
ob_end_clean();
$json = [
    "status" => true
];
$json = json_encode($json,480);
echo $json;
exit;


function load() {
    $All = glob(__DIR__."/plugin/*.php");
    foreach($All as $name) {
        try {
            require_once($name);
        } catch (Throwable $e) {
            wlog("插件加载失败: ".$name." 错误: ".$e->getMessage()." 行数: ".$e->getLine());
            continue;
        }
    }
}