<?php
/**
 * OneBot示例插件主文件
 *
 * 本插件演示了如何编写OneBot协议的QQ机器人插件
 *
 * @author 开发者
 * @version 1.0.0
 */

// 插件初始化（可选）
function onebot_example_plugin_init() {
    wlog('onebot_example_plugin', 'OneBot示例插件已加载');
}

// 消息处理主函数
function onebot_example_plugin_handle_message($raw) {
    // 解析原始消息
    $msg = isset($raw['raw_message']) ? $raw['raw_message'] : '';
    
    // 获取群号和QQ号
    $group_id = isset($raw['group_id']) ? $raw['group_id'] : 0;
    $user_id = isset($raw['user_id']) ? $raw['user_id'] : 0;
    
    // 处理命令
    if (onebot_处理_你好($msg, $group_id, $user_id)) {
        return true; // 消息已处理
    }
    
    if (onebot_处理_帮助($msg, $group_id, $user_id)) {
        return true;
    }
    
    if (onebot_处理_天气($msg, $group_id, $user_id)) {
        return true;
    }
    
    return false; // 未匹配任何命令
}

// 处理 #你好 命令
function onebot_处理_你好($msg, $group_id, $user_id) {
    if (trim($msg) === '#你好') {
        $reply = "你好！我是OneBot示例插件~ 很高兴为你服务！\n发送 #帮助 查看更多功能";
        
        if ($group_id) {
            // 群聊消息
            群($group_id, text($reply));
        } else {
            // 私聊消息
            私($user_id, text($reply));
        }
        
        wlog('onebot_example_plugin', "处理了 #你好 命令 from {$user_id}");
        return true;
    }
    return false;
}

// 处理 #帮助 命令
function onebot_处理_帮助($msg, $group_id, $user_id) {
    if (trim($msg) === '#帮助') {
        $help = "📚 OneBot示例插件帮助\n\n" .
                "#你好 - 与机器人打招呼\n" .
                "#天气 [城市] - 查询天气\n" .
                "#帮助 - 显示帮助信息\n";
        
        if ($group_id) {
            群($group_id, text($help));
        } else {
            私($user_id, text($help));
        }
        
        wlog('onebot_example_plugin', "处理了 #帮助 命令 from {$user_id}");
        return true;
    }
    return false;
}

// 处理 #天气 命令
function onebot_处理_天气($msg, $group_id, $user_id) {
    if (strpos($msg, '#天气') === 0) {
        $city = trim(str_replace('#天气', '', $msg));
        if (empty($city)) {
            $city = '北京'; // 默认城市
        }
        
        $reply = "🌤️ {$city}天气预报：\n" .
                 "☀️ 白天：晴朗 25°C\n" .
                 "🌙 夜晚：多云 18°C\n" .
                 "💨 风力：微风 2级\n" .
                 "\n（本数据为示例，非真实天气）";
        
        if ($group_id) {
            群($group_id, text($reply));
        } else {
            私($user_id, text($reply));
        }
        
        wlog('onebot_example_plugin', "处理了 #天气 命令 (城市: {$city}) from {$user_id}");
        return true;
    }
    return false;
}

// 注册插件
onebot_example_plugin_init();