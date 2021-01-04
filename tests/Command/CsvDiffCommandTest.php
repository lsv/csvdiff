<?php

namespace Lsv\CsvDiffTest\Command;

use Generator;
use Lsv\CsvDiff\Command\CsvDiffCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CsvDiffCommandTest extends TestCase
{

    public function dataProvider(): Generator
    {
        yield [__DIR__.'/../smallold.csv', __DIR__.'/../smallnew.csv', 1];
        yield [__DIR__.'/../bigold.csv', __DIR__.'/../bignew.csv', 8];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCanViewDiff(string $oldfile, string $newfile, int $lines): void
    {
        $tester = new CommandTester(new CsvDiffCommand());
        $tester->execute(
            [
                'old' => $oldfile,
                'new' => $newfile,
            ]
        );
        $display = $tester->getDisplay();
        self::assertCount($lines, explode("\n", $display));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCanWriteDiff(string $oldfile, string $newfile, int $lines): void
    {
        $write = __DIR__.'/../write.csv';
        if (file_exists($write)) {
            unlink($write);
        }

        $tester = new CommandTester(new CsvDiffCommand());
        $tester->execute(
            [
                'old' => $oldfile,
                'new' => $newfile,
                'write' => $write,
            ]
        );
        $display = $tester->getDisplay();
        self::assertStringEndsWith('is written', $display);

        $data = file_get_contents($write);
        self::assertCount($lines, explode("\n", $data));

        if (file_exists($write)) {
            unlink($write);
        }
    }

    public function testNewFileDoesNotExists(): void
    {
        $tester = new CommandTester(new CsvDiffCommand());
        $tester->execute(
            [
                'old' => __DIR__ . '/../smallold.csv',
                'new' => 'file_does_not_exists',
            ]
        );
        self::assertStringContainsString('file does not exists or is not readable', $tester->getDisplay(true));
        self::assertSame(0, $tester->getStatusCode());
    }

    public function testOldFileDoesNotExists(): void
    {
        $tester = new CommandTester(new CsvDiffCommand());
        $tester->execute(
            [
                'old' => 'file_does_not_exists',
                'new' => __DIR__ . '/../smallnew.csv'
            ]
        );
        self::assertStringContainsString('file does not exists or is not readable', $tester->getDisplay(true));
        self::assertSame(0, $tester->getStatusCode());
    }

    public function testWriteFileNotWritable(): void
    {
        $tester = new CommandTester(new CsvDiffCommand());
        $tester->execute(
            [
                'old' => __DIR__ . '/../smallold.csv',
                'new' => __DIR__ . '/../smallnew.csv',
                'write' => __DIR__,
            ]
        );
        self::assertStringContainsString('is not writable', $tester->getDisplay(true));
        self::assertSame(0, $tester->getStatusCode());
    }

}
