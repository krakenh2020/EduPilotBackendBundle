<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Tests\Service;

use ItsDangerous\BadData\SignatureExpired;
use ItsDangerous\Signer\TimedSerializer;
use PHPUnit\Framework\TestCase;

class ItsDangerousTest extends TestCase
{

    public function testTest()
    {
        $this->assertNotEmpty(true);
    }

    public function testSerialize()
    {
        $ser = new TimedSerializer("asecret");
        $c = $ser->dumps("KRAKEN");

        echo $c;

        $this->assertTrue(true);
    }

    public function testSerializeDeserialize()
    {
        $data = "KRAKEN";

        $ser = new TimedSerializer("asecret");
        $c1 = $ser->dumps($data);
        $c2 = $ser->loads($c1);

        $this->assertEquals($data, $c2);

        echo $c1;
    }

    public function testSerializeDeserializeTimeout()
    {
        $this->expectException(SignatureExpired::class);

        $data = "KRAKEN";

        $ser = new TimedSerializer("asecret");
        $c1 = $ser->dumps($data);
        $c2 = $ser->loads($c1, -1); // → SignatureExpired

        $this->fail("Timestamp was too old, but no exception thrown ...");

    }
}
