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
 * Content.
 *
 * @property Docalist\Type\TableEntry $type
 * @property Docalist\Type\LargeText $value
 */
class Content extends MultiField {
    static public function loadSchema() {
        return [
            'label' => 'Textes (Content)',
            'description' => 'Textes de présentation (Content)',
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:content',
                    'label' => __('Type', 'docalist-biblio'),
    //                 'description' => __('Nature de la note', 'docalist-biblio'),
                ],
                'value' => [
                    'type' => 'Docalist\Type\LargeText',
                    'label' => __('Contenu', 'docalist-biblio'),
                    'description' => __('Résumé, notes et remarques sur le contenu.', 'docalist-biblio'),
                    'editor' => 'textarea',
                ]
            ]
        ];
    }

    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('content')->text();
    }

    public function mapData(array & $document) {
        $document['content'][] = $this->__get('value')->value();
    }

    protected static function shortenText($text, $maxlen = 240, $ellipsis = '…') {
        if (strlen($text) > $maxlen) {
            // Tronque le texte
            $text = wp_html_excerpt($text, $maxlen, '');

            // Supprime le dernier mot (coupé) et la ponctuation de fin
            $text = preg_replace('~\W+\w*$~u', '', $text);

            // Ajoute l'ellipse
            $text .= $ellipsis;
        }

        return $text;
    }

    protected static function prepareText($content, Contents $parent) {
        if ($maxlen = $parent->schema()->maxlen()) {
            $maxlen && $content = self::shortenText($content, $maxlen);
        }

        if ($replace = $parent->schema()->newlines()) {
            $content = str_replace( ["\r\n", "\r", "\n"], $replace, $content);
        }

        return $content;
    }

    public function getAvailableFormats()
    {
        return [
            'v'     => __('Contenu', 'docalist-biblio'),
            't : v' => __('Type : Contenu', 'docalist-biblio'),
            't: v'  => __('Type: Contenu', 'docalist-biblio'),
        ];
    }

/*
    TODO : à porter vers le nouveau système / transférer dans LargeText

    public function displaySettings() {
        $name = $this->schema->name();

        $form = parent::displaySettings();

        $form->input('newlines')
            ->attribute('id', $name . '-newlines')
            ->attribute('class', 'newlines regular-text')
            ->label(__("Remplacer les CR/LF par", 'docalist-biblio'))
            ->description(__("Indiquez par quoi remplacer les retours chariots (par exemple : <code>&lt;br/&gt;</code>), ou videz le champ pour les laisser inchangés.", 'docalist-biblio'));

        $form->input('maxlen')
            ->attribute('id', $name . '-maxlen')
            ->attribute('class', 'maxlen small-text')
            ->label(__("Couper à x caractères", 'docalist-biblio'))
            ->description(__("Coupe les textes trop longs pour qu'ils ne dépassent pas la limite indiquée, ajoute une ellipse (...) si le texte a été tronqué.", 'docalist-biblio'));

        return $this->addTableSelect($form, 'content', __("Table des types de contenus", 'docalist-biblio'), true);
    }
*/
    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        $content = $this->formatField('value', $options);
        switch ($format) {
            case 'v':
                return $content;
            case 't : v':
                if (isset($this->type)) {
                    $content = $this->formatField('type', $options) . ' : '. $content; // insécable avant
                }
                return $content;
            case 't: v':
                if (isset($this->type)) {
                    $content = $this->formatField('type', $options) . ': '. $content;
                }
                return $content;
        }

        throw new InvalidArgumentException("Invalid Content format '$format'");
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a que le type et pas de contenu
        return $this->filterEmptyProperty('value');
    }
}