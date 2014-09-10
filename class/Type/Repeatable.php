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

use Docalist\Forms\Fragment;
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

    public function displaySettings() {
        $form = $this->traitDisplaySettings();

        $name = $this->schema->name();

        is_null($this->schema->sep) && $this->schema->sep = ', ';

        // Récupère le type des éléments de cette collection
        $type = $this->schema->type();

        // Option "vue éclatée" si c'est un multi-field
        if (is_a($type, 'Docalist\Biblio\Type\MultiField', true)) {
            $groupkey = $type::groupkey();
            if (empty($groupkey)) {
                throw new \Exception("La classe $type doit surcharger la propriété \$groupkey.");
            }
            $label = $this->schema->field($groupkey)->label();
            $description = sprintf(
                __("Affiche un champ différent pour chaque %s et utilise le libellé indiqué dans la table d'autorité associée.", 'docalist-biblio'),
                lcfirst($label)
            );

            $form->checkbox('explode')
                ->label(__("Vue éclatée", 'docalist-biblio'))
                ->description($description);
        }

        // Choix du format d'affichage si c'est un objet
        if (is_a($type, 'Docalist\Biblio\Type\Object', true)) {
            $formats = $type::formats();
            is_null($this->schema->format) && $this->schema->format = $type::defaultFormat();

            $form->select('format')
                ->label(__("Format d'affichage", 'docalist-biblio'))
                ->description(__("Choisissez un format d'affichage parmi ceux proposés dans la liste.", 'docalist-biblio'))
                ->options($formats);
        }

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

    /**
     * Insére un select permettant de choisir la table d'autorité à utiliser
     * pour un champ dans le formulaire passé en paramètre.
     *
     * @param Fragment $form Formulaire dans lequel insérer le select.
     *
     * @param string $type Type des tables ('genres', 'medias', etc.)
     *
     * @param string $label Optionnel, libellé à afficher (par défaut : "table
     * d'autorité").
     *
     * @param bool $inherit Optionnel, indique s'il faut ajouter comme
     * première entrée la valeur "utiliser la table par défaut", false par
     * défaut.
     *
     * @aram bool $table2 (optionnel, internal), paramètre utilisé par
     * addTable2Select().
     *
     * @return Fragment Le formulaire modifié.
     */
    protected function addTableSelect(Fragment $form, $type, $label = '', $inherit = false, $table2 = false) {
        empty($label) && $label = __("Table d'autorité", 'docalist-biblio');

        $table = $table2 ? 'table2' : 'table';
        $inherit && $table .= 'spec';

        $select = $form->select($table)
            ->label($label)
            ->options($this->tablesOfType($type));

        if ($inherit) {
            $default = $table2 ? 'table2default' : 'tabledefault';
            $default = $this->schema()->$default;
            $default = sprintf(__("Utiliser la table par défaut (%s)", 'docalist-biblio'), $default);
            $select->firstOption($default);
            $description  = __("Par défaut, la table d'autorité définie dans la grille de base est utilisée. ", 'docalist-biblio');
            $description .= __("Vous pouvez définir une table différente spécifique à cette grille (par exemple pour avoir des libellés différents pour les entrées de la table). ", 'docalist-biblio');
            $description .= __("Mais attention, cela complique la maintenance car les différentes tables utilisées doivent rester synchonisées.", 'docalist-biblio');

        } else {
            $select->firstOption(false);
            $description = __("Choisissez la table d'autorité à utiliser parmi celles proposées dans la liste. ", 'docalist-biblio');
            $description .= __("La table indiquée ici sera utilisée comme table par défaut pour toutes les autres grilles. ", 'docalist-biblio');
        }
        $select->description($description);

        return $form;

    }

    // comme addTableSelect mais pour table2
    protected function addTable2Select(Fragment $form, $type, $label = '', $inherit = false) {
        empty($label) && $label = __("Seconde table d'autorité", 'docalist-biblio');

        return $this->addTableSelect($form, $type, $label, $inherit, true);
    }

}