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
use Docalist\Forms\Table;

/**
 * Description d'un événement (colloque, réunion, soutenance, etc.)
 *
 * @property String $title
 * @property String $date
 * @property String $place
 * @property String $number
 */
class Event extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'title' => [
                    'label' => __('Titre', 'docalist-biblio'),
                    'description' => __("Titre du congrès, nom de la réunion, etc.", 'docalist-biblio'),
                ],
                'date' => [
                    'label' => __('Date', 'docalist-biblio'),
                    'description' => __("Date de l'évènement.", 'docalist-biblio'),
                ],
                'place' => [
                    'label' => __('Lieu', 'docalist-biblio'),
                    'description' => __("Lieu de l'événement (ville et/ou pays).", 'docalist-biblio'),
                ],
                'number' => [
                    'label' => __('Numéro', 'docalist-biblio'),
                    'description' => __("Numéro éventuel associé à l'évènement.", 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->input('title')->addClass('event-title');
        $field->input('date')->addClass('event-date');
        $field->input('place')->addClass('event-place');
        $field->input('number')->addClass('event-number');

        return $field;
    }
}