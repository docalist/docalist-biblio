<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package Docalist
 * @subpackage Biblio
 * @author Daniel Ménard <daniel.menard@laposte.net>
 * @version SVN: $Id$
 */
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Data\Entity\AbstractEntity;

/**
 * Auteur personne physique.
 *
 * @property string $name
 * @property string $firstname
 * @property string $role
 */
class Author extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'name' => array(
                'label' => __('Nom', 'docalist-biblio'),
                'description' => __("Nom de l'auteur", 'docalist-biblio'),
            ),
            'firstname' => array(
                'label' => __('Prénom', 'docalist-biblio'),
                'description' => __("Prénom de l'auteur", 'docalist-biblio'),
            ),
            'role' => array(
                'label' => __('Rôle', 'docalist-biblio'),
                'description' => __('Nature de la contribution pour un auteur secondaire', 'docalist-biblio'),
            )
        );
        // @formatter:on
    }

    public function __toString() {
        $result = $this->name;

        $this->firstname && $result .= ' (' . $this->firstname . ')';
        $this->role && $result .= ' / ' . $this->role;

        return $result;
    }
}