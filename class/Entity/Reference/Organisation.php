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

use Docalist\Biblio\Type\Object;

/**
 * Organisme.
 *
 * @property String $name
 * @property String $acronym
 * @property String $city
 * @property String $country
 * @property String $role
 */
class Organisation extends Object {
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
}