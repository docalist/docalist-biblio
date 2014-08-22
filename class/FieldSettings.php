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
namespace Docalist\Biblio;

use Docalist\Type\Object;
use Docalist\Type\String;
use Docalist\Type\Boolean;

use Docalist\Forms\Fragment;
use Docalist\Table\TableManager;
use Docalist\Table\TableInfo;

/**
 * Un champ d'une base.
 *
 * @property String     $name           Nom du champ
 * @property String     $label          Libellé du champ
 * @property String     $description    Description du champ
 * @property String[]   $table          Nom des tables d'autorité associées au champ
 * @property String     $format         Format d'affichage
 * @property Boolean    $split          Eclater le champ
 */
class FieldSettings extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'name' => [
                'label' => __('Nom', 'docalist-biblio'),
                'description' => __("Nom du champ", 'docalist-biblio'),
            ],
            'label' => [
                'label' => __('Libellé', 'docalist-biblio'),
                'description' => __("Libellé du champ", 'docalist-biblio'),
            ],
            'description' => [
                'label' => __('Description', 'docalist-biblio'),
                'description' => __("Description du champ", 'docalist-biblio'),
            ],
            'table' => [
                'label' => __('Table', 'docalist-biblio'),
                'description' => __('Table d\'autorité associée au champ.', 'docalist-biblio'),
            ],
            'table2' => [
                'label' => __('Table2', 'docalist-biblio'),
                'description' => __('Seconde table d\'autorité associée.', 'docalist-biblio'),
            ],
            'format' => [
                'label' => __('Format d\'affichage', 'docalist-biblio'),
                'description' => __('Pour certains champs (ex auteur), choix du format d\'affichage', 'docalist-biblio'),
            ],
            'split' => [
                'type' => 'bool',
                'label' => __('Split', 'docalist-biblio'),
                'description' => __('Pour les champs "combo" (ex. othertitle ou topics), éclater le champ en plusieurs champs ?', 'docalist-biblio'),
            ]
        ];
        // @formatter:on
    }

    /**
     *
     * @return Fragment
     */
    public function editForm() {
        $name = $id = $this->name();

        // Champs communs
        $form = new Fragment($id);
        $form->hidden('name')
             ->attribute('class', 'name');
        $form->input('label')
             ->attribute('id', $id . '-label')
             ->attribute('class', 'label regular-text');
        $form->textarea('description')
             ->attribute('id', $id . '-description')
             ->attribute('class', 'description large-text')
             ->attribute('rows', 2);

        if (0 === strncmp($name, 'group', 5)) {
            $name = 'group'; // pour que le test marche dans le switch
        }
        switch ($name) {
            case 'genre':
                $form->select('table')->options($this->tables('genres'));
                break;
            case 'media':
                $form->select('table')->options($this->tables('medias'));
                break;
            case 'author':
                $form->select('table')->options($this->tables('roles'));
                break;
            case 'organisation':
                $form->select('table')
                     ->options($this->tables('countries'))
                     ->label(__('Table des pays', 'docalist-biblio'));
                $form->select('table2')
                     ->options($this->tables('roles'))
                     ->label(__('Table des rôles', 'docalist-biblio'));
                break;
            case 'othertitle':
                $form->select('table')->options($this->tables('titles'));
                break;
            case 'translation':
            case 'language':
            case 'abstract':
                $form->select('table')->options($this->tables('languages'));
                break;
            case 'date':
                $form->select('table')->options($this->tables('dates'));
                break;
            case 'number':
                $form->select('table')->options($this->tables('numbers'));
                break;
            case 'extent':
                $form->select('table')->options($this->tables('extent'));
                break;
            case 'format':
                $form->select('table')->options($this->tables('format'));
                break;
            case 'editor':
                $form->select('table')
                     ->options($this->tables('countries'))
                     ->label(__('Table des pays', 'docalist-biblio'));
                $form->select('table2')
                     ->options($this->tables('roles'))
                     ->label(__('Table des rôles', 'docalist-biblio'));
                break;
//             case'edition': // todo
//             case'degree': // todo
//             case'topic': // todo
            case 'topic':
                $form->select('table')->options($this->tables('topics'));
                break;
            case 'content':
                $form->select('table')->options($this->tables('content'));
                break;
            case 'link':
                $form->select('table')->options($this->tables('links'));
                break;
            case 'relation':
                $form->select('table')->options($this->tables('relations'));
                break;

            // Si c'est un groupe, ajoute un bouton "supprimer ce groupe"
            case 'group':
            case'{group-number}':
                $form->button(__('Supprimer ce groupe', 'docalist-biblio'))
                     ->attribute('class', 'delete-group button right');
                break;

        }

        return $form;
    }

    protected function tables($type) {
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
}