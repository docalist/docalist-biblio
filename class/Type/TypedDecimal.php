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
use Docalist\Type\TableEntry;
use Docalist\Type\Decimal;

/**
 * Nombre typé : un type composite associant un type provenant d'une table d'autorité
 * de type number à une valeur de type texte.
 * La table associée contient une colonne format qui indique comment formatter les
 * entrées.
 *
 * @property TableEntry $type   Type
 * @property Decimal    $value  Value
 */
class TypedDecimal extends TypedText
{
    public static function loadSchema()
    {
        return [
            'label' => __('Chiffres clés', 'docalist-core'),
            'description' => __('Nombres et chiffres clés.', 'docalist-core'),
            'fields' => [
                'type' => [
                    'description' => __('Type de chiffre.', 'docalist-core'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\Decimal',
                    'label' => __('Nombre', 'docalist-core'),
                    'description' => __('Valeur.', 'docalist-core'),
                ],
            ],
        ];
    }

    public function getAvailableFormats()
    {
        return [
            'format' => __("Format indiqué dans la table d'autorité", 'docalist-core'),
            'label' => __('Libellé indiqué dans la table suivi du numéro', 'docalist-core'),
            'v' => __('Nombre uniquement, sans aucune mention', 'docalist-core'),
            'v (t)' => __('Nombre suivi du type entre parenthèses', 'docalist-core'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        $type = $this->formatField('type', $options);
        $number = $this->formatField('value', $options);

        switch ($format) {
            case 'format':
                $format = $this->type->getEntry('format') ?: $this->type() . ' %s';

                return trim(sprintf($format, $number));

            // mal nommé, plutôt 't v'
            case 'label':   return trim($type . ' ' . $number); // insécable
            case 'v':       return $number;
            case 'v (t)':   return empty($type) ? $number : $number . ' ('  . $type . ')'; // espace insécable avant '('
        }

        throw new InvalidArgumentException("Invalid Number format '$format'");
    }
}
