<?php

namespace BitWasp\Bitcoin\Network\Messages;

use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Network\NetworkSerializable;
use BitWasp\Buffertools\Parser;

class Reject extends NetworkSerializable
{
    const REJECT_MALFORMED = 0x01;
    const REJECT_INVALID = 0x10;
    const REJECT_OBSOLETE = 0x11;
    const REJECT_DUPLICATE = 0x12;
    const REJECT_NONSTANDARD = 0x40;
    const REJECT_DUST = 0x41;
    const REJECT_INSUFFICIENTFEE = 0x42;
    const REJECT_CHECKPOINT = 0x43;

    /**
     * @var Buffer
     */
    protected $message;

    /**
     * @var int
     */
    protected $ccode;

    /**
     * @var Buffer
     */
    protected $reason;

    /**
     * @var Buffer
     */
    protected $data;

    /**
     * @param Buffer $message
     * @param int $ccode
     * @param Buffer $reason
     * @param Buffer $data
     */
    public function __construct(
        Buffer $message,
        $ccode,
        Buffer $reason,
        Buffer $data = null
    ) {
        if (false === $this->checkCCode($ccode)) {
            throw new \InvalidArgumentException('Invalid code provided to reject message');
        }

        $this->message = $message;
        $this->ccode = $ccode;
        $this->reason = $reason;
        $this->data = $data ?: new Buffer();
    }

    /**
     * @return string
     */
    public function getNetworkCommand()
    {
        return 'reject';
    }

    /**
     * @param $code
     * @return bool
     */
    private function checkCCode($code)
    {
        return in_array(
            $code,
            [
                self::REJECT_MALFORMED, self::REJECT_INVALID,
                self::REJECT_OBSOLETE, self::REJECT_DUPLICATE,
                self::REJECT_NONSTANDARD, self::REJECT_DUST,
                self::REJECT_INSUFFICIENTFEE, self::REJECT_CHECKPOINT
            ]
        ) === true;
    }

    /**
     * @return Buffer
     */
    public function getBuffer()
    {
        $parser = new Parser();
        $parser
            ->writeWithLength($this->message)
            ->writeInt(1, $this->ccode)
            ->writeWithLength($this->reason)
            ->writeWithLength($this->data);

        return $parser->getBuffer();
    }
}
