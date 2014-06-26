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

use Docalist\Data\Entity\AbstractEntity;

/**
 * Description d'un événement (colloque, réunion, soutenance, etc.)
 *
 * @property string $title
 * @property string $date
 * @property string $place
 * @property string $number
 */
class Event extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'title' => array(
                'label' => __('Titre', 'docalist-biblio'),
                'description' => __("Titre du congrès, nom de la réunion, etc.", 'docalist-biblio'),
            ),
            'date' => array(
                'label' => __('Date', 'docalist-biblio'),
                'description' => __("Date de l'évènement.", 'docalist-biblio'),
            ),
            'place' => array(
                'label' => __('Lieu', 'docalist-biblio'),
                'description' => __("Lieu de l'événement (ville et/ou pays).", 'docalist-biblio'),
            ),
            'number' => array(
                'label' => __('Numéro', 'docalist-biblio'),
                'description' => __("Numéro éventuel associé à l'évènement.", 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}