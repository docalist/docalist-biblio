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
 * @version     $Id$
 */
namespace Docalist\Biblio\Settings;

use Docalist\Type\Object;
use Docalist\Type\String;
use Docalist\Type\Integer;
use Docalist\Schema\Schema;
use DateTime;
use Exception;

/**
 * Paramètres d'une base de données.
 *
 * Une base est essentiellement une liste de types.
 *
 * @property String $name Identifiant de la base.
 * @property String $slug Slug de la base de données.
 * @property String $label Libellé de la base.
 * @property String $description Description de la base.
 * @property String $stemming Stemming / analyseur par défaut.
 * @property TypeSettings[] $types Types de notices gérés dans cette base,
 * indexés par nom.
 * @property Integer $creation Date de création de la base.
 */
class DatabaseSettings extends Object {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'name' => [
                    'label' => __('Nom de la base de données', 'docalist-biblio'),
                    'description' => __("Nom de code utilisé en interne pour gérer la base de données, de 1 à 14 caractères, lettres minuscules, chiffres et tiret autorisés.", 'docalist-biblio'),
                ],

                'slug' => [
                    'label' => __('Slug de la base', 'docalist-biblio'),
                    'description' => __("Votre base sera accessible à l'adresse <code>http://votre-site/<b>slug</b></code> et les références auront une url de la forme <code>http://votre-site/<b>slug</b>/ref</code>. Au moins un caractère, lettres minuscules et tiret autorisés.", 'docalist-biblio'),
                ],

                'label' => [
                    'label' => __('Libellé à afficher', 'docalist-biblio'),
                    'description' => __('Libellé affiché dans les menus et dans les pages du back-office.', 'docalist-biblio'),
                ],

                'description' => [
                    'label' => __('Description, notes, remarques', 'docalist-biblio'),
                    'description' => __("Vous pouvez utiliser cette zone pour stocker toute information utile : historique, modifications apportées, etc.", 'docalist-biblio'),
                ],

                'stemming' => [
                    'label' => __('Stemming', 'docalist-biblio'),
                    'description' => __("Définit le stemming qui sera appliqué aux champs textes des notices.", 'docalist-biblio'),
                    'default' => 'fr',
                ],

                'types' => [
                    'type' => 'TypeSettings*',
                    'key' => 'name',
                    'label' => __('Types de notices gérés dans cette base', 'docalist-biblio'),
                ],

                'creation' => [
                    'type' => 'int',
                    'label' => __('Date/heure de création de la base', 'docalist-biblio'),
                ]
            ]
        ];
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
        if (!preg_match('~^[a-z][a-z0-9-]{1,13}$~', $this->name())) {
            throw new Exception(__("Le nom de la base est invalide.", 'docalist-biblio'));
        }

        if (! preg_match('~^[a-z0-9-]+$~', $this->slug())) {
            throw new Exception(__('Le slug de la base est incorrect.', 'docalist-biblio'));
        }

        $this->label = strip_tags($this->label());
        $this->label() === '' && $this->label = $this->name;

        return true;
    }

    public function postType() {
        return 'dclref' . $this->name();
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