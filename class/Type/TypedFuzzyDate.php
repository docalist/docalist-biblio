<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Type;

use Docalist\Type\TableEntry;
use Docalist\Type\FuzzyDate;

/**
 * Date typée : un type composite associant un champ TableEntry à une valeur de type FuzzyDate.
 *
 * @property TableEntry $type   Type de date.
 * @property FuzzyDate  $value  Date associée.
 */
class TypedFuzzyDate extends TypedText
{
    public static function loadSchema()
    {
        return [
            'label' => __('Date', 'docalist-biblio'),
            'description' => __('Date et type de date.', 'docalist-biblio'),
            'fields' => [
                'type' => [
                    'table' => 'table:dates',
                    'description' => __('Type de date', 'docalist-biblio'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\FuzzyDate',
                    'label' => __('Date', 'docalist-biblio'),
                    'description' => __('Date au format AAAAMMJJ', 'docalist-biblio'),
                ]
            ]
        ];
    }
}
