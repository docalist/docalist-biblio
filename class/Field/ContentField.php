<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Field;

use Docalist\Data\Field\ContentField as BaseContentField;

/**
 * Champ "content" : les contenus textuels décrivant le document catalogué.
 *
 * Ce champ répétable permet de décrire le contenu du document et d'indiquer une présentation, un résumé,
 * des remarques, une critique, etc.
 *
 * Chaque occurence du champ content comporte deux sous-champs :
 * - `type` : type de contenu,
 * - `value` : contenu.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les valeurs possibles
 * ("table:content" par défaut).
 *
 * @property TableEntry $type   Type de contenu.
 * @property Text       $value  Contenu.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ContentField extends BaseContentField
{
    public static function loadSchema(): array
    {
        return [
            'description' => __(
                'Description textuelle du document : résumé, présentation, critique, remarques...',
                'docalist-biblio'
            ),
            'fields' => [
                'type' => [
                    'table' => 'table:content',
                ],
                'value' => [
                    'label' => __('Contenu', 'docalist-biblio'),
                    'description' => __('Résumé, notes et remarques sur le contenu.', 'docalist-biblio'),
                ]
            ]
        ];
    }

/*
    TODO : à porter vers le nouveau système / transférer dans LargeText

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
        if ($maxlen = $parent->getSchema()->maxlen()) {
            $maxlen && $content = self::shortenText($content, $maxlen);
        }

        if ($replace = $parent->getSchema()->newlines()) {
            $content = str_replace( ["\r\n", "\r", "\n"], $replace, $content);
        }

        return $content;
    }

    public function displaySettings() {
        $name = $this->getSchema()->name();

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
}
