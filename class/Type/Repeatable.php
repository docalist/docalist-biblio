<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Type;

/**
 * Type de base pour tous les champs répétables.
 */
class Repeatable extends \Docalist\Type\Collection implements BiblioField {
    use BiblioFieldTrait;

    public function map(array & $doc) {
        foreach($this->value as $item) { /* @var $item BiblioField */
            $item->map($doc);
        }
    }

    public function formatSettings() {
        $form = $this->traitFormatSettings();

        $name = $this->schema->name();

        is_null($this->schema->sep) && $this->schema->sep = ', ';

        $form->input('prefix')
            ->attribute('id', $name . '-prefix')
            ->attribute('class', 'prefix regular-text')
            ->label(__('Avant chaque article', 'docalist-biblio'))
            ->description(__('Préfixe ou code html à insérer avant chaque article.', 'docalist-biblio'));

        $form->input('sep')
            ->attribute('id', $name . '-sep')
            ->attribute('class', 'label small-text')
            ->label(__('Entre les articles', 'docalist-biblio'))
            ->description(__('Séparateur ou code html à insérer entre les articles.', 'docalist-biblio'));

        $form->input('suffix')
            ->attribute('id', $name . '-suffix')
            ->attribute('class', 'suffix regular-text')
            ->label(__('Après chaque article', 'docalist-biblio'))
            ->description(__('Suffixe ou code html à insérer après chaque article.', 'docalist-biblio'));

        $form->input('limit')
            ->attribute('id', $name . '-limit')
            ->attribute('class', 'limit small-text')
            ->label(__('Limite', 'docalist-biblio'))
            ->description(__("Permet de limiter le nombre d'articles affichés (3 : les trois premiers, -3 : les trois derniers, 0 ou vide : afficher tout).", 'docalist-biblio'));

        $form->input('ellipsis')
            ->attribute('id', $name . '-limit')
            ->attribute('class', 'limit regular-text')
            ->label(__('Ellipse', 'docalist-biblio'))
            ->description(__("Texte à afficher si la liste est tronquée (i.e. si le nombre d'articles dépasse la limite indiquée plus haut).", 'docalist-biblio'));

        return $form;
    }

    public function format() {
        $items = $this->value;
        $limit = (int) $this->schema->limit;
        if ($limit && (abs($limit) < count($items))) {
            if ($limit > 0) {
                $items = array_slice($items, 0, $limit);
            } else {
                $items = array_slice($items, $limit);
            }
            $ellipsis = $this->schema->ellipsis;
        } else {
            $ellipsis = null;
        }

        $prefix = $this->schema->prefix;
        $suffix = $this->schema->suffix;
        $sep = $this->schema->sep ?: ', ';
        if ($this->schema->explode) {
            $result = [];
            foreach($items as & $item) { /* @var $item BiblioField */
                list($label, $content) = $item->format($this);
                empty($label) && $label = $this->schema()->label;
                $content = $prefix . $content . $suffix;
                if (isset($result[$label])) {
                    $result[$label] .= $sep . $content;
                } else {
                    $result[$label] = $content;
                }
            }
            !is_null($ellipsis) && $result[''] = [$ellipsis];

            return $result;
        }

        foreach($items as & $item) { /* @var $item BiblioField */
            $item = $prefix . $item->format($this) . $suffix;
        }
        $items = implode($sep, $items);
        !is_null($ellipsis) && $items .= $ellipsis;
        return $items;

    }
}