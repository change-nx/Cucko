<?php
namespace Cucko\Example;


if (消息 == "菜单") {
    群(
      群号,
      text("Example\n1.变量")
    );
}

if (消息 == "变量") {
    群(
      群号,
      text("Adapter->".adapter),
      text("\nUin->".uin),
      text("\nEvent->".event),
      text("\n\n消息->".消息),
      text("\n消息ID->".消息ID),
      text("\nQQ->".QQ),
      text("\n昵称->".昵称),
      text("\n身份->".身份),
      text("\n群号->".群号),
      text("\n群名->".群名),
      text("\n时间->".time)
    );
}

if (event == "群聊-名片变更") {
    群(群号,text(json_encode(raw,480)));
}

if (消息 == "测试") {
群伪造(群号,伪造(QQ,昵称,text(版本信息())));
}