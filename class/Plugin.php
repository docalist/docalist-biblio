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

use Docalist\Table\TableManager;
use Docalist\Table\TableInfo;
use Docalist\Biblio\Entity\Reference;
use Exception;

/**
 * Plugin de gestion de notices bibliographiques.
 */
class Plugin {

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

    public function __construct() {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-biblio', false, 'docalist-biblio/languages');

        // Charge la configuration du plugin
        $this->settings = new Settings('docalist-biblio');

        add_action('init', function() {

            // Crée les bases de données définies par l'utilisateur
            $this->databases = array();
            foreach ($this->settings->databases as $settings) {
                /* @var $settings DatabaseSettings */
                $database = new Database($settings);
                $this->databases[$database->postType()] = $database;
            }
        });

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

        // Crée la page Réglages » Docalist-Biblio
        add_action('admin_menu', function () {
            new AdminDatabases($this->settings);
        });

        // Nos filtres
        add_filter('docalist_biblio_get_reference', array($this, 'getReference'), 10, 2);

        add_filter('get_the_excerpt', function($content) {
            global $post;

            // Récupère le type du post en cours
            $type = $post->post_type;

            // Vérifie que c'est une notice
            if (! isset($this->databases[$type])) {
                return $content;
            }

            // Construit un extrait de la notice
            $excerpt = 'un extrait de ma notice ' . $type . ' ' . $post->ID;

            return $excerpt;
        }, 11);

        // Liste des exporteurs définis dans ce plugin
        add_filter('docalist_biblio_get_exporters', function(array $exporters, Database $database) {
            $exporters['docalist-biblio-json'] = [
                'label' => 'Docalist - JSON',
                'description' => 'Notices en format natif Docalist, fichier au format JSON.',
                'classname' => 'Docalist\Biblio\Export\Json',
            ];

            $exporters['docalist-biblio-json-pretty'] = [
                'label' => 'Docalist - JSON (formatté)',
                'description' => 'Notices en format natif Docalist, fichier au format JSON (indenté et formatté).',
                'classname' => 'Docalist\Biblio\Export\Json',
                'settings' => [
                    'pretty' => true,
                ],
            ];

            $exporters['docalist-biblio-xml'] = [
                'label' => 'Docalist - XML',
                'description' => 'Notices en format natif Docalist, fichier au format XML.',
                'classname' => 'Docalist\Biblio\Export\Xml',
            ];

            $exporters['docalist-biblio-xml-pretty'] = [
                'label' => 'Docalist - XML (formatté)',
                'description' => 'Notices en format natif Docalist, fichier au format XML (indenté et formatté).',
                'classname' => 'Docalist\Biblio\Export\Xml',
                'settings' => [
                    'indent' => 4,
                ],
            ];

            return $exporters;
        }, 10, 2);


    }

    /**
     * Retourne l'objet référence dont l'id est passé en paramètre.
     *
     * Implémentation du filtre 'docalist_biblio_get_reference'.
     *
     * @param string $id POST_ID de la référence à charger.
     * @param boolean $raw Par défaut, retourne un objet Reference. En passant
     * raw=true, on obtient un tableau contenant les donnnées brutes.
     *
     * @return Reference
     *
     * @throws Exception
     */
    public function getReference($id = null, $raw = false) {
        is_null($id) && $id = get_the_ID();
        $type = get_post_type($id);

        if (! isset($this->databases[$type])) {
            throw new Exception("Ce n'est pas une Reference"); // @todo
        }

        return $this->databases[$type]->load($id, $raw ? false : null);
    }

    /**
     * Enregistre les tables prédéfinies.
     *
     * @param TableManager $tableManager
     */
    public function registerTables(TableManager $tableManager) {
        //return;
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tables'  . DIRECTORY_SEPARATOR;

        // Etiquettes de rôles
        $tableManager->register(new TableInfo([
            'name' => 'marc21-relators_fr',
            'path' => $dir . 'relators/marc21-relators_fr.txt',
            'label' => __('Etiquettes de rôles marc21 en français', 'docalist-biblio'),
            'format' => 'thesaurus',
            'type' => 'roles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'marc21-relators_en',
            'path' => $dir . 'relators/marc21-relators_en.txt',
            'label' => __('Etiquettes de rôles marc21 en anglais', 'docalist-biblio'),
            'format' => 'thesaurus',
            'type' => 'roles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'relators_unimarc-to-marc21',
            'path' => $dir . 'relators/relators_unimarc-to-marc21.txt',
            'label' => __('Table de conversion des codes de fonction Unimarc en relators code Marc21.', 'docalist-core'),
            'format' => 'conversion',
            'type' => 'roles',
            'user' => false,
        ]));

        // Exemple de thesaurus
        $tableManager->register(new TableInfo([
            'name' => 'thesaurus-example',
            'path' => $dir . 'thesaurus-example.txt',
            'label' => __('Exemple de table thesaurus', 'docalist-biblio'),
            'format' => 'thesaurus',
            'type' => 'thesaurus',
            'user' => false,
        ]));

        // Supports
        $tableManager->register(new TableInfo([
            'name' => 'medias',
            'path' => $dir . 'medias.txt',
            'label' => __("Supports de documents", 'docalist-biblio'),
            'format' => 'thesaurus',
            'type' => 'medias',
            'user' => false,
        ]));

        // Dates
        $tableManager->register(new TableInfo([
            'name' => 'dates',
            'path' => $dir . 'dates.txt',
            'label' => __("Types de dates", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'dates',
            'user' => false,
        ]));

        // Anciennes tables
        $tableManager->register(new TableInfo([
            'name' => 'genres-article',
            'path' => $dir . 'genres-article.txt',
            'label' => __("Table des genres pour les références de type Article", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-book',
            'path' => $dir . 'genres-book.txt',
            'label' => __("Table des genres pour les références de type Book", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-degree',
            'path' => $dir . 'genres-degree.txt',
            'label' => __("Table des genres pour les références de type Degree", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-legislation',
            'path' => $dir . 'genres-legislation.txt',
            'label' => __("Table des genres pour les références de type Legislation", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-meeting',
            'path' => $dir . 'genres-meeting.txt',
            'label' => __("Table des genres pour les références de type Meeting", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-periodical',
            'path' => $dir . 'genres-periodical.txt',
            'label' => __("Table des genres pour les références de type Periodical", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-report',
            'path' => $dir . 'genres-report.txt',
            'label' => __("Table des genres pour les références de type Report", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'genres-website',
            'path' => $dir . 'genres-website.txt',
            'label' => __("Table des genres pour les références de type WebSite", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'genres',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'titles',
            'path' => $dir . 'titles.txt',
            'label' => __("Types de titres", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'titles',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'topics',
            'path' => $dir . 'topics.php',
            'label' => __("Liste des vocabulaires disponibles pour l'indexation", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'topics',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'content',
            'path' => $dir . 'content.txt',
            'label' => __("Contenu", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'content',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'links',
            'path' => $dir . 'links.txt',
            'label' => __("Types de liens", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'links',
            'user' => false,
        ]));

        $tableManager->register(new TableInfo([
            'name' => 'relations',
            'path' => $dir . 'relations.txt',
            'label' => __("Types de relations", 'docalist-biblio'),
            'format' => 'table',
            'type' => 'relations',
            'user' => false,
        ]));
    }
}