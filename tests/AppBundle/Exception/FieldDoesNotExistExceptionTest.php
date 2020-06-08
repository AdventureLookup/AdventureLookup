<?php

namespace Tests\AppBundle\Exception;

use AppBundle\Exception\FieldDoesNotExistException;
use PHPUnit\Framework\TestCase;

class FieldDoesNotExistExceptionTest extends TestCase
{
    public function testIsRuntimeException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectException(FieldDoesNotExistException::class);

        $e = new FieldDoesNotExistException();
        throw $e;
    }
}
