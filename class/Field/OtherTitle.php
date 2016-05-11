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
 * Autre titre.
 *
 * @property Docalist\Type\TableEntry $type
 * @property Docalist\Type\Text $value
 *
 */
class OtherTitle extends MultiField {
    static public function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:titles',
                    'label' => __('Type de titre', 'docalist-biblio'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Autre titre', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('othertitle')->text();
    }

    public function mapData(array & $document) {
        $document['othertitle'][] = $this->__get('value')->value();
    }
*/
    public function getAvailableFormats()
    {
        return [
            'v' => __('Titre', 'docalist-biblio'),
            't : v' => __('Type : Titre', 'docalist-biblio'),
            't: v' => __('Type: Titre', 'docalist-biblio'),
            'v (t)' => __('Titre (Type)', 'docalist-biblio'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        $type = $this->formatField('type', $options);
        $title = $this->formatField('value', $options);

        switch ($format) {
            case 'v':       return $title;
            case 't : v':   return $type . ' : ' . $title; // espace insécable avant le ':'
            case 't: v':    return $type . ': ' . $title;
            case 'v (t)':   return empty($type) ? $title : $title . ' ('  . $type . ')'; // espace insécable avant '('
        }

        throw new InvalidArgumentException("Invalid OtherTitle format '$format'");
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on a que le type de titre et pas de valeur
        return $this->filterEmptyProperty('value');
    }
}