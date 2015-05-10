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
 * @version     $Id$
 */
namespace Docalist\Biblio\Type;

use Docalist\Table\TableInterface;
use Docalist\Table\TableManager;

use Docalist\Search\MappingBuilder;

use Docalist\Forms\Fragment;
use Docalist\Forms\Select;
use Docalist\Forms\Tag;

trait BiblioFieldTrait {
    public function baseSettings() {
        $name = $this->schema->name();
        $form = new Fragment($name);
        $form->hidden('name')
             ->attribute('class', 'name');
        $form->input('label')
             ->attribute('id', $name . '-label')
             ->attribute('class', 'label regular-text')
             ->label(__('Libellé du champ', 'docalist-biblio'))
             ->description(__("Libellé utilisé pour désigner le champ. Le libellé indiqué ici sera utilisé comme valeur par défaut dans toutes les autres grilles.", 'docalist-biblio'));
        $form->textarea('description')
             ->attribute('id', $name . '-description')
             ->attribute('class', 'description large-text')
             ->attribute('rows', 2)
             ->label(__('Description', 'docalist-biblio'))
             ->description(__("Description du champ : rôle, particularités, format, etc. Le texte indiqué ici sera utilisé comme texte d'aide par défaut dans la grille de saisie.", 'docalist-biblio'));

        return $form;
    }

    /**
     * Implémentation de base de BiblioField::editSettings().
     *
     * Retourne un formulaire qui contient les contrôles name, label et
     * description.
     *
     * @return Fragment
     */
    public function editSettings() {
        $name = $this->schema->name();

        $form = new Fragment($name);
        $form->hidden('name')
             ->attribute('class', 'name');
        $form->input('labelspec')
             ->attribute('id', $name . '-label')
             ->attribute('class', 'labelspec regular-text')
             ->attribute('placeholder', $this->schema->label ?: __('(aucun libellé)', 'docalist-biblio'))
             ->label(__('Libellé en saisie', 'docalist-biblio'))
             ->description(__("Libellé affiché en saisie. Par défaut, c'est le libellé indiqué dans la grille de base qui est utilisé mais vous pouvez indiquer un libellé différent si vous le souhaitez.", 'docalist-biblio'));
        $form->textarea('descriptionspec')
             ->attribute('id', $name . '-description')
             ->attribute('class', 'description large-text')
             ->attribute('rows', 2)
             ->attribute('placeholder', $this->schema->description ?: __('(pas de description)', 'docalist-biblio'))
             ->label(__('Aide à la saisie', 'docalist-biblio'))
             ->description(__("Texte qui sera affiché pour indiquer à l'utilisateur comment saisir le champ. Par défaut, c'est la description du champ qui figure dans la grille de base qui est utilisée.", 'docalist-biblio'));

        $default = $this->editForm()->name('default');

        if ($this->schema->repeatable()) {
            $default->label(__('Valeurs par défaut', 'docalist-biblio'));
            if (($default instanceof Select) && $default->multiple()) {
                // pas besoin du bouton "add"
            } else {
                $default->repeatable(true);
            }
        } else {
            $default->label(__('Valeur par défaut', 'docalist-biblio'));
        }

        $form->add($default);

        return $form;
    }

    /**
     * Implémentation de base de BiblioField::displaySettings().
     *
     * Retourne un formulaire qui contient les contrôles name, label,
     * before et after.
     *
     * @return Fragment
     */
    public function displaySettings() {
        return $this->traitDisplaySettings();
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

        $tables = [];
        foreach($tableManager->tables($type) as $table) { /* @var $tableInfo TableInfo */
            if ($table->format() !== 'conversion') {
                $key = $table->format() . ':' . $table->name();
                $tables[$key] = sprintf('%s (%s)', $table->label(), $table->name());
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
     * Implémentation par défaut de BiblioField::mapping().
     *
     * Par défaut, ne fait rien (champ non indexé).
     *
     * @param MappingBuilder $mapping
     */
    public function mapping(MappingBuilder $mapping) {
    }

    /**
     * Implémentation par défaut de BiblioField::map().
     *
     * Par défaut, ne fait rien.
     *
     * @param array $doc
     */
    public function map(array & $document) {
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

    /*
     * Pour implémenter displaySettings(), certains types (exemple : Repeatable)
     * ont besoin d'appeller la fonction du même nom fournie par le trait.
     * Comme on ne peut pas faire parent:xxx() pour un trait, j'incorporais le
     * trait dans Repeatable en utilisant
     * class Repeatable
     *     use BiblioFieldTrait {
     *         displaySettings as protected traitDisplaySettings;
     *     }
     * Ce qui permettait ensuite, dans formatSetting() d'appeller
     * $this->traitDisplaySettings().
     * Le problème, c'est que sur mon poste, ça fait planter php, et apache en
     * boucle (un hit ça marche, le hit suivant cela ne marche plus et ainsi
     * de suite).
     * Je soupçonne fortement apc d'être à l'origine du problème. Le fait
     * d'enlever le renommage fait lors de l'import du trait règle immédiatement
     * le problème.
     * Pour contourner ça, j'utilise le "hack" suivant :
     * - le trait dispose d'une méthode (protected) qui s'appelle
     *   traitDisplaySettings()
     * - le trait dispose d'une méthode (public) displaySettings() qui se contente
     *   d'appeller traitDisplaySettings()
     * - dans Repeatable, plus besoin de renommage : on peut surcharger
     *   displaySettings() et appeller traitDisplaySettings() si on en a besoin.
     * DM, 04/09/14
     */

    public function traitDisplaySettings() {
        $name = $this->schema->name();
        $form = new Fragment($name);
        $form->hidden('name')
             ->attribute('class', 'name');
        $form->input('labelspec')
             ->attribute('id', $name . '-label')
             ->attribute('class', 'labelspec regular-text')
             ->attribute('placeholder', $this->schema->label ?: __('(aucun libellé)', 'docalist-biblio'))
             ->label(__('Libellé', 'docalist-biblio'))
             ->description(__("Libellé affiché avant le champ. Par défaut, c'est le même que dans la grille de saisie mais vous pouvez saisir un nouveau texte si vous voulez un libellé différent.", 'docalist-biblio'));
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