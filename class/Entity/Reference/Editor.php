<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
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

use Docalist\Data\Entity\AbstractEntity;

/**
 * Editeur
 *
 * @property string $name
 * @property string $city
 * @property string $country
 */
class Editor extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'name' => array(
                'label' => __('Nom', 'docalist-biblio'),
                'description' => __("Nom de l'éditeur", 'docalist-biblio'),
            ),
            'city' => array(
                'label' => __('Ville', 'docalist-biblio'),
                'description' => __("Ville du siège social de l'éditeur", 'docalist-biblio'),
            ),
            'country' => array(
                'label' => __('Pays', 'docalist-biblio'),
                'description' => __("Pays du siège social de l'éditeur", 'docalist-biblio'),
            ),
            'role' => array(
                'label' => __('Rôle', 'docalist-biblio'),
                'description' => __('Fonction', 'docalist-biblio'),
            )
        );
        // @formatter:on
    }

    public function __toString() {
        $result = $this->name;

        if ($this->city || $this->country) {
            $result .= ' (';
            $this->city && $result .= $this->city;
            if ($this->country) {
                $this->city && $result .= ', ';
                $result .= $this->country;
            }
            $result .= ')';
        }

        return $result;
    }
}