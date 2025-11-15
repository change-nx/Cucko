<?php
require("function/proto_en.php");
require("function/proto_de.php");

$http_url = info["url"];
$token = info["url_token"];

function BOTAPI($url,$json) {
global $http_url;
global $token;
$url = $http_url . $url;
$header = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
];
return curl($url,"POST",$header,$json);
}

function text($content) {
$json = [
    "type" => "text",
    "data" => [
        "text" => $content
    ]
];
return $json;
}

function reply($content) {
$json = [
    "type" => "reply",
    "data" => [
        "id" => $content
    ]
];
return $json;
}

function face($id) {
$json = [
    "type" => "face",
    "data" => [
        "id" => $id
    ]
];
return $json;
}

function image($content) {
$json = [
    "type" => "image",
    "data" => [
        "file" => $content
    ]
];
return $json;
}

function video($content) {
$json = [
    "type" => "video",
    "data" => [
        "file" => $content
    ]
];
return $json;
}

function music($type,$id) {
$json = [
    "type" => "music",
    "data" => [
        "type" => (string)$type,
        "id" => (string)$id
    ]
];
return $json;
}

function at($content) {
$json = [
    "type" => "at",
    "data" => [
        "qq" => $content
    ]
];
return $json;
}

function kp($content) {
$json = [
    "type" => "json",
    "data" => [
        "data" => $content
    ]
];
return $json;
}

function 伪造($QQ,$name,...$msgs) {
$json = [
    "type" => "node",
    "data" => [
        "uin" => $QQ,
        "name" => $name,
        "content" => []
    ]
];
    foreach ($msgs as $msg) {
        $json["data"]["content"][] = $msg;
    }
    return $json;
}

function 群伪造($group,...$msgs) {
$json = [
    "group_id" => $group,
    "messages" => []
];
    foreach ($msgs as $msg) {
        $json["messages"][] = $msg;
    }
    $json = json_encode($json);
    return BOTAPI("/send_group_forward_msg",$json);
}

function 私伪造($group,...$msgs) {
$json = [
    "user_id" => $group,
    "messages" => []
];
    foreach ($msgs as $msg) {
        $json["messages"][] = $msg;
    }
    $json = json_encode($json);
    return BOTAPI("/send_private_forward_msg",$json);
}


function 群($group,...$msgs) {
$json = [
    "group_id" => $group,
    "message" => []
];
    foreach ($msgs as $msg) {
        $json["message"][] = $msg;
    }
    $json = json_encode($json);
    return BOTAPI("/send_group_msg",$json);
}

function 私($group,...$msgs) {
$json = [
    "user_id" => $group,
    "message" => []
];
    foreach ($msgs as $msg) {
        $json["message"][] = $msg;
    }
    $json = json_encode($json);
    return BOTAPI("/send_private_msg",$json);
}

function 点赞($QQ,$times) {
$json = [
    "user_id" => $QQ,
    "times" => $times
];
$json = json_encode($json);
return BOTAPI("/send_like",$json);
}

function 好友列表() {
$r = BOTAPI("/get_friend_list",0);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 删除好友($QQ) {
$json = [
    "user_id" => $QQ
];
$json = json_encode($json);
return BOTAPI("/delete_friend",$json);
}

function 获取陌生人信息($QQ) {
$json = [
    "user_id" => $QQ
];
$json = json_encode($json);
$r = BOTAPI("/get_stranger_info",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 设置头像($url) {
$json = [
    "file" => $url
];
$json = json_encode($json);
return BOTAPI("/set_qq_avatar",$json);
}

function 群列表() {
$r = BOTAPI("/get_group_list",0);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 群详情($QQ) {
$json = [
    "group_id" => $QQ
];
$json = json_encode($json);
$r = BOTAPI("/get_group_info",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 群成员列表($QQ) {
$json = [
    "group_id" => $QQ
];
$json = json_encode($json);
$r = BOTAPI("/get_group_member_list",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 群成员信息($group,$QQ) {
$json = [
    "group_id" => $group,
    "user_id" => $QQ
];
$json = json_encode($json);
$r = BOTAPI("/get_group_member_info",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 设置群管理($group,$QQ) {
$json = [
    "group_id" => $group,
    "user_id" => $QQ,
    "enable" => true
];
$json = json_encode($json);
return BOTAPI("/set_group_admin",$json);
}

function 取消群管理($group,$QQ) {
$json = [
    "group_id" => $group,
    "user_id" => $QQ,
    "enable" => false
];
$json = json_encode($json);
return BOTAPI("/set_group_admin",$json);
}

function 设置群名片($group,$QQ,$name) {
$json = [
    "group_id" => $group,
    "user_id" => $QQ,
    "card" => $name
];
$json = json_encode($json);
$r = BOTAPI("/set_group_card",$json);
return $r;
}

function 禁言($group,$QQ,$time) {
$json = [
    "group_id" => $group,
    "user_id" => $QQ,
    "duration" => $time
];
$json = json_encode($json);
return BOTAPI("/set_group_ban",$json);
}

function 解禁($group,$QQ) {
$json = [
    "group_id" => $group,
    "user_id" => $QQ,
    "duration" => 0
];
$json = json_encode($json);
return BOTAPI("/set_group_ban",$json);
}

function 全体禁言($group) {
$json = [
    "group_id" => $group,
    "enable" => true
];
$json = json_encode($json);
return BOTAPI("/set_group_whole_ban",$json);
}

function 全体解禁($group) {
$json = [
    "group_id" => $group,
    "enable" => false
];
$json = json_encode($json);
return BOTAPI("/set_group_whole_ban",$json);
}

function 禁言列表($QQ) {
$json = [
    "group_id" => $QQ
];
$json = json_encode($json);
$r = BOTAPI("/get_group_shut_list",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}


function 设置群名($group,$name) {
$json = [
    "group_id" => $group,
    "group_name" => $name
];
$json = json_encode($json);
return BOTAPI("/set_group_name",$json);
}

function 踢($group,$QQ,$status = false) {
$json = [
    "group_id" => $group,
    "user_id" => $QQ,
    "reject_add_request" => $status
];
$json = json_encode($json);
return BOTAPI("/set_group_kick",$json);
}

function 批量踢($group,...$QQ) {
$json = [
    "group_id" => $group,
    "user_id" => [],
    "reject_add_request" => false
];
    foreach ($QQ as $qq) {
        $json["user_id"][] = $qq;
    }
    $json = json_encode($json);
    return BOTAPI("/set_group_kick_members",$json);
}

function 设置群头衔($group,$QQ,$title) {
$json = [
    "group_id" => $group,
    "user_id" => $QQ,
    "special_title" => $title
];
$json = json_encode($json);
return BOTAPI("/set_group_special_title",$json);
}


function 设置群公告($group,$content,$image=null) {
$json = [
    "group_id" => $group,
    "content" => $content,
    "image" => $image
];
$json = json_encode($json);
return BOTAPI("/_send_group_notice",$json);
}

function 撤回($id) {
$json = [
    "message_id" => $id
];
$json = json_encode($json);
return BOTAPI("/delete_msg",$json);
}

function 消息详情($QQ) {
$json = [
    "message_id" => $QQ
];
$json = json_encode($json);
$r = BOTAPI("/get_msg",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 贴表情($id,$emoji) {
$json = [
    "message_id" => $id,
    "emoji_id" => $emoji,
    "set" => true
];
$json = json_encode($json);
return BOTAPI("/set_msg_emoji_like",$json);
}

function 群文件($group,$file,$name,$folder = null) {
$json=json_encode([
    "group_id"=>$group,
    "file"=>$file,
    "name"=>$name,
    "folder_id"=>$folder_id
]);
return BOTAPI("/upload_group_file",$json);
}

function 取消息($raw,$type) {
$image = [];
$QQ = [];
$json = json_decode($raw,true);
$message = $json["message"];
    foreach ($message as $value) {
        $t = $value["type"];
        if ($type==$t&&$type=="reply") {
            return $value["data"]["id"];
        } elseif ($type==$t&&$type=="json") {
            return $value["data"]["data"];
        } elseif ($type==$t&&$type=="audio") {
            return $value["data"]["url"];
        } elseif ($type==$t&&$type=="video") {
            return $value["data"]["url"];
        } elseif ($type==$t&&$type=="at") {
            $QQ[]=$value["data"]["qq"];
        } elseif ($type==$t&&$type=="image") {
            $image[]=$value["data"]["url"];
        }
    }
    if ($type=="at") {
        return $QQ;
    } elseif ($type=="image") {
        return $image;
    }
}


function AI语音($group,$id,$msg) {
$json = [
    "group_id" => $group,
    "character" => $id,
    "text" => $msg
];
$json = json_encode($json);
$r = BOTAPI("/send_group_ai_record",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function AI语音角色列表($group) {
$json = [
    "group_id" => $group
];
$json = json_encode($json);
$r = BOTAPI("/get_ai_characters",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 音乐卡片($type,$title,$desc,$url,$image,$audio) {
    $json = json_encode([
        "type" => $type,
        "title" => $title,
        "singer" => $desc,
        "url" => $url,
        "image" => $image,
        "audio" => $audio
    ]);
    $url = "https://ss.xingzhige.com/music_card/card";
    $header = [
        'Content-Type: application/json'
    ];
    return curl($url,"POST",$header,$json);
}

function 群头像($group) {
return "http://p.qlogo.cn/gh/{$group}/{$group}/640";
}

function 头像($QQ) {
return "http://q1.qlogo.cn/g?b=qq&nk={$QQ}&s=640";
}

function owner($QQ) {
    $owner = require("owner.php");
    if (in_array($QQ,$owner)) {
        return true;
    } else {
        return false;
    }
}

function owner_list() {
    $owner = require("owner.php");
    return json_encode($owner,480);
}

function 群打卡($group) {
$json = [
    "group_id" => $group
];
$json = json_encode($json);
$r = BOTAPI("/set_group_sign",$json);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function clientkey() {
$r = BOTAPI("/get_clientkey",0);
$json = json_decode($r,true);
return $json["data"]["clientkey"];
}


function 登录号信息() {
$r = BOTAPI("/get_login_info",0);
$json = json_decode($r,true);
return json_encode($json["data"],480);
}

function 处理加群申请($flag,$approve) {
$json = [
    "flag" => $flag,
    "approve" => $approve
];
$json = json_encode($json);
$r = BOTAPI("/set_group_add_request",$json);
return $r;
}

function 发包($cmd,$pb) {
$jsonData = json_decode($pb,true);
$serializedData = ProtobufSerializer::serializeJsonToProtobuf($jsonData);
$hex = bin2hex($serializedData);
$json = [
    "cmd" => $cmd,
    "data" => $hex
];
$json = json_encode($json);
$r = BOTAPI("/send_packet",$json);
$json = json_decode($r,true);
$input = $json["data"];
$binaryData = hex2bin($input);
$deserializedData = ProtobufDeserializer::deserialize($binaryData);
$jsonReadyData = convertForJson($deserializedData);
$json = json_encode($jsonReadyData,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    array_walk_recursive($jsonReadyData, function(&$value) {
        if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
            $value = 'hex->' . strtoupper(bin2hex($value));
        }
    });
    $json = json_encode($jsonReadyData,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
return $json;
}

function 群历史消息($group,$count) {
$json = [
    "group_id" => $group,
    "count" => $count,
];
$json = json_encode($json);
$r = BOTAPI("/get_group_msg_history",$json);
$json = json_decode($r,true);
return json_encode($json["data"]["messages"],480);
}