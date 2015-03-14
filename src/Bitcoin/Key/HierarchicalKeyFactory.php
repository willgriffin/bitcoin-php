<?php

namespace Afk11\Bitcoin\Key;

use Afk11\Bitcoin\Bitcoin;
use Afk11\Bitcoin\Exceptions\Base58ChecksumFailure;
use Afk11\Bitcoin\Math\Math;
use Afk11\Bitcoin\Network\NetworkInterface;
use Afk11\Bitcoin\Crypto\Hash;
use Afk11\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;
use Afk11\Bitcoin\Serializer\Key\HierarchicalKey\HexExtendedKeySerializer;
use Mdanter\Ecc\GeneratorPoint;

class HierarchicalKeyFactory
{
    /**
     * @param Math $math
     * @param $generator
     * @param NetworkInterface $network
     * @return ExtendedKeySerializer
     */
    public static function getSerializer($math, $generator, $network)
    {
        $extSerializer = new ExtendedKeySerializer($network, new HexExtendedKeySerializer($math, $generator, $network));
        return $extSerializer;
    }

    /**
     * @param Math $math
     * @param GeneratorPoint $generator
     * @return HierarchicalKey
     */
    public static function generateMasterKey(Math $math = null, GeneratorPoint $generator = null)
    {
        $math = $math ?: Bitcoin::getMath();
        $generator = $generator ?: Bitcoin::getGenerator();

        $buffer  = PrivateKeyFactory::create(true, $math, $generator);
        $private = self::fromEntropy($buffer->getBuffer()->serialize('hex'));
        return $private;
    }

    /**
     * @param string $entropy
     * @param Math $math
     * @param GeneratorPoint $generator
     * @return HierarchicalKey
     */
    public static function fromEntropy($entropy, Math $math = null, GeneratorPoint $generator = null)
    {
        $math = $math ?: Bitcoin::getMath();
        $generator = $generator ?: Bitcoin::getGenerator();

        $hash = Hash::hmac('sha512', pack("H*", $entropy), "Bitcoin seed");

        $key = new HierarchicalKey(
            $math,
            $generator,
            0,
            0,
            0,
            $math->hexDec(substr($hash, 64, 64)),
            PrivateKeyFactory::fromHex(substr($hash, 0, 64), true)
        );

        return $key;
    }

    /**
     * @param string $extendedKey
     * @param NetworkInterface $network
     * @return HierarchicalKey
     * @throws Base58ChecksumFailure
     */
    public static function fromExtended($extendedKey, NetworkInterface $network, Math $math = null, GeneratorPoint $generator = null)
    {
        $math = $math ?: Bitcoin::getMath();
        $generator = $generator ?: Bitcoin::getGenerator();

        $extSerializer = self::getSerializer($math, $generator, $network);
        $key = $extSerializer->parse($extendedKey);
        return $key;
    }
}