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
namespace Docalist\Biblio;

use Docalist\Biblio\Reference;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Repository\PostTypeRepository;
use Docalist\Biblio\Pages\ListReferences;
use Docalist\Biblio\Pages\EditReference;
use Docalist\Biblio\Pages\ImportPage;
use Docalist\Search\Indexer;
use Docalist\Search\TypeIndexer;
use WP_Post;
use InvalidArgumentException;

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
        'post_name'             => 'slug',
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

        add_filter('the_excerpt', function($content) {
            global $post;

            // Vérifie que c'est une de nos notices
            if ($post->post_type !== $this->postType) {
                return $content;
            }

            // remarque : pour "court-circuiter" tous les filtres qui sont
            // après nous en priorité (wp_autop, etc.), on pourrait faire :
            // global $wp_filter;
            // end($wp_filter['the_excerpt']);
            // hyper dépendant du code qu'on a dans apply_filters() mais
            // cela fonctionne.

            // Charge la notice en mode "affichage court"
            $ref = $this->load($post->ID, 'excerpt');

            // Formatte la notice
            return $ref->format();
        }, 9999); // priorité très haute pour ignorer wp_autop et cie.

        add_filter('the_content', function($content) {
            global $post;

            // Vérifie que c'est une de nos notices
            if ($post->post_type !== $this->postType) {
                return $content;
            }

            // Charge la notice en mode "affichage long" (court si archive)
            $ref = $this->load($post->ID, is_archive() ? 'excerpt' : 'content');

            // Formatte la notice
            return $ref->format();
        }, 9999); // priorité très haute pour ignorer wp_autop et cie.
    }

    /**
     * @return Reference
     */
    public function load($id, $context = null) {
        // Vérifie que l'ID est correct
        $id = $this->checkId($id);

        // Charge le post wordpress
        $post = $this->loadData($id);

        // Crée la référence
        return $this->fromPost($post, $context);
    }

    /**
     *
     * @param WP_Post|array $post
     * @param string $context
     * @throws InvalidArgumentException
     * @return Reference
     */
    public function fromPost($post, $context = null) {
        // Si on nous passé un objet WP_Post, on le convertit en tableau
        if (is_object($post)) {
            $post = (array) $post;
        } elseif (! is_array($post)) {
            throw new InvalidArgumentException('Expected post (array or WP_Post)');
        }

        // Récupère l'ID du post
        $id = isset($post['ID']) ? $post['ID'] : null;

        // Construit les données brutes de la notice à partir des données du post
        $data = $this->decode($post, $id);

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
                    // schéma = null = grille par défaut
                } else {
                    throw new InvalidArgumentException($msg);
                }
            } else {
                if (! isset($this->settings->types[$type]->grids[$context])) {
                    $msg = __("La grille %s n'existe pas pour le type %s.", 'docalist-biblio');
                    throw new InvalidArgumentException(sprintf($msg, $context, $type));
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
     * @throws InvalidArgumentException Si Ref invalide ou erreur dans la vue
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
            throw new InvalidArgumentException('invalid ref');
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
            'menu_icon' => 'dashicons-feedback',
//             'menu_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNS4wLjIsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMzZweCIgaGVpZ2h0PSIzNnB4IiB2aWV3Qm94PSI2IDYgMzYgMzYiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgNiA2IDM2IDM2IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxnPg0KCQk8Zz4NCgkJCTxwYXRoIGZpbGw9IiNGRkZGRkYiIGQ9Ik0yNCw5LjYzQzE2LjA3Nyw5LjYzLDkuNjMxLDE2LjA3Nyw5LjYzMSwyNHM2LjQ0NiwxNC4zNjgsMTQuMzcsMTQuMzY4DQoJCQkJYzcuOTIzLDAsMTQuMzY4LTYuNDQ1LDE0LjM2OC0xNC4zNjhTMzEuOTI0LDkuNjMsMjQsOS42M3ogTTI3LjQ4MSwzMC45NjRsLTYuNDIxLTYuMzg1bC0wLjA0MywwLjA0NHY2LjM0MWgtMS41MDl2LTE0LjI5aDEuNTA5DQoJCQkJdjUuOTI5bDYuMDI5LTUuOTI5aDIuMDcybC03LjAwNyw2Ljg5M2w3LjQxNiw3LjM5N0gyNy40ODF6Ii8+DQoJCTwvZz4NCgk8L2c+DQoJPGc+DQoJCTxnPg0KCQkJPHBhdGggZmlsbD0iI0ZGRkZGRiIgZD0iTTI0LDZDMTQuMDU5LDYsNiwxNC4wNTksNiwyNGMwLDkuOTQsOC4wNTksMTgsMTgsMThjOS45NCwwLDE4LTguMDU5LDE4LTE4UzMzLjk0MSw2LDI0LDZ6IE0yNCw0MC4wOQ0KCQkJCWMtOC44NzMsMC0xNi4wOS03LjIxOC0xNi4wOS0xNi4wOWMwLTguODczLDcuMjE4LTE2LjA5MSwxNi4wOS0xNi4wOTFTNDAuMDksMTUuMTI3LDQwLjA5LDI0QzQwLjA5LDMyLjg3MiwzMi44NzIsNDAuMDksMjQsNDAuMDkNCgkJCQl6Ii8+DQoJCTwvZz4NCgk8L2c+DQo8L2c+DQo8L3N2Zz4NCg==',
//             'menu_icon' => 'http://www.femixsports.fr/templates/rt_oculus/images/icons/icon-home.png',
//             'menu_icon' => 'http://upload.wikimedia.org/wikipedia/commons/a/a3/Report.svg',

// http://melchoyce.github.io/dashicons/
// http://mannieschumpert.com/blog/using-wordpress-3-8-icons-custom-post-types-admin-menu/
// https://icomoon.io/
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

        add_filter("docalist_search_get_{$type}_indexer", function(TypeIndexer $indexer = null) {
            return new DatabaseIndexer($this);
        });
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
        return parent::decode($post, $id);
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