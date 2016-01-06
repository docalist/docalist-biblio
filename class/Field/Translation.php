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
use Docalist\MappingBuilder;
use InvalidArgumentException;

/**
 * Une traduction du titre original du document.
 *
 * @property Docalist\Type\TableEntry $language
 * @property Docalist\Type\Text $title
 */
class Translation extends MultiField {
    static public function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'language' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'label' => __('Langue', 'docalist-biblio'),
                    'table' => 'table:ISO-639-2_alpha3_EU_fr',
                ],
                'title' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Titre traduit', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    protected function getCategoryField()
    {
        return 'language';
    }

    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('translation')->text();
    }

    public function mapData(array & $document) {
        $document['translation'][] = $this->title();
    }

    public function getAvailableFormats()
    {
        return [
            't' => 'Traduction',
            'l : t' => 'langue : Traduction',
            'l: t' => 'langue : Traduction',
            't (l)' => 'Traduction (langue)',
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        $language = $this->formatField('language', $options);
        $title = $this->formatField('title', $options);

        switch ($format) {
            case 't':       return $title;
            case 'l : t':   return $language . ' : ' . $title; // espace insécable avant le ':'
            case 'l: t':    return $language . ': ' . $title;
            case 't (l)':   return empty($language) ? $title : $title . ' ('  . $language . ')'; // espace insécable avant '('
        }

        throw new InvalidArgumentException("Invalid Translation format '$format'");
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on a la langue mais pas le titre traduit
        return $this->filterEmptyProperty('title');
    }
}