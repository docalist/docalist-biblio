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
        // Récupère le post_type de cette base
        $type = $settings->postType();

        // Construit le dépôt
        parent::__construct($type, 'Docalist\Biblio\Reference');

        // Stocke nos paramètres
        $this->settings = $settings;

        // Crée le custom post type WordPress
        $this->registerPostType();

        // Indique à docalist-search que cette base est indexable
        add_filter('docalist_search_get_types', function (array $types) {
            $types[$this->settings->postType()] = $this->settings->label();

            return $types;
        });

        // Retourne l'indexeur à utiliser pour indexer les notices de cette base
        add_filter("docalist_search_get_{$type}_indexer", function(TypeIndexer $indexer = null) {
            return new DatabaseIndexer($this);
        });

        // Retourne le filtre standard de recherche pour cette base
        add_filter("docalist_search_get_{$type}_filter", function($filter, $type) {
            return docalist('docalist-search-engine')->defaultFilter($type);
        }, 10, 2);

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
        $type = $this->postType();

        // Compatibilité avec les bases antérieures (à supprimer une fois que le .net sera à jour)
        !isset($this->settings->icon)       && $this->settings->icon = 'dashicons-list-view';
        !isset($this->settings->thumbnail)  && $this->settings->thumbnail = true;
        !isset($this->settings->revisions)  && $this->settings->revisions = true;
        !isset($this->settings->comments)   && $this->settings->revisions = false;

        // Détermine les fonctionnalités qu'il faut activer
        $supports = ['author'];
        $this->settings->thumbnail() && $supports[] = 'thumbnail';
        $this->settings->revisions() && $supports[] = 'revisions';
        $this->settings->comments()  && $supports[] = 'comments'; // + 'trackbacks'

        register_post_type($type, [
            'labels'                => $this->postTypeLabels(),
            'description'           => $this->settings->description(),
            'public'                => true,  //
            'hierarchical'          => false, // WP est inutilisable si on met à true (cache de la hiérarchie)
            'exclude_from_search'   => true,  // Inutile que WP recherche avec du like dans nos milliers de notices
            'publicly_queryable'    => true,  // Permet d'avoir des query dclrefbase=xxx
            'show_ui'               => true,  // Laisse WP générer l'interface
            'show_in_menu'          => true,  // Afficher dans le menu wordpress
            'show_in_nav_menus'     => false, // Gestionnaire de menus inutilisable si true : charge tout
            'show_in_admin_bar'     => true,  // Afficher dans la barre d'outils admin
            'menu_position'         => 20,    // En dessous de "Pages", avant "commentaires"
            'menu_icon'             => $this->settings->icon(),
            'capability_type'       => ["{$type}_reference", "{$type}_references"], // Inutile car on définit 'capabilities', mais évite que wp_front dise : "Uses 'Posts' capabilities. Upgrade to Pro"
            'capabilities'          => $this->settings->capabilities(),
            'map_meta_cap'          => true,  // Doit être à true pour que WP traduise correctement nos droits
            'supports'              => $supports,
            'register_meta_box_cb'  => null,
            'taxonomies'            => [],    // Aucune pour le moment
            'has_archive'           => false, // On gère nous même la page d'accueil
            'rewrite'               => false, // On gère nous-mêmes les rewrite rules (cf. ci-dessous)
            'query_var'             => true,  // Laisse WP créer la QV dclrefbase=xxx
            'can_export'            => true,  // A tester, est-ce que l'export standard de WP arrive à exporter nos notices ?
            'delete_with_user'      => false, // On ne veut pas supprimer

            // Pour réactiver les archives :

            'has_archive'           => true,
            'rewrite' => [
                'slug' => $this->settings->slug(),
                'with_front' => false,
            ],

        ]);

        // Vérifie que le CPT est déclaré correctement
        // var_dump(get_post_type_object($type)); die();

        /*
            Crée les rewrite-rules dont on a besoin

            On ne laisse pas WordPress générer lui-même les rewrite rules car
            on veut garder la possibilité d'utiliser une page existante comme
            slug de la base. Pour cela, il faut pouvoir faire la différence
            entre "base/12" (une notice) et "base/help" (une page) mais par
            défaut, WordPress utilise une regexp trop large pour le rewrite tag
            qu'il génère (en gros : ".*"). Pour gérer ça nous-même, on indique
            à WordPress "pas de rewrite" (dans l'appel à register_post_type) et
            on crée nous-mêmes le rewrite_tag et la permastruct de notre base.
         */
        add_rewrite_tag("%$type%", '(\d+)', "$type=");
        add_permastruct( $type, $this->settings->slug() . "/%$type%", [
            'with_front' => false,
            'ep_mask' => EP_NONE,
            'paged' => false,
            'feed' => false,
            'forcomments' => false,
            'walk_dirs' => false,
            'endpoints' => false,
        ] );

        /*
            Remarque : bien qu'on indique EP_NONE, WordPress génère tout de même
            les rewrite rules liées aux endpoints (trackback, feed, comments,
            page...) Bug ?
            Actuellement, les règles générées par WordPress pour une base qui
            a "mabase" comme slug sont les suivantes :

            // Attachment sur une notice, trackback/feed/comment sur cet attachment
             1  mabase/\d+/attachment/([^/]+)/?$                                index.php?attachment=$1
             2  mabase/\d+/attachment/([^/]+)/trackback/?$                      index.php?attachment=$1&tb=1
             3  mabase/\d+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$  index.php?attachment=$1&feed=$2
             4  mabase/\d+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$       index.php?attachment=$1&feed=$2
             5  mabase/\d+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$       index.php?attachment=$1&cpage=$2

            // Trackback sur une notice
             6  mabase/(\d+)/trackback/?$                                       index.php?dclrefprisme=$1&tb=1

            // Pagination d'une notice (balise <!––nextpage––>)
             7  mabase/(\d+)(/[0-9]+)?/?$                                       index.php?dclrefprisme=$1&page=$2

             8  mabase/\d+/([^/]+)/?$                                           index.php?attachment=$1
             9  mabase/\d+/([^/]+)/trackback/?$                                 index.php?attachment=$1&tb=1
            10  mabase/\d+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$             index.php?attachment=$1&feed=$2
            11  mabase/\d+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$                  index.php?attachment=$1&feed=$2
            12  mabase/\d+/([^/]+)/comment-page-([0-9]{1,})/?$                  index.php?attachment=$1&cpage=$2

            Seule la règle 7 (sans la pagination) nous intéresse. Idéalement, on devrait uniquement avoir :
                mabase/(\d+)/?$                                                 index.php?dclrefprisme=$1
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
        // cf. wp-includes/post.php:get_post_type_labels()
        return array(
            'name' => $label,
            'singular_name' => $singular,
            'add_new' => $new,
            'add_new_item' => $new,
            'edit_item' => $edit,
            'new_item' => $new,
            'view_item' => $view,
            'search_items' => $search,
            'not_found' => $notfound,
            'not_found_in_trash' => $notfound,
            'parent_item_colon' => '', // not used
            'all_items' => $all,
            'menu_name' => $label,
            'name_admin_bar' => $singular,
        );
        // @formatter:on
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