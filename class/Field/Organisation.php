<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\MultiField;
use Docalist\Type\Text;
use Docalist\Type\TableEntry;
use InvalidArgumentException;

/**
 * Organisme auteur.
 *
 * Ce champ permet de saisir les organismes qui ont contribué à l'élaboration du document catalogué.
 *
 * On peut également indiquer pour chaque organisme une étiquette de rôle qui précise la nature de sa contribution
 * (organisme auteur, commanditaire, financeur...)
 *
 * Chaque organisme comporte cinq sous-champs :
 * - `name` : nom de l'organisme,
 * - `acronym` : sigle ou acronym éventuel,
 * - `city` : ville,
 * - `country` : pays,
 * - `role` : étiquette de rôle éventuelle.
 *
 * Le sous-champ `country` est associé à une table d'autorité qui contient les codes pays disponibles
 * (par défaut, il s'agit de la table des codes ISO à trois lettres).
 *
 * Le sous-champ `role` est associé à une table d'autorité qui contient les étiquettes de rôles disponibles
 * (par défaut, il s'agit de la table "marc21 relators").
 *
 * @property Text       $name       Nom de l'organisme.
 * @property Text       $acronym    Sigle ou acronyme.
 * @property Text       $city       Ville.
 * @property TableEntry $country    Pays.
 * @property TableEntry $role       Rôle.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Organisation extends MultiField
{
    public static function loadSchema()
    {
        return [
            'label' => __('Organismes', 'docalist-biblio'),
            'description' => __(
                "Organismes qui ont contribué au document (organisme auteur, commanditaire, financeur...)",
                'docalist-biblio'
            ),
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
    }

    protected function getCategoryField()
    {
        return 'role';
    }

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

    public function filterEmpty($strict = true)
    {
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
