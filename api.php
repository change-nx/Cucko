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
}


function adapter_list() {
    $path = __DIR__ . "/Adapter.json";
    if (!$path) {
        file_put_contents($path,"{}");
        $list = [];
    } else {
        $content = file_get_contents($path);
        $list = json_decode($content,true);
    }
    
    echo json_encode([
        "code" => 200,
        "msg" => "获取成功",
        "data" => $list
    ],480);
}

/*
{
    "id": "适配器昵称",
    "QQ": QQ号,
    "type": "OneBot",
    "token": "token",
    "API": "http://127.0.0.1:3000",
    "APItoken": "APItoken"
}
{
    "id": "适配器名称",
    "type": "QQ",
    "appid": "appid",
    "secret": "secret"
}
*/
function adapter_add($data) {
    $path = __DIR__ . "/Adapter.json";
    if (!$path) {
        file_put_contents($path,"{}");
        $list = [];
    } else {
        $content = file_get_contents($path);
        $list = json_decode($content,true);
    }
    
    $list[] = $data;
    file_put_contents($path,
        json_encode($list,480)
    );
    echo json_encode([
        "code" => 200,
        "msg" => "添加成功",
    ],480);
}

function adapter_del($data) {
    $path = __DIR__ . "/Adapter.json";
    if (!$path) {
        file_put_contents($path,"{}");
        $list = [];
    } else {
        $content = file_get_contents($path);
        $list = json_decode($content,true);
    }
    
    $name = $data["adapter"];
    foreach ($list as $index => $json) {
        $id = $json["id"];
        if ($name == $id) {
            unset($list[$index]);
            break;
        } else {
            continue;
        }
    }
    
    file_put_contents($path,
        json_encode(
            array_values($list)
        ,480)
    );
    echo json_encode([
        "code" => 200,
        "msg" => "删除成功",
    ],480);
}


function plugin_list($data) {
    $adapter = $data["adapter"];
    $path = __DIR__ . "/plugin/{$adapter}";
    $list = scandir($path);
    $ls = [];
    
    foreach ($list as $file) {
        $info_path = "{$path}/{$file}/info.json";
        $backend = "{$path}/{$file}/backend.json";
        $reamde = "{$path}/{$file}/REAMDE.md";
        $web = "{$path}/{$file}/web/index.html";
        
        if (!is_file($info_path)) continue;        
        $info = file_get_contents($info_path);
        
        if (!is_file($backend)) {
            $backend = false;
        } else {
            $backend = file_get_contents($backend);
        }
        
        if (!is_file($reamde)) {
            $reamde = false;
        } else {
            $reamde = file_get_contents($reamde);
        }
        
        if (!is_file($web)) {
            $web = false;
        } else {
            $web = "/plugin/{$adapter}/{$file}/web/index.html";
        }
        
        $ls[] = [
            "info" => $info,
            "backend" => $backend,
            "REAMDE" => $reamde,
            "web" => $web
        ];
    }
    echo json_encode($ls,480);
}