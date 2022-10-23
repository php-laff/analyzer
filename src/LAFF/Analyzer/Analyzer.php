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

use LAFF\Analyzer\Model\Box;
use LAFF\Analyzer\Model\Container;
use LAFF\Analyzer\Model\Package;
use LAFF\Analyzer\Model\PackageEdge;

final class Analyzer
{
    /**
     * @var array<string, Container>
     */
    private array $containers;

    /**
     * @var array<string, Package>
     */
    private array $packages;

    public function __construct()
    {
    }

    /**
     * @param Package[]   $packages
     * @param Container[] $containers
     */
    public function analyze(iterable $packages, iterable $containers = []): void
    {
        if (!$packages) {
            throw new \InvalidArgumentException('No packages passed to analyze!');
        }

        foreach ($packages as $package) {
            $this->packages[$package->identifier] = $package;
        }

        foreach ($containers ?: [$this->createVirtualContainer()] as $container) {
            $this->containers[$container->identifier] = $container;
        }

        $this->startNewLayer($this->containers[array_key_first($this->containers)]);
    }

    /**
     * @return array<string, Container>
     */
    public function getContainers(): array
    {
        return $this->containers;
    }

    public function countPackedPackages(): int
    {
        $i = 0;
        foreach ($this->containers as $container) {
            foreach ($container->packages as $layer) {
                $i += \count($layer);
            }
        }

        return $i;
    }

    public function getPackedVolume(): int
    {
        $volume = 0;
        foreach ($this->containers as $container) {
            foreach ($container->packages as $layer) {
                foreach ($layer as $package) {
                    $volume += $package->getVolume();
                }
            }
        }

        return $volume;
    }

    /**
     * @return array<string, Package>
     */
    public function getRemainingBoxes(): array
    {
        return $this->packages;
    }

    public function getRemainingVolume(): int
    {
        $volume = 0;
        foreach ($this->packages as $package) {
            $volume += $package->getVolume();
        }

        return $volume;
    }

    private function createVirtualContainer(): Container
    {
        $edges = [Box::LENGTH, Box::WIDTH, Box::HEIGHT];

        $longestEdge = PackageEdge::calculateLongestEdge($this->packages, $edges);
        $secondLongestEdge = PackageEdge::calculateLongestEdge($this->packages, array_diff($edges, [$longestEdge->name]));

        return new Container($longestEdge->size, $secondLongestEdge->size, 0, virtual: true);
    }

    private function checkIfPackageFitsWithRotation(Package $existingPackage, Package $package): bool
    {
        if ($this->checkIfPackageFits($existingPackage, $package)) {
            return true;
        }

        return $this->checkIfPackageFits(Package::rotate($existingPackage), $package);
    }

    private function checkIfPackageFits(Package $existingPackage, Package $package): bool
    {
        if ($existingPackage->length > $package->length) {
            return false;
        }

        if ($existingPackage->width > $package->width) {
            return false;
        }

        if ($existingPackage->height > $package->height) {
            return false;
        }

        return true;
    }

    private function startNewLayer(Container $container): void
    {
        // Skip full containers
        if ($container->full) {
            return;
        }

        $biggestPackage = $this->findBiggestPackage();

        // For virtual container we can increase its height (ck = ck + ci)
        if ($container->virtual) {
            $container = Container::increaseHeight($container, $biggestPackage->height);

            $this->containers[$container->identifier] = $container;
        }

        $layer = array_key_last($container->packages) + 1;
        $container->packages[$layer][] = $biggestPackage;

        // Package is in container (ki = ki - 1)
        unset($this->packages[$biggestPackage->identifier]);

        if (!$this->packages) {
            return;
        }

        // No space left
        if (($container->getArea() - $biggestPackage->getArea()) <= 0) {
            // Predefined container cannot be resized
            if (!$container->virtual) {
                $container->full = true;

                // If available, start layer on next container
                $nextContainer = next($this->containers);
                if (false === $nextContainer) {
                    return;
                }

                $this->startNewLayer($nextContainer);
            } else {
                $this->startNewLayer($container);
            }

            return;
        }

        // Fill the space if package fits
        if (($container->length - $biggestPackage->length) > 0) {
            $this->insertPackageIntoContainer(
                $container,
                new Package(
                    $container->length - $biggestPackage->length,
                    $container->width,
                    $biggestPackage->height
                ),
                $layer
            );
        }

        if (($container->width - $biggestPackage->width) > 0) {
            $this->insertPackageIntoContainer(
                $container,
                new Package(
                    $biggestPackage->length,
                    $container->width - $biggestPackage->width,
                    $biggestPackage->height
                ),
                $layer
            );
        }

        // Remaining packages must go on a new layer
        if ($this->packages) {
            $this->startNewLayer($container);
        }
    }

    private function insertPackageIntoContainer(Container $container, Package $package, int $layer): void
    {
        $spaceVolume = $package->getVolume();

        $fittingPackageIndex = null;
        $fittingPackageVolume = null;
        foreach ($this->packages as $index => $existingPackage) {
            $packageVolume = $existingPackage->getVolume();

            // Packages with higher volume than target space should be ignored
            if ($packageVolume > $spaceVolume) {
                continue;
            }

            if ($this->checkIfPackageFitsWithRotation($existingPackage, $package)) {
                if (null !== $fittingPackageVolume || $packageVolume > $fittingPackageVolume) {
                    $fittingPackageIndex = $index;
                    $fittingPackageVolume = $packageVolume;
                }
            }
        }

        if (null === $fittingPackageIndex) {
            return;
        }

        $existingPackage = $this->packages[$fittingPackageIndex];

        $container->packages[$layer][] = $existingPackage;
        unset($this->packages[$fittingPackageIndex]);

        if (($package->length - $existingPackage->length) > 0) {
            $this->insertPackageIntoContainer(
                $container,
                new Package(
                    $package->length - $existingPackage->length,
                    $package->width,
                    $existingPackage->height
                ),
                $layer
            );
        }

        if (($package->width - $existingPackage->width) > 0) {
            $this->insertPackageIntoContainer(
                $container,
                new Package(
                    $existingPackage->length,
                    $package->width - $existingPackage->width,
                    $existingPackage->height
                ),
                $layer
            );
        }
    }

    private function findBiggestPackage(): Package
    {
        $biggestPackageIndex = null;
        $biggestPackageArea = 0;

        // Find package with the biggest volume and minimum height
        foreach ($this->packages as $index => $package) {
            $packageArea = $package->getArea();

            if ($packageArea > $biggestPackageArea) {
                $biggestPackageArea = $packageArea;
                $biggestPackageIndex = $index;
            } elseif ($packageArea === $biggestPackageArea) {
                if (null === $biggestPackageIndex || ($package->height < $this->packages[$biggestPackageIndex]->height)) {
                    $biggestPackageIndex = $index;
                }
            }
        }

        return $this->packages[$biggestPackageIndex];
    }
}
