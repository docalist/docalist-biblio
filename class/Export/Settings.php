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

use Docalist\Type\Settings as TypeSettings;
use Docalist\Type\Integer;

/**
 * Options de configuration du plugin.
 *
 * @property Integer        $exportpage ID de la page "export".
 * @property LimitSetting   $limit      Limites de l'export.
 */
class Settings extends TypeSettings
{
    protected $id = 'docalist-biblio-export';

    public static function loadSchema()
    {
        return [
            'fields' => [
                'exportpage' => [
                    'type' => 'Docalist\Type\Integer',
                    'label' => __("Page pour l'export", 'docalist-biblio'),
                    'description' => __(
                        "Page WordPress sur laquelle l'export sera disponible.",
                        'docalist-biblio'
                    ),
                    'default' => 0,
                ],
                'limit' => [
                    'type' => 'Docalist\Biblio\Export\LimitSetting*',
                    'label' => __("Limites de l'export", 'docalist-biblio'),
                    'description' => __(
                        'Liste des rôles autorisés à exporter des notices et nombre maximum de notices par rôle.',
                        'docalist-biblio'
                    ),
                    'key' => 'role',
                ],
            ],
        ];
    }
}
