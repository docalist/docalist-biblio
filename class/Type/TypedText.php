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

use Docalist\Type\MultiField;
use Docalist\Type\TableEntry;
use Docalist\Type\Text;
use InvalidArgumentException;

/**
 * Texte typé : un type composite associant un champ TableEntry à une valeur de type Text.
 *
 * @property TableEntry $type   Type    Type de texte.
 * @property Text       $value  Value   Texte associé.
 */
class TypedText extends MultiField
{
    public static function loadSchema()
    {
        return [
            'label' => __('Texte', 'docalist-biblio'),
            'description' => __('Texte et type de texte.', 'docalist-biblio'),
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'label' => __('Type', 'docalist-biblio'),
                    'table' => '',
                ],
                'value' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Texte', 'docalist-biblio'),
                ],
            ],
        ];
    }

    public function getAvailableFormats()
    {
        return [
            'v'     => __('Valeur', 'docalist-biblio'),
            'v (t)' => __('Valeur (Type)', 'docalist-biblio'),
            't : v' => __('Type : Valeur', 'docalist-biblio'),
            't: v'  => __('Type: Valeur', 'docalist-biblio'),
            't v'   => __('Type Valeur', 'docalist-biblio'),
        ];
    }

    public function getDefaultFormat()
    {
        return 't: v';
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        $type = $this->formatField('type', $options);
        $value = $this->formatField('value', $options);

        switch ($format) {
            case 'v':
                return $value;

            case 'v (t)': // Espace insécable avant la parenthèse ouvrante
                return empty($type) ? $value : $value . ' ('  . $type . ')';

            case 't : v': // Espace insécable avant le ':'
                return empty($type) ? $value : $type . ' : ' . $value;

            case 't: v':
                return empty($type) ? $value : $type . ': ' . $value;

            case 't v':
                return empty($type) ? $value : $type . ' ' . $value; // espace insécable
        }

        throw new InvalidArgumentException("Invalid TypedText format '$format'");
    }

    public function filterEmpty($strict = true)
    {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a que le type et pas de valeur
        return $this->filterEmptyProperty('value');
    }
}
