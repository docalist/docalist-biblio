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
use Docalist\MappingBuilder;
use InvalidArgumentException;

/**
 * Auteur personne physique.
 *
 * @property Docalist\Type\Text $name
 * @property Docalist\Type\Text $firstname
 * @property Docalist\Type\TableEntry $role
 */
class Author extends MultiField {
    static public function loadSchema() {
        return [
            'fields' => [
                'name' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom de la personne", 'docalist-biblio'),
                ],
                'firstname' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Prénom', 'docalist-biblio'),
                    'description' => __("Prénom(s) ou initiales", 'docalist-biblio'),
                ],
                'role' => [
                    'type' => 'Docalist\Type\TableEntry',
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
     * @return Author
     */
    public static function etal() {
        return new self(['name' => 'et al.']);
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('author')->literal()->filter()->suggest();
    }

    public function mapData(array & $document)
    {
        echo __METHOD__, '<br />';
        $document['author'][] = $this->name() . '¤' . $this->firstname();
    }
*/
    public function getAvailableFormats()
    {
        return [
            'f n (r)'   => 'Charlie Chaplin (Acteur)',
            'f n'       => 'Charlie Chaplin',
            'n (f) / r' => 'Chaplin (Charlie) / Acteur',
            'n (f)'     => 'Chaplin (Charlie) / Acteur',
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
                isset($this->firstname) && $t[] = $this->formatField('firstname', $options);;
                isset($this->name) && $t[] = $this->formatField('name', $options);
                break;
            case 'n (f) / r':
                isset($this->name) && $t[] = $this->formatField('name', $options);
                isset($this->firstname) && $t[] = '(' . $this->formatField('firstname', $options) . ')';
                isset($this->role) && $t[] =  '/ ' . $this->formatField('role', $options); // espace insécable après le slash
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