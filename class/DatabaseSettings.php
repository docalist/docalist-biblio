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
namespace Docalist\Biblio;

use Docalist\Data\Entity\AbstractEntity;
use DateTime;

/**
 * Paramètres d'une base de données.
 *
 * Une base est essentiellement une liste de types.
 *
 * @property string $name Identifiant de la base
 * @property string $label Libellé de la base
 * @property string $slug Slug de la base de données
 * @property TypeSettings[] $types Types de notices gérés dans cette base
 * @property string $creation Date de création de la base
 */
class DatabaseSettings extends AbstractEntity {
    protected function loadSchema() {
        // @formatter:off
        return array(
            'name' => array(
                'label' => __('Nom de la base de données', 'docalist-biblio'),
                'description' => __('Détermine le post-type (le préfixe dclref est ajouté). 14 caractères maxi, [a-z-]+.', 'docalist-biblio'),
            ),

            'label' => array(
                'label' => __('Libellé de la base de données', 'docalist-biblio'),
                'description' => __('Libellé utilisé dans les menus, dans les écrans, etc.', 'docalist-biblio'),
            ),

            'description' => array(
                'label' => __('Description de la base de données', 'docalist-biblio'),
                'description' => __('Description de la base.', 'docalist-biblio'),
            ),

            'slug' => array(
                'label' => __('Slug de la base de données', 'docalist-biblio'),
                'description' => __('Détermine la page d\'accueil de la base et les urls des notices.', 'docalist-biblio'),
            ),

            'types' => array(
                'type' => 'TypeSettings*',
                'label' => __('Types de notices gérés dans cette base', 'docalist-biblio'),
            ),

            'creation' => array(
                'label' => __('Date/heure de création de la base', 'docalist-biblio'),
                //'default' => array($this, 'now')
            ),
        );
        // @formatter:on
    }

    public function postType() {
        return 'dclref' . $this->name;
    }

    private function now() {
        return new DateTime;
    }

    public function typeNames() {
        $types = array();
        foreach($this->types as $type) {
            /* @var $type TypeSettings */
            $types[$type->name] = $type->label;
        }

        return $types;
    }
}
