<?php
require(__DIR__."/Proto/proto_en.php");
require(__DIR__."/Proto/proto_de.php");

$api = config["API"];
$token = config["APItoken"];

function BOTAPI($url,$json) {
    global $api;
    global $token;
    $url = $api . $url;
    $header = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ];
    return curl($url,"POST",$header,$json);
}

function event_type($raw) {
    $post_type = $raw["post_type"];
    $type = $raw[$post_type . "_type"];
    $sub_type = $raw["sub_type"];
    $all = "{$post_type}-{$type}-{$sub_type}";
    
    $json = [
        // 消息
        "message-group-normal" => "群聊",
        "message-private-friend" => "私聊",
        "message-private-group" => "私聊",
        "message-private-other" => "私聊",
        // 通知
        "notice-group_upload-" => "群聊-文件上传",
        "notice-group_admin-set" => "群聊-管理员-设置",
        "notice-group_admin-unset" => "群聊-管理员-取消",
        "notice-group_decrease-leave" => "群聊-退群",
        "notice-group_decrease-kick" => "群聊-退群",
        "notice-group_decrease-kick_me" => "群聊-退群",
        "notice-group_increase-approve" => "群聊-进群",
        "notice-group_increase-invite" => "群聊-进群",
        "notice-group_ban-ban" => "群聊-禁言",
        "notice-group_ban-lift_ban" => "群聊-解禁",
        "notice-group_recall-" =>"群聊-撤回",
        "notice-notify-poke-" => "群聊-戳一戳",
        "notice-group_card-" => "群聊-昵称变更",
        "notice-notify-title-" => "群聊-头衔变更",
        "notice-friend_add-" => "好友-添加",
        "notice-friend_recall" => "好友-撤回",
        // 自身消息
        "message_sent-private-friend" => "私聊",
        "message_sent-private-group" => "私聊",
        "message_sent-group-normal" => "群聊",
        // 请求
        "request-friend-" => "请求-好友",
        "request-group-add" => "请求-群聊",
        "request-group-invite" => "请求-被邀"
    ];
    
    return $json[$all];
}

function CQ_message($raw) {
$re = preg_replace("/\[CQ:image[^\]]*\]/","[图片]",$raw);
$re = preg_replace("/\[CQ:at[^\]]*\]/","[艾特]",$re);
$re = preg_replace("/\[CQ:face[^\]]*\]/","[表情]",$re);
$re = preg_replace("/\[CQ:record[^\]]*\]/","[语音]",$re);
$re = preg_replace("/\[CQ:video[^\]]*\]/","[视频]",$re);
$re = preg_replace("/\[CQ:json[^\]]*\]/","[卡片]",$re);
$re = preg_replace("/\[CQ:reply[^\]]*\]/","[回复]",$re);
$re = preg_replace("/\[CQ:file[^\]]*\]/","[文件]",$re);
$re = preg_replace("/\[CQ:markdown[^\]]*\]/","[markdown]",$re);
$rejson = [
    '&amp;' => '&',
    '&#91;' => '[',
    '&#93;' => ']',
    '&#44;' => ','
];
return str_replace(array_keys($rejson),array_values($rejson),$re);
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


function 群详细信息($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_group_detail_info",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群列表() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/get_group_list",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群成员列表($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_group_member_list",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群成员信息($group,$user) {
    $json = [
        "group_id" => $group,
        "user_id" => $user
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_group_member_info",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 处理加群请求($group,$approve,$reason=null) {
    $json = [
        "group_id" => $group,
        "approve" => $approve,
        "reason" => $reason
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_add_request",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 退群($group,$is_dismiss=false) {
    $json = [
        "group_id" => $group,
        "is_dismiss" => $is_dismiss
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_leave",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群头像($group) {
    return "http://p.qlogo.cn/gh/{$group}/{$group}/640";
}

function 头像($QQ) {
    return "http://q1.qlogo.cn/g?b=qq&nk={$QQ}&s=640";
}

function 取消息($raw,$type) {
$image = [];
$QQ = [];
$message = $raw["message"];
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

function 全体禁言($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_whole_ban",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 禁言($group,$user,$time) {
    $json = [
        "group_id" => $group,
        "user_id" => $user,
        "duration" => $time
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_ban",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 踢($group,$user,$back=false) {
    $json = [
        "group_id" => $group,
        "user_id" => $user,
        "reject_add_request" => $back
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_kick",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 设置群管理员($group,$user,$back=true) {
    $json = [
        "group_id" => $group,
        "user_id" => $user,
        "enable" => $back
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_admin",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 设置群名称($group,$name) {
    $json = [
        "group_id" => $group,
        "group_name" => $name
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_name",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 设置群名片($group,$user,$name) {
    $json = [
        "group_id" => $group,
        "user_id" => $user,
        "card" => $name
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_card",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群公告列表($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/_get_group_notice",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群精华消息列表($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_essence_msg_list",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群忽略通知列表($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_group_ignored_notifies",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 移除群精华消息($msgid) {
    $json = [
        "message_id" => $msgid
    ];
    $json = json_encode($json);
    $response = BOTAPI("/delete_essence_msg",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 设置群精华消息($msgid) {
    $json = [
        "message_id" => $msgid
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_essence_msg",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 删除群公告($group,$notice) {
    $json = [
        "group_id" => $group,
        "notice_id" => $notice
    ];
    $json = json_encode($json);
    $response = BOTAPI("/_del_group_notice",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}


function 群禁言列表($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_group_shut_list",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群打卡($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_sign",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群戳一戳($group,$qq) {
    $json = [
        "group_id" => $group,
        "target_id" => $qq
    ];
    $json = json_encode($json);
    $response = BOTAPI("/group_poke",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 私戳一戳($qq) {
    $json = [
        "user_id" => $qq
    ];
    $json = json_encode($json);
    $response = BOTAPI("/group_poke",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 发送群公告($group,$msg,$image=null) {
    $json = [
        "group_id" => $group,
        "content" => $msg,
        "image" => $image
    ];
    $json = json_encode($json);
    $response = BOTAPI("/_send_group_notice",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 创建收藏($title,$content) {
    $json = [
        "brief" => $title,
        "rawData" => $content
    ];
    $json = json_encode($json);
    $response = BOTAPI("/create_collection",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 设置个性签名($msg) {
    $json = [
        "longNick" => $msg
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_self_longnick",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 设置QQ头像($file) {
    $json = [
        "file" => $file
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_qq_avatar",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 英文单词翻译($msg) {
    $json = [
        "words" => $msg
    ];
    $json = json_encode($json);
    $response = BOTAPI("/translate_en2zh",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function Clientkey() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/get_clientkey",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 设置头衔($group,$user,$title) {
    $json = [
        "group_id" => $group,
        "user_id" => $user,
        "special_title" => $title
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_group_special_title",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function AI角色列表($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_ai_characters",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 登录号信息() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/get_login_info",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 版本信息() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/get_version_info",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function CSRF_token() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/get_csrf_token",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 登录凭证($domain) {
    $json = [
        "domain" => $domain
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_credentials",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 重启服务() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/set_restart",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 群系统消息($count=50) {
    $json = [
        "count" => $count
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_group_system_msg",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 清理缓存() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/clean_cache",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 点赞($user,$time) {
    $json = [
        "user_id" => $user,
        "times" => $time
    ];
    $json = json_encode($json);
    $response = BOTAPI("/send_like",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 好友列表() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/get_friend_list",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 处理加好友请求($flag,$approve) {
    $json = [
        "flag" => $flag,
        "approve" => $approve
    ];
    $json = json_encode($json);
    $response = BOTAPI("/set_friend_add_request",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function Cookies($domain) {
    $json = [
        "domain" => $domain
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_cookies",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function Rkey() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/get_rkey",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function Rkey服务器() {
    $json = [];
    $json = json_encode($json);
    $response = BOTAPI("/get_rkey_server",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 收藏列表($count) {
    $json = [
        "count" => $count,
        "category" => 0
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_collection_list",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
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

function 分享群Ark($group) {
    $json = [
        "group_id" => $group
    ];
    $json = json_encode($json);
    $response = BOTAPI("/ArkShareGroup",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 分享用户Ark($user) {
    $json = [
        "user_id" => $user
    ];
    $json = json_encode($json);
    $response = BOTAPI("/ArkSharePeer",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 点击按钮($appid,$group,$button,$button_id=1) {
    $json = [
        "group_id" => $group,
        "bot_appid" => $appid,
        "button_id" => $button_id,
        "callback_data" => $button,
        "msg_seq" => rand(1,50)
    ];
    $json = json_encode($json);
    $response = BOTAPI("/click_inline_keyboard_button",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 获取文件($id) {
    $json = [
        "file_id" => $file
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_file",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 获取图片($id) {
    $json = [
        "file_id" => $file
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_image",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 获取语音($id) {
    $json = [
        "file_id" => $file,
        "out_format" => "mp3"
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_record",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 获取群文件URL($group,$file) {
    $json = [
        "group_id" => $group,
        "file_id" => $file
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_group_file_url",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 获取私文件URL($user,$file) {
    $json = [
        "user_id" => $user,
        "file_id" => $file
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_private_file_url",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 获取消息($id) {
    $json = [
        "message_id" => $id
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_msg",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 撤回消息($id) {
    $json = [
        "message_id" => $id
    ];
    $json = json_encode($json);
    $response = BOTAPI("/delete_msg",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 获取AI语音($group,$id,$text) {
    $json = [
        "charactere_id" => $id,
        "group_id" => $group,
        "text" => $text
    ];
    $json = json_encode($json);
    $response = BOTAPI("/get_ai_record",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}

function 发送AI语音($group,$id,$text) {
    $json = [
        "charactere_id" => $id,
        "group_id" => $group,
        "text" => $text
    ];
    $json = json_encode($json);
    $response = BOTAPI("/send_group_ai_record",$json);
    $response_json = json_decode($response,true);
    $status = $response_json["status"];
    if ($status == "ok") {
        return json_encode($response_json["data"],480);
    } else {
        return false;
    }
}