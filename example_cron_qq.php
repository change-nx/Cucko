<?php
/**
 * 示例：QQ 适配器定时任务脚本
 * 每天早上8点发送群消息
 */

require_once __DIR__ . '/function/other/CronHelper.php';

// 配置参数
$适配器ID = 'your_adapter_id_here'; // 替换为实际的适配器ID
$群号 = '123456789'; // 替换为实际的群号

// 发送消息
$时间 = date('Y-m-d H:i:s');
$消息 = "【每日提醒】\n\n";
$消息 .= "现在是 {$时间}\n";
$消息 .= "祝您今天愉快！";

echo "正在发送消息...\n";
$result = 定时_QQ_群消息($适配器ID, $群号, $消息);
echo "发送结果: " . $result . "\n";
