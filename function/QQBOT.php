<?php
function BOT凭证(){
       $time=读("QQBOT/function/".appid,"time",0);
       if (time() < $time) {
         return 读("QQBOT/function/".appid,"Access",0);
       } else {
         $url="https://bots.qq.com/app/getAppAccessToken";
         $appid=appid;
         $secret=secret;
         $json=json_encode([
         "appId"=>"{$appid}",
         "clientSecret"=>$secret
         ]);
         $header=['Content-Type: application/json'];
         $fw=curl($url,"POST",$header,$json);
         $fw=json_decode($fw,true);
         $Access=$fw["access_token"];
         $time=$fw["expires_in"];
         写("QQBOT/function/".appid,"time",time()+$time);
         写("QQBOT/function/".appid,"Access",$Access);
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
        "content" => "\n$content",
        "msg_type" => 0,
        "msg_seq" => rand(1,99999)
   ];
   
   switch (消息来源) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".来源."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
     case "文字子频道":
        unset($json["msg_type"], $json["msg_seq"]);
        $json["msg_id"] = 消息ID;
        return BOTAPI("/channels/".来源."/messages","POST",json_encode($json));
        break;
   }
}


function 富媒体($type,$image) {
    $types = ["图片" => 1, "视频" => 2, "语音" => 3];
    $t = $types[$type] ?? 1;
    if (preg_match('/^http(s)?:\/\//i', $image)) {
        $jsonData = [
            "file_type" => $t,
            "url" => $image,
            "srv_send_msg" => false
        ];
    } else {
        $jsonData = [
            "file_type" => $t,
            "file_data" => base64_encode($image),
            "srv_send_msg" => false
        ];
    }
    $json = json_encode($jsonData);
        switch (消息来源) {
           case "加群":
           case "退群":
           case "群聊":
               return json_decode(BOTAPI("/v2/groups/".来源."/files", "POST",$json),true);
               break;
           case "私聊":
               return json_decode(BOTAPI("/v2/users/".来源."/files", "POST",$json),true);
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
   
   switch (消息来源) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        if (isset($json["content"])) {
            $json["content"] = "\n{$json['content']}";
        }
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".来源."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
     case "文字子频道":
        $json = [
             "content" => $content,
             "file_image" => $image,
             "msg_id" => 消息ID
         ];
         return BOTAPI("/channels/".来源."/messages","POST",json_encode($json));
         break;
   }
}


function silk($link){
    $link = str_replace("&","%26",$link);
    $url = "https://oiapi.net/API/Mp32Silk?url=".$link;
    $r = json_decode(curl($url,"GET",[],''), true);
    return $r["message"] ?? '';
}



function 语音($yy) {
   $silk = silk($yy);
   $file_info = 富媒体("语音",$silk);
   if (isset($file_info['message'])) {
       return 文字($file_info['message']);
   }
   $file = $file_info['file_info'];
   
   $json = [
        "msg_type" => 7,
        "msg_seq" => mt_rand(1, 9999),
        "media" => ["file_info" => $file]
   ];
   
   switch (消息来源) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".来源."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
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
   
   switch (消息来源) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".来源."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
   }
}


function 按钮($key) {
   $json = [
        "msg_type" => 2,
        "msg_seq" => mt_rand(1, 9999),
        "keyboard" => [
            "id" => $key
        ]
   ];
   
   switch (消息来源) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".来源."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
   }
}

function 头像($id){
   return "https://q.qlogo.cn/qqapp/".appid."/{$id}/640";
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
    
    switch (消息来源) {
         case "群聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/groups/".来源."/messages", "POST", json_encode($json));
         break;
         case "私聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/users/".来源."/messages", "POST", json_encode($json));
         break;
         case "加群":
         case "退群":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/groups/".来源."/messages", "POST", json_encode($json));
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
    
    switch (消息来源) {
         case "群聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/groups/".来源."/messages", "POST", json_encode($json));
         break;
         case "私聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/users/".来源."/messages", "POST", json_encode($json));
         break;
         case "加群":
         case "退群":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/groups/".来源."/messages", "POST", json_encode($json));
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
    
    switch (消息来源) {
         case "群聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/groups/".来源."/messages", "POST", json_encode($json));
         break;
         case "私聊":
           $json["msg_id"] = 消息ID;
           return BOTAPI("/v2/users/".来源."/messages", "POST", json_encode($json));
         break;
         case "加群":
         case "退群":
           $json["event_id"] = 事件ID;
           return BOTAPI("/v2/groups/".来源."/messages", "POST", json_encode($json));
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
        $curl = BOTAPI("/v2/users/".来源."/messages", "POST", json_encode($json));
        $json = json_decode($curl, true);
        $id = $json["id"];
        $index++;
    }
    return $curl;
}

function 撤回($id){
   $type = [
      "群聊"=>"groups",
      "私聊"=>"users"
   ];
   $type = $type[消息来源];
   return BOTAPI("/v2/{$type}/".来源."/messages/".$id,"DELETE","");
}

function MD($id,$md,$keyboard = null) {
   $json = [
       "content" => "",
       "msg_type" => 2,
       "msg_seq" => rand(1,9999),
       "markdown" => [
           "custom_template_id" => $id,
           "params" => [],
       ],
       "keyboard" => [
           "id" => $keyboard
       ]
   ];
   
   foreach ($md as $name => $value) {
       $json["markdown"]["params"][] = [
           "key" => $name,
           "values" => [$value]
       ];
   }
   
   switch (消息来源) {
     case "群聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
     case "私聊":
        $json["msg_id"] = 消息ID;
        return BOTAPI("/v2/users/".来源."/messages","POST",json_encode($json));
        break;
     case "加群":
     case "退群":
        $json["event_id"] = 事件ID;
        return BOTAPI("/v2/groups/".来源."/messages","POST",json_encode($json));
        break;
   }
}