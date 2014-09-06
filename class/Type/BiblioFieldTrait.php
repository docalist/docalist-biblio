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
use Docalist\Forms\Tag;
use Docalist\Table\TableManager;
use Docalist\Table\TableInterface;
use Docalist\Schema\Field;

trait BiblioFieldTrait {
    /**
     * Implémentation de base de BiblioField::settingsForm().
     *
     * Retourne un formulaire qui contient les contrôles name, label et
     * description.
     *
     * @return Fragment
     */
    public function settingsForm() {
        $name = $this->schema->name();
        $form = new Fragment($name);
        $form->hidden('name')
             ->attribute('class', 'name');
        $form->input('label')
             ->attribute('id', $name . '-label')
             ->attribute('class', 'label regular-text')
             ->label(__('Libellé', 'docalist-biblio'));
        $form->textarea('description')
             ->attribute('id', $name . '-description')
             ->attribute('class', 'description large-text')
             ->attribute('rows', 2)
             ->label(__('Description', 'docalist-biblio'));

        return $form;
    }

    /**
     * Retourne toutes les tables d'un type donné.
     * Cette méthode utilitaire sert aux champs qui utilisent ce trait pour
     * afficher un select contenant la liste des tables possibles (par exemple
     * la liste des tables de type "pays" pour le champ Organisation).
     *
     * @param string $type Le type souhaité/
     *
     * @return array Un tableau de la forme code => libellé utilisable dans un
     * select.
     */
    protected function tablesOfType($type) {
        /* @var $tableManager TableManager */
        $tableManager = docalist('table-manager');

        /* @var $tableInfo TableInfo */
        $tables = [];
        foreach($tableManager->info(null, $type) as $name => $tableInfo) {
            if ($tableInfo->format() !== 'conversion') {
                $key = $tableInfo->format() . ':' . $name;
                $tables[$key] = sprintf('%s (%s)', $tableInfo->label(), $name);
            }
        }

        return $tables;
    }

    /**
     * Génère une erreur si un champ n'a pas implémenté la méthode
     * BiblioField::editForm.
     *
     * @return Tag
     */
    public function editForm() {
        return new Tag('p', get_class($this) . '::editForm() not implemented');
    }

    /**
     * Implémentation par défaut de BiblioFIeld::map().
     *
     * Par défaut, ne fait rien.
     *
     * @param array $doc
     */
    public function map(array & $doc) {

    }

    /**
     * Ouvre la table indiquée dans le schéma.
     *
     * @param bool $table2 Par défaut, c'est la table indiquée dans la propriété
     * 'table' du schéma du champ qui est ouverte. Si vous passez true, c'est la
     * table indiquée dans la propriété 'table2' qui sera utilisée (exemple :
     * author, editor).
     *
     * @return TableInterface
     */
    public function table($table2 = false) {
        // Détermine la table à utiliser
        if ($table2) {
            $table = $this->schema()->table2spec() ?: $this->schema()->table2();
        } else {
            $table = $this->schema()->tablespec() ?: $this->schema()->table();
        }

        // Le nom de la table est de la forme "type:nom", on ne veut que le nom
        $table = explode(':', $table)[1];

        return docalist('table-manager')->get($table);
    }

    /**
     * Convertit le code indiqué en libellé en effectuant un lookup dans la
     * table associée au champ.
     *
     * @param string $code Le code recherché.
     * @param bool $table2 Optionnel, true pour utiliser la propriété 'table2'
     * du schéma plutôt que la propriété 'table'.
     *
     * @return string Retourne le libellé associé au code. Si le code ne figure
     * pas dans la table, retourne le code.
     */
    public function lookup($code, $table2 = false, $search = 'code', $return = 'label') {
        return $this->table($table2)->find($return, "$search='$code'") ?: $code;
    }

    /**
     * Implémentation par défaut de BiblioFIeld::ESmapping().
     *
     * Par défaut, ne fait rien.
     *
     * @param array $doc
     */
    public static function ESmapping(array & $mappings, Field $schema) {
    }

    /**
     * Mapping standard pour un champ texte.
     *
     * @param bool $includeInAll Indique s'il faut ajouter la clause
     * "include_in_all" au mapping (false par défaut).
     *
     * @param string $analyzer Nom de l'analyseur à utiliser.
     *
     * @return array
     */
    protected static function stdIndex($includeInAll = false, $analyzer = 'dclref-default-fr') {
        $mapping =  [
            'type' => 'string',
            'analyzer' => $analyzer,
        ];

        if ($includeInAll) {
            $mapping['include_in_all'] = true;
        }

        return $mapping;
    }

    /**
     * Mapping standard champ texte + filtre.
     *
     * @param bool $includeInAll Indique s'il faut ajouter la clause
     * "include_in_all" au mapping (false par défaut).
     *
     * @param string $analyzer Nom de l'analyseur à utiliser.
     *
     * @return array
     */
    protected static function stdIndexAndFilter($includeInAll = false, $analyzer = 'dclref-default-fr') {
        $mapping = self::stdIndex($includeInAll, $analyzer);

        $mapping['fields'] = [
            'filter' => [
                'type' => 'string',
                'index' => 'not_analyzed',
            ]
        ];

        return $mapping;
    }

    /**
     * Mapping standard champ texte + filtre + lookup.
     *
     * @param bool $includeInAll Indique s'il faut ajouter la clause
     * "include_in_all" au mapping (false par défaut).
     *
     * @param string $analyzer Nom de l'analyseur à utiliser.
     *
     * @return array
     */
    protected static function stdIndexFilterAndSuggest($includeInAll = false, $analyzer = 'dclref-default-fr') {
        $mapping = self::stdIndexAndFilter($includeInAll, $analyzer);

        $mapping['fields']['suggest'] = [
            'type' => 'completion',
            'index_analyzer' => 'suggest', // utile ?
            'search_analyzer' => 'suggest', // utile ?
        ];

        return $mapping;
    }

    /*
     * Pour implémenter formatSettings(), certains types (exemple : Repeatable)
     * ont besoin d'appeller la fonction fu même nom fournie par le trait.
     * Comme on ne peut pas faire parent:xxx() pour un trait, j'incorporais le
     * trait dans Repeatable en utilisant
     * class Repeatable
     *     use BiblioFieldTrait {
     *         formatSettings as protected traitFormatSettings;
     *     }
     * Ce qui permettait ensuite, dans formatSetting() d'appeller
     * $this->traitFormatSettings().
     * Le problème, c'est que sur mon poste, ça fait planter php, et apache en
     * boucle (un hit ça marche, le hit suivant cela ne marche plus et ainsi
     * de suite).
     * Je soupçonne fortement apc d'être à l'origine du problème. Le fait
     * d'enlever le renommage fait lors de l'import du trait règle immédiatement
     * le problème.
     * Pour contourner ça, j'utilise le "hack" suivant :
     * - le trait dispose d'une méthode (protected) qui s'appelle
     *   traitFormatSettings()
     * - le trait dispose d'une méthode (public) formatSettings() qui se contente
     *   d'appeller traitFormatSettings()
     * - dans Repeatable, plus besoin de renomage : on peut surcharger
     *   formatSettings() et appeller traitFormatSettings() si on en a besoin.
     * DM, 04/09/14
     */

    public function formatSettings() {
        return $this->traitFormatSettings();
    }

    public function traitFormatSettings() {
        $name = $this->schema->name();
        $form = new Fragment($name);
        $form->hidden('name')
             ->attribute('class', 'name');
        $form->input('label')
             ->attribute('id', $name . '-label')
             ->attribute('class', 'label regular-text')
             ->label(__('Libellé', 'docalist-biblio'))
             ->description(__('Libellé affiché avant le champ.', 'docalist-biblio'));
        $form->input('before')
             ->attribute('id', $name . '-before')
             ->attribute('class', 'before regular-text')
             ->label(__('Avant le champ', 'docalist-biblio'))
             ->description(__('Texte ou code html à insérer avant le contenu du champ.', 'docalist-biblio'));
        $form->input('after')
             ->attribute('id', $name . '-before')
             ->attribute('class', 'after regular-text')
             ->label(__('Après le champ', 'docalist-biblio'))
             ->description(__('Texte ou code html à insérer après le contenu du champ.', 'docalist-biblio'));

        return $form;
    }

    public function format() {
        return get_class($this) . '::format() not implemented';
    }
}