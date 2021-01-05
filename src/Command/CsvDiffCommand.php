<?php

namespace Lsv\CsvDiff\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CsvDiffCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('csv:diff')
            ->setDescription('Diff two csv files')
            ->addArgument('old', InputArgument::REQUIRED, 'The old csv file')
            ->addArgument('new', InputArgument::REQUIRED, 'The new csv file that needs to be diffed with old')
            ->addArgument('write', InputArgument::OPTIONAL, 'A filename to write the output to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $old = $input->getArgument('old');
        // @codeCoverageIgnoreStart
        if (!is_string($old)) {
            throw new RuntimeException('Argument "old" should be a string');
        }
        // @codeCoverageIgnoreEnd

        $new = $input->getArgument('new');
        // @codeCoverageIgnoreStart
        if (!is_string($new)) {
            throw new RuntimeException('Argument "new" should be a string');
        }
        // @codeCoverageIgnoreEnd

        $old = realpath($old);
        $new = realpath($new);

        if (!file_exists($old) || !is_readable($old)) {
            throw new RuntimeException('The "old" file ('.$old.') file does not exists or is not readable');
        }

        if (!file_exists($new) || !is_readable($new)) {
            throw new RuntimeException('The "new" file ('.$new.') file does not exists or is not readable');
        }

        $newcsv = $this->getDiff($new, $old);

        if ($write = $input->getArgument('write')) {
            // @codeCoverageIgnoreStart
            if (!is_string($write)) {
                throw new RuntimeException('Argument "write" should be a string');
            }
            // @codeCoverageIgnoreEnd

            if (!$writeHandler = @fopen($write, 'wb')) {
                throw new RuntimeException('The "write" file ('.$write.') is not writable');
            }

            fwrite($writeHandler, $newcsv);
            fclose($writeHandler);
            $output->write('Diff file "'.$write.'" is written');

            return 1;
        }

        $output->write($newcsv);

        return 1;
    }

    private function getDiff(string $newfile, string $oldfile): string
    {
        $process = new Process(
            [
                'diff',
                '-Z',
                '--changed-group-format=%<%>',
                '--unchanged-group-format=',
                '--unchanged-line-format=',
                '--old-line-format=',
                '--new-line-format=%L',
                $newfile,
                $oldfile,
            ]
        );
        $process->run();
        $output = $process->getOutput();
        $output = explode("\n", $output);
        array_pop($output);

        return implode("\n", $output);
    }

}
