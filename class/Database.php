<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio;

use Docalist\Repository\PostTypeRepository;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Biblio\Pages\ListReferences;
use Docalist\Biblio\Pages\EditReference;
use Docalist\Biblio\Pages\ImportPage;
use Docalist\Search\SearchUrl;
use Docalist\Search\SearchRequest;
use WP_Post;
use WP_Query;
use InvalidArgumentException;

/**
 * Une base de données documentaire.
 */
class Database extends PostTypeRepository
{
    protected static $fieldMap = [
        'post_author' => 'createdBy',
        'post_date' => 'creation',
     // 'post_date_gmt'         => '',
     // 'post_content'          => '',
        'post_title' => 'posttitle',
     // 'post_excerpt'          => '',
        'post_status' => 'status',
     // 'comment_status'        => '',
     // 'ping_status'           => '',
        'post_password' => 'password',
        'post_name' => 'slug',
     // 'to_ping'               => '',
     // 'pinged'                => '',
        'post_modified' => 'lastupdate',
     // 'post_modified_gmt'     => '',
     // 'post_content_filtered' => '',
        'post_parent' => 'parent',
     // 'guid'                  => '',
     // 'menu_order'            => '',
        'post_type' => 'posttype',
     // 'post_mime_type'        => 'type',
     // 'comment_count'         => '',
    ];

    /**
     * @var DatabaseSettings
     */
    protected $settings;

    /**
     * Crée une nouvelle base de données documentaire.
     *
     * @param DatabaseSettings $settings Paramètres de la base.
     */
    public function __construct(DatabaseSettings $settings)
    {
        // Récupère le post_type de cette base
        $type = $settings->postType();

        // Construit le dépôt
        parent::__construct($type, 'Docalist\Biblio\Reference');

        // Stocke nos paramètres
        $this->settings = $settings;

        // Crée le custom post type WordPress
        $this->registerPostType();

        // Indique à docalist-search que cette base est indexable
        add_filter('docalist_search_get_indexers', function ($indexers) {
            $indexers[$this->settings->postType()] = new DatabaseIndexer($this);

            return $indexers;
        });

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
            add_filter('wp_insert_post_data', function (array $data) {
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

        // Pour l'excerpt, on filtre 'get_the_excerpt' car the_content() appelle get_the_content() et
        // les deux ont des filtres.
        add_filter('get_the_excerpt', function ($excerpt, WP_Post $post) {
            // Vérifie que c'est une de nos notices
            if ($post->post_type !== $this->postType) {
                return $excerpt;
            }

            // Charge la notice en mode
            $ref = $this->load($post->ID);

            // Charge la grille "format court"
            $grid = $this->settings->types[$ref->type()]->grids['excerpt'];

            // Formatte la notice
            return $ref->getFormattedValue($grid);
        }, 9999,2); // priorité très haute pour ignorer wp_autop et cie.

        // Par contre pour le content, on est obligé de filtre the_content() car il n'y a aucun
        // filtre dans get_the_content(). Donc si un thème appelle get_the_content, il n'aura rien.
        add_filter('the_content', function ($content) {
            global $post;

            // Vérifie que c'est une de nos notices
            if ($post->post_type !== $this->postType) {
                return $content;
            }

            // Charge la notice
            $ref = $this->load($post->ID);

            // Détermine la grille à utiliser : "affichage long" par défaut, "affichage court" si archive
            $grid = is_archive() ? 'excerpt' : 'content';

            // Charge la grille
            $grid = $this->settings->types[$ref->type()]->grids[$grid];

            // Formatte la notice
            return $ref->getFormattedValue($grid);
        }, 9999); // priorité très haute pour ignorer wp_autop et cie.
    }

    /**
     * @return Reference
     */
    public function load($id)
    {
        // Vérifie que l'ID est correct
        $id = $this->checkId($id);

        // Charge le post wordpress
        $post = $this->loadData($id);

        // Crée la référence
        return $this->fromPost($post);
    }

    /**
     * @param WP_Post|array $post
     * @param string $context
     * @throws InvalidArgumentException
     * @return Reference
     */
    public function fromPost($post)
    {
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
        if (!isset($data['type'])) {
            throw new InvalidArgumentException('No type found in reference');
        }
        $type = $data['type'];

        // Vérifie que ce type de notice figure dans la base
        if (! isset($this->settings->types[$type])) {
            $msg = __('Cette référence a un type de notice (%s) qui ne figure pas dans la base.', 'docalist-biblio');
            $msg = sprintf($msg, $type);
            throw new InvalidArgumentException($msg);
        }

        // Debug - vérifie que la grille 'base' existe
        if (! isset($this->settings->types[$type]->grids['base'])) {
            $msg = __("La grille de base n'existe pas pour le type %s.", 'docalist-biblio');
            throw new InvalidArgumentException(sprintf($msg, $type));
        }

        // Récupère le schéma à utiliser
        $schema = $this->settings->types[$type]->grids['base'];

        // Détermine le nom de la classe php correspondant au type de notice
        $class = $this->getClassForType($type);

        // Crée et retourne la notice
        return new $class($data, $schema, $id);
    }

    /**
     * Retourne la liste des types disponibles.
     *
     * Lors du premier appel, le filtre 'docalist_biblio_get_types' est exécuté et le résultat est stocké en cache.
     *
     * @return string[] Un tableau de la forme type => nom complet de la classe php qui gère ce type.
     */
    public static function getAvailableTypes()
    {
        static $types;

        // Initialise la liste des types disponibles lors du premier appel
        if (is_null($types)) {
            $types = apply_filters('docalist_biblio_get_types', []);
        }

        return $types;
    }

    /**
     * Retourne le nom complet de la classe PHP qui gère le type indiqué.
     *
     * @param string $type Le nom du type recherché.
     * @return string Le nom de la classe php.
     *
     * @throws InvalidArgumentException Si le type indiqué ne figure pas dans la liste retournée par
     * getAvailableTypes().
     */
    public static function getClassForType($type)
    {
        static $types;

        // Récupère la liste des types disponibles
        $types = self::getAvailableTypes();

        // Génère une exception si le type demandé n'existe pas
        if (! isset($types[$type])) {
            throw new InvalidArgumentException("Type '$type' is not available");
        }

        // Ok
        return $types[$type];
    }

    /**
     * Crée une notice du type indiqué.
     *
     * Par défaut la notice est initialisée avec les valeurs par défaut qui figurent dans le schéma
     * du type (grille 'base'). Si on indique une grille, il doit s'agit d'un formulaire de saisie
     * ('edit') et dans ce cas, ce sont les valeurs par défaut indiquées pour le formualaire qui
     * seront appliquées.
     *
     * @param string $type Nom du type de notice à créer.
     * @param array  $data Optionnel, données initiales de la notice. Si null, utilise la valeur par défaut.
     * @param string $grid Optionnel, nom du formulaire de saisie à utiliser pour initialiser la valeur par défaut.
     * N'est utilisé que si $data vaut null.
     *
     * @return Type
     *
     * @throws InvalidArgumentException
     */
    public function createReference($type, array $data = null, $grid = null)
    {
        // Remarque : valeur par défaut / données initiales de la notice
        // - Si $data a été transmis (non null), ce sont ces données qu'on va utiliser pour initialiser la notice.
        // - Si $data est null, Any va initialiser la notice avec la valeur par défaut du schéma qu'on lui passe
        // - Si un formulaire a été indiqué et que $data est null, c'est la valeur par défaut du formulaire qui
        //   sera utilisée

        // Vérifie que le type indiqué figure dans la base
        if (! isset($this->settings->types[$type])) {
            throw new InvalidArgumentException("Type '$type' does not exist in database");
        }

        // Vérifie que la grille de base (schéma) existe (debug / sanity check)
        if (! isset($this->settings->types[$type]->grids['base'])) {
            throw new InvalidArgumentException("Grid 'base' does not exist for type '$type'");
        }

        // Ok, on a le schéma
        $schema = $this->settings->types[$type]->grids['base'];

        // Si une grille a été indiquée, vérifie qu'elle existe et que c'est bien un formulaire
        if (is_null($data) && ! is_null($grid)) {
            // La grille doit exister
            if (! isset($this->settings->types[$type]->grids[$grid])) {
                throw new InvalidArgumentException("Grid '$grid' does not exist for type '$type'");
            }
            $grid = $this->settings->types[$type]->grids[$grid];

            // La grille doit être du type 'edit'
            if ($grid->gridtype() !== 'edit') {
                throw new InvalidArgumentException("Grid '$grid' is not an edit form");
            }

            // Si on n'a pas de données, on utilise la valeur par défaut du formulaire
            if (is_null($data)) {
                $data = $grid->getDefaultValue();   // peut retourner []
                empty($data) && $data = null;       // dans ce cas, utilise la valeur par défaut du schéma
            }
        }

        // Détermine le nom de la classe php correspondant au type de notice
        $class = $this->getClassForType($type);

        // Crée la notice
        $ref = new $class($data, $schema);
        $ref->type = $type;

        // Ok
        return $ref;
    }

    /**
     * Retourne les paramètres de la base de données.
     *
     * @return DatabaseSettings
     */
    public function settings()
    {
        return $this->settings;
    }

    /**
     * Retourne l'ID de la page "accueil" indiquée dans les paramètres de la
     * base.
     *
     * @return int
     */
    public function homePage()
    {
        return $this->settings->homepage();
    }

    /**
     * Retourne l'ID de la page "liste des réponses" indiquée dans les
     * paramètres de la base.
     *
     * @return int
     */
    public function searchPage()
    {
        return $this->settings->searchpage();
    }

    /**
     * Retourne l'URL de la page "liste des réponses" indiquée dans les
     * paramètres de la base.
     *
     * @return string
     */
    public function searchPageUrl()
    {
        return get_permalink($this->settings->searchpage());
    }

    /**
     * Crée un custom post type wordpress pour la base documentaire.
     *
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */
    private function registerPostType()
    {
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

        // Détermine les paramètres du custom post type
        $args = [
            'labels' => $this->postTypeLabels(),
            'description' => $this->settings->description(),
            'public' => true,  //
            'hierarchical' => false, // WP est inutilisable si on met à true (cache de la hiérarchie)
            'exclude_from_search' => true,  // Inutile que WP recherche avec du like dans nos milliers de notices
            'publicly_queryable' => true,  // Permet d'avoir des query dbbase=xxx
            'show_ui' => true,  // Laisse WP générer l'interface
            'show_in_menu' => true,  // Afficher dans le menu wordpress
            'show_in_nav_menus' => false, // Gestionnaire de menus inutilisable si true : charge tout
            'show_in_admin_bar' => true,  // Afficher dans la barre d'outils admin
         // 'menu_position'         => 20,    // En dessous de "Pages", avant "commentaires"
            'menu_icon' => $this->settings->icon(),
            'capability_type' => $this->settings->capabilitySuffix(), // Inutile car on définit 'capabilities', mais évite que wp_front dise : "Uses 'Posts' capabilities. Upgrade to Pro"
            'capabilities' => $this->settings->capabilities(),
            'map_meta_cap' => true,  // Doit être à true pour que WP traduise correctement nos droits
            'supports' => $supports,
            'register_meta_box_cb' => null,
            'taxonomies' => [],    // Aucune pour le moment
            'has_archive' => false, // On gère nous même la page d'accueil
            'rewrite' => false, // On gère nous-mêmes les rewrite rules (cf. ci-dessous)
            'query_var' => true,  // Laisse WP créer la QV dbbase=xxx
            'can_export' => true,  // A tester, est-ce que l'export standard de WP arrive à exporter nos notices ?
            'delete_with_user' => false, // On ne veut pas supprimer
        ];

        // Active les archives si homemode=archive
        if ($this->settings->homemode() === 'archive') {
            $args = [
                'has_archive' => true,
                'rewrite' => [
                    'slug' => $this->settings->slug(),
                    'with_front' => false,
                ],
            ] + $args;
        }

        // Déclare le CPT
        register_post_type($type, $args);

        // Crée une requête quand on est sur la page d'accueil
        add_filter('docalist_search_create_request', function (SearchRequest $request = null, WP_Query $query, & $displayResults) {
            // Si quelqu'un a déjà créé une requête, on le laisse gérer
            if ($request) {
                return $request;
            }

            // Si c'est une page back-office, on ne fait rien
            if (is_admin()) {
                return $request;
            }

            // Pages "liste des réponses" et "accueil" en mode 'page' ou 'search'
            if ($query->is_page && $page = $query->get_queried_object_id()) {
                // Page liste des réponses
                if ($page === $this->searchPage()) {
                    // on fait une recherche et on affiche les réponses
                    $searchUrl = new SearchUrl($_SERVER['REQUEST_URI'], [$this->postType]);
                    $displayResults = true;

                    return $searchUrl->getSearchRequest();
                }

                // Page d'accueil
                if ($page === $this->homePage()) {
                    // en mode 'page', on fait une recherche mais on laisse wp afficher la page
                    // en mode 'search', on affiche les réponses obtenues
                    $searchUrl = new SearchUrl($this->searchPageUrl(), [$this->postType]);
                    $displayResults = ($this->settings->homemode() === 'search');

                    return $searchUrl->getSearchRequest();
                }
            }

            // Page d'accueil - mode 'archive'
            elseif ($query->is_post_type_archive && $query->get('post_type') === $this->postType) {
                // on fait une recherche, mais on laisse wp afficher les archives
                $searchUrl = new SearchUrl($this->searchPageUrl(), [$this->postType]);
                $displayResults = false;

                return $searchUrl->getSearchRequest();
            }

            // Ce n'est pas une de nos pages
            return $request;
        }, 10, 3);


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
        add_permastruct($type, $this->settings->slug() . "/%$type%", [
            'with_front' => false,
            'ep_mask' => EP_NONE,
            'paged' => false,
            'feed' => false,
            'forcomments' => false,
            'walk_dirs' => false,
            'endpoints' => false,
        ]);

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
             6  mabase/(\d+)/trackback/?$                                       index.php?dbprisme=$1&tb=1

            // Pagination d'une notice (balise <!––nextpage––>)
             7  mabase/(\d+)(/[0-9]+)?/?$                                       index.php?dbprisme=$1&page=$2

             8  mabase/\d+/([^/]+)/?$                                           index.php?attachment=$1
             9  mabase/\d+/([^/]+)/trackback/?$                                 index.php?attachment=$1&tb=1
            10  mabase/\d+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$             index.php?attachment=$1&feed=$2
            11  mabase/\d+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$                  index.php?attachment=$1&feed=$2
            12  mabase/\d+/([^/]+)/comment-page-([0-9]{1,})/?$                  index.php?attachment=$1&cpage=$2

            Seule la règle 7 (sans la pagination) nous intéresse. Idéalement, on devrait uniquement avoir :
                mabase/(\d+)/?$                                                 index.php?dbprisme=$1
         */
    }

    /**
     * Retourne les libellés à utiliser pour la base documentaire.
     *
     * @return string[]
     *
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */
    private function postTypeLabels()
    {
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
        return [
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
        ];
        // @formatter:on
    }

    /**
     * Retourne le libellé de la base.
     *
     * @return string
     */
    public function label()
    {
        return $this->settings->label();
    }

    /**
     * Rendue publique car EditReference::save() en a besoin.
     * @see \Docalist\Repository\PostTypeRepository::encode()
     */
    public function encode(array $data)
    {
        return parent::encode($data);
    }

    /**
     * Rendue publique car EditReference::save() en a besoin.
     * @see \Docalist\Repository\PostTypeRepository::decode()
     */
    public function decode($post, $id)
    {
        return parent::decode($post, $id);
    }

    /**
     * Indique à Docalist Search les facettes disponibles pour une notice
     * documentaire.
     */
    protected function docalistSearchFacets()
    {
        static $done = false;

        // Evite de le faire pour chaque base, une fois ça suffit
        if ($done) {
            return;
        }
        $done = true;

        add_filter('docalist_search_get_facets', function ($facets) {
            $facets += [
                'ref.status' => [
                    'label' => __('Statut', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'status.filter',
                    ],
                ],
                'ref.type' => [
                    'label' => __('Type de document', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'type.filter',
                        // 'order' => 'term',
                    ],
                ],
                'ref.genre' => [
                    'label' => __('Genre de document', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'genre.filter',
                    ],
                ],
                'ref.media' => [
                    'label' => __('Support de document', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'media.filter',
                    ],
                ],
                'ref.author' => [
                    'label' => __('Auteur', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'author.filter',
                        'exclude' => ['et al.¤'],
                    ],
                ],
                'ref.organisation' => [
                    'label' => __('Organisme', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'organisation.filter',
                    ],
                ],
                'ref.date' => [
                    'label' => __('Année du document', 'docalist-biblio'),
                    'type' => 'date_histogram',
                    'facet' => [
                        'field' => 'date',
                        'interval' => 'year',
                    ],
                ],
                'ref.journal' => [
                    'label' => __('Revue', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'journal.filter',
                    ],
                ],
                'ref.editor' => [
                    'label' => __('Editeur', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'editor.filter',
                    ],
                ],
                'ref.event' => [
                    'label' => __('Evénement', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'event.filter',
                    ],
                ],
                'ref.degree' => [
                    'label' => __('Diplôme', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'degree.filter',
                    ],
                ],
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
                'ref.topic' => [
                    'label' => __('Mot-clé', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'topic.filter',
                        'size' => 10,
                        //                    'order' => 'term',
                    ],
                ],
                'ref.owner' => [
                    'label' => __('Producteur de la notice', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'owner.filter',
                    ],
                ],

                'ref.creation' => [
                    'label' => __('Année de création de la notice', 'docalist-biblio'),
                    'type' => 'date_histogram',
                    'facet' => [
                        'field' => 'creation',
                        'interval' => 'year',
                    ],
                ],

                'ref.lastupdate' => [
                    'label' => __('Année de mise à jour de la notice', 'docalist-biblio'),
                    'type' => 'date_histogram',
                    'facet' => [
                        'field' => 'lastupdate',
                        'interval' => 'year',
                    ],
                ],

                'ref.error' => [
                    //'state' => 'closed',
                    'label' => __('Erreurs détectées', 'docalist-biblio'),
                    'facet' => [
                        'field' => 'error.filter',
                    ],
                ],
            ];

            return $facets;
        });
    }
}
