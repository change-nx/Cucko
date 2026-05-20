<?php
/**
 * 定时任务示例插件
 * 展示如何在插件中创建和管理定时任务
 */

// 插件命名空间（可选）
namespace Cuckoo\CronExample;

// 定义常量
define('CRON_EXAMPLE_PATH', __DIR__);
define('CRON_EXAMPLE_ADAPTER', adapter);

// 处理群聊消息
if (defined('消息') && 消息 == '定时测试') {
    if (defined('群号')) {
        群(群号, text("定时任务测试插件已加载！\n\n"),
           text("可用命令：\n"),
           text("定时添加 - 添加每日提醒任务\n"),
           text("定时删除 - 删除提醒任务\n"),
           text("定时列表 - 查看所有任务"));
    }
}

if (defined('消息') && 消息 == '定时添加') {
    if (defined('群号')) {
        // 创建定时任务脚本
        $脚本内容 = '<?php
require_once __DIR__ . \'/function/other/CronHelper.php\';
$适配器ID = \'' . CRON_EXAMPLE_ADAPTER . '\';
$群号 = \'' . (defined('群号') ? 群号 : '') . '\';
$时间 = date(\'Y-m-d H:i:s\');
$消息 = "【每日提醒】\\n\\n现在是 {$时间}\\n祝您今天愉快！";
定时_QQ_群消息($适配器ID, $群号, $消息);
';
        
        $脚本路径 = CRON_EXAMPLE_PATH . '/daily_reminder.php';
        file_put_contents($脚本路径, $脚本内容);
        
        // 添加定时任务
        $结果 = 定时::添加(
            'daily_reminder_' . md5(CRON_EXAMPLE_ADAPTER . (defined('群号') ? 群号 : '')),
            '每天8点',
            $脚本路径
        );
        
        if ($结果['success']) {
            群(群号, text("定时任务添加成功！\n每天8点会发送提醒"));
        } else {
            群(群号, text("添加失败：" . $结果['message']));
        }
    }
}

if (defined('消息') && 消息 == '定时删除') {
    if (defined('群号')) {
        $结果 = 定时::删除('daily_reminder_' . md5(CRON_EXAMPLE_ADAPTER . (defined('群号') ? 群号 : '')));
        
        if ($结果['success']) {
            群(群号, text("定时任务已删除"));
        } else {
            群(群号, text("删除失败：" . $结果['message']));
        }
    }
}

if (defined('消息') && 消息 == '定时列表') {
    if (defined('群号')) {
        $任务列表 = json_decode(定时::列表(), true);
        $回复 = "当前定时任务：\n";
        
        if (empty($任务列表)) {
            $回复 .= "暂无定时任务";
        } else {
            foreach ($任务列表 as $ID => $任务) {
                $回复 .= "ID: {$ID}\n";
                $回复 .= "时间: {$任务['时间']}\n";
                $回复 .= "脚本: {$任务['脚本']}\n\n";
            }
        }
        
        群(群号, text($回复));
    }
}
