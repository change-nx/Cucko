<?php

class ProtobufSerializer
{
    const WIRE_TYPE_VARINT = 0;
    const WIRE_TYPE_64BIT = 1;
    const WIRE_TYPE_LENGTH_DELIMITED = 2;
    const WIRE_TYPE_32BIT = 5;

    /**
     * 将JSON格式的数据序列化为Protobuf格式
     *
     * @param array $jsonData JSON格式的数据，例如 {"1": "value", "2": 123}
     * @return string 序列化后的Protobuf二进制数据
     */
    public static function serializeJsonToProtobuf($jsonData)
    {
        $data = '';

        foreach ($jsonData as $fieldNumber => $value) {
            $fieldNumber = intval($fieldNumber); // 确保字段编号是整数
            $wireType = self::getWireType($value); // 根据值的类型确定wire type

            if ($wireType === null) {
                throw new Exception("Unsupported value type for field $fieldNumber");
            }

            // 如果是数组，需要判断是单个字段还是重复字段
            if (is_array($value)) {
                // 如果数组是关联数组（嵌套消息），递归编码
                if (self::isAssociativeArray($value)) {
                    $nestedData = self::serializeJsonToProtobuf($value);
                    $data .= self::encodeField($fieldNumber, self::WIRE_TYPE_LENGTH_DELIMITED);
                    $data .= self::encodeVarint(strlen($nestedData)) . $nestedData;
                } else {
                    // 如果数组是索引数组（重复字段），逐个编码
                    foreach ($value as $item) {
                        $data .= self::encodeField($fieldNumber, self::WIRE_TYPE_LENGTH_DELIMITED);
                        $data .= self::encodeValue(self::WIRE_TYPE_LENGTH_DELIMITED, $item);
                    }
                }
            } else {
                // 编码字段标签和wire type
                $data .= self::encodeField($fieldNumber, $wireType);
                // 编码字段值
                $data .= self::encodeValue($wireType, $value);
            }
        }

        return $data;
    }

    /**
     * 判断是否为关联数组
     *
     * @param array $array 数组
     * @return bool 如果是关联数组返回true，否则返回false
     */
    private static function isAssociativeArray($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * 根据值的类型确定wire type
     *
     * @param mixed $value 字段值
     * @return int|null 返回对应的wire type，如果类型不支持则返回null
     */
    private static function getWireType($value)
    {
        if (is_int($value)) {
            return self::WIRE_TYPE_VARINT;
        } elseif (is_float($value)) {
            // 判断是float还是double
            return (abs($value) > 16777216 || abs($value) < 1.0E-37) ? self::WIRE_TYPE_64BIT : self::WIRE_TYPE_32BIT;
        } elseif (is_string($value)) {
            return self::WIRE_TYPE_LENGTH_DELIMITED;
        } elseif (is_bool($value)) {
            return self::WIRE_TYPE_VARINT;
        } elseif (is_array($value)) {
            return self::WIRE_TYPE_LENGTH_DELIMITED;
        }

        return null;
    }

    /**
     * 编码字段标签和wire type
     *
     * @param int $fieldNumber 字段编号
     * @param int $wireType wire type
     * @return string 编码后的字段标签
     */
    private static function encodeField($fieldNumber, $wireType)
    {
        return self::encodeVarint(($fieldNumber << 3) | $wireType);
    }

    /**
     * 编码字段值
     *
     * @param int $wireType wire type
     * @param mixed $value 字段值
     * @return string 编码后的字段值
     */
    private static function encodeValue($wireType, $value)
    {
        switch ($wireType) {
            case self::WIRE_TYPE_VARINT:
                return self::encodeVarint($value);
            case self::WIRE_TYPE_64BIT:
                return self::encodeDouble($value);
            case self::WIRE_TYPE_32BIT:
                return self::encodeFloat($value);
            case self::WIRE_TYPE_LENGTH_DELIMITED:
                if (is_string($value)) {
                    return self::encodeString($value);
                } elseif (is_array($value)) {
                    // 如果是嵌套消息（关联数组），递归编码
                    if (self::isAssociativeArray($value)) {
                        $nestedData = self::serializeJsonToProtobuf($value);
                        return self::encodeVarint(strlen($nestedData)) . $nestedData;
                    } else {
                        // 如果是重复字段（索引数组），逐个编码
                        return self::encodeRepeatedField($value);
                    }
                }
                break;
        }

        throw new Exception("Unsupported wire type: $wireType");
    }

    /**
     * 编码变长整数（Varint）
     *
     * @param int $value 整数值
     * @return string 编码后的Varint
     */
    private static function encodeVarint($value)
    {
        $data = '';
        while ($value > 0x7F) {
            $data .= chr(($value & 0x7F) | 0x80);
            $value >>= 7;
        }
        $data .= chr($value);
        return $data;
    }

    /**
     * 编码字符串
     *
     * @param string $value 字符串值
     * @return string 编码后的字符串
     */
    private static function encodeString($value)
    {
        $length = strlen($value);
        return self::encodeVarint($length) . $value;
    }

    /**
     * 编码浮点数（32-bit）
     *
     * @param float $value 浮点数值
     * @return string 编码后的32-bit浮点数
     */
    private static function encodeFloat($value)
    {
        return pack('G', $value);
    }

    /**
     * 编码双精度浮点数（64-bit）
     *
     * @param float $value 双精度浮点数值
     * @return string 编码后的64-bit浮点数
     */
    private static function encodeDouble($value)
    {
        return pack('E', $value);
    }

    /**
     * 编码重复字段
     *
     * @param array $values 重复字段的值
     * @return string 编码后的重复字段
     */
    private static function encodeRepeatedField($values)
    {
        $data = '';
        foreach ($values as $value) {
            $wireType = self::getWireType($value);
            if ($wireType === null) {
                throw new Exception("Unsupported value type in repeated field");
            }
            $data .= self::encodeValue($wireType, $value);
        }
        return self::encodeVarint(strlen($data)) . $data;
    }
}
