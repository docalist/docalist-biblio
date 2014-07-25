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
use Exception;

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
                'description' => __("Nom de code utilisé en interne pour gérer la base de données, de 1 à 14 caractères, lettres minuscules et tiret autorisés.", 'docalist-biblio'),
            ),

            'slug' => array(
                'label' => __('Slug de la base', 'docalist-biblio'),
                'description' => __("Votre base sera accessible à l'adresse <code>http://votre-site/<b>slug</b></code> et les références auront une url de la forme <code>http://votre-site/<b>slug</b>/ref</code>. Au moins un caractère, lettres minuscules et tiret autorisés.", 'docalist-biblio'),
            ),

            'label' => array(
                'label' => __('Libellé à afficher', 'docalist-biblio'),
                'description' => __('Libellé affiché dans les menus et dans les pages du back-office.', 'docalist-biblio'),
            ),

            'description' => array(
                'label' => __('Description, notes, remarques', 'docalist-biblio'),
                'description' => __("Vous pouvez utiliser cette zone pour stocker toute information utile : historique, modifications apportées, etc.", 'docalist-biblio'),
            ),

            'types' => array(
                'type' => 'TypeSettings*',
                'key' => 'name',
                'label' => __('Types de notices gérés dans cette base', 'docalist-biblio'),
            ),

            'creation' => array(
                'label' => __('Date/heure de création de la base', 'docalist-biblio'),
                //'default' => array($this, 'now')
            ),
        );
        // @formatter:on
    }

    /**
     * Valide les propriétés de la base.
     *
     * Retourne true si tout est correct, génère une exception sinon.
     *
     * @return boolean
     *
     * @throws Exception
     */
    public function validate() {
        if (!preg_match('~^[a-z-]{1,14}$~', $this->name)) {
            throw new Exception(__("Le nom de la base est invalide.", 'docalist-biblio'));
        }

        if (! preg_match('~^[a-z-]+$~', $this->slug)) {
            throw new Exception(__('Le slug de la base est incorrect.', 'docalist-biblio'));
        }

        $this->label = strip_tags($this->label);
        empty($this->label) && $this->label = $this->name;

        return true;
    }

    public function postType() {
        return 'dclref' . $this->name;
    }

    /**
     * Retourne l'url de la page d'accueil de la base.
     *
     * @return string
     */
    public function url() {
        return get_post_type_archive_link($this->postType());
    }

    private function now() {
        return new DateTime;
    }
}