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

namespace LAFF\Analyzer\Tests\Model;

use LAFF\Analyzer\Model\Box;
use LAFF\Analyzer\Model\Package;
use LAFF\Analyzer\Model\PackageEdge;
use PHPUnit\Framework\TestCase;

final class PackageEdgeTest extends TestCase
{
    public function testCalculateFailsWithUnknownEdge(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown edge name given: test');

        PackageEdge::calculateLongestEdge([], ['test']);
    }

    public function testCalculateFailsWithoutAnyPackage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass packages information!');

        PackageEdge::calculateLongestEdge([], [Box::LENGTH]);
    }

    public function testCalculateFailsWithoutAnyRealPackage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Element of packages is not a Package class!');

        // @phpstan-ignore-next-line
        PackageEdge::calculateLongestEdge(['test'], [Box::LENGTH]);
    }

    public function testCalculateWorks(): void
    {
        $this->assertEquals(
            new PackageEdge(10, Box::LENGTH),
            PackageEdge::calculateLongestEdge([new Package(10, 10, 5)], [Box::LENGTH])
        );
    }
}
