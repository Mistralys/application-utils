<?php

declare(strict_types=1);

namespace AppUtils;

class ConvertHelper_ThrowableInfo_Serializer
{
    public const SERIALIZED_CODE = 'code';
    public const SERIALIZED_MESSAGE = 'message';
    public const SERIALIZED_REFERER = 'referer';
    public const SERIALIZED_CONTEXT = 'context';
    public const SERIALIZED_AMOUNT_CALLS = 'amountCalls';
    public const SERIALIZED_DATE = 'date';
    public const SERIALIZED_PREVIOUS = 'previous';
    public const SERIALIZED_CALLS = 'calls';
    public const SERIALIZED_OPTIONS = 'options';
    public const SERIALIZED_CLASS = 'class';
    public const SERIALIZED_DETAILS = 'details';

    /**
     * @param ConvertHelper_ThrowableInfo $info
     * @return array<string,mixed>
     * @throws ConvertHelper_Exception
     */
    public static function serialize(ConvertHelper_ThrowableInfo $info)
    {
        $result = array(
            self::SERIALIZED_CLASS => $info->getClass(),
            self::SERIALIZED_DETAILS => $info->getDetails(),
            self::SERIALIZED_MESSAGE => $info->getMessage(),
            self::SERIALIZED_CODE => $info->getCode(),
            self::SERIALIZED_DATE => $info->getDate()->getISODate(),
            self::SERIALIZED_REFERER => $info->getReferer(),
            self::SERIALIZED_CONTEXT => $info->getContext(),
            self::SERIALIZED_AMOUNT_CALLS => $info->countCalls(),
            self::SERIALIZED_OPTIONS => $info->getOptions(),
            self::SERIALIZED_CALLS => array(),
            self::SERIALIZED_PREVIOUS => null,
        );

        if($info->hasPrevious())
        {
            $result[self::SERIALIZED_PREVIOUS] = $info->getPrevious()->serialize();
        }

        $calls = $info->getCalls();
        foreach($calls as $call)
        {
            $result[self::SERIALIZED_CALLS][] = $call->serialize();
        }

        return $result;
    }

    /**
     * @param array<string,mixed> $serialized
     * @return array<string,mixed>
     * @throws ConvertHelper_Exception
     */
    public static function unserialize(array $serialized) : array
    {
        $data = self::validateSerializedData($serialized);
        $data[self::SERIALIZED_PREVIOUS] = self::unserializePrevious($data[self::SERIALIZED_PREVIOUS]);

        if(!isset($data[self::SERIALIZED_CLASS]))
        {
            $data[self::SERIALIZED_CLASS] = '';
        }

        if(!isset($data[self::SERIALIZED_DETAILS]))
        {
            $data[self::SERIALIZED_DETAILS] = '';
        }

        return $data;
    }

    /**
     * @param array<string,mixed> $serialized
     * @return array<string,mixed>
     * @throws ConvertHelper_Exception
     */
    private static function validateSerializedData(array $serialized) : array
    {
        $keys = array(
            self::SERIALIZED_CODE => 'integer',
            self::SERIALIZED_MESSAGE => 'string',
            self::SERIALIZED_DATE => 'string',
            self::SERIALIZED_REFERER => 'string',
            self::SERIALIZED_CONTEXT => 'string',
            self::SERIALIZED_AMOUNT_CALLS => 'integer',
            self::SERIALIZED_OPTIONS => 'array',
            self::SERIALIZED_CALLS => 'array'
        );

        foreach($keys as $key => $type)
        {
            if(!isset($serialized[$key]) || gettype($serialized[$key]) !== $type)
            {
                throw self::createTypeException($key, $type);
            }
        }

        return $serialized;
    }

    private static function createTypeException(string $keyName, string  $expectedType) : ConvertHelper_Exception
    {
        return new ConvertHelper_Exception(
            'Invalid serialized throwable key',
            sprintf(
                'The key [%s] does not have the expected data type [%s].',
                $keyName,
                $expectedType
            ),
            ConvertHelper_ThrowableInfo::ERROR_INVALID_SERIALIZED_DATA_TYPE
        );
    }

    private static function unserializePrevious(?array $previous) : ?ConvertHelper_ThrowableInfo
    {
        if(!empty($previous))
        {
            return ConvertHelper_ThrowableInfo::fromSerialized($previous);
        }

        return null;
    }
}
