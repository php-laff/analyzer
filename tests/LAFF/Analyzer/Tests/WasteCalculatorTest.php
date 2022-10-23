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
use LAFF\Analyzer\WasteCalculator;
use PHPUnit\Framework\TestCase;

final class WasteCalculatorTest extends TestCase
{
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

        $calculator = new WasteCalculator();

        // Waste is zero cause only one package fit into the container
        $this->assertSame(0, $calculator->getWastePercentage($analyzer->getContainers()));
        $this->assertSame(0, $calculator->getWasteVolume($analyzer->getContainers()));
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

        $calculator = new WasteCalculator();

        $this->assertSame(33, $calculator->getWastePercentage($analyzer->getContainers()));
        $this->assertSame(13552, $calculator->getWasteVolume($analyzer->getContainers()));
    }
}
