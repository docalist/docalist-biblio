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
 * Etendue du document : pagination, nombre de pages, durée en minutes, etc.
 *
 * @property Docalist\Type\TableEntry $type
 * @property Docalist\Type\Text $value
 */
class Extent extends MultiField {
    static public function loadSchema() {
        return [
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:extent',
                    'label' => __("Type", 'docalist-biblio'),
                    'description' => __("Type d'étendue", 'docalist-biblio'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Valeur', 'docalist-biblio'),
                    'description' => __('Etendue dans le format indiqué par le type (n° de page, nb de pages, durée, etc.)', 'docalist-biblio'),
                ]
            ]
        ];
    }

    // map : champ non indexé

    public function getAvailableFormats()
    {
        return [
            'format'    => __("Format indiqué dans la table d'autorité", 'docalist-biblio'),
            'label'     => __("Libellé indiqué dans la table suivi du numéro", 'docalist-biblio'),
            'v'         => __('Valeur uniquement, sans aucune mention', 'docalist-biblio'),
            'v (t)'     => __('Valeur suivie du type entre parenthèses', 'docalist-biblio'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        $value = $this->formatField('value', $options);
        switch ($format) {
            case 'format':
                $format = $this->type->getEntry('format') ?: $this->type() . ' %s';
                return trim(sprintf($format, $value));
            case 'label': // mal nommé, plutôt 't v'
                return trim($this->formatField('type', $options) . ' ' . $value); // insécable
            case 'v':
                return $value;
            case 'v (t)':
                if (isset($this->type)) {
                    $value && $value .= ' '; // espace insécable avant '('
                    $value .= '(' . $this->formatField('type', $options) . ')';
                }
                return $value;
        }

        throw new InvalidArgumentException("Invalid Extent format '$format'");
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // L'entrée "non paginé" ne prend pas de valeur, donc pas vide
        if ($this->type() === 'no-pages') {
            return false;
        }

        // Retourne true si on n'a que le type et pas de valeur
        return $this->filterEmptyProperty('value');
    }
}