<?php
require "function/function.php";
//ob_start();

$headers = getallheaders();
$raw = file_get_contents("php://input");

$Adapter = file_get_contents("Adapter.json");
$Adapter = json_decode($Adapter,true);

$index_QQ = array_column($Adapter,null,'QQ');
$index_Appid = array_column($Adapter,null,'appid');
if (isset($headers["X-Self-Id"])) {
    $config = $index_QQ[$headers["X-Self-Id"]];
} elseif (isset($headers["X-Bot-Appid"])) {
    $config = $index_Appid[$headers["X-Bot-Appid"]];
}

if (empty($config)) exit;

wlog($config["id"],$raw);

$adapter_type = $config["type"];
$raw = json_decode($raw,true);

define("raw",$raw);
define("config",$config);

switch ($adapter_type) {
    case "OneBot":
        require("OneBot.php");
        break;
    case "QQ":
        require("QQ.php");
        break;
}

//ob_end_clean();