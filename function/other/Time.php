<?php
class 定时 {
    
    private static $phpPath = '/usr/bin/php';
    
    /**
     * 人性化时间转crontab格式
     */
    private static function parseTime($humanTime) {
        $humanTime = trim($humanTime);
        
        // 一次性任务（年-月-日 时:分）
        if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})\s+(\d{1,2}):(\d{1,2})/', $humanTime, $m)) {
            $min = sprintf("%02d", $m[5]);
            $hour = sprintf("%02d", $m[4]);
            $day = sprintf("%02d", $m[3]);
            $month = sprintf("%02d", $m[2]);
            return "{$min} {$hour} {$day} {$month} *";
        }
        
        // 每N分钟
        if (preg_match('/每(\d+)分钟/', $humanTime, $m)) {
            return "*/{$m[1]} * * * *";
        }
        
        // 每N小时
        if (preg_match('/每(\d+)小时/', $humanTime, $m)) {
            return "0 */{$m[1]} * * *";
        }
        
        // 每天X点Y分
        if (preg_match('/每天(\d+)(?:点|时)(\d+)分/', $humanTime, $m)) {
            return sprintf("%02d %02d * * *", $m[2], $m[1]);
        }
        
        // 每天X点
        if (preg_match('/每天(\d+)(?:点|时)/', $humanTime, $m)) {
            return sprintf("0 %02d * * *", $m[1]);
        }
        
        // 每周X Y点
        $weekMap = ['周一'=>1,'周二'=>2,'周三'=>3,'周四'=>4,'周五'=>5,'周六'=>6,'周日'=>0];
        if (preg_match('/每周(周一|周二|周三|周四|周五|周六|周日)(\d+)(?:点|时)/', $humanTime, $m)) {
            return sprintf("0 %02d * * %d", $m[2], $weekMap[$m[1]]);
        }
        
        // 每月X号Y点
        if (preg_match('/每月(\d+)号(\d+)(?:点|时)/', $humanTime, $m)) {
            return sprintf("0 %02d %02d * *", $m[2], $m[1]);
        }
        
        // 常用快捷词
        $quick = [
            '每分钟'   => '* * * * *',
            '每小时'   => '0 * * * *',
            '每天凌晨' => '0 0 * * *',
            '每天午夜' => '0 0 * * *',
            '每周一'   => '0 0 * * 1',
        ];
        
        return $quick[$humanTime] ?? $humanTime;
    }
    
    /**
     * crontab时间转可读时间
     */
    private static function formatTime($cronTime) {
        $parts = preg_split('/\s+/', $cronTime);
        if (count($parts) < 5) return $cronTime;
        
        list($min, $hour, $day, $month, $week) = $parts;
        
        if ($min == '0' && $hour == '0' && $day == '*' && $month == '*' && $week == '*') return '每天凌晨';
        if ($min == '0' && $hour == '*' && $day == '*' && $month == '*' && $week == '*') return '每小时';
        if ($min == '*' && $hour == '*' && $day == '*' && $month == '*' && $week == '*') return '每分钟';
        
        if (preg_match('/^\*\/\d+$/', $min)) {
            $val = substr($min, 2);
            return "每{$val}分钟";
        }
        
        if ($hour != '*' && $day == '*' && $month == '*' && $week == '*') {
            return "每天{$hour}点" . ($min != '0' ? "{$min}分" : '');
        }
        
        return $cronTime;
    }
    
    /**
     * 获取任务列表（JSON格式）
     * @return string JSON
     */
    public static function 列表() {
        exec('crontab -l 2>/dev/null', $output, $code);
        if ($code !== 0) return json_encode([]);
        
        $tasks = [];
        $currentComment = '';
        
        foreach ($output as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // 记录注释行（作为ID标识）
            if (strpos($line, '#') === 0) {
                $currentComment = trim(substr($line, 1));
                continue;
            }
            
            // 解析任务行
            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 6) {
                $cronTime = implode(' ', array_slice($parts, 0, 5));
                $command = implode(' ', array_slice($parts, 5));
                
                // 提取脚本路径
                $scriptPath = '';
                if (preg_match('/\/usr\/bin\/php\s+(.+\.php)/', $command, $m)) {
                    $scriptPath = $m[1];
                } elseif (preg_match('/(.+\.php)/', $command, $m)) {
                    $scriptPath = $m[1];
                }
                
                // 用注释作为ID，如果没有注释则用路径作为ID
                $id = $currentComment ?: md5($scriptPath);
                
                $tasks[$id] = [
                    '时间' => self::formatTime($cronTime),
                    '脚本' => $scriptPath ?: $command
                ];
                
                $currentComment = '';
            }
        }
        
        return json_encode($tasks,480);
    }
    
    /**
     * 添加定时任务
     * @param string $id 任务ID（用于标识和删除）
     * @param string $time 时间（人性化或crontab格式）
     * @param string $path 脚本绝对路径
     * @return array
     */
    public static function 添加($id, $time, $path) {
        if (empty($id) || empty($time) || empty($path)) {
            return ['success' => false, 'message' => 'ID、时间和脚本路径都不能为空'];
        }
        
        // 确保路径是绝对路径
        if (!preg_match('/^\//', $path)) {
            return ['success' => false, 'message' => '脚本路径必须是绝对路径'];
        }
        
        if (!file_exists($path)) {
            return ['success' => false, 'message' => '脚本文件不存在: ' . $path];
        }
        
        $cronTime = self::parseTime($time);
        $command = self::$phpPath . ' ' . $path;
        
        // 获取现有任务（带注释的完整内容）
        exec('crontab -l 2>/dev/null', $output, $code);
        $tasks = ($code === 0) ? $output : [];
        
        // 检查ID是否已存在
        foreach ($tasks as $line) {
            if (strpos($line, '# ' . $id) === 0) {
                return ['success' => false, 'message' => 'ID已存在: ' . $id];
            }
        }
        
        // 添加新任务（ID作为注释）
        $newTask = "# {$id}\n{$cronTime} {$command}";
        $tasks[] = $newTask;
        
        return self::保存($tasks);
    }
    
    /**
     * 删除定时任务
     * @param string $id 任务ID
     * @return array
     */
    public static function 删除($id) {
        exec('crontab -l 2>/dev/null', $output, $code);
        if ($code !== 0) {
            return ['success' => false, 'message' => '没有找到crontab任务'];
        }
        
        $newTasks = [];
        $removed = false;
        $skipNext = false;
        
        foreach ($output as $line) {
            $line = rtrim($line);
            
            // 如果上一行是匹配的ID注释，跳过当前任务行
            if ($skipNext) {
                $skipNext = false;
                $removed = true;
                continue;
            }
            
            // 检查是否是目标ID的注释行
            if (strpos($line, '# ' . $id) === 0) {
                $skipNext = true;
                continue;
            }
            
            $newTasks[] = $line;
        }
        
        if (!$removed) {
            return ['success' => false, 'message' => '未找到ID为 ' . $id . ' 的任务'];
        }
        
        return self::保存($newTasks);
    }
    
    /**
     * 保存任务到crontab
     */
    private static function 保存($tasks) {
        // 过滤空行
        $tasks = array_filter($tasks, function($line) {
            return trim($line) !== '';
        });
        
        $tempFile = tempnam(sys_get_temp_dir(), 'cron_');
        file_put_contents($tempFile, implode("\n", $tasks) . "\n");
        
        exec("crontab {$tempFile} 2>&1", $output, $code);
        unlink($tempFile);
        
        if ($code === 0) {
            return ['success' => true, 'message' => '操作成功'];
        }
        return ['success' => false, 'message' => implode("\n", $output)];
    }
}