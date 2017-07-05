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

use Docalist\Biblio\Type\TypedNumber;
use Docalist\Type\TableEntry;
use Docalist\Type\Decimal;

/**
 * Nombre typé : un type composite associant un type provenant d'une table d'autorité de type number à une valeur
 * numérique de de type Decimal : chiffres clés, données chiffrées, caractéristiques...
 *
 * La table associée contient une colonne format qui indique comment formatter les entrées.
 *
 * @property TableEntry $type   Type    Type de chiffre clé.
 * @property Decimal    $value  Value   Nombre associé.
 */
class TypedDecimal extends TypedNumber
{
    public static function loadSchema()
    {
        return [
            'label' => __('Chiffres clés', 'docalist-biblio'),
            'description' => __('Chiffres clés, nombres, caractéristiques...', 'docalist-biblio'),
            'fields' => [
                'type' => [
                    'description' => __('Type de chiffre clé.', 'docalist-biblio'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\Decimal',
                    'label' => __('Nombre', 'docalist-biblio'),
                    'description' => __('Nombre associé.', 'docalist-biblio'),
                ],
            ],
        ];
    }
}
