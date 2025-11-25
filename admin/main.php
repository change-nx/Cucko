<?php
if (!isset($_COOKIE['login'])) {
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
    case "market":
        require_once "market.html";
        break;
}

require_once "footer.html";