<?php

namespace VC4SM\Bundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use VC4SM\Bundle\Service\BatchDataExporter;

class BatchDataExporterTest extends TestCase
{

    private $exporterURL = 'http://127.0.0.1:5000';
    private $logger;

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
        $exporter = new BatchDataExporter($this->logger, $this->exporterURL);
        $online = $exporter->checkConnection();

        $this->assertTrue($online);
    }

    public function testExporterUnreachable()
    {
        $exporter = new BatchDataExporter($this->logger, "http://foo.bar");
        $online = $exporter->checkConnection();

        $this->assertFalse($online);
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
