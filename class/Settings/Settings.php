<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Settings;

/**
 * Config de Docalist Biblio.
 *
 * @property DatabaseSettings[] $databases Liste des bases.
 */
class Settings extends \Docalist\Type\Settings
{
    protected $id = 'docalist-biblio-settings';

    public static function loadSchema()
    {
        return [
            'fields' => [
                'databases' => [
                    'type' => 'Docalist\Biblio\Settings\DatabaseSettings*',
                    'key' => 'name',
                    'label' => __('Liste des bases de données documentaires', 'docalist-biblio'),
                ],
            ],
        ];
    }
}
