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
namespace Docalist\Biblio\Type;

use InvalidArgumentException;

class TypedFuzzyDate extends TypedText
{
    static public function loadSchema() {
        return [
            'label' => __('Date', 'docalist-core'),
            'description' => __('Date et type de date.', 'docalist-core'),
            'fields' => [
                'type' => [
                    'table' => 'table:dates',
                    'description' => __('Type de date', 'docalist-core'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\FuzzyDate',
                    'label' => __('Date', 'docalist-core'),
                    'description' => __('Date au format AAAAMMJJ', 'docalist-core'),
                ]
            ]
        ];
    }

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
}
