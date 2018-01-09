<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\Composite;
use Docalist\Type\Text;
use Docalist\Type\FuzzyDate;
use InvalidArgumentException;

/**
 * Description de l'événement à l'origine du document cataogué (colloque, réunion, soutenance, etc.)
 *
 * @property Text       $title      Nom de l'événement.
 * @property FuzzyDate  $date       Date de l'évènement.
 * @property Text       $place      Lieu de l'événement.
 * @property Text       $number     Numéro éventuel associé à l'évènement.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Event extends Composite
{
    public static function loadSchema()
    {
        return [
            'label' => __('Événement', 'docalist-biblio'),
            'description' => __(
                "Événement à l'origine du document (congrès, colloque, manifestation, soutenance de thèse...)",
                'docalist-biblio'
            ),
            'fields' => [
                'title' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom du congrès, de la réunion, etc.", 'docalist-biblio'),
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

/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('event')->text();
    }

    public function mapData(array & $document) {
        $document['event'][] = $this->title() . '¤' . $this->date() . '¤' . $this->place() . '¤' . $this->number();
    }
*/
    public function getAvailableFormats()
    {
        return [
            't (n), p, d' => __('Nom (numéro), lieu, date', 'docalist-biblio'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        switch ($format) {
            case 't (n), p, d':
                $h = $this->formatField('title', $options);
                isset($this->number) && $h .= ' (' . $this->formatField('number', $options) . ')';
                isset($this->place) && $h .= ', ' . $this->formatField('place', $options);
                isset($this->date) && $h .= ', ' . $this->formatField('date', $options);

                return $h;
        }

        throw new InvalidArgumentException("Invalid Event format '$format'");
    }

    public function filterEmpty($strict = true)
    {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a de titre
        return $this->filterEmptyProperty('title');
    }
}
