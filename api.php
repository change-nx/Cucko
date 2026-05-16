<?php
$raw = file_get_contents("php://input");

if (empty($raw)) {
    echo json_encode(["code"=>-1,"msg"=>"传入数据为空"],480);
    exit;
}

$data = json_decode($raw,true);
$value = $data["data"];

switch ($data["type"]) {
    case "adapter_list":
        adapter_list();
        break;
    case "adapter_add":
        adapter_add($value);
        break;
    case "adapter_del":
        adapter_del($value);
        break;
    case "plugin_list":
        plugin_list($value);
        break;
    case "plugin_add":
        plugin_add($value);
        break;
    case "plugin_del":
        plugin_del($value);
        break;
    case "log_list":
        log_list();
        break;
    case "log_content":
        log_content($value);
        break;
    case "log_del":
        log_del($value);
        break;
    case "version":
        version_info();
        break;
}

function adapter_list() {
    $path = __DIR__ . "/Adapter.json";
    if (!file_exists($path)) {
        file_put_contents($path,"{}");
        $list = [];
    } else {
        $content = file_get_contents($path);
        $list = json_decode($content, true);
    }
    
    echo json_encode([
        "code" => 200,
        "msg" => "获取成功",
        "data" => $list
    ],480);
}

function adapter_add($data) {
    $path = __DIR__ . "/Adapter.json";
    if (!file_exists($path)) {
        file_put_contents($path,"[]");
        $list = [];
    } else {
        $content = file_get_contents($path);
        $list = json_decode($content, true);
    }
    
    $list[] = $data;
    
    if (file_put_contents($path, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        echo json_encode([
            "code" => 200,
            "msg" => "添加成功",
            "data" => $list
        ],480);
    } else {
        echo json_encode([
            "code" => -1,
            "msg" => "添加失败"
        ],480);
    }
}

function adapter_del($value) {
    $path = __DIR__ . "/Adapter.json";
    if (!file_exists($path)) {
        echo json_encode(["code"=>-1,"msg"=>"文件不存在"],480);
        return;
    }
    
    $content = file_get_contents($path);
    $list = json_decode($content, true);
    
    $id = $value["id"];
    $newList = [];
    foreach ($list as $item) {
        if ($item["id"] !== $id) {
            $newList[] = $item;
        }
    }
    
    if (file_put_contents($path, json_encode($newList, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        echo json_encode([
            "code" => 200,
            "msg" => "删除成功",
            "data" => $newList
        ],480);
    } else {
        echo json_encode([
            "code" => -1,
            "msg" => "删除失败"
        ],480);
    }
}

function plugin_list($value) {
    $adapter = $value["adapter"];
    $pluginPath = __DIR__ . "/plugin/" . $adapter;
    
    if (!is_dir($pluginPath)) {
        echo json_encode([
            "code" => 200,
            "msg" => "获取成功",
            "data" => []
        ],480);
        return;
    }
    
    $plugins = [];
    $dirs = scandir($pluginPath);
    
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') continue;
        if (!is_dir($pluginPath . "/" . $dir)) continue;
        
        $infoFile = $pluginPath . "/" . $dir . "/info.json";
        if (file_exists($infoFile)) {
            $infoContent = file_get_contents($infoFile);
            $info = json_decode($infoContent, true);
            if ($info) {
                $plugins[] = $info;
            }
        }
    }
    
    echo json_encode([
        "code" => 200,
        "msg" => "获取成功",
        "data" => $plugins
    ],480);
}

function plugin_add($value) {
    $adapter = $value["adapter"];
    $pluginId = $value["id"];
    $pluginPath = __DIR__ . "/plugin/" . $adapter . "/" . $pluginId;
    
    if (is_dir($pluginPath)) {
        echo json_encode([
            "code" => -1,
            "msg" => "插件已存在"
        ],480);
        return;
    }
    
    if (!mkdir($pluginPath, 0755, true)) {
        echo json_encode([
            "code" => -1,
            "msg" => "创建失败"
        ],480);
        return;
    }
    
    $info = [
        "id" => $pluginId,
        "name" => $value["name"],
        "author" => $value["author"],
        "desc" => $value["desc"]
    ];
    file_put_contents($pluginPath . "/info.json", json_encode($info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    
    file_put_contents($pluginPath . "/backend.json", "{}");
    file_put_contents($pluginPath . "/main.php", "<?php\n");
    file_put_contents($pluginPath . "/README.md", "# " . $info["name"] . "\n\n" . $info["desc"]);
    
    mkdir($pluginPath . "/web", 0755, true);
    file_put_contents($pluginPath . "/web/index.html", "<!DOCTYPE html>\n<html>\n<head>\n    <title>" . $info["name"] . "</title>\n</head>\n<body>\n    <h1>插件创建成功</h1>\n</body>\n</html>");
    
    echo json_encode([
        "code" => 200,
        "msg" => "添加成功",
        "data" => $info
    ],480);
}

function plugin_del($value) {
    $adapter = $value["adapter"];
    $pluginId = $value["id"];
    $pluginPath = __DIR__ . "/plugin/" . $adapter . "/" . $pluginId;
    
    if (!is_dir($pluginPath)) {
        echo json_encode([
            "code" => -1,
            "msg" => "插件不存在"
        ],480);
        return;
    }
    
    function deleteDir($dir) {
        if (!is_dir($dir)) return false;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . "/" . $file;
            is_dir($path) ? deleteDir($path) : unlink($path);
        }
        return rmdir($dir);
    }
    
    if (deleteDir($pluginPath)) {
        echo json_encode([
            "code" => 200,
            "msg" => "删除成功"
        ],480);
    } else {
        echo json_encode([
            "code" => -1,
            "msg" => "删除失败"
        ],480);
    }
}

function log_list() {
    $logPath = __DIR__ . "/Log";
    
    if (!is_dir($logPath)) {
        mkdir($logPath, 0755, true);
    }
    
    $logs = [];
    $files = scandir($logPath);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $fullPath = $logPath . "/" . $file;
        if (is_file($fullPath)) {
            $logs[] = [
                "name" => $file,
                "size" => filesize($fullPath),
                "time" => filemtime($fullPath)
            ];
        }
    }
    
    usort($logs, function($a, $b) {
        return $b["time"] - $a["time"];
    });
    
    echo json_encode([
        "code" => 200,
        "msg" => "获取成功",
        "data" => $logs
    ],480);
}

function log_content($value) {
    $logPath = __DIR__ . "/Log";
    $name = $value["name"];
    $fullPath = $logPath . "/" . $name;
    
    if (!file_exists($fullPath)) {
        echo json_encode([
            "code" => -1,
            "msg" => "日志文件不存在"
        ],480);
        return;
    }
    
    $content = file_get_contents($fullPath);
    
    echo json_encode([
        "code" => 200,
        "msg" => "获取成功",
        "data" => [
            "name" => $name,
            "content" => $content
        ]
    ],480);
}

function log_del($value) {
    $logPath = __DIR__ . "/Log";
    $name = $value["name"];
    $fullPath = $logPath . "/" . $name;
    
    if (!file_exists($fullPath)) {
        echo json_encode([
            "code" => -1,
            "msg" => "日志文件不存在"
        ],480);
        return;
    }
    
    if (unlink($fullPath)) {
        echo json_encode([
            "code" => 200,
            "msg" => "删除成功"
        ],480);
    } else {
        echo json_encode([
            "code" => -1,
            "msg" => "删除失败"
        ],480);
    }
}

function version_info() {
    $path = __DIR__ . "/version.json";
    
    if (!file_exists($path)) {
        file_put_contents($path, "[]");
    }
    
    $content = file_get_contents($path);
    $version = json_decode($content, true);
    
    echo json_encode([
        "code" => 200,
        "msg" => "获取成功",
        "data" => $version
    ],480);
}
