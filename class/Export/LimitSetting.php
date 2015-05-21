<?php
/**
 * This file is part of the "Docalist Biblio Export" plugin.
 *
 * Copyright (C) 2015-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */
namespace Docalist\Biblio\Export;

use Docalist\Type\Object;

/**
 * Options de configuration du plugin.
 *
 * @property Integer $exportpage ID de la page "export".
 */
class LimitSetting extends Object {
    static protected function loadSchema() {
        return [
            'fields' => [
                'role' => [
                    'type' => 'string',
                    'label' => __('Rôle WordPress', 'docalist-biblio-export'),
                    'description' => __("Nom du groupe d'utilisateurs", 'docalist-biblio-export'),
                ],
                'limit' => [
                    'type' => 'int',
                    'label' => __('Limite pour ce rôle', 'docalist-biblio-export'),
                    'description' => __("Nombre maximum de notices exportables pour ce rôle (0 = pas de limite).", 'docalist-biblio-export'),
                ]
            ]
        ];
    }
}