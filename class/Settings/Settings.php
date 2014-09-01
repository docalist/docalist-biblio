<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Settings;

/**
 * Config de Docalist Biblio.
 *
 * @property DatabaseSettings[] $databases Liste des bases.
 */
class Settings extends \Docalist\Type\Settings {
    protected $id = 'docalist-biblio-settings';

    static protected function loadSchema() {
        return [
            'fields' => [
                'databases' => [
                    'type' => 'DatabaseSettings*',
                    'key' => 'name',
                    'label' => __('Liste des bases de données documentaires', 'docalist-biblio'),
                ]
            ]
        ];
    }
}