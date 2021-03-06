<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\Factory;
use Symfony\Component\Finder\Finder;

/**
 * This class finds a benchmark (or benchmarks depending on the path), loads
 * their metadata and builds a collection of BenchmarkMetadata instances.
 */
class CollectionBuilder
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Factory $factory
     * @param Finder $finder
     */
    public function __construct(Factory $factory, Finder $finder = null)
    {
        $this->factory = $factory;
        $this->finder = $finder ?: new Finder();
    }

    /**
     * Build the BenchmarkMetadata collection.
     *
     * @param string $path
     * @param array $subjectFilter
     * @param array $groupFilter
     */
    public function buildCollection($path, array $filters = array(), array $groupFilter = array())
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File or directory "%s" does not exist (cwd: %s)',
                $path,
                getcwd()
            ));
        }

        if (is_dir($path)) {
            $this->finder->in($path)
                ->name('*Bench.php');
        } else {
            // the path is already a file, just restrict the finder to that.
            $this->finder->in(dirname($path))
                ->depth(0)
                ->name(basename($path));
        }

        $benchmarks = array();

        foreach ($this->finder as $file) {
            if (!is_file($file)) {
                continue;
            }

            $benchmarkMetadata = $this->factory->getMetadataForFile($file->getPathname());

            if (null === $benchmarkMetadata) {
                continue;
            }

            if ($groupFilter) {
                $benchmarkMetadata->filterSubjectGroups($groupFilter);
            }

            if ($filters) {
                $benchmarkMetadata->filterSubjectNames($filters);
            }

            if (false === $benchmarkMetadata->hasSubjects()) {
                continue;
            }

            $benchmarks[] = $benchmarkMetadata;
        }

        return new Collection($benchmarks);
    }
}
