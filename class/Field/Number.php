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
 * Un numéro propre au document (ISSN, ISBN, Volume, Fascicule...)
 *
 * @property Docalist\Type\TableEntry $type
 * @property Docalist\Type\Text $value
 */
class Number extends MultiField {
    static public function loadSchema() {
        return [
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:numbers',
                    'label' => __('Type', 'docalist-biblio'),
                    'description' => __('Type de numéro', 'docalist-biblio'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Numéro', 'docalist-biblio'),
                    'description' => __('Numéro dans le format indiqué par le type.', 'docalist-biblio'),
                ]
            ]
        ];
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('number')->literal();
        $mapping->addTemplate('number.*')->copyFrom('number')->copyDataTo('number');
    }

    public function mapData(array & $document) {
        $document['number.' . $this->type()][] = $this->__get('value')->value();
    }
*/
    public function getAvailableFormats()
    {
        return [
            'format'    => __("Format indiqué dans la table d'autorité", 'docalist-biblio'),
            'label'     => __("Libellé indiqué dans la table suivi du numéro", 'docalist-biblio'),
            'v'         => __('Numéro uniquement, sans aucune mention', 'docalist-biblio'),
            'v (t)'     => __('Numéro suivi du type entre parenthèses', 'docalist-biblio'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        $number = $this->formatField('value', $options);
        switch ($format) {
            case 'format':
                $format = $this->type->getEntry('format') ?: $this->type() . ' %s';
                return trim(sprintf($format, $number));
            case 'label': // mal nommé, plutôt 't v'
                return trim($this->formatField('type', $options) . ' ' . $number); // insécable
            case 'v':
                return $number;
            case 'v (t)':
                if (isset($this->type)) {
                    $number && $number .= ' '; // espace insécable avant '('
                    $number .= '(' . $this->formatField('type', $options) . ')';
                }
                return $number;
        }

        throw new InvalidArgumentException("Invalid Number format '$format'");
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas de valeur
        return $this->filterEmptyProperty('value');
    }
}