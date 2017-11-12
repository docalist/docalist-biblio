<?php
/**
 * This file is part of the "Docalist Biblio" plugin.
 *
 * Copyright (C) 2015-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Export;

use Docalist\Type\Composite;
use Docalist\Type\Text;
use Docalist\Type\Integer;

/**
 * Options de configuration du plugin.
 *
 * @property Text       $role   Rôle Wordpress.
 * @property Integer    $limit  Limite pour ce rôle.
 */
class LimitSetting extends Composite
{
    public static function loadSchema()
    {
        return [
            'fields' => [
                'role' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Rôle WordPress', 'docalist-biblio'),
                    'description' => __("Nom du groupe d'utilisateurs", 'docalist-biblio'),
                ],
                'limit' => [
                    'type' => 'Docalist\Type\Integer',
                    'label' => __('Limite pour ce rôle', 'docalist-biblio'),
                    'description' => __(
                        'Nombre maximum de notices exportables pour ce rôle (0 = pas de limite).',
                        'docalist-biblio'
                    ),
                ],
            ],
        ];
    }
}
