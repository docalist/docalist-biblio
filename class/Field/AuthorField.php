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
 * Champ "author" : personnes physiques ayant contribué au document catalogué.
 *
 * Ce champ répétable permet de saisir les noms et prénoms des personnes qui ont contribué à l'élaboration du
 * document catalogué.
 *
 * On peut également indiquer pour chaque personne une étiquette de rôle qui précise la nature de sa contribution
 * (traducteur, auteur de la préface, illustrations...)
 *
 * Chaque occurence du chap author comporte trois sous-champs :
 * - `name` : nom de la personne,
 * - `firstname` : prénom ou initiale de la personne,
 * - `role` : étiquette de rôle éventuelle.
 *
 * Le sous-champ role est associé à une table d'autorité qui contient les étiquettes de rôles disponibles (par
 * défaut, il s'agit de la table "marc21 relators").
 *
 * @property Text       $name       Nom de la personne.
 * @property Text       $firstname  Prénom.
 * @property TableEntry $role       Rôle.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Author extends MultiField
{
    public static function loadSchema()
    {
        return [
            'name' => 'author',
            'repeatable' => true,
            'label' => __('Auteurs', 'docalist-biblio'),
            'description' => __(
                "Personnes qui ont contribué au document (auteur, coordonnateur, réalisateur...)",
                'docalist-biblio'
            ),
            'fields' => [
                'name' => [
                    'type' => Text::class,
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom de la personne", 'docalist-biblio'),
                ],
                'firstname' => [
                    'type' => Text::class,
                    'label' => __('Prénom', 'docalist-biblio'),
                    'description' => __("Prénom(s) ou initiales", 'docalist-biblio'),
                ],
                'role' => [
                    'type' => TableEntry::class,
                    'label' => __('Rôle', 'docalist-biblio'),
                    'description' => __('Fonction', 'docalist-biblio'),
                    'table' => 'thesaurus:marc21-relators_fr',
                ]
            ]
        ];
    }

    protected function getCategoryField()
    {
        return 'role';
    }

    /**
     * Retourne l'auteur "et al."
     *
     * @return Author
     */
    public static function etal()
    {
        return new self(['name' => 'et al.']);
    }

    public function getAvailableFormats()
    {
        return [
            'f n (r)'   => __('Charlie Chaplin (Acteur)', 'docalist-biblio'),
            'f n'       => __('Charlie Chaplin', 'docalist-biblio'),
            'n (f) / r' => __('Chaplin (Charlie) / Acteur', 'docalist-biblio'),
            'n (f)'     => __('Chaplin (Charlie) / Acteur', 'docalist-biblio'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        $t = [];
        switch ($format) {
            case 'f n (r)':
                isset($this->firstname) && $t[] = $this->formatField('firstname', $options);
                isset($this->name) && $t[] = $this->formatField('name', $options);
                isset($this->role) && $t[] =  '(' . $this->formatField('role', $options) . ')';
                break;

            case 'f n':
                isset($this->firstname) && $t[] = $this->formatField('firstname', $options);
                isset($this->name) && $t[] = $this->formatField('name', $options);
                break;

            case 'n (f) / r':
                isset($this->name) && $t[] = $this->formatField('name', $options);
                isset($this->firstname) && $t[] = '(' . $this->formatField('firstname', $options) . ')';
                isset($this->role) && $t[] =  '/ ' . $this->formatField('role', $options); // insécable après le slash
                break;

            case 'n (f)':
                isset($this->name) && $t[] = $this->formatField('name', $options);
                isset($this->firstname) && $t[] = '(' . $this->formatField('firstname', $options) . ')';
                break;

            default:
                throw new InvalidArgumentException("Invalid Author format '$format'");
        }

        return implode(' ', $t); // espace insécable
    }

    public function filterEmpty($strict = true)
    {
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
