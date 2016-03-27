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

use Docalist\Type\MultiField;
use InvalidArgumentException;

/**
 * Date.
 *
 * @property Docalist\Type\TableEntry $type
 * @property Docalist\Type\Text $value
 */
class Date extends MultiField {
    static public function loadSchema() {
        return [
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:dates',
                    'label' => __('Type de date', 'docalist-biblio'),
    //                 'description' => __('Date', 'docalist-biblio'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\FuzzyDate',
                    'label' => __('Date', 'docalist-biblio'),
                ]
            ]
        ];
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('date')->date();
        $mapping->addTemplate('date.*')->copyFrom('date')->copyDataTo('date');
    }

    public function mapData(array & $document) {
        $document['date.' . $this->type()][] = $this->__get('value')->value();
    }
*/
    public function getAvailableFormats()
    {
        return [
            'date'          => 'Date uniquement',
            'date (type)'   => 'Date (type)',
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        $date = $this->formatField('value', $options);
        switch ($format) {
            case 'date':
            case 'month/year': // format dispo avant, à virer
            case 'year': // format dispo avant, à virer
                return $date;

            case 'date (type)':
                if (isset($this->type)) {
                    $date && $date .= ' '; // espace insécable avant '('
                    $date .= '(' . $this->formatField('type', $options) . ')';
                }
                return $date;
        }
        throw new InvalidArgumentException("Invalid Date format '$format'");
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a que le type et pas de date
        return $this->filterEmptyProperty('value');
    }
}