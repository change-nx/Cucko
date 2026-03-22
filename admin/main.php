<?php
if (!isset($_COOKIE['Cucko'])) {
    header('Location: index.html');
    exit;
}

$type = $_REQUEST["type"];

require_once "header.html";

switch ($type) {
    case "main":
        require_once "home.html";
        break;
    case "log":
        require_once "log.html";
        break;
    case "plugin":
        require_once "plugin.html";
        break;
    case "set":
        require_once "set.html";
        break;
}

require_once "footer.html";