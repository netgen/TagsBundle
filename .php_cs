<?php

return Symfony\CS\Config\Config::create()
    ->setUsingLinter(false)
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        'concat_with_spaces',
        'phpdoc_order',
        'long_array_syntax',
        '-short_array_syntax',
        '-psr0',
        '-concat_without_spaces',
        '-phpdoc_params',
        '-phpdoc_to_comment',
        '-spaces_cast',
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in(__DIR__)
            ->exclude([
                'vendor',
                'node_modules',
            ])
            ->files()->name('*.php')
    )
;
