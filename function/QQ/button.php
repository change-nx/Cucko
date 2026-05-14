<?php

class 按钮 {
    private static $id计数器 = 0;
    private $数据 = [];
    
    public static function 开(string $标签): self {
        $obj = new self();
        $obj->数据 = [
            'id' => 'btn_' . (++self::$id计数器),
            'render_data' => [
                'label' => $标签,
                'visited_label' => $标签,
                'style' => 0
            ],
            'action' => [
                'type' => 1,
                'permission' => ['type' => 2],
                'data' => '',
                'unsupport_tips' => '当前版本不支持'
            ]
        ];
        return $obj;
    }
    
    public function 类型(string $类型): self {
        $map = ['消息' => 2, '回调' => 1, '跳转' => 0];
        $this->数据['action']['type'] = $map[$类型] ?? 1;
        return $this;
    }
    
    public function 样式(int $样式): self {
        $this->数据['render_data']['style'] = $样式;
        return $this;
    }
    
    public function 返(string $数据): self {
        $this->数据['action']['data'] = $数据;
        return $this;
    }
    
    public function 指定人(string ...$用户IDs): self {
        $this->数据['action']['permission'] = [
            'type' => 0,
            'specify_user_ids' => $用户IDs
        ];
        return $this;
    }
    
    public function 管理员(): self {
        $this->数据['action']['permission']['type'] = 1;
        return $this;
    }
    
    public function 所有人(): self {
        $this->数据['action']['permission']['type'] = 2;
        return $this;
    }
    
    public function 取数组(): array {
        return $this->数据;
    }
    
    public static function 加(...$按钮们): array {
        $结果 = [];
        foreach ($按钮们 as $btn) {
            if ($btn instanceof 按钮) {
                $结果[] = $btn->取数组();
            }
        }
        return $结果;
    }
    
    public static function 构(array ...$行数组): string {
        $行们 = [];
        foreach ($行数组 as $一行按钮) {
            if (isset($一行按钮[0]) && !isset($一行按钮[0]['id'])) {
                $行们[] = ['buttons' => $一行按钮];
            } else {
                $行们[] = ['buttons' => $一行按钮];
            }
        }
        return json_encode(['rows' => $行们]);
    }
}
