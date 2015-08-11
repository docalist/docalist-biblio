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
 * Organisme.
 *
 * @property String $name
 * @property String $acronym
 * @property String $city
 * @property String $country
 * @property String $role
 */
class Organisation extends MultiField {
    static protected $groupkey = 'role';
    static protected $table2ForGroupkey = true;

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'name' => [
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom de l'organisme", 'docalist-biblio'),
                ],
                'acronym' => [
                    'label' => __('Sigle', 'docalist-biblio'),
                    'description' => __("Sigle ou acronyme", 'docalist-biblio'),
                ],
                'city' => [
                    'label' => __('Ville', 'docalist-biblio'),
                    'description' => __('Ville du siège social', 'docalist-biblio'),
                ],
                'country' => [
                    'label' => __('Pays', 'docalist-biblio'),
                    'description' => __('Pays du siège social', 'docalist-biblio'),
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
        $result = $this->name;

        if (isset($this->acronym)) {
            $result .= ' - ';
            $result .= $this->acronym();
        }

        if (isset($this->city) || isset($this->country)) {
            $result .= ' (';
            isset($this->city) && $result .= $this->city();
            if (isset($this->country)) {
                isset($this->city) && $result .= ', ';
                $result .= $this->country();
            }
            $result .= ')';
        }

        isset($this->role) && $result .= ' / ' . $this->role();

        return $result;
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('organisation')->text()->filter()->suggest(); // stemming sur les noms d'organismes
    }

    public function map(array & $document) {
        $document['organisation'][] = $this->name() . '¤' . $this->acronym() . '¤' . $this->city() . '¤' . $this->country();
    }

    protected static function initFormats() {
        self::registerFormat('n (a), t, c, r', 'Nom (sigle), ville, pays, rôle', function(Organisation $org, Organisations $parent) {
            if (isset($org->name) && isset($org->acronym)) {
                $h = $org->name() . ' (' . $org->acronym() . ')';
            } else {
                $h = $org->name() . $org->acronym(); // l'un des deux est vide
            }

            if (isset($org->city)) {
                $h && $h .= ', ';
                $h .= $org->city();
            }

            if (isset($org->country)) {
                $h && $h .= ', ';
                //$h .= $org->country();
                $h .= $parent->lookup($org->country()); // table1
            }

            if (isset($org->role)) {
                $h && $h .= ' / '; // espaces insécables
                $h .= $parent->lookup($org->role(), true); // table2
            }
            return $h;
        });

        self::registerFormat('name', 'Nom ou sigle uniquement', function(Organisation $org) {
            return $org->name() ?: $org->acronym();
        });
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a ni nom ni sigle
        return $this->filterEmptyProperty('name') && $this->filterEmptyProperty('acronym');
    }
}