<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\System;

class ReportTest extends SystemTestCase
{
    /**
     * It should generate a report.
     */
    public function testGenerateReport()
    {
        $process = $this->phpbench(
            'report --file=results/report1.xml --report=default'
        );
        $this->assertEquals(0, $process->getExitCode());
        $output = $process->getOutput();
        $this->assertContains('benchMd5', $output);
    }

    /**
     * It should throw an exception if no reports are specified.
     */
    public function testNoReports()
    {
        $process = $this->phpbench('report --file=results/report1.xml');
        $this->assertExitCode(1, $process);
        $this->assertContains('You must specify or con', $process->getErrorOutput());
    }

    /**
     * It should throw an exception if the report file does not exist.
     */
    public function testNonExistingFile()
    {
        $process = $this->phpbench('report tests/Systemist.xml');
        $this->assertExitCode(1, $process);
    }

    /**
     * It should generate in different output formats.
     *
     * @dataProvider provideOutputs
     */
    public function testOutputs($output)
    {
        $process = $this->phpbench(
            'report --file=results/report1.xml --output=' . $output . ' --report=default'
        );

        $this->assertExitCode(0, $process);
    }

    public function provideOutputs()
    {
        return array(
            array('html'),
            array('markdown'),
        );
    }
}
