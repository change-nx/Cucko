<?php

class ProtobufDeserializer
{
    const WIRE_TYPE_VARINT = 0;
    const WIRE_TYPE_64BIT = 1;
    const WIRE_TYPE_LENGTH_DELIMITED = 2;
    const WIRE_TYPE_32BIT = 5;

    /**
     * 反序列化Protobuf二进制数据为PHP数组
     */
    public static function deserialize($binaryData)
    {
        $data = [];
        $offset = 0;
        $length = strlen($binaryData);

        while ($offset < $length) {
            // 检查是否有足够的数据解析tag
            if ($offset >= $length) {
                throw new Exception("Unexpected end of data while reading tag");
            }

            list($fieldNumber, $wireType, $offset) = self::decodeTag($binaryData, $offset, $length);

            // 根据wire type解析字段值
            switch ($wireType) {
                case self::WIRE_TYPE_VARINT:
                    list($value, $offset) = self::decodeVarint($binaryData, $offset, $length);
                    break;
                case self::WIRE_TYPE_64BIT:
                    list($value, $offset) = self::decode64Bit($binaryData, $offset, $length);
                    break;
                case self::WIRE_TYPE_LENGTH_DELIMITED:
                    list($value, $offset) = self::decodeLengthDelimited($binaryData, $offset, $length);
                    break;
                case self::WIRE_TYPE_32BIT:
                    list($value, $offset) = self::decode32Bit($binaryData, $offset, $length);
                    break;
                default:
                    throw new Exception("Unsupported wire type: $wireType");
            }

            // 存储字段值
            if (isset($data[$fieldNumber])) {
                if (!is_array($data[$fieldNumber])) {
                    $data[$fieldNumber] = [$data[$fieldNumber]];
                }
                $data[$fieldNumber][] = $value;
            } else {
                $data[$fieldNumber] = $value;
            }
        }

        return $data;
    }

    private static function decodeTag($binaryData, $offset, $length)
    {
        list($tag, $offset) = self::decodeVarint($binaryData, $offset, $length);
        $fieldNumber = $tag >> 3;
        $wireType = $tag & 0x07;
        return [$fieldNumber, $wireType, $offset];
    }

    private static function decodeVarint($binaryData, $offset, $length)
    {
        $value = 0;
        $shift = 0;

        do {
            if ($offset >= $length) {
                throw new Exception("Unexpected end of data while reading varint");
            }
            $byte = ord($binaryData[$offset++]);
            $value |= ($byte & 0x7F) << $shift;
            $shift += 7;
        } while (($byte & 0x80) !== 0);

        return [$value, $offset];
    }

    private static function decode64Bit($binaryData, $offset, $length)
    {
        if ($offset + 8 > $length) {
            throw new Exception("Not enough data for 64-bit value");
        }
        $value = unpack('P', substr($binaryData, $offset, 8))[1];
        return [$value, $offset + 8];
    }

    private static function decode32Bit($binaryData, $offset, $length)
    {
        if ($offset + 4 > $length) {
            throw new Exception("Not enough data for 32-bit value");
        }
        $value = unpack('V', substr($binaryData, $offset, 4))[1];
        return [$value, $offset + 4];
    }

    private static function decodeLengthDelimited($binaryData, $offset, $length)
    {
        list($valueLength, $offset) = self::decodeVarint($binaryData, $offset, $length);
        
        if ($offset + $valueLength > $length) {
            throw new Exception("Not enough data for length-delimited value");
        }
        
        $value = substr($binaryData, $offset, $valueLength);
        $offset += $valueLength;

        // 尝试递归解析嵌套消息
        try {
            $nestedData = self::deserialize($value);
            return [$nestedData, $offset];
        } catch (Exception $e) {
            return [$value, $offset];
        }
    }
}

// 递归转换二进制数据为可JSON编码的格式
function convertForJson($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = convertForJson($value);
        }
        return $data;
    } elseif (is_string($data)) {
        // 检查字符串是否包含非UTF-8字符
        if (!mb_check_encoding($data, 'UTF-8')) {
            // 转换为hex表示
            return 'hex->' . strtoupper(bin2hex($data));
        }
        return $data;
    }
    return $data;
}
?>