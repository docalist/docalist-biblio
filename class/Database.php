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

use Docalist\Biblio\Reference;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Repository\PostTypeRepository;
use Docalist\Biblio\Pages\ListReferences;
use Docalist\Biblio\Pages\EditReference;
use Docalist\Biblio\Pages\ImportPage;
use Docalist\Search\Indexer;
use Exception;

/**
 * Une base de données documentaire.
 */
class Database extends PostTypeRepository {
    protected static $fieldMap = [
     // 'post_author'           => '',
        'post_date'             => 'creation',
     // 'post_date_gmt'         => '',
     // 'post_content'          => '',
        'post_title'            => 'title',
     // 'post_excerpt'          => '',
        'post_status'           => 'status',
     // 'comment_status'        => '',
     // 'ping_status'           => '',
        'post_password'         => 'password',
        'post_name'             => 'ref',
     // 'to_ping'               => '',
     // 'pinged'                => '',
        'post_modified'         => 'lastupdate',
     // 'post_modified_gmt'     => '',
     // 'post_content_filtered' => '',
        'post_parent'           => 'parent',
     // 'guid'                  => '',
     // 'menu_order'            => '',
        'post_type'             => 'posttype',
     // 'post_mime_type'        => 'type',
     // 'comment_count'         => '',
    ];

    /**
     *
     * @var DatabaseSettings
     */
    protected $settings;

    /**
     * Crée une nouvelle base de données documentaire.
     *
     * @param DatabaseSettings $settings Paramètres de la base.
     */
    public function __construct(DatabaseSettings $settings) {
        // Construit le dépôt
        parent::__construct($settings->postType(), 'Docalist\Biblio\Reference');

        // Stocke nos paramètres
        $this->settings = $settings;

        // Crée le custom post type WordPress
        $this->registerPostType();

        // Installe les hooks Docalist Search
        $this->docalistSearchHooks();

        // Déclare nos facettes
        $this->docalistSearchFacets();

        // Comme on stocke les données dans post_excerpt, on doit garantir qu'il n'est jamais modifié (autosave, heartbeat, etc.)
        global $pagenow;
        if (
            $pagenow === 'admin-ajax.php'
            // && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
            && isset($_POST['data']['wp_autosave']['post_type'])
            && $_POST['data']['wp_autosave']['post_type'] === $this->postType
        ) {
            add_filter('wp_insert_post_data', function(array $data) {
                unset($data['post_excerpt']);
                unset($data['post_name']);

                return $data;
            }, 999); // EditReference a également un filtre wp_insert_post_data avec ne priorité supérieure. Les priorités doivent rester synchro.
        }

        // Crée la page "Liste des références"
        add_action('admin_init', function () {
            /*
                Remarque : on utilise admin_init car admin_menu n'est pas
                appellé pour une requête ajax. Dans ce cas, quand on fait un
                "quick edit" les colonnes custom ne sont pas affichées car
                ListReferences n'a pas été créée.
            */
            new ListReferences($this);
        });

        // Crée les pages "Formulaire de saisie" et "Gestion de la base"
        add_action('admin_menu', function () {
            new EditReference($this);
            new ImportPage($this);
        });

        add_filter('get_the_excerpt', function($content) {
            global $post;

            // Vérifie que c'est une de nos notices
            if ($post->post_type !== $this->postType) {
                return $content;
            }

            // Charge la notice en mode "affichage court"
            $ref = $this->load($post->ID, 'excerpt');

            // Formatte la notice
            return $ref->format();
        }, 10);

        add_filter('the_content', function($content) {
            global $post;

            // Vérifie que c'est une de nos notices
            if ($post->post_type !== $this->postType) {
                return $content;
            }

            // Charge la notice en mode "affichage long"
            $ref = $this->load($post->ID, is_archive() ? 'excerpt' : 'content');

            // Formatte la notice
            return $ref->format();
        });
    }

    /**
     * @return Reference
     */
    public function load($id, $context = null) {
        // Charge les données brutes de la notice
        $data = $this->loadRaw($id);

        // Récupère le type de la notice
        if (isset($data['type'])) {
            $type = $data['type'];
        }

        // Si la notice n'a pas de type (erreur interne), impossible d'utiliser un contexte
        else {
            if ($context === 'edit') {
                add_action('admin_notices', function() {
                    printf('<div class="error"><p>%s %s</p></div>',
                        __('Aucun type de notice indiqué dans cette référence.', 'docalist-biblio'),
                        __('Chargement de la grille par défaut.', 'docalist-biblio')
                    );
                });
            }
            $context = null;
        }

        // Détermine le schéma à utiliser en fonction du contexte demandé
        if (is_null($context)) {
            $schema = null; // reference brute sans schéma personnalisé
        } else {
            if (! isset($this->settings->types[$type])) {
                // erreur : on a une notice dont le type ne figure pas dans les settings de la base
                $msg = __('Cette référence a un type de notice (%s) qui ne figure pas dans la base.', 'docalist-biblio');
                $msg = sprintf($msg, $type);
                if ($context === 'edit') {
                    add_action('admin_notices', function() use ($msg) {
                        printf('<div class="error"><p>%s %s</p></div>',
                            $msg,
                            __('Chargement de la grille par défaut.', 'docalist-biblio')
                        );
                    });
                    // shcmea = null = grille par défaut
                } else {
                    throw new \Exception($msg);
                }
            } else {
                if (! isset($this->settings->types[$type]->grids[$context])) {
                    $msg = __("La grille %s n'existe pas pour le type %s.", 'docalist-biblio');
                    throw new \Exception(sprintf($msg, $context, $type));
                } else {
                    $schema = $this->settings->types[$type]->grids[$context];
                }
            }
        }

        // Crée la référence avec le schéma demandé
        $type = $this->type; // Reference
        return new $type($data, $schema, $id);
    }

    /**
     * Affiche une notice.
     *
     * @param string $format Nom du format d'affichage (correspond au nom de la
     * vue qui sera utilisée : docalist-biblio:format/$format).
     * @param null|int|Reference $ref La notice à formatter.
     *
     * @throws Exception Si Ref invalide ou erreur dans la vue
     */
    protected function display($format = 'content', $ref = null) {
        // Aucune ref passée en paramètre
        if (is_null($ref)) {
            global $post;

            $ref = $this->load($post->ID);
        }

        // On nous a passé un numéro de référence
        elseif (is_scalar($ref)) {
            $ref = $this->load($ref);
        }

        // On nous a passé un objet Reference
        elseif ($ref instanceof Reference) {
            // ok
        }

        // Erreur
        else {
            throw new Exception('invalid ref');
        }

        // Exécute la vue
        $view = "docalist-biblio:format/$format";
        docalist('views')->display($view, ['this' => $this, 'ref' => $ref]);
    }

    /**
     * Retourne les paramètres de la base de données.
     *
     * @return DatabaseSettings
     */
    public function settings() {
        return $this->settings;
    }

    /**
     * Crée un custom post type wordpress pour la base documentaire.
     *
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */
    private function registerPostType() {
        $supports = [
         // 'title', // les notices utilisent le champ post_title natif de wp
         // 'editor',
            'author',
            'thumbnail',
         // 'excerpt',
            'revisions',
            'trackbacks',
         // 'custom-fields',
            'comments',
         // 'page-attributes',
         // 'post-formats'
        ];

        // @formatter:off
        register_post_type($this->postType(), array(
            'labels' => $this->postTypeLabels(),
            'public' => true, // cf. remarque ci-dessous
            'show_ui'              => true,
            'show_in_menu'         => true,
            'show_in_nav_menus'    => true,
            'show_in_admin_bar'    => true,

            'rewrite' => array(
                'slug' => $this->settings->slug(),
                'with_front' => false,
            ),
            'hierarchical' => false, // wp inutilisable si on met à true (cache de la hiérarchie ?)
            'capability_type' => 'post',
            'supports' => $supports,
            'has_archive' => true,
            'show_in_nav_menus' => false,
            'delete_with_user' => false,
        ));
        // @formatter:on

        /*
         * remarque :
         * on met public à false pour empêcher wp de générer un "permalink sample"
         * (cf edit-form-advanced, lignes 450 et suivantes).
         * du coup on met tous les autres show_xx à true
         * Update : en fait c'est génant de mettre à false car dans ce cas, on
         * n'a plus les liens "afficher la notice" quand on est dans le back-office.
         */
    }

    /**
     * Retourne les libellés à utiliser pour la base documentaire.
     *
     * @return string[]
     *
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */
    private function postTypeLabels() {
        $label = $this->settings->label();

        // translators: une notice bibliographique unique
        $singular = __('Notice %s', 'docalist-biblio');
        $singular = sprintf($singular, $label);

        // translators: une liste de notices bibliographiques
        $all = __('Liste des notices', 'docalist-biblio');

        // translators: créer une notice
        $new = __('Créer une notice', 'docalist-biblio');

        // translators: modifier une notice
        $edit = __('Modifier', 'docalist-biblio');

        // translators: afficher une notice
        $view = __('Afficher', 'docalist-biblio');

        // translators: rechercher des notices
        $search = __('Rechercher', 'docalist-biblio');

        // translators: aucune notice trouvée
        $notfound = __('Aucune notice trouvée dans la base %s.', 'docalist-biblio');
        $notfound = sprintf($notfound, $label);

        // @formatter:off
        return array(
            'name' => $label,
            'singular_name' => $singular,
            'menu_name' => $label,
            'all_items' => $all,
            'add_new' => $new,
            'add_new_item' => $new,
            'edit_item' => $edit,
            'new_item' => $new,
            'view_item' => $view,
            'items_archive' => $all,
            'search_items' => $search,
            'not_found' => $notfound,
            'not_found_in_trash' => $notfound,
            'name_admin_bar' => $singular,
        );
        // @formatter:on
    }

    /**
     * Installe les filtres et les actions qui vont permettre à Docalist Search
     * d'indexer les données de cette base.
     */
    protected function docalistSearchHooks() {
        $type = $this->postType();

        // Signale à docalist-search que cette base est indexable
        add_filter('docalist_search_get_types', function ($types) use ($type) {
            $types[$type] = $this->settings->label();

            return $types;
        });

        // Settings de l'index Elastic Search
        add_filter("docalist_search_get_{$type}_settings", function ($settings) {
            $ours = require __DIR__ . '/../mappings/dclref-index-settings.php';
            $settings = array_merge_recursive($settings, $ours);

            return $settings; // @todo
        });

        // Mappings
        add_filter("docalist_search_get_{$type}_mappings", function ($mappings) {
            return Reference::mappings();
        });

        // Reindexation
        add_action("docalist_search_reindex_{$type}", function(Indexer $indexer){
            $this->reindex($indexer);
        });
    }

    /**
     * Transforme une notice de la base en document Docalist Search.
     *
     * @return array
     */
    public function map(array $ref) {
/*
        $ref=array (
            'ref' => 1,
            'creation' => array (
                'date' => '20000127',
                'by' => 'IRTS-Ile-de-France-Montrouge-Neuilly sur Marne',
            ),
            'owner' => array ('IRTS-Ile-de-France'),
            'type' => 'Article',
            'title' => 'L\'avenir du social à l\'aube du XXIème siècle',
            'journal' => 'Actualités sociales hebdomadaires (ASH)',
            'issue' => 'n° 2149',
            'date' => '20000114',
            'pagination' => '21-25',
            'othertitle' =>  array (
                array (
                  'type' => 'dossier',
                  'title' => 'Le social historique',
                ),
                array (
                  'type' => 'hs',
                  'title' =>  'titre du hors série',
                ),
            ),
            'topic' =>  array (
                array (
                  'type' => 'period',
                  'term' => array ('XXIEME SIECLE'),
                ),
                array (
                  'type' => 'prisme',
                  'term' =>  array('TRAVAIL SOCIAL', 'TRAVAILLEUR SOCIAL', 'ASSOCIATION LOI 1901'),
                ),
            ),
            'language' => array ('fre'),
        );
*/

//         if (isset($ref['title']) && isset($ref['language'])) {
//             $ref['title' . $ref['language']] = $ref['title'];
//         }
//         if (isset($ref['othertitle'])) {
//             var_dump($ref);
//             die();
//             foreach($ref['othertitle'] as $title) {

//             }
//         }
/*
        echo 'AVANT<pre>', var_export($ref,true), '</pre>';
        $this->degroup($ref, 'othertitle', 'type', 'title');
        $this->degroup($ref, 'abstract', 'language', 'content');
        $this->degroup($ref, 'topic', 'type', 'term');
        // $this->degroup($ref, 'note', 'type', 'content');
        echo 'APRES<pre>', var_export($ref,true), '</pre>';
*/
/*
        if (isset($ref['author'])) {
            foreach($ref['author'] as &$author) {
                $aut = isset($author['name']) ? $author['name'] : '';
                $aut .= '¤';
                isset($author['firstname']) && $aut .= $author['firstname'];

                $author = $aut;
            }
        }
*/
        $this->concat($ref, 'author', 'name', 'firstname');
        $this->concat($ref, 'organisation', 'name', 'city', 'country');
        $this->concat($ref, 'othertitle', 'title');
        $this->concat($ref, 'date', 'date');
        $this->concat($ref, 'translation', 'title');
        unset($ref['pagination']);
        unset($ref['format']);
        $this->concat($ref, 'editor', 'name', 'city', 'country');
        unset($ref['edition']);
        $this->concat($ref, 'collection', 'name');
        $this->concat($ref, 'event', 'title', 'date', 'place', 'number');
        $this->concat($ref, 'degree', 'level', 'title');
        $this->concat($ref, 'note', 'content');
        if (isset($ref['topic'])) {
            $terms = array();
            foreach($ref['topic'] as $topic) {
                isset($topic['term']) && $terms += $topic['term'];
            }
            $ref['topic'] = $terms;
        }
        $this->concat($ref, 'link', 'url');

        unset($ref['statusdate']);
        unset($ref['imported']);
        unset($ref['todo']);
// var_dump($ref);
// die();
        return $ref; // @todo à affiner
    }

    protected function concat(& $ref, $field, $subfield1 /*...*/) {
        // Si le champ est vide, rien à faire
        if (! isset($ref[$field])) {
            return;
        }

        $args = func_get_args();
        $field = & $ref[$field];

        // Champ structuré non répétable (exemple : event)
        if (is_string(key($field))) {
            $result = '';
            for ($i = 2 ; $i < count($args) ; $i++) {
                $i > 2 && $result .= '¤';
                $result .= isset($field[$args[$i]]) ? $field[$args[$i]] : '';
            }
            $field = $result;

            return;
        }

        // Champ structuré répétable
        foreach($field as & $item) {
            $result = '';
            for ($i = 2 ; $i < count($args) ; $i++) {
                $i > 2 && $result .= '¤';
                $result .= isset($item[$args[$i]]) ? $item[$args[$i]] : '';
            }
            $item = $result;
        }
    }

    /**
     * Réindexe toutes les notices de la base dans Docalist Search.
     *
     * @param Indexer $indexer L'indexeur docalist-search à utiliser.
     */
    protected function reindex(Indexer $indexer) {
        global $wpdb;

        // Prépare la requête utilisée pour charger les posts par lots de $limit
        $type = $this->postType();
        $offset = 0;
        $limit = 1000;
        $sql = "SELECT ID FROM $wpdb->posts
                WHERE post_type = %s AND post_status = 'publish'
                ORDER BY ID DESC
                LIMIT %d OFFSET %d";

        // Tant qu'on gagne, on joue
        for (;;) {
            // Génère (et prepare) la requête pour ce lot
            $query = $wpdb->prepare($sql, $type, $limit, $offset);

            // $output == OBJECT est le plus efficace, pas de recopie
            $posts = $wpdb->get_results($query);

            // Si le lot est vide, c'est qu'on a terminé
            if (empty($posts)) {
                break;
            }

            // Indexe tous les posts de ce lot
            foreach($posts as $post) {
                $ref = $this->load($post->ID);
                $indexer->index($type, $post->ID, $ref->map());
            }

            // Passe au lot suivant
            $offset += count($posts);

            // La ligne (commentée) suivante est pratique pour les tests
          if ($offset >= 1000) break;
        }
    }

    /**
     * Retourne le libellé de la base
     *
     * @return string
     */
    public function label() {
        return $this->settings->label();
    }

    /**
     * Rendue publique car EditReference::save() en a besoin.
     * @see \Docalist\Repository\PostTypeRepository::encode()
     */
    public function encode(array $data) {
        return parent::encode($data);
    }

    /**
     * Rendue publique car EditReference::save() en a besoin.
     * @see \Docalist\Repository\PostTypeRepository::decode()
     */
    public function decode($post, $id) {
        $data = parent::decode($post, $id);
//         if (isset($data['ref']) && is_string($data['ref'])) {
//             if ($data['ref'] === '') {
//                 unset($data['ref']);
//             } else {
//                 $data['ref'] = (int) $data['ref'];
//                 if ($data['ref'] === 0) {
//                     throw new \Exception("ref non int pour notice $id");
//                 }
//             }
//         }
        return $data;
    }

    /**
     * Indique à Docalist Search les facettes disponibles pour une notice
     * documentaire.
     */
    protected function docalistSearchFacets() {
        static $done = false;

        // Evite de le faire pour chaque base, une fois ça suffit
        if ($done) {
            return;
        }
        $done = true;

        add_filter('docalist_search_get_facets', function($facets) {
            $facets += array(
                'ref.status' => array(
                    'label' => __('Statut', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'status.filter',
                    )
                ),
                'ref.type' => array(
                    'label' => __('Type de document', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'type.filter',
                        // 'order' => 'term',
                    )
                ),
                'ref.genre' => array(
                    'label' => __('Genre de document', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'genre.filter',
                    )
                ),
                'ref.media' => array(
                    'label' => __('Support de document', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'media.filter',
                    )
                ),
                'ref.author' => array(
                    'label' => __('Auteur', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'author.filter',
                        'exclude' => array('et al.¤'),
                    )
                ),
                'ref.organisation' => array(
                    'label' => __('Organisme', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'organisation.filter',
                    )
                ),
                'ref.date' => array(
                    'label' => __('Année du document', 'docalist-biblio'),
                    'type' => 'date_histogram',
                    'facet' => array(
                        'field' => 'date',
                        'interval' => 'year'
                    )
                ),
                'ref.journal' => array(
                    'label' => __('Revue', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'journal.filter',
                    )
                ),
                'ref.editor' => array(
                    'label' => __('Editeur', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'editor.filter',
                    )
                ),
                'ref.event' => array(
                    'label' => __('Evénement', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'event.filter',
                    )
                ),
                'ref.degree' => array(
                    'label' => __('Diplôme', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'degree.filter',
                    )
                ),
                /*//@todo : pas trouvé comment
                'ref.abstract' => array(
                    'label' => __('Résumé', 'docalist-biblio'),
                    'type' => 'range',
                    'facet' => array(
                        'abstract' => array(
                            array('from' => '*', 'to' => '*')

                        ),
                    )
                ),
                */
                'ref.topic' => array(
                    'label' => __('Mot-clé', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'topic.filter',
                        'size'  => 10,
                        //                    'order' => 'term',
                    )
                ),
                'ref.owner' => array(
                    'label' => __('Producteur de la notice', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'owner.filter',
                    )
                ),

                'ref.creation' => array(
                    'label' => __('Année de création de la notice', 'docalist-biblio'),
                    'type' => 'date_histogram',
                    'facet' => array(
                        'field' => 'creation',
                        'interval' => 'year'
                    )
                ),

                'ref.lastupdate' => array(
                    'label' => __('Année de mise à jour de la notice', 'docalist-biblio'),
                    'type' => 'date_histogram',
                    'facet' => array(
                        'field' => 'lastupdate',
                        'interval' => 'year'
                    )
                ),

                // creation
                // lastupdate
                'ref.error' => array(
                    //'state' => 'closed',
                    'label' => __('Erreurs détectées', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'error.filter',
                    )
                ),
            );

            return $facets;
        });
    }
}