<?php
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$request = json_decode($raw, true);

if (!isset($request['type'])) {
    response(['code' => 400, 'msg' => '缺少 type 参数']);
}

$type = $request['type'];
$data = $request['data'] ?? [];

switch ($type) {
    case 'adapter_list':
        adapter_list();
        break;
    case 'adapter_add':
        adapter_add($data);
        break;
    case 'adapter_del':
        adapter_del($data);
        break;
    case 'log_list':
        log_list($data);
        break;
    case 'log_content':
        log_content($data);
        break;
    case 'log_del':
        log_del($data);
        break;
    case 'plugin_list':
        plugin_list($data);
        break;
    case 'plugin_add':
        plugin_add($data);
        break;
    case 'plugin_del':
        plugin_del($data);
        break;
    case 'plugin_content':
        plugin_content($data);
        break;
    case 'plugin_write':
        plugin_write($data);
        break;
    default:
        response(['code' => 400, 'msg' => '未知的 type: ' . $type]);
}

function response($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function get_adapters() {
    $file = __DIR__ . '/Adapter.json';
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function save_adapters($adapters) {
    $file = __DIR__ . '/Adapter.json';
    file_put_contents($file, json_encode($adapters, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function adapter_list() {
    $adapters = get_adapters();
    response(['code' => 200, 'data' => $adapters]);
}

function adapter_add($data) {
    if (!isset($data['id']) || !isset($data['type'])) {
        response(['code' => 400, 'msg' => '缺少必要参数']);
    }
    
    $adapters = get_adapters();
    foreach ($adapters as $a) {
        if ($a['id'] === $data['id']) {
            response(['code' => 400, 'msg' => '适配器ID已存在']);
        }
    }
    
    $adapters[] = $data;
    save_adapters($adapters);
    
    $pluginDir = __DIR__ . '/plugin/' . $data['id'];
    if (!is_dir($pluginDir)) {
        mkdir($pluginDir, 0777, true);
    }
    
    response(['code' => 200, 'msg' => '添加成功']);
}

function adapter_del($data) {
    if (!isset($data['id'])) {
        response(['code' => 400, 'msg' => '缺少 id 参数']);
    }
    
    $adapters = get_adapters();
    $found = false;
    foreach ($adapters as $i => $a) {
        if ($a['id'] === $data['id']) {
            unset($adapters[$i]);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        response(['code' => 404, 'msg' => '适配器不存在']);
    }
    
    save_adapters(array_values($adapters));
    response(['code' => 200, 'msg' => '删除成功']);
}

function log_list($data) {
    $adapter = $data['adapter'] ?? null;
    $logDir = __DIR__ . '/Log';
    if (!is_dir($logDir)) {
        response(['code' => 200, 'data' => []]);
    }
    
    $list = [];
    $dirs = $adapter ? [$logDir . '/' . $adapter] : glob($logDir . '/*', GLOB_ONLYDIR);
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;
        $adapterName = basename($dir);
        $files = glob($dir . '/*.log');
        foreach ($files as $file) {
            $list[] = [
                'adapter' => $adapterName,
                'file' => basename($file),
                'size' => filesize($file),
                'mtime' => filemtime($file)
            ];
        }
    }
    
    response(['code' => 200, 'data' => $list]);
}

function log_content($data) {
    if (!isset($data['adapter']) || !isset($data['file'])) {
        response(['code' => 400, 'msg' => '缺少必要参数']);
    }
    
    $file = __DIR__ . '/Log/' . $data['adapter'] . '/' . $data['file'];
    if (!file_exists($file)) {
        response(['code' => 404, 'msg' => '文件不存在']);
    }
    
    $content = file_get_contents($file);
    response(['code' => 200, 'data' => $content]);
}

function log_del($data) {
    if (!isset($data['adapter']) || !isset($data['file'])) {
        response(['code' => 400, 'msg' => '缺少必要参数']);
    }
    
    $file = __DIR__ . '/Log/' . $data['adapter'] . '/' . $data['file'];
    if (!file_exists($file)) {
        response(['code' => 404, 'msg' => '文件不存在']);
    }
    
    unlink($file);
    response(['code' => 200, 'msg' => '删除成功']);
}

function plugin_list($data) {
    $adapter = $data['adapter'] ?? null;
    $pluginBaseDir = __DIR__ . '/plugin';
    if (!is_dir($pluginBaseDir)) {
        response(['code' => 200, 'data' => []]);
    }
    
    $list = [];
    $adapterDirs = $adapter ? [$pluginBaseDir . '/' . $adapter] : glob($pluginBaseDir . '/*', GLOB_ONLYDIR);
    
    foreach ($adapterDirs as $adapterDir) {
        if (!is_dir($adapterDir)) continue;
        $adapterName = basename($adapterDir);
        $pluginDirs = glob($adapterDir . '/*', GLOB_ONLYDIR);
        
        foreach ($pluginDirs as $pluginDir) {
            $pluginName = basename($pluginDir);
            $infoFile = $pluginDir . '/info.json';
            $mainFile = $pluginDir . '/main.php';
            $backendFile = $pluginDir . '/backend.php';
            $mdFile = $pluginDir . '/README.md';
            $webDir = $pluginDir . '/web';
            $webIndexFile = $webDir . '/index.php';
            
            $info = [];
            if (file_exists($infoFile)) {
                $info = json_decode(file_get_contents($infoFile), true) ?: [];
            }
            
            $hasBackend = file_exists($backendFile);
            $hasMD = file_exists($mdFile);
            $hasWebIndex = file_exists($webIndexFile);
            $webPath = $hasWebIndex ? 'plugin/' . $adapterName . '/' . $pluginName . '/web/index.php' : null;
            
            $list[] = [
                'adapter' => $adapterName,
                'name' => $pluginName,
                'info' => $info,
                'hasBackend' => $hasBackend,
                'hasMD' => $hasMD,
                'hasWebIndex' => $hasWebIndex,
                'webPath' => $webPath
            ];
        }
    }
    
    response(['code' => 200, 'data' => $list]);
}

function plugin_add($data) {
    if (!isset($data['adapter']) || !isset($data['name'])) {
        response(['code' => 400, 'msg' => '缺少必要参数']);
    }
    
    $pluginDir = __DIR__ . '/plugin/' . $data['adapter'] . '/' . $data['name'];
    if (is_dir($pluginDir)) {
        response(['code' => 400, 'msg' => '插件已存在']);
    }
    
    mkdir($pluginDir, 0777, true);
    
    $info = $data['info'] ?? [];
    $infoFile = $pluginDir . '/info.json';
    file_put_contents($infoFile, json_encode($info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    
    $mainContent = $data['main'] ?? "<?php\n";
    $mainFile = $pluginDir . '/main.php';
    file_put_contents($mainFile, $mainContent);
    
    response(['code' => 200, 'msg' => '添加成功']);
}

function plugin_del($data) {
    if (!isset($data['adapter']) || !isset($data['name'])) {
        response(['code' => 400, 'msg' => '缺少必要参数']);
    }
    
    $pluginDir = __DIR__ . '/plugin/' . $data['adapter'] . '/' . $data['name'];
    if (!is_dir($pluginDir)) {
        response(['code' => 404, 'msg' => '插件不存在']);
    }
    
    delete_dir($pluginDir);
    response(['code' => 200, 'msg' => '删除成功']);
}

function plugin_content($data) {
    if (!isset($data['adapter']) || !isset($data['name']) || !isset($data['file'])) {
        response(['code' => 400, 'msg' => '缺少必要参数']);
    }
    
    $file = __DIR__ . '/plugin/' . $data['adapter'] . '/' . $data['name'] . '/' . $data['file'];
    if (!file_exists($file)) {
        response(['code' => 404, 'msg' => '文件不存在']);
    }
    
    $content = file_get_contents($file);
    response(['code' => 200, 'data' => $content]);
}

function plugin_write($data) {
    if (!isset($data['adapter']) || !isset($data['name']) || !isset($data['file']) || !isset($data['content'])) {
        response(['code' => 400, 'msg' => '缺少必要参数']);
    }
    
    $file = __DIR__ . '/plugin/' . $data['adapter'] . '/' . $data['name'] . '/' . $data['file'];
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    file_put_contents($file, $data['content']);
    response(['code' => 200, 'msg' => '写入成功']);
}

function delete_dir($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? delete_dir($path) : unlink($path);
    }
    rmdir($dir);
}
