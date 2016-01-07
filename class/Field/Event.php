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
namespace Docalist\Biblio\Field;

use Docalist\Type\Text;
use Docalist\Type\Composite;
use Docalist\Forms\Table;
use Docalist\MappingBuilder;
use InvalidArgumentException;

/**
 * Description d'un événement (colloque, réunion, soutenance, etc.)
 *
 * @property Text $title
 * @property Text $date
 * @property Text $place
 * @property Text $number
 */
class Event extends Composite {
    static public function loadSchema() {
        return [
            'fields' => [
                'title' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Titre', 'docalist-biblio'),
                    'description' => __("Titre du congrès, nom de la réunion, etc.", 'docalist-biblio'),
                ],
                'date' => [
                    'type' => 'Docalist\Type\FuzzyDate',
                    'label' => __('Date', 'docalist-biblio'),
                    'description' => __("Date de l'évènement.", 'docalist-biblio'),
                ],
                'place' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Lieu', 'docalist-biblio'),
                    'description' => __("Lieu de l'événement (ville et/ou pays).", 'docalist-biblio'),
                ],
                'number' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Numéro', 'docalist-biblio'),
                    'description' => __("Numéro éventuel associé à l'évènement.", 'docalist-biblio'),
                ]
            ]
        ];
    }

    public function getEditorForm($options = null)
    {
        $field = new Table($this->schema->name());
        $field->input('title')->addClass('event-title');
        $field->input('date')->addClass('event-date');
        $field->input('place')->addClass('event-place');
        $field->input('number')->addClass('event-number');

        return $field;
    }

    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('event')->text();
    }

    public function mapData(array & $document) {
        $document['event'][] = $this->title() . '¤' . $this->date() . '¤' . $this->place() . '¤' . $this->number();
    }

    public function getAvailableFormats()
    {
        return [
            'default'   => __("Format par défaut", 'docalist-biblio'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        switch ($format) {
            case 'default':
                $h = $this->formatField('title', $options);
                isset($this->number) && $h .= ' (' . $this->formatField('number', $options) . ')';
                isset($this->place) && $h .= ', ' . $this->formatField('place', $options);
                isset($this->date) && $h .= ', ' . $this->formatField('date', $options);

                return $h;

        }

        throw new InvalidArgumentException("Invalid Event format '$format'");
    }
}