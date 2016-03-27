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

/**
 * Editeur
 *
 * @property Docalist\Type\Text $name
 * @property Docalist\Type\Text $city
 * @property Docalist\Type\TableEntry $country
 * @property Docalist\Type\TableEntry $role
 */
class Editor extends MultiField {
    static public function loadSchema() {
        // @formatter:off
        return [
            'category-field' => 'role', // voir si ça marche, enlever getCategoryField() si c'est le cas
            'fields' => [
                'name' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom de l'éditeur", 'docalist-biblio'),
                ],
                'city' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Ville', 'docalist-biblio'),
                    'description' => __("Ville de l'éditeur", 'docalist-biblio'),
                ],
                'country' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'label' => __('Pays', 'docalist-biblio'),
                    'description' => __("Pays d'édition", 'docalist-biblio'),
                ],
                'role' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'thesaurus:marc21-relators_fr',
                    'label' => __('Rôle', 'docalist-biblio'),
                    'description' => __('Fonction', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    protected function getCategoryField()
    {
        return 'role';
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('editor')->text()->filter()->suggest(); // stemming sur les noms d'organismes
    }

    public function mapData(array & $document) {
        $document['editor'][] = $this->name() . '¤' . $this->city() . '¤' . $this->country();
    }
*/
    protected static function initFormats() {
        self::registerFormat('n, t, c, r', "Nom de l'éditeur, ville, pays, rôle", function(Editor $ed, Editors $parent) {
            $h = $ed->name();

            if (isset($ed->city)) {
                $h && $h .= ', ';
                $h .= $ed->city();
            }

            if (isset($ed->country)) {
                $h && $h .= ', ';
                //$h .= $ed->country();
                $h .= $parent->lookup($ed->country()); // table1
            }

            if (isset($ed->role)) {
                $h && $h .= ' / '; // espaces insécables
                $h .= $parent->lookup($ed->role(), true); // table2
            }
            return $h;
        });
        self::registerFormat('name', 'Nom uniquement', function(Editor $ed) {
            return $ed->name();
        });
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas de nom
        return $this->filterEmptyProperty('name');
    }
}