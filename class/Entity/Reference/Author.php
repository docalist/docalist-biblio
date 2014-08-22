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
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Type\Object;
use Docalist\Type\String;

/**
 * Auteur personne physique.
 *
 * @property String $name
 * @property String $firstname
 * @property String $role
 */
class Author extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
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
}