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
use InvalidArgumentException;

/**
 * Organisme.
 *
 * @property Docalist\Type\Text $name
 * @property Docalist\Type\Text $acronym
 * @property Docalist\Type\Text $city
 * @property Docalist\Type\TableEntry $country
 * @property Docalist\Type\TableEntry $role
 */
class Organisation extends MultiField {
    static public function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'name' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom de l'organisme", 'docalist-biblio'),
                ],
                'acronym' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Sigle', 'docalist-biblio'),
                    'description' => __("Sigle ou acronyme", 'docalist-biblio'),
                ],
                'city' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Ville', 'docalist-biblio'),
                    'description' => __('Ville du siège social', 'docalist-biblio'),
                ],
                'country' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'label' => __('Pays', 'docalist-biblio'),
                    'description' => __('Pays du siège social', 'docalist-biblio'),
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
        $mapping->addField('organisation')->text()->filter()->suggest(); // stemming sur les noms d'organismes
    }

    public function mapData(array & $document) {
        $document['organisation'][] = $this->name() . '¤' . $this->acronym() . '¤' . $this->city() . '¤' . $this->country();
    }
*/
    public function getAvailableFormats()
    {
        return [
            'n (a), t, c, r' => 'Nom (sigle), ville, pays, rôle',
            'name' => 'Nom ou sigle uniquement',
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        switch ($format) {
            case 'n (a), t, c, r':
                $name = $this->formatField('name', $options);
                $acronym = $this->formatField('acronym', $options);
                if ($name && $acronym) {
                    $h = $name . ' (' . $acronym . ')';
                } else {
                    $h = $name . $acronym; // l'un des deux est vide
                }

                if (isset($this->city)) {
                    $h && $h .= ', ';
                    $h .= $this->formatField('city', $options);
                }

                if (isset($this->country)) {
                    $h && $h .= ', ';
                    $h .= $this->formatField('country', $options);
                }

                if (isset($this->role)) {
                    $h && $h .= ' / '; // espaces insécables
                    $h .= $this->formatField('role', $options);
                }
                return $h;

            case 'name':
                return $this->formatField(isset($this->name) ? 'name' : 'acronym', $options);
        }
        throw new InvalidArgumentException("Invalid Organization format '$format'");
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