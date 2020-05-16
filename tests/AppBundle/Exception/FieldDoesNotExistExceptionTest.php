<?php


namespace Tests\AppBundle\Exception;

use AppBundle\Exception\FieldDoesNotExistException;

class FieldDoesNotExistExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsRuntimeException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectException(FieldDoesNotExistException::class);

        $e = new FieldDoesNotExistException();
        throw $e;
    }
}
