<?php

declare(strict_types=1);

/*
 * This file is part of the PHP-LAFF package.
 *
 * (c) Joseph Bielawski <stloyd@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LAFF\Analyzer\Tests;

use LAFF\Analyzer\Analyzer;
use LAFF\Analyzer\Model\Container;
use LAFF\Analyzer\Model\Package;
use PHPUnit\Framework\TestCase;

final class AnalyzerTest extends TestCase
{
    public function testEmptyPackerFails(): void
    {
        $this->expectExceptionMessage('No packages passed to analyze!');
        $this->expectException(\InvalidArgumentException::class);

        (new Analyzer())->analyze([]);
    }

    public function testCalculationOfContainerHeight(): void
    {
        $analyzer = new Analyzer();
        $analyzer->analyze(
            [
                new Package(50, 50, 8),
                new Package(33, 8, 8),
                new Package(16, 20, 8),
                new Package(3, 18, 8),
                new Package(14, 12, 8),
            ]
        );

        $containers = $analyzer->getContainers();
        $this->assertCount(1, $containers);

        /** @var Container $container */
        $container = reset($containers);

        $this->assertTrue($container->virtual);
        $this->assertSame(['length' => 50, 'width' => 50, 'height' => 16], $container->toArray());

        $this->assertSame(2, $container->countLayers());

        $this->assertSame(5, $analyzer->countPackedPackages());
        $this->assertSame(26448, $analyzer->getPackedVolume());

        $this->assertCount(0, $analyzer->getRemainingBoxes());

        $layerOne = $container->getLayerDimensions(1);
        $this->assertSame(['length' => 50, 'width' => 50, 'height' => 8], $layerOne->toArray());

        $layerTwo = $container->getLayerDimensions(2);
        $this->assertSame(['length' => 33, 'width' => 20, 'height' => 8], $layerTwo->toArray());
    }

    public function testPackingWithPredefinedContainer(): void
    {
        $container = new Container(65, 60, 8);

        $analyzer = new Analyzer();
        $analyzer->analyze(
            [
                new Package(50, 50, 8),
                new Package(33, 8, 8),
                new Package(16, 20, 8),
                new Package(3, 18, 8),
                new Package(14, 12, 8),
            ],
            [$container]
        );

        $this->assertSame(1, $container->countLayers());
        $this->assertFalse($container->full);

        $this->assertSame(5, $analyzer->countPackedPackages());
    }

    public function testPackingPackagesWithDifferentHeight(): void
    {
        $analyzer = new Analyzer();
        $analyzer->analyze(
            [
                new Package(33, 8, 12),
                new Package(16, 20, 8),
                new Package(3, 18, 3),
                new Package(14, 12, 5),
            ]
        );

        $containers = $analyzer->getContainers();
        $this->assertCount(1, $containers);

        /** @var Container $container */
        $container = reset($containers);

        $this->assertTrue($container->virtual);
        $this->assertSame(['length' => 33, 'width' => 20, 'height' => 20], $container->toArray());

        $this->assertSame(2, $container->countLayers());

        $this->assertSame(4, $analyzer->countPackedPackages());
        $this->assertCount(0, $analyzer->getRemainingBoxes());

        // Container must have 2 layers with given amount of packages on each
        $layers = [1 => 3, 2 => 1];
        foreach ($container->packages as $layer => $packages) {
            $this->assertCount($layers[$layer], $packages);
        }
    }

    public function testPackingWithPredefinedContainerLeavesSomePackage(): void
    {
        $container = new Container(50, 50, 8);

        $analyzer = new Analyzer();
        $analyzer->analyze(
            [
                new Package(50, 50, 8),
                new Package(33, 8, 8),
                new Package(16, 20, 8),
                new Package(3, 18, 8),
                new Package(14, 12, 8),
            ],
            [$container]
        );

        $this->assertSame(1, $container->countLayers());
        $this->assertTrue($container->full);

        $this->assertSame(1, $analyzer->countPackedPackages());
        $this->assertCount(4, $analyzer->getRemainingBoxes());
        $this->assertSame(6448, $analyzer->getRemainingVolume());
    }

    public function testPackingWithPredefinedContainers(): void
    {
        $container1 = new Container(50, 50, 8);
        $container2 = new Container(50, 50, 8);

        $analyzer = new Analyzer();
        $analyzer->analyze(
            [
                new Package(50, 50, 8),
                new Package(33, 8, 8),
                new Package(16, 20, 8),
                new Package(3, 18, 8),
                new Package(14, 12, 8),
            ],
            [$container1, $container2]
        );

        $this->assertSame(1, $container1->countLayers());
        $this->assertTrue($container1->full);
        $this->assertSame(1, $container2->countLayers());
        $this->assertFalse($container2->full);

        $this->assertSame(5, $analyzer->countPackedPackages());
        $this->assertCount(0, $analyzer->getRemainingBoxes());

        $this->assertSame(0, $container1->getWastePercentage());
        $this->assertSame(0, $container1->getWasteVolume());
        $this->assertSame(67, $container2->getWastePercentage());
        $this->assertSame(13552, $container2->getWasteVolume());
    }
}
