<?php
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

function 群文字($msg,$group,$content) {
   $json = [
        "content" => "$content",
        "msg_type" => 0,
        "msg_seq" => rand(1,99999),
        "msg_id" => $msg,
   ];
   return BOTAPI("/v2/groups/".$group."/messages","POST",json_encode($json));
}

function 私文字($msg,$group,$content) {
   $json = [
        "content" => "$content",
        "msg_type" => 0,
        "msg_seq" => rand(1,99999),
        "msg_id" => $msg,
   ];
   return BOTAPI("/v2/users/".$group."/messages","POST",json_encode($json));
}

function 事件群文字($msg,$group,$content) {
   $json = [
        "content" => "$content",
        "msg_type" => 0,
        "msg_seq" => rand(1,99999),
        "event_id" => $msg,
   ];
   return BOTAPI("/v2/groups/".$group."/messages","POST",json_encode($json));
}

function 事件私文字($msg,$group,$content) {
   $json = [
        "content" => "$content",
        "msg_type" => 0,
        "msg_seq" => rand(1,99999),
        "event_id" => $msg,
   ];
   return BOTAPI("/v2/users/".$group."/messages","POST",json_encode($json));
}