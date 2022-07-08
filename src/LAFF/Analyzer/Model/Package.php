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

final class Package extends Box
{
    public static function rotate(self $box): self
    {
        return new self($box->width, $box->length, $box->height);
    }
}
