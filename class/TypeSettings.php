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

/**
 * Paramètres d'un type de référence.
 *
 * Un type est essentiellement une liste de champs
 *
 * @property String $name Identifiant du type
 * @property String $label Libellé du type
 * @property String $description Description du type
 * @property FieldSettings[] $fields Liste des champs de ce type
 */
class TypeSettings extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'name' => [
                'label' => __('Nom', 'docalist-biblio'),
                'description' => __('Doit être un type enregistré (article, book, periodical, website, etc.)', 'docalist-biblio'),
            ],

            'label' => [
                'label' => __('Libellé', 'docalist-biblio'),
                'description' => __('Libellé utilisé pour désigner ce type', 'docalist-biblio'),
            ],

            'description' => [
                'label' => __('Description', 'docalist-biblio'),
                'description' => __('Description de ce type de référence, texte d\'intro, etc.', 'docalist-biblio'),
            ],

            'fields' => [
                'type' => 'FieldSettings*',
                'key' => 'name',
                'label' => __('Grille de saisie', 'docalist-biblio'),
                'description' => __('Liste des champs et paramètres de chaque champ.', 'docalist-biblio'),
            ],
        ];
        // @formatter:on
    }
}