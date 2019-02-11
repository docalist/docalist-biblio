<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Field;

use Docalist\Type\Composite;
use Docalist\Type\Text;
use Docalist\Type\FuzzyDate;

/**
 * Description de l'événement à l'origine du document cataogué (colloque, réunion, soutenance, etc.)
 *
 * @property Text       $title      Nom ou description du contexte.
 * @property FuzzyDate  $date       Date liée.
 * @property Text       $place      Lieu associé.
 * @property Text       $number     Numéro éventuel.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ContextField extends Composite
{
    public static function loadSchema()
    {
        return [
            'name' => 'context',
            'repeatable' => false,
            'label' => __('Contexte', 'docalist-biblio'),
            'description' => __(
                'Contexte dans lequel a été produit le document (congrès, soutenance de thèse, exposition...)',
                'docalist-biblio'
            ),
            'fields' => [
                'title' => [
                    'type' => Text::class,
                    'label' => __('Nom ou description', 'docalist-biblio'),
                    'description' => __('Nom du congrès, de la réunion, etc.', 'docalist-biblio'),
                ],
                'date' => [
                    'type' => FuzzyDate::class,
                    'label' => __('Date', 'docalist-biblio'),
                    'description' => __('Date liée.', 'docalist-biblio'),
                ],
                'place' => [
                    'type' => Text::class,
                    'label' => __('Lieu', 'docalist-biblio'),
                    'description' => __('Lieu associé.', 'docalist-biblio'),
                ],
                'number' => [
                    'type' => Text::class,
                    'label' => __('Numéro', 'docalist-biblio'),
                    'description' => __('Numéro éventuel.', 'docalist-biblio'),
                ]
            ],
            'editor' => 'table',
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

        return parent::getFormattedValue($options);
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
