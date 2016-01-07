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

/**
 * Texte large typé : un type composite associant un type provenant d'une table d'autorité
 * à une valeur de type LargeText.
 */
class TypedLargeText extends TypedText
{
    public static function loadSchema()
    {
        return [
            'fields' => [
                'value' => [
                    'type' => 'Docalist\Type\LargeText',
                    'label' => __('Texte', 'docalist-core'),
                ],
            ],
        ];
    }
}
