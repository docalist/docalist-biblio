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

use Docalist\AbstractPlugin;
use Docalist\Table\TableManager;
use Docalist\Table\TableInfo;
use Docalist\Biblio\Entity\Reference;

/**
 * Plugin de gestion de notices bibliographiques.
 */
class Plugin extends AbstractPlugin {

    /**
     * La configuration du plugin.
     *
     * @var Settings
     */
    protected $settings;

    /**
     * La liste des bases
     *
     * @var Database[]
     */
    protected $databases;

    /**
     * Les taxonomies créées par ce plugin
     *
     * @var Taxonomies
     */
    protected $taxonomies;

    public function register() {
        // Charge la configuration du plugin
        $this->settings = new Settings('docalist-biblio');

        // Crée les bases de données définies par l'utilisateur
        $this->databases = array();
        foreach ($this->settings->databases as $settings) {
            /* @var $settings DatabaseSettings */
            $database = new Database($settings);
            $this->databases[$database->postType()] = $database;
        }

        // Crée les taxonomies
        $this->registerTaxonomies();

        // Enregistre les types de référence pré-définis
        add_filter('docalist_biblio_get_types', function(array $types) {
            return $types + [
                'article'       => 'Docalist\Biblio\Type\Article',
                'Article'       => 'Docalist\Biblio\Type\Article', // TODO: à enlever, type incorrect dans la base prisme actuelle
                'book'          => 'Docalist\Biblio\Type\Book',
                'chapter'       => 'Docalist\Biblio\Type\Chapter',
                'degree'        => 'Docalist\Biblio\Type\Degree',
                'issue'         => 'Docalist\Biblio\Type\Issue',
                'legislation'   => 'Docalist\Biblio\Type\Legislation',
                'meeting'       => 'Docalist\Biblio\Type\Meeting',
                'periodical'    => 'Docalist\Biblio\Type\Periodical',
                'report'        => 'Docalist\Biblio\Type\Report',
                'website'       => 'Docalist\Biblio\Type\WebSite',
            ];
        });

        // Enregistre les tables prédéfinies
        add_action('docalist_register_tables', array($this, 'registerTables'));

        // Back office
        add_action('admin_menu', function () {
            // Page "Gestion des bases"
            new AdminDatabases($this->settings);

            // Bases de données
            foreach($this->databases as $database) {
                new ListReferences($database);
                new EditReference($database);

                new ImportPage($database);
            }
        });

        // Nos filtres
        add_filter('docalist_biblio_get_reference', array($this, 'getReference'), 10, 1);

        add_filter('get_the_excerpt', function($content) {
            global $post;

            // Récupère le type du post en cours
            $type = $post->post_type;

            // Vérifie que c'est une notices
            if (! isset($this->databases[$type])) {
                return $content;
            }

            // Construit un extrait de la notice
            $excerpt = 'un extrait de ma notice ' . $type . ' ' . $post->ID;

            return $excerpt;
        }, 11);

    }

    /**
     * Retourne l'objet référence dont l'id est passé en paramètre.
     *
     * Implémentation du filtre 'docalist_biblio_get_reference'.
     *
     * @param string $id
     * @return Reference
     *
     * @throws Exception
     */
    public function getReference($id = null) {
        is_null($id) && $id = get_the_ID();
        $type = get_post_type($id);

        if (! isset($this->databases[$type])) {
            throw new Exception("Ce n'est pas une Reference"); // @todo
        }

        return $this->databases[$type]->load($id);
    }

    /**
     * Déclare dans WordPress les taxonomies utilisées.
     */
    protected function registerTaxonomies() {
        // Paramètres communs à toutes les taxonomies
        // @formatter:off
        $args = array(
            'hierarchical' => false,
            'show_ui' => true,
            'query_var' => false,
            'rewrite' => false,
        );
        // @formatter:on

        // Codes pays
        $args['label'] = __('Codes pays', 'docalist-biblio');
        register_taxonomy('dclcountry', array(), $args);

        // Codes langues
        $args['label'] = __('Codes langues', 'docalist-biblio');
        register_taxonomy('dcllanguage', array(), $args);

        // Types de documents
        $args['label'] = __('Types de documents', 'docalist-biblio');
        register_taxonomy('dclreftype', array(), $args);

        // Genres de documents
        $args['label'] = __('Genres de documents', 'docalist-biblio');
        register_taxonomy('dclrefgenre', array(), $args);

        // Supports de documents
        $args['label'] = __('Supports de documents', 'docalist-biblio');
        register_taxonomy('dclrefmedia', array(), $args);

        // DONE Etiquettes de rôle
        $args['label'] = __('Etiquettes de rôle', 'docalist-biblio');
        register_taxonomy('dclrefrole', array(), $args);

        // Types de titres
        $args['label'] = __('Types de titres', 'docalist-biblio');
        register_taxonomy('dclreftitle', array(), $args);

        // Types de notes
        $args['label'] = __('Types de notes', 'docalist-biblio');
        register_taxonomy('dclrefnote', array(), $args);

        // Types de liens
        $args['label'] = __('Types de liens', 'docalist-biblio');
        register_taxonomy('dclreflink', array(), $args);

        // Types de relations
        $args['label'] = __('Types de relations', 'docalist-biblio');
        register_taxonomy('dclrefrelation', array(), $args);

        // collations
        // degreee levels
        // liste des thesaurus
    }

    /**
     * Enregistre les tables prédéfinies.
     *
     * @param TableManager $tableManager
     */
    public function registerTables(TableManager $tableManager) {
        //return;
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tables'  . DIRECTORY_SEPARATOR;

        $tableManager->register(new TableInfo([
            'name' => 'roles-author',
            'path' => $dir . 'roles-author.txt',
            'label' => __('Etiquettes de rôles pour les auteurs', 'docalist-core'),
            'type' => 'roles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'roles-organisations',
            'path' => $dir . 'roles-organisations.txt',
            'label' => __('Etiquettes de rôles pour les organismes', 'docalist-core'),
            'type' => 'roles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-article',
            'path' => $dir . 'genres-article.txt',
            'label' => __("Genres d'articles", 'docalist-core'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-book',
            'path' => $dir . 'genres-book.txt',
            'label' => __("Genres de livres", 'docalist-core'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-legislation',
            'path' => $dir . 'genres-legislation.txt',
            'label' => __("Genres de législations", 'docalist-core'),
            'type' => 'genres',
            'user' => false,
        ]));
    }
}