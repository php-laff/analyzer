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

final class Container extends Box
{
    public readonly bool $virtual;
    /**
     * @var array<int, array<int, Package>>
     */
    public array $packages = [];
    public bool $full = false;

    public function __construct(int $length, int $width, int $height, ?string $identifier = null, bool $virtual = false)
    {
        parent::__construct($length, $width, $height, $identifier);

        $this->virtual = $virtual;
    }

    public static function increaseHeight(self $container, int $height): self
    {
        if (!$container->virtual) {
            throw new \RuntimeException('Only virtual container can be resized!');
        }

        $newContainer = new self($container->length, $container->width, $container->height + $height, $container->identifier, true);
        $newContainer->packages = $container->packages;

        return $newContainer;
    }

    public function countLayers(): int
    {
        return \count($this->packages);
    }

    public function getLayerDimensions(int $layer): Layer
    {
        if ($layer < 0 || $layer > \count($this->packages) || !isset($this->packages[$layer])) {
            throw new \OutOfRangeException(sprintf('Passed layer %d was not found!', $layer));
        }

        $edges = [self::LENGTH, self::WIDTH, self::HEIGHT];

        $longestEdge = PackageEdge::calculateLongestEdge($this->packages[$layer], $edges);
        $secondLongestEdge = PackageEdge::calculateLongestEdge($this->packages[$layer], array_diff($edges, [$longestEdge->name]));

        return new Layer(
            $longestEdge->size,
            $secondLongestEdge->size,
            // Height of each layer is determined by height of the biggest package
            $this->packages[$layer][0]->height
        );
    }

    public function getPackedVolume(): int
    {
        $volume = 0;

        foreach ($this->packages as $packages) {
            foreach ($packages as $package) {
                $volume += $package->getVolume();
            }
        }

        return $volume;
    }

    public function getWasteVolume(): int
    {
        return $this->getVolume() - $this->getPackedVolume();
    }

    public function getWastePercentage(): int
    {
        $containersVolume = $this->getVolume();
        $packedVolume = $this->getPackedVolume();

        return $containersVolume > 0 && $packedVolume > 0 ? (int) ((($containersVolume - $packedVolume) / $containersVolume) * 100) : 0;
    }
}
