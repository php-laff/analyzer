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

abstract class Box
{
    public const HEIGHT = 'height';
    public const LENGTH = 'length';
    public const WIDTH = 'width';

    public readonly string $identifier;

    public function __construct(public readonly int $length, public readonly int $width, public readonly int $height, ?string $identifier = null)
    {
        $this->identifier = $identifier ?: bin2hex(random_bytes(10));
    }

    public function getArea(): int
    {
        return $this->length * $this->width;
    }

    public function getVolume(): int
    {
        return $this->length * $this->width * $this->height;
    }

    /**
     * @return array{length: int, width: int, height: int}
     */
    public function toArray(): array
    {
        return [
            self::LENGTH => $this->length,
            self::WIDTH => $this->width,
            self::HEIGHT => $this->height,
        ];
    }
}
