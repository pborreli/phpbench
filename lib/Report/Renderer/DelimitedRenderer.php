<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Renderer;

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Dom\Element;
use PhpBench\Registry\Config;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Renders the report as a delimited list.
 */
class DelimitedRenderer implements RendererInterface, OutputAwareInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Render the table.
     *
     * @param mixed $tableDom
     * @param mixed $config
     */
    public function render(Document $reportDom, Config $config)
    {
        foreach ($reportDom->firstChild->query('./report') as $reportEl) {
            foreach ($reportEl->query('.//table') as $tableEl) {
                $this->renderTableElement($tableEl, $config);
            }
        }
    }

    protected function renderTableElement(Element $tableEl, $config)
    {
        $rows = array();

        if (true === $config['header']) {
            $header = array();
            foreach ($tableEl->query('.//row') as $rowEl) {
                foreach ($rowEl->query('.//cell') as $cellEl) {
                    $colName = $cellEl->getAttribute('name');
                    $header[$colName] = $colName;
                }
            }
            $rows[] = $header;
        }

        foreach ($tableEl->query('.//row') as $rowEl) {
            $row = array();
            foreach ($rowEl->query('.//cell') as $cellEl) {
                $colName = $cellEl->getAttribute('name');
                $row[$colName] = $cellEl->nodeValue;
            }

            $rows[] = $row;
        }

        if ($config['file']) {
            $pointer = fopen($config['file'], 'w');
        } else {
            $pointer = fopen('php://temp', 'w+');
        }
        foreach ($rows as $row) {
            // use fputcsv to handle escaping
            fputcsv($pointer, $row, $config['delimiter']);
        }
        rewind($pointer);
        $this->output->write(stream_get_contents($pointer));
        fclose($pointer);

        if ($config['file']) {
            $this->output->writeln('Dumped delimited file:');
            $this->output->writeln($config['file']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return array(
            'delimiter' => "\t",
            'file' => null,
            'header' => false,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'delimiter' => array(
                    'type' => 'string',
                ),
                'file' => array(
                    'type' => array('string', 'null'),
                ),
                'header' => array(
                    'type' => 'boolean',
                ),
            ),
            'additionalProperties' => false,
        );
    }
}
