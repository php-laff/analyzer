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

$fileHeaderComment = <<<'EOF'
This file is part of the PHP-LAFF package.

(c) Joseph Bielawski <stloyd@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->append([__FILE__])
;

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'header_comment' => ['header' => $fileHeaderComment],
            'protected_to_private' => false,
            'native_constant_invocation' => ['strict' => false],
            'modernize_strpos' => true,
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
