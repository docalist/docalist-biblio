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
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\Object;

/**
 * Etendue du document : pagination, nombre de pages, durée en minutes, etc.
 *
 * @property String $type
 * @property String $value
 */
class Extent extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __("Type", 'docalist-biblio'),
                    'description' => __("Type d'étendue", 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Valeur', 'docalist-biblio'),
                    'description' => __('Etendue dans le format indiqué par le type (n° de page, nb de pages, durée, etc.)', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    // map : champ non indexé
}