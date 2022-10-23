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

namespace LAFF\Analyzer;

use LAFF\Analyzer\Model\Container;

final class WasteCalculator
{
    /**
     * @param Container[] $containers
     */
    public function getWasteVolume(array $containers): int
    {
        return $this->getContainersVolume(...$containers) - $this->getPackedVolume(...$containers);
    }

    /**
     * @param Container[] $containers
     */
    public function getWastePercentage(array $containers): int
    {
        $containersVolume = $this->getContainersVolume(...$containers);
        $packedVolume = $this->getPackedVolume(...$containers);

        return $containersVolume > 0 && $packedVolume > 0 ? (int) ((($containersVolume - $packedVolume) / $containersVolume) * 100) : 0;
    }

    private function getPackedVolume(Container ...$containers): int
    {
        $volume = 0;
        foreach ($containers as $container) {
            foreach ($container->packages as $layer) {
                foreach ($layer as $package) {
                    $volume += $package->getVolume();
                }
            }
        }

        return $volume;
    }

    private function getContainersVolume(Container ...$containers): int
    {
        $volume = 0;
        foreach ($containers as $container) {
            $volume += $container->getVolume();
        }

        return $volume;
    }
}
