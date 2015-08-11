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

use Docalist\Biblio\Type\MultiField;
use Docalist\Search\MappingBuilder;

/**
 * Auteur personne physique.
 *
 * @property String $name
 * @property String $firstname
 * @property String $role
 */
class Author extends MultiField {
    static protected $groupkey = 'role';

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'name' => [
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom de la personne", 'docalist-biblio'),
                ],
                'firstname' => [
                    'label' => __('Prénom', 'docalist-biblio'),
                    'description' => __("Prénom(s) ou initiales", 'docalist-biblio'),
                ],
                'role' => [
                    'label' => __('Rôle', 'docalist-biblio'),
                    'description' => __('Fonction', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function __toString() {
        $result = $this->name();

        isset($this->firstname) && $result .= ' (' . $this->firstname() . ')';
        isset($this->role) && $result .= ' / ' . $this->role();

        return $result;
    }

    /**
     * Retourne l'auteur "et al."
     * @return Author
     */
    public static function etal() {
        return new self(['name' => 'et al.']);
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('author')->string()->filter()->suggest();
    }

    public function map(array & $document) {
        $document['author'][] = $this->name() . '¤' . $this->firstname();
    }

    protected static function initFormats() {
        self::registerFormat('f n (r)', 'Charlie Chaplin (Acteur)', function(Author $aut, Authors $parent) {
            //self::callFormat('f n', $aut, $parent);
            $t = [];
            isset($aut->firstname) && $t[] = $aut->firstname();
            isset($aut->name) && $t[] = $aut->name();
            isset($aut->role) && $t[] =  '(' . self::formatRole($parent, $aut->role()) . ')';

            return implode(' ', $t); // espace insécable
        });

        self::registerFormat('f n', 'Charlie Chaplin', function(Author $aut, Authors $parent) {
            $t = [];
            isset($aut->firstname) && $t[] = $aut->firstname();
            isset($aut->name) && $t[] = $aut->name();
            return implode(' ', $t); // espace insécable
        });

        self::registerFormat('n (f) / r', 'Chaplin (Charlie) / Acteur', function(Author $aut, Authors $parent) {
            $t = [];
            isset($aut->name) && $t[] = $aut->name();
            isset($aut->firstname) && $t[] = '(' . $aut->firstname() . ')';
            isset($aut->role) && $t[] =  '/' . self::formatRole($parent, $aut->role());

            return implode(' ', $t); // espace insécable
        });

        self::registerFormat('n (f)', 'Chaplin (Charlie) / Acteur', function(Author $aut, Authors $parent) {
            $t = [];
            isset($aut->name) && $t[] = $aut->name();
            isset($aut->firstname) && $t[] = '(' . $aut->firstname() . ')';
            return implode(' ', $t); // espace insécable
        });
    }

    protected static function formatRole(Authors $parent, $role) {
        $roles = $parent->table();

        return $roles->find('label', 'code=' . $roles->quote($role)) ?: $role;
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