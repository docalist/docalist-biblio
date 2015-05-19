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
use Exception;

/**
 * Paramètres d'une base de données.
 *
 * Une base est essentiellement une liste de types.
 *
 * @property String $name Nom de la base de données.
 * @property Integer $homepage ID de la page d'accueil de la base de données.
 * @property String $label Libellé de la base.
 * @property String $description Description de la base.
 * @property String $stemming Stemming / analyseur par défaut.
 * @property TypeSettings[] $types Types de notices gérés dans cette base, indexés par nom.
 * @property String $creation Date de création de la base.
 * @property String $lastupdate Date de dernière modification des paramètres de la base.
 * @property String $icon Icône à utiliser pour cette base.
 * @property String $notes Notes et historique de la base.
 * @property Boolean $thumnbail Indique si les notices peuvent avoir une image à la une.
 * @property Boolean $revisions Indique si les modifications des notices font l'objet de révisions.
 * @property Boolean $comments Indique si les notices peuvent avoir des commentaires.
 */
class DatabaseSettings extends Object {
    // supprime l'ancienne propriété slug  à enlever une fois les bases .net migrées
    public function assign($value) {
        if (isset($value['slug'])) {
            unset($value['slug']);
        }
        parent::assign($value);
    }

    static protected function loadSchema() {
        return [
            'fields' => [
                'name' => [
                    'label' => __('Nom de la base', 'docalist-biblio'),
                    'description' => __("Nom de code interne de la base de données.", 'docalist-biblio'),
                ],

                'homepage' => [
                    'type' => 'int',
                    'label' => __("Page d'accueil", 'docalist-biblio'),
                    'description' => __("Page d'accueil de la base.", 'docalist-biblio'),
                ],

                'label' => [
                    'label' => __('Libellé à afficher', 'docalist-biblio'),
                    'description' => __('Libellé affiché dans les menus et dans les pages du back-office.', 'docalist-biblio'),
                ],

                'description' => [
                    'label' => __('Description', 'docalist-biblio'),
                    'description' => __("Description de la base.", 'docalist-biblio'),
                ],

                'stemming' => [
                    'label' => __('Stemming', 'docalist-biblio'),
                    'description' => __("Stemming qui sera appliqué aux champs textes des notices.", 'docalist-biblio'),
                    'default' => 'fr',
                ],

                'types' => [
                    'type' => 'TypeSettings*',
                    'key' => 'name',
                    'label' => __('Types de notices gérés dans cette base', 'docalist-biblio'),
                ],

                'creation' => [
                    'type' => 'string',
                    'label' => __('Date de création', 'docalist-biblio'),
                    'description' => __("Date/heure de création de la base.", 'docalist-biblio'),
                ],

                'lastupdate' => [
                    'type' => 'string',
                    'label' => __('Dernière modification', 'docalist-biblio'),
                    'description' => __("Date/heure de dernière modification des paramètres de la base.", 'docalist-biblio'),
                ],

                'icon' => [
                    'label' => __('Icône', 'docalist-biblio'),
                    'default' => 'dashicons-feedback',
                    'description' => __("Nom de la dashicon affichée dans les menus WordPress.", 'docalist-biblio'),
                ],

                'notes' => [
                    'label' => __('Notes et historique', 'docalist-biblio'),
                    'description' => __("Notes pour les administrateurs.", 'docalist-biblio'),
                ],

                'thumbnail' => [
                    'type' => 'boolean',
                    'label' => __('Image à la une', 'docalist-biblio'),
                    'description' => __("Les références peuvent avoir une image à la une.", 'docalist-biblio'),
                ],

                'revisions' => [
                    'type' => 'boolean',
                    'label' => __('Activer les révisions', 'docalist-biblio'),
                    'description' => __("Journaliser les modifications apportées aux références.", 'docalist-biblio'),
                ],

                'comments' => [
                    'type' => 'boolean',
                    'label' => __('Activer les commentaires', 'docalist-biblio'),
                    'description' => __("Les références peuvent avoir des commentaires.", 'docalist-biblio'),
                ],
            ]
        ];
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

        $this->label = strip_tags($this->label());
        $this->label() === '' && $this->label = $this->name;

        return true;
    }

    public function postType() {
        return 'dclref' . $this->name();
    }

    /**
     * Retourne le slug de la page d'accueil de la base.
     *
     * @return string
     */
    public function slug() {
        return get_page_uri($this->homepage());
    }

    /**
     * Retourne l'url de la page d'accueil de la base.
     *
     * @return string
     */
    public function url() {
        return get_permalink($this->homepage());
    }

    /**
     * Retourne toutes les capacités liées à la base de données.
     *
     * Utilise get_post_type_capabilities() pour laisser WordPress générer les
     * droits standards.
     *
     * @return array Retourne un tableau de capacités dans le format attendu
     * par register_post_type().
     */
    public function capabilities() {
        $cap = $this->capabilitySuffix();
        return (array) get_post_type_capabilities((object) [
            'capability_type' => [$cap, "{$cap}s"],
            'map_meta_cap' => true,
            'capabilities' => [
                // Par défaut, tout le monde peut voir les notices.
                // On crée un droit spécifique pour pouvoir avoir des bases
                // privées ou réservées à certains rôles.
                //'read_post' => "read_{$cap}",
                'read' => "read_{$cap}",

                // Par défaut, create_posts est simplement mappé vers edit_posts
                // On fait le mappage nous mêmes pour disposer d'un droit spécifique.
                //'create_posts' => "create_{$cap}s",
                /*
                 * En fait, ne marche pas : pour un CPT, on ne peut pas distinguer
                 * edit_post de create_post.
                 *
                 * C'est un bug WordPress :
                 * http://herbmiller.me/2014/09/21/wordpress-capabilities-restrict-add-new-allowing-edit/
                 * https://core.trac.wordpress.org/ticket/29714
                 * https://core.trac.wordpress.org/ticket/22895
                 *
                 * Dans user_can_access_admin_page(), wordpress teste si
                 * l'utilisateur encours a le droits d'accéder à la page du menu.
                 * Mais quand il teste la page edit.php?post_type=dbprisme
                 * il utilise $pagenow qui vaut edit.php tout court.
                 * Donc il teste si on a le droit indiqué (edit_posts) et comme
                 * ce n'est pas le cas, il nous refuse.
                 * Le bug, c'est que pagenow ne contient pas le bon truc...
                 */

                // Droit supplémentaire : importer des notices dans la base
                'import' => "import_{$cap}s"
            ],

        ]);
    }

    /**
     * Retourne le suffixe utilisé pour les droits spécifiques à cette base.
     *
     * Tous les droits spécifiques à une base contiennent le nom de cette
     * base suivi du suffixe 'ref' ou 'refs' (exemple : create_dbprisme_refs).
     * - Les "primary caps" finissent par "_refs" (au pluriel)
     * - Les "meta caps" finissent par "_ref" (au singulier)
     *
     * Dans la gestion des rôles et des droits, seuls des primary caps doivent
     * être accordées. Les meta caps sont des pseudo droits qui sont mappés en
     * fonction de la notice à laquelle ils sont appliqués.
     *
     * Le suffixe retourné par la méthode est le préfixe utilisé pour les
     * meta capabilities (au singulier donc).
     *
     * Il suffit d'ajouter un "s" pour obtenir le suffixe utilisé pour les
     * "primary capabilities".
     *
     * @return string
     */
    public function capabilitySuffix() {
        // TODO : en attendant que "dclref" soit remplacé partout par "db"
        return 'db' . $this->name() . '_ref';
        // return $this->postType() . '_ref';
    }
}