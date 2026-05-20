<?php
include(__DIR__."/button.php");


function BOT凭证(){
    $time = 读(".QQBOT/" . adapter, "time", 0);
    
    if (time() < $time) {
        return 读(".QQBOT/" . adapter, "Access", 0);
    } else {
        $url = "https://bots.qq.com/app/getAppAccessToken";
        $appid = config["appid"];
        $secret = config["secret"];
        $json = json_encode([
            "appId" => "{$appid}",
            "clientSecret" => $secret
        ]);
        $header = ['Content-Type: application/json'];
        $fw = curl($url, "POST", $header, $json);
        $fw = json_decode($fw, true);
        $Access = $fw["access_token"];
        $time = $fw["expires_in"];
        
        写(".QQBOT/" . adapter, "time", time() + $time);
        写(".QQBOT/" . adapter, "Access", $Access);
        
        return $Access;
    }
}

function BOTAPI($Address,$me,$json){
    $url = "https://api.sgroup.qq.com".$Address;
    $header = ["Authorization: QQBot ".BOT凭证(), 'Content-Type: application/json'];
    $curl=curl($url,$me,$header,$json);
    return $curl;
}


function 文字($content) {
   $json = [
        "content" => $content,
        "msg_type" => 0,
        "msg_seq" => rand(1,99999)
   ];
   
   switch (事件) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".群号."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
     case "回调":
        $json["event_id"] = 事件ID;
        return json_decode(BOTAPI("/v2/groups/".来源."/files", "POST",$json),true);
        break;
    case "私聊回调":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
   }
}


function 富媒体($type,$image,$name = null) {
    $types = ["图片" => 1, "视频" => 2, "语音" => 3 , "文件" => 4];
    $t = $types[$type] ?? 1;
    if (preg_match('/^http(s)?:\/\//i', $image)) {
        $jsonData = [
            "file_type" => $t,
            "url" => $image,
            "file_name" => $name,
            "srv_send_msg" => false
        ];
    } else {
        $jsonData = [
            "file_type" => $t,
            "file_data" => base64_encode($image),
            "file_name" => $name,
            "srv_send_msg" => false
        ];
    }
    $json = json_encode($jsonData);
        switch (事件) {
           case "加群":
           case "退群":
           case "群聊":
           case "回调":
               return json_decode(BOTAPI("/v2/groups/".群号."/files", "POST",$json),true);
               break;
           case "私聊":
           case "私聊回调":
               return json_decode(BOTAPI("/v2/users/".QQ."/files", "POST",$json),true);
               break;
        }
}


function 图片($image,$content=null) {
   $file_info =富媒体("图片",$image);
   if (isset($file_info['message'])) {
       return 文字($file_info['message']);
   }
   $file = $file_info['file_info'];
   
   $json = [
        "msg_type" => 7,
        "msg_seq" => mt_rand(1, 9999),
        "media" => ["file_info" => $file]
   ];
   
   if ($content !== null) {
       $json["content"] = $content;
   }
   
   switch (事件) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        if (isset($json["content"])) {
            $json["content"] = $json['content'];
        }
        return BOTAPI("/v2/groups/".群号."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
     case "回调":
        $json["event_id"] = 事件ID;
        return json_decode(BOTAPI("/v2/groups/".来源."/files", "POST",$json),true);
        break;
    case "私聊回调":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
   }
}




function 语音($yy) {
   $file_info = 富媒体("语音",$yy);
   if (isset($file_info['message'])) {
       return 文字($file_info['message']);
   }
   $file = $file_info['file_info'];
   
   $json = [
        "msg_type" => 7,
        "msg_seq" => mt_rand(1, 9999),
        "media" => ["file_info" => $file]
   ];
   
   switch (事件) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".群号."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
     case "回调":
        $json["event_id"] = 事件ID;
        return json_decode(BOTAPI("/v2/groups/".来源."/files", "POST",$json),true);
        break;
    case "私聊回调":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
   }
}

function 文件($yy,$nm) {
   $file_info = 富媒体("文件",$yy,$nm);
   if (isset($file_info['message'])) {
       return 文字($file_info['message']);
   }
   $file = $file_info['file_info'];
   
   $json = [
        "msg_type" => 7,
        "msg_seq" => mt_rand(1, 9999),
        "media" => ["file_info" => $file]
   ];
   
   switch (事件) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".群号."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
     case "回调":
        $json["event_id"] = 事件ID;
        return json_decode(BOTAPI("/v2/groups/".来源."/files", "POST",$json),true);
        break;
    case "私聊回调":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
   }
}



function 视频($video) {
   $file_info =富媒体("视频",$video);
   if (isset($file_info['message'])) {
       return 文字($file_info['message']);
   }
   $file = $file_info['file_info'];
   
   $json = [
        "msg_type" => 7,
        "msg_seq" => mt_rand(1, 9999),
        "media" => ["file_info" => $file]
   ];
   
   switch (事件) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".群号."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
     case "回调":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/groups/".群号."/messages","POST",json_encode($json));
        break;
    case "私聊回调":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
        break;
   }
}



function 头像($id){
   return "https://q.qlogo.cn/qqapp/".config["appid"]."/{$id}/640";
}

function BOT信息(){
  return BOTAPI("/users/@me","GET",0);
}

function 文卡(...$items) {
    $list_items = [];
    foreach ($items as $item) {
        if (isset($item['url'])) {
            $list_items[] = [
                "obj_kv" => [
                    ["key" => "desc", "value" => $item['text']],
                    ["key" => "link", "value" => $item['url']]
                ]
            ];
        } else {
            $list_items[] = [
                "obj_kv" => [
                    ["key" => "desc", "value" => $item['text']]
                ]
            ];
        }
    }
    $json = [
        "msg_type" => 3,
        "msg_seq" => mt_rand(1, 9999),
        "ark" => [
            "template_id" => 23,
            "kv" => [
                ["key" => "#DESC#", "value" => "愿为西南风,长逝入君怀"],
                ["key" => "#PROMPT#", "value" => "愿为西南风,长逝入君怀"],
                ["key" => "#LIST#", "obj" => $list_items]
            ]
        ]
    ];
    
    switch (事件) {
         case "群聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/groups/".群号."/messages", "POST", json_encode($json));
         break;
         case "私聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/users/".QQ."/messages", "POST", json_encode($json));
         break;
         case "加群":
         case "退群":
         case "回调":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/groups/".群号."/messages", "POST", json_encode($json));
         break;
         case "私聊回调":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
           break;
    }
}

function 大图($title,$xtitle,$iurl){
    $json = [
        "msg_type" => 3,
        "msg_seq" => mt_rand(1, 9999),
        "ark" => [
            "template_id" => 37,
            "kv" => [
                ["key" => "#METATITLE#", "value" => $title],
                ["key" => "#METASUBTITLE#", "value" => $xtitle],
                ["key" => "#PROMPT#", "value" => "愿为西南风,长逝入君怀"],
                ["key" => "#METACOVER#", "value" => $iurl]
            ]
        ]
    ];
    
    switch (事件) {
         case "群聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/groups/".群号."/messages", "POST", json_encode($json));
         break;
         case "私聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/users/".QQ."/messages", "POST", json_encode($json));
         break;
         case "加群":
         case "退群":
         case "回调":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/groups/".群号."/messages", "POST", json_encode($json));
         break;
         case "私聊回调":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
           break;
    }
}

function 跳转卡($title,$desc,$image,$tz){
    $json = [
        "msg_type" => 3,
        "msg_seq" => mt_rand(1, 9999),
        "ark" => [
            "template_id" => 24,
            "kv" => [
                ["key" => "#DESC#", "value" => "愿为西南风,长逝入君怀"],
                ["key" => "#PROMPT#", "value" => "愿为西南风,长逝入君怀"],
                ["key" => "#TITLE#", "value" => $title],
                ["key" => "#METADESC#", "value" => $desc],
                ["key" => "#IMG#", "value" => $image],
                ["key" => "#LINK#", "value" => $tz],
                ["key" => "#SUBTITLE#", "value" => "愿为西南风,长逝入君怀"]
            ]
        ]
    ];
    
    switch (事件) {
         case "群聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/groups/".群号."/messages", "POST", json_encode($json));
         break;
         case "私聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/users/".QQ."/messages", "POST", json_encode($json));
         break;
         case "加群":
         case "退群":
         case "回调":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/groups/".群号."/messages", "POST", json_encode($json));
         break;
         case "私聊回调":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
           break;
    }
}


function 流式(...$msgs){
    $id = null;
    $index = 0;
    $total = count($msgs);
    foreach ($msgs as $msg) {
        $isLast = ($index === $total - 1);
        $json = [
            "content" => (string)$msg,
            "msg_id" => 消息ID,
            "msg_seq" => rand(1, 99999),
            "stream" => [
                "state" => $isLast ? 10 : 1,
                "id" => $id,
                "index" => $index,
                "reset" => false
            ]
        ];
        $curl = BOTAPI("/v2/users/".QQ."/messages", "POST", json_encode($json));
        $json = json_decode($curl, true);
        $id = $json["id"];
        $index++;
    }
    return $curl;
}

function 撤回($id){
    switch (事件) {
         case "群聊":
         case "加群":
         case "退群":
         case "回调":
             return BOTAPI("/v2/groups/".群号."/messages/".$id,"DELETE","");
         break;
         case "私聊回调":
         case "私聊":
             return BOTAPI("/v2/users/".QQ."/messages/".$id,"DELETE","");
           break;
    }
}

function MD($md, $keyboard = null) {
   $json = [
       "content" => "",
       "msg_type" => 2,
       "msg_seq" => rand(1, 9999),
       "markdown" => [
           "content" => $md,
       ],
       "keyboard" => [
           "content" => json_decode($keyboard,true)
       ]
   ];
   
   switch (事件) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".群号."/messages", "POST", json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".QQ."/messages", "POST", json_encode($json));
        break;
     case "加群":
     case "退群":
     case "回调":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/groups/".群号."/messages", "POST", json_encode($json));
        break;
     case "私聊回调":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/users/".QQ."/messages","POST",json_encode($json));
           break;
   }
}

function MDinput($text,$textt) {
    return "[](mqqapi://aio/inlinecmd?command=%E6%88%91%E6%98%AF%E5%B0%8F%E5%8D%97%E6%A2%81&enter=false)[{$text}](mqqapi://aio/inlinecmd?command={$textt}&enter=false1)";
}

function MDfill($text,$textt) {
    return '<qqbot-cmd-input text="'.$textt.'" show="'.$text.'" reference="false"/>';
}

function QQ_独立_群文字($adapter_id, $group_id, $content) {
    global $config;
    $old_config = $config;
    $config = 读("config", $adapter_id, []);
    $result = BOTAPI("/v2/groups/{$group_id}/messages", "POST", json_encode([
        "content" => $content,
        "msg_type" => 0,
        "msg_seq" => rand(1, 99999)
    ]));
    $config = $old_config;
    return $result;
}

function QQ_独立_私文字($adapter_id, $user_id, $content) {
    global $config;
    $old_config = $config;
    $config = 读("config", $adapter_id, []);
    $result = BOTAPI("/v2/users/{$user_id}/messages", "POST", json_encode([
        "content" => $content,
        "msg_type" => 0,
        "msg_seq" => rand(1, 99999)
    ]));
    $config = $old_config;
    return $result;
}