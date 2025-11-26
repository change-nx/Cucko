<div align="center">

![:name](https://count.getloli.com/@Cucko?name=Cucko&theme=&padding=6&offset=0&align=top&scale=1&pixelated=1&darkmode=auto

# Cucko
> 基于Napcat的PHP框架

## 前言
> 作者∶长歌(3355236800)

> 群聊∶323843010

> 由于本人常年在校,有问题建议群内询问

## 配置准备
- Napcat
- PHP7.4

## 框架配置
打开info.php
```php
<?php
return [
    "url" => "http://127.0.0.1:3000",
    "url_token" => "",
    "http_token" => "",
    "登录账号" => ,
    "自触" => false
];
```
url -> HTTP服务器地址

url_token -> HTTP服务器Token

http_token -> HTTP客户端Token(选填)

登录账号 -> 机器人QQ

自触 -> 需要开启napcat的机器人自身消息上报

owner.php(主人列表)
```php
<?php
$owner = [
    10001
];
return $owner;
```

admin.php(后端)
地址:域名/admin
```php
<?php
return [
    "username" => "admin",//用户名
    "password" => "123456"//密码
];
```

## 插件
> 存放路径∶plugin/

> 本插件未做bot自身上报相关处理,如需开启请注意插件编写防止刷屏

示例∶test.php
```php
<?php
if (消息来源 != "群聊") return;

if (消息 == "测试") {
    群(群号,text("你好啊"));
}
```

## 变量

### 全局变量

| 变量名 | 类型 | 说明 | 触发条件 |
|--------|------|------|----------|
| raw | string | 接收到的原始数据 | 所有事件 |
| 消息来源 | string | 事件类型 | 所有事件 |
| 消息 | string | 消息内容 | 消息事件 |
| 群号 | int | 群号 | 群聊相关事件 |
| 消息ID | int | 消息 ID | 消息事件 |
| QQ | int | 用户 QQ 号 | 用户相关事件 |
| 昵称 | string | 用户昵称 | 私聊/群聊消息 |
| 群昵称 | string | 群名片 | 群聊消息 |
| 群身份 | string | 群身份(owner/admin/member) | 群聊消息 |
| 头衔 | string | 群头衔 | 群聊消息 |
| 操作者 | int | 操作者 QQ 号 | 群管理事件 |
| 禁言时长 | int | 禁言时长（秒） | 群成员禁言 |
| 申请ID | string | 申请标识 | 加群申请 |

### 消息来源类型
- `群聊` - 群聊消息
- `私聊` - 私聊消息  
- `有人入群` - 新成员加入
- `有人退群` - 成员退出
- `群消息撤回` - 群消息撤回
- `好友消息撤回` - 好友消息撤回
- `群成员禁言` - 群成员被禁言
- `群成员解禁` - 群成员解除禁言
- `管理员添加` - 设置管理员
- `管理员减少` - 取消管理员
- `有人申请入群` - 加群申请
- `心跳` - 心跳事件


## 函数

### text($content)
创建文本消息
- `$content`: 文本内容

### reply($message_id)
创建回复消息
- `$message_id`: 要回复的消息ID

### face($id)
创建表情消息
- `$id`: 表情ID

### image($file)
创建图片消息
- `$file`: 图片文件路径或URL

### video($file)
创建视频消息
- `$file`: 视频文件路径或URL

### music($type, $id)
创建音乐分享
- `$type`: 音乐平台类型
- `$id`: 音乐ID

### at($qq)
创建@消息
- `$qq`: 要@的QQ号

### kp($json_data)
创建JSON消息
- `$json_data`: JSON

### 群($group_id, ...$messages)
发送群消息
- `$group_id`: 群号
- `...$messages`: 消息内容数组

### 私($user_id, ...$messages)
发送私聊消息
- `$user_id`: 用户QQ号
- `...$messages`: 消息内容数组

### 伪造($user_id, $name, ...$messages)
创建转发消息节点
- `$user_id`: 显示的QQ号
- `$name`: 显示的昵称
- `...$messages`: 消息内容数组

### 群伪造($group_id, ...$messages)
发送群合并转发
- `$group_id`: 群号
- `...$messages`: 消息节点数组

### 私伪造($user_id, ...$messages)
发送私聊合并转发
- `$user_id`: 用户QQ号
- `...$messages`: 消息节点数组

### 好友列表()
获取好友列表

### 删除好友($user_id)
删除好友
- `$user_id`: 好友QQ号

### 获取陌生人信息($user_id)
获取陌生人信息
- `$user_id`: 目标QQ号

### 点赞($user_id, $times)
给用户点赞
- `$user_id`: 目标QQ号
- `$times`: 点赞次数

### 设置头像($file_url)
设置机器人头像
- `$file_url`: 头像图片URL

### 群列表()
获取机器人加入的群列表

### 群详情($group_id)
获取群详细信息
- `$group_id`: 群号

### 群成员列表($group_id)
获取群成员列表
- `$group_id`: 群号

### 群成员信息($group_id, $user_id)
获取群成员详细信息
- `$group_id`: 群号
- `$user_id`: 成员QQ号

### 设置群管理($group_id, $user_id)
设置群管理员
- `$group_id`: 群号
- `$user_id`: 成员QQ号

### 取消群管理($group_id, $user_id)
取消群管理员
- `$group_id`: 群号
- `$user_id`: 成员QQ号

### 设置群名片($group_id, $user_id, $card)
设置群成员名片
- `$group_id`: 群号
- `$user_id`: 成员QQ号
- `$card`: 名片内容

### 禁言($group_id, $user_id, $duration)
禁言群成员
- `$group_id`: 群号
- `$user_id`: 成员QQ号
- `$duration`: 禁言时长(秒)

### 解禁($group_id, $user_id)
解除群成员禁言
- `$group_id`: 群号
- `$user_id`: 成员QQ号

### 全体禁言($group_id)
开启全体禁言
- `$group_id`: 群号

### 全体解禁($group_id)
关闭全体禁言
- `$group_id`: 群号

### 设置群名($group_id, $group_name)
设置群名称
- `$group_id`: 群号
- `$group_name`: 新群名称

### 踢($group_id, $user_id, $reject_add_request = false)
踢出群成员
- `$group_id`: 群号
- `$user_id`: 成员QQ号
- `$reject_add_request`: 是否拒绝再次加群

### 设置群头衔($group_id, $user_id, $special_title)
设置群专属头衔
- `$group_id`: 群号
- `$user_id`: 成员QQ号
- `$special_title`: 头衔内容

### 设置群公告($group_id, $content, $image = null)
设置群公告
- `$group_id`: 群号
- `$content`: 公告内容
- `$image`: 公告图片(可选)

### 撤回($message_id)
撤回消息
- `$message_id`: 消息ID

### 消息详情($message_id)
获取消息详情
- `$message_id`: 消息ID

### 取消息($raw_message, $type)
从原始消息中提取特定类型内容
- `$raw_message`: 原始消息JSON
- `$type`: 消息类型(text, image, at, reply等)

### 群文件($group_id, $file, $name, $folder_id = null)
上传群文件
- `$group_id`: 群号
- `$file`: 文件路径
- `$name`: 文件名
- `$folder_id`: 文件夹ID(可选)

### AI语音($group_id, $character_id, $text)
发送AI语音消息
- `$group_id`: 群号
- `$character_id`: 语音角色ID
- `$text`: 语音文本

### AI语音角色列表($group_id)
获取AI语音角色列表
- `$group_id`: 群号

### 群打卡($group_id)
群签到
- `$group_id`: 群号

### 处理加群申请($flag, $approve)
处理加群请求
- `$flag`: 请求标识
- `$approve`: 是否同意

### 群头像($group_id)
获取群头像URL
- `$group_id`: 群号

### 头像($user_id)
获取用户头像URL
- `$user_id`: QQ号

### owner($user_id)
检查是否是主人
- `$user_id`: QQ号

### owner_list()
获取主人列表

### 登录号信息()
获取登录账号信息

### clientkey()
获取客户端key

### 发包($cmd, $pb_data)
发送Protobuf数据包
- `$cmd`: 命令字
- `$pb_data`: Protobuf数据

### 群历史消息($group_id, $count)
获取群历史消息
- `$group_id`: 群号
- `$count`: 消息数量