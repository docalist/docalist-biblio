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
namespace Docalist\Biblio\Settings;

use Docalist\Type\Object;
use Docalist\Type\String;
use Docalist\Schema\Schema;

/**
 * Les paramètres d'un type au sein d'une base de données.
 *
 * Chaque type contient un nom qui indique le type de notice (article,
 * book, degree...) et des grilles (des schémas) qui sont utilisées
 * pour l'édition et l'affichage des notices de ce type.
 *
 * La grille de saisie a un nom particulier 'edit'. Toutes les autres
 * grilles sont des formats d'affichage.
 *
 * @property String $name Nom du type (article, book, degree...)
 * @property String $label Libellé.
 * @property String $description Description.
 * @property Schema[] $grids Grilles de saisie et d'affichage.
 */
class TypeSettings extends Object {
    static protected function loadSchema() {
        return [
            'fields' => [
                'name' => [ // article, book, etc.
                    'label' => __('Nom du type', 'docalist-biblio'),
                    'description' => __("Nom de code utilisé en interne pour désigner le type.", 'docalist-biblio'),
                ],

                'label' => [
                    'label' => __('Libellé du type', 'docalist-biblio'),
                    'description' => __('Libellé utilisé pour désigner ce type.', 'docalist-biblio'),
                ],

                'description' => [
                    'label' => __('Description', 'docalist-biblio'),
                    'description' => __("Description du type.", 'docalist-biblio'),
                ],

                // helpurl -> lien vers page qui décrit le type
                // droits ?

                'grids' => [
                    'type' => 'Docalist\Schema\Schema*',
                    'key' => 'name', // edit, display-full, display-short, ...
                    'label' => __('Grilles et formulaires', 'docalist-biblio'),
                    'description' => __("Grilles de saisie et d'affichage pour ce type.", 'docalist-biblio'),
]
            ]
        ];
    }
}
