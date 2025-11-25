<?php
define("admin",require("admin.php"));
define("info",require("info.php"));

$type = $_REQUEST["type"];

switch ($type) {
    case "login":
        $username = $_REQUEST["username"];
        $password = $_REQUEST["password"];
        if ($username == admin["username"] && $password == admin["password"]) {
            $cookie_value = base64_encode($username . "|" . time());
            setcookie("login", $cookie_value, time() + 86400, "/", "", false, true);
            echo json_encode([
                "status" => true,
                "msg" => "登录成功"
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "msg" => "登录失败"
            ]);
        }
        break;
        
    case "check":
        if(isset($_COOKIE['login'])) {
            $cookie_data = base64_decode($_COOKIE['login']);
            $username = explode("|", $cookie_data)[0];
            
            echo json_encode([
                "status" => true,
                "msg" => "已登录",
                "username" => $username
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "msg" => "未登录"
            ]);
        }
        break;
        
    case "logout":
        setcookie("login", "", time() - 3600, "/");
        echo json_encode([
            "status" => true,
            "msg" => "退出成功"
        ]);
        break;
     
    case "home":
        $log = 0;
        $plugin = 0;
        $logFiles = scandir("log");
        foreach($logFiles as $file) {
            if($file != "." && $file != "..") {
                $fullPath = "log" . DIRECTORY_SEPARATOR . $file;
                if(is_file($fullPath)) {
                    $lines = count(file($fullPath));
                    $log += $lines;
                }
            }
        }
        $pluginFiles = scandir("plugin");
        foreach($pluginFiles as $file) {
            if($file != "." && $file != ".." && is_file("plugin" . DIRECTORY_SEPARATOR . $file)) {
                $plugin++;
            }
        }
        echo json_encode([
            "QQ" => info["登录账号"],
            "image" => "http://q1.qlogo.cn/g?b=qq&nk=" . info["登录账号"] . "&s=640",
            "log" => $log,
            "plugin" => $plugin
        ]);
        break;
    case "log":
        $filename = "log/".date("Y-m-d").".log";
        $result = [
            'QQ' => info["登录账号"],
            '报错' => [],
            '群聊' => [],
            '私聊' => []
        ];
        
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (trim($line) === '' || strpos($line, '0x%5B%5D=androxgh0st') !== false) {
                continue;
            }
            $jsonData = json_decode($line, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $result['报错'][] = $line;
                continue;
            }
            
            if (isset($jsonData['raw_message'])) {
                $re = $jsonData['raw_message'];
                $re = preg_replace("/\[CQ:image[^\]]*\]/", "[图片]", $re);
                $re = preg_replace("/\[CQ:at[^\]]*\]/", "[艾特]", $re);
                $re = preg_replace("/\[CQ:face[^\]]*\]/", "[表情]", $re);
                $re = preg_replace("/\[CQ:record[^\]]*\]/", "[语音]", $re);
                $re = preg_replace("/\[CQ:video[^\]]*\]/", "[视频]", $re);
                $re = preg_replace("/\[CQ:json[^\]]*\]/", "[卡片]", $re);
                $re = preg_replace("/\[CQ:reply[^\]]*\]/", "[回复]", $re);
                $re = preg_replace("/\[CQ:file[^\]]*\]/", "[文件]", $re);
                $re = preg_replace("/\[CQ:markdown[^\]]*\]/", "[markdown]", $re);
                $re = preg_replace("/\[CQ:forward[^\]]*\]/", "[转发]", $re);
                
                $rejson = [
                    '&amp;' => '&',
                    '&#91;' => '[',
                    '&#93;' => ']',
                    '&#44;' => ','
                ];
                $jsonData['raw_message'] = str_replace(array_keys($rejson), array_values($rejson), $re);
            }
            
            if (isset($jsonData['group_id'])) {
                $groupId = $jsonData['group_id'];
                if (!isset($result['群聊'][$groupId])) {
                    $result['群聊'][$groupId] = [];
                }
                $result['群聊'][$groupId][] = $jsonData;
            } elseif (isset($jsonData['user_id'])) {
                $userId = $jsonData['user_id'];
                if (!isset($result['私聊'][$userId])) {
                    $result['私聊'][$userId] = [];
                }
                $result['私聊'][$userId][] = $jsonData;
            }
        }
        echo json_encode($result);
        break;
}
