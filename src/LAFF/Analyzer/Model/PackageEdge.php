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

namespace LAFF\Analyzer\Model;

final class PackageEdge
{
    public function __construct(public readonly int $size, public readonly string $name)
    {
    }

    /**
     * @param Package[] $packages
     * @param string[]  $edges    Edges to select the longest from
     */
    public static function calculateLongestEdge(array $packages, array $edges = [Box::LENGTH, Box::WIDTH, Box::HEIGHT]): self
    {
        self::checkEdgeName($edges);

        if (!$packages) {
            throw new \InvalidArgumentException('You must pass packages information!');
        }

        $longestEdge = 0;
        $longestEdgeField = null;

        foreach ($packages as $package) {
            if (!$package instanceof Package) {
                throw new \InvalidArgumentException('Element of packages is not a Package class!');
            }

            foreach ($edges as $edge) {
                if ($package->{$edge} > $longestEdge) {
                    $longestEdge = (int) $package->{$edge};
                    $longestEdgeField = $edge;
                }
            }
        }

        if (!$longestEdge || !$longestEdgeField) {
            throw new \InvalidArgumentException('Cannot find longest edge!');
        }

        return new self($longestEdge, $longestEdgeField);
    }

    /**
     * @param string[] $edges
     */
    private static function checkEdgeName(array $edges): void
    {
        foreach ($edges as $edge) {
            if (!\in_array($edge, [Box::HEIGHT, Box::LENGTH, Box::WIDTH], true)) {
                throw new \InvalidArgumentException(sprintf('Unknown edge name given: %s', $edge));
            }
        }
    }
}
