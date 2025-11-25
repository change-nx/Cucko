<?php
include(__DIR__."/function/qrcode.php");

function wlog($content) {
    $date = date('Y-m-d H:i:s');
    $logDir = "log/";
    $logFile = $logDir . '/' . date('Y-m-d') . '.log';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logContent = "{$content}" . PHP_EOL;
    file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
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

function curl($url, $method, $headers, $params){
$url = str_replace(" ", "%20", $url);
    if (is_array($params)) {
        $requestString = http_build_query($params);
    } else {
        $requestString = $params ? : '';
    }
    if (empty($headers)) {
        $headers = array('Content-type: text/json'); 
    } elseif (!is_array($headers)) {
        parse_str($headers,$headers);
    }
    // setting the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // turning off the server and peer verification(TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    // setting the POST FIELD to curl
    switch ($method){  
        case "GET" : curl_setopt($ch, CURLOPT_HTTPGET, 1);break;  
        case "POST": curl_setopt($ch, CURLOPT_POST, 1);
                     curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);break;  
        case "PUT" : curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");   
                     curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);break;  
        case "DELETE":  curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");   
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);break;  
    }
    // getting response from server
    $response = curl_exec($ch);
    
    //close the connection
    curl_close($ch);
    
    //return the response
    if (stristr($response, 'HTTP 404') || $response == '') {
        return array('Error' => '请求错误');
    }
    return $response;
}

function 前缀后($str,$prefix) {
    if (strpos($str,$prefix) !== false) {
        return substr($str, strlen($prefix));
    } else {
        return $str;
    }
}
function 前缀($str,$prefix) {
    if (strpos($str,$prefix) === 0) {     
        return true;
    } else {
       
        return false;
    }
}


function 写($文件, $键, $值) {
    $文件路径 = "database/" . $文件;
    $目录 = dirname($文件路径);
    if (!is_dir($目录)) {
        if (!mkdir($目录, 0777, true)) {
            error_log("无法创建目录: {$目录}");
            return false;
        }
    }
    $fp = fopen($文件路径, "c+");
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }
    try {
        $内容 = filesize($文件路径) > 0 ? fread($fp, filesize($文件路径)) : '{}';
        $数据 = json_decode($内容, true) ?: [];
        $数据[$键] = $值;
        $json = json_encode($数据, JSON_UNESCAPED_UNICODE);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $json);
        return true;
    } catch (Exception $e) {
        error_log("写入文件出错: {$文件路径}, 错误: " . $e->getMessage());
        return false;
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

function 读($文件, $键, $默认值 = null) {
    $文件路径 = "database/" . $文件;
    if (!file_exists($文件路径)) {
        return $默认值;
    }
    $fp = fopen($文件路径, "r");
    if (!flock($fp, LOCK_SH)) {
        fclose($fp);
        return $默认值;
    }
    try {
        $内容 = fread($fp, filesize($文件路径));
        $数据 = json_decode($内容, true);
        return $数据[$键] ?? $默认值;
    } catch (Exception $e) {
        return $默认值;
    } finally {
        flock($fp, LOCK_UN); 
        fclose($fp);
    }
}

function 二维码($content){
ob_start();
Toplib_Lib_QRcode::png($content, false, QR_ECLEVEL_L, 7, 1, false, [255,255,255], [0,0,0]);
return base64_encode(ob_get_clean());
}