<?php
/**
 * File containing the bootstrapping of eZ Publish API for unit test use
 *
 * Setups class loading.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

// Get global config.php settings
if ( !( $settings = include ( __DIR__ . '/vendor/ezsystems/ezpublish-kernel/config.php' ) ) )
{
    throw new \RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php & customize to your needs!' );
}

require_once __DIR__ . '/vendor/autoload.php';

return include __DIR__ . '/vendor/ezsystems/ezpublish-kernel/container.php';
