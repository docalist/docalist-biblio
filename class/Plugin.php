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
use Exception;

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

        // Enregistre les types de référence pré-définis
        add_filter('docalist_biblio_get_types', function(array $types) {
            $dir = dirname(__DIR__) . '/types';
            return $types + [
                'article'           => "$dir/article.php",
                'book'              => "$dir/book.php",
                'book-chapter'      => "$dir/book-chapter.php",
                'degree'            => "$dir/degree.php",
                'periodical-issue'  => "$dir/periodical-issue.php",
                'legislation'       => "$dir/legislation.php",
                'meeting'           => "$dir/meeting.php",
                'periodical'        => "$dir/periodical.php",
                'report'            => "$dir/report.php",
                'website'           => "$dir/website.php",
            ];
        });

        /**
         * Permet à un tiers de récupérer un objet TypeSettings pour le type
         * indiqué ou les valeurs par défaut de ce type si $instantiate est à
         * false
         *
         * @param string $type Le nom du type a retourner
         * @param bool $instantiate true (valeur par défaut) pour instancier le
         * type, false pour retourner ses valeurs par défaut.
         *
         * @return TypeSettings|array
         *
         * @throws Exception Si le type indiqué n'existe pas.
         */
        add_filter('docalist_biblio_get_type', function($type, $instantiate = true) {
            // Récupère la liste des types enregistrés
            $types = apply_filters('docalist_biblio_get_types', array());

            // Vérifie que le type demandé existe
            if (! isset($types[$type])) {
                $msg = __("Le type '%s' n'existe pas", 'docalist-biblio');
                throw new Exception(sprintf($msg, $type));
            }

            // La définition d'un type peut être faite en retournant :
            // - un tableau contenant les valeurs par défaut de ce type
            // - le path d'un fichier php qui retourne le tableau
            // - une closure qui prend en paramètre le nom du type et retourne le tableau
            $defaults = $types[$type];

            // Path
            if (is_string($defaults)) {
                $defaults = require $defaults;
            }

            // Closure
            elseif (is_callable($defaults)) {
                $defaults = $defaults($name);
            }

            return $instantiate ? new TypeSettings($defaults) : $defaults;
        }, 10, 2);

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
            'label' => __('Etiquettes de rôles pour les auteurs', 'docalist-biblio'),
            'type' => 'roles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'roles-organisation',
            'path' => $dir . 'roles-organisation.txt',
            'label' => __('Etiquettes de rôles pour les organismes', 'docalist-biblio'),
            'type' => 'roles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'roles-editor',
            'path' => $dir . 'roles-editor.txt',
            'label' => __('Etiquettes de rôles pour les éditeurs', 'docalist-biblio'),
            'type' => 'roles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-article',
            'path' => $dir . 'genres-article.txt',
            'label' => __("Table des genres pour les références de type Article", 'docalist-biblio'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-book',
            'path' => $dir . 'genres-book.txt',
            'label' => __("Table des genres pour les références de type Book", 'docalist-biblio'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-degree',
            'path' => $dir . 'genres-degree.txt',
            'label' => __("Table des genres pour les références de type Degree", 'docalist-biblio'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-legislation',
            'path' => $dir . 'genres-legislation.txt',
            'label' => __("Table des genres pour les références de type Legislation", 'docalist-biblio'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-meeting',
            'path' => $dir . 'genres-meeting.txt',
            'label' => __("Table des genres pour les références de type Meeting", 'docalist-biblio'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-periodical',
            'path' => $dir . 'genres-periodical.txt',
            'label' => __("Table des genres pour les références de type Periodical", 'docalist-biblio'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-report',
            'path' => $dir . 'genres-report.txt',
            'label' => __("Table des genres pour les références de type Report", 'docalist-biblio'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-website',
            'path' => $dir . 'genres-website.txt',
            'label' => __("Table des genres pour les références de type WebSite", 'docalist-biblio'),
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'medias',
            'path' => $dir . 'medias.txt',
            'label' => __("Supports de documents", 'docalist-biblio'),
            'type' => 'medias',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'titles',
            'path' => $dir . 'titles.txt',
            'label' => __("Types de titres", 'docalist-biblio'),
            'type' => 'titles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'notes',
            'path' => $dir . 'notes.txt',
            'label' => __("Types de notes", 'docalist-biblio'),
            'type' => 'notes',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'links',
            'path' => $dir . 'links.txt',
            'label' => __("Types de liens", 'docalist-biblio'),
            'type' => 'links',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'relations',
            'path' => $dir . 'relations.txt',
            'label' => __("Types de relations", 'docalist-biblio'),
            'type' => 'relations',
            'user' => false,
        ]));
    }
}