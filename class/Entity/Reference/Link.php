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
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Biblio\Type\Object;

/**
 * Lien internet.
 *
 * @property String $type
 * @property String $url
 * @property String $label
 * @property String $date
 * @property String $lastcheck
 * @property String $checkstatus
 */
class Link extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type', 'docalist-biblio'),
                    'description' => __('Type de lien', 'docalist-biblio'),
                ],
                'url' => [
                    'label' => __('Adresse', 'docalist-biblio'),
                    'description' => __('Url complète du lien', 'docalist-biblio'),
                ],
                'label' => [
                    'label' => __('Libellé', 'docalist-biblio'),
                    'description' => __('Texte à afficher', 'docalist-biblio'),
                ],
                'date' => [
                    'label' => __('Accédé le', 'docalist-biblio'),
                    'description' => __('Date', 'docalist-biblio'),
                ],
                'lastcheck' => [
                    'label' => __('Lien vérifié le', 'docalist-biblio'),
                    'description' => __('Date de dernière vérification du lien', 'docalist-biblio'),
                ],
                'status' => [
                    'label' => __('Statut', 'docalist-biblio'),
                    'description' => __('Statut du lien lors de la dernière vérification.', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }
}