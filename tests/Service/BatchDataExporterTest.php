<?php

namespace VC4SM\Bundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use VC4SM\Bundle\Service\BatchDataExporter;

class BatchDataExporterTest extends TestCase
{

    private $exporterURL = 'http://127.0.0.1:5000';
    private $logger;

    private $disableCItests = true;  // disable tests that won't work in CI at the moment

    public function setUp(): void
    {
        parent::setUp();
        $this->logger = new ExporterMockLogger("BatchExporter");
    }

    public function testTest()
    {
        $this->assertNotEmpty(true);
    }

    public function testExporterReachable()
    {
        if ($this->disableCItests) {
            $this->markTestSkipped("currently not supported in CI");
        }

        $exporter = new BatchDataExporter($this->logger, $this->exporterURL);
        $online = $exporter->checkConnection();

        $this->assertTrue($online);
    }

    public function testExporterUnreachable()
    {
        if ($this->disableCItests) {
            $this->markTestSkipped("currently not supported in CI");
        }

        $exporter = new BatchDataExporter($this->logger, "http://foo.bar");
        $online = $exporter->checkConnection();

        $this->assertFalse($online);
    }

    public function testExport1()
    {
        if ($this->disableCItests) {
            $this->markTestSkipped("currently not supported in CI");
        }
        
        $exporter = new BatchDataExporter($this->logger, $this->exporterURL);
        $online = $exporter->checkConnection();
        $this->assertTrue($online);

        $dummyCred = "This is a test. No need to be JSON.";

        try {
            $status = $exporter->exportData($dummyCred, "testing", "asdf");
        } catch (ExceptionInterface $e) {
            $this->fail($e);
        }
        self::assertTrue($status);
    }


}

class ExporterMockLogger
{
    private $name;

    public function __construct(string $name = "BatchExporter")
    {
        $this->name = $name;
    }

    public function warning($text)
    {
        echo "[$this->name] Warning: " . $text . "\n";
    }

    public function info($text)
    {
        echo "[$this->name] Info: " . $text . "\n";
    }
}
