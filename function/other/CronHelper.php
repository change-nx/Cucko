<?php
/**
 * 定时任务辅助类
 * 用于简化定时任务的创建和消息发送
 */

function 定时任务_初始化() {
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
    }
    require_once ROOT_PATH . '/function/function.php';
    require_once ROOT_PATH . '/function/QQ/main.php';
    require_once ROOT_PATH . '/function/OneBot/main.php';
}

/**
 * QQ 适配器发送群消息（定时任务用）
 * @param string $adapter_id 适配器ID
 * @param string $group_id 群号
 * @param string $content 消息内容
 * @return mixed API响应
 */
function 定时_QQ_群消息($adapter_id, $group_id, $content) {
    定时任务_初始化();
    return QQ_独立_群文字($adapter_id, $group_id, $content);
}

/**
 * QQ 适配器发送私聊消息（定时任务用）
 * @param string $adapter_id 适配器ID
 * @param string $user_id 用户ID
 * @param string $content 消息内容
 * @return mixed API响应
 */
function 定时_QQ_私消息($adapter_id, $user_id, $content) {
    定时任务_初始化();
    return QQ_独立_私文字($adapter_id, $user_id, $content);
}

/**
 * OneBot 适配器发送群消息（定时任务用）
 * @param string $adapter_id 适配器ID
 * @param string $group_id 群号
 * @param mixed ...$msgs 消息内容（可以是 text()、image() 等消息段）
 * @return mixed API响应
 */
function 定时_OB_群消息($adapter_id, $group_id, ...$msgs) {
    定时任务_初始化();
    return OB_独立_群消息($adapter_id, $group_id, ...$msgs);
}

/**
 * OneBot 适配器发送私聊消息（定时任务用）
 * @param string $adapter_id 适配器ID
 * @param string $user_id 用户ID
 * @param mixed ...$msgs 消息内容（可以是 text()、image() 等消息段）
 * @return mixed API响应
 */
function 定时_OB_私消息($adapter_id, $user_id, ...$msgs) {
    定时任务_初始化();
    return OB_独立_私消息($adapter_id, $user_id, ...$msgs);
}

/**
 * 快速创建定时任务（包装函数）
 * @param string $task_id 任务ID
 * @param string $time 时间（人性化格式）
 * @param string $script_path 脚本绝对路径
 * @return array 操作结果
 */
function 定时_添加($task_id, $time, $script_path) {
    定时任务_初始化();
    return 定时::添加($task_id, $time, $script_path);
}

/**
 * 删除定时任务
 * @param string $task_id 任务ID
 * @return array 操作结果
 */
function 定时_删除($task_id) {
    定时任务_初始化();
    return 定时::删除($task_id);
}

/**
 * 获取所有定时任务
 * @return array 任务列表
 */
function 定时_列表() {
    定时任务_初始化();
    $result = 定时::列表();
    return json_decode($result, true) ?: [];
}
