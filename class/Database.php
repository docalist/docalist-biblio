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

use Docalist\Biblio\Entity\Reference;
use Docalist\Data\Repository\PostTypeRepository;
use Docalist\Data\Entity\EntityInterface;
use Docalist\Search\Indexer;
use WP_Post;

/**
 * Une base de données documentaire.
 */
class Database extends PostTypeRepository {
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
        parent::__construct('Docalist\Biblio\Entity\Reference', $settings->postType());

        // Stocke nos paramètres
        $this->settings = $settings;

        // Crée le custom post type WordPress
        $this->registerPostType();

        // Installe les hooks Docalist Search
        $this->docalistSearchHooks();

        // Déclare nos facettes
        $this->docalistSearchFacets();

        // Comme on stocke les données dans post_excerpt, on doit garantir qu'il n'est jamais modifié (autosave, heartbeat, etc.)
        add_filter('wp_insert_post_data', function(array $data) {
            if ($data['post_type'] === $this->postType) {
                unset($data['post_excerpt']);
            }
            return $data;
        }, 999); // EditReference a également un filtre wp_insert_post_data avec ne priorité supérieure. Les priorités doivent rester synchro.

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
            'labels' => $this->postTypelabels(),
            'public' => true,
            'rewrite' => array(
                'slug' => $this->settings->slug,
                'with_front' => false,
            ),
            'hierarchical' => true,
            'capability_type' => 'post',
            'supports' => $supports,
            'has_archive' => true,
            'show_in_nav_menus' => false,
        ));
        // @formatter:on
    }

    /**
     * Retourne les libellés à utiliser pour la base documentaire.
     *
     * @return string[]
     *
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */
    private function postTypelabels() {
        $label = $this->settings->label;

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
            $types[$type] = $this->settings->label;

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
            return require __DIR__ . '/../mappings/dclref-mapping.php';
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
        $this->concat($ref, 'translation', 'title');
        unset($ref['pagination']);
        unset($ref['format']);
        $this->concat($ref, 'editor', 'name', 'city', 'country');
        unset($ref['edition']);
        $this->concat($ref, 'collection', 'name');
        $this->concat($ref, 'event', 'title', 'date', 'place', 'number');
        $this->concat($ref, 'degree', 'level', 'title');
        $this->concat($ref, 'abstract', 'content');
        if (isset($ref['topic'])) {
            $terms = array();
            foreach($ref['topic'] as $topic) {
                isset($topic['term']) && $terms += $topic['term'];
            }
            $ref['topic'] = $terms;
        }
        unset($ref['note']);
        $this->concat($ref, 'link', 'url');

        unset($ref['statusdate']);
        unset($ref['imported']);
        unset($ref['todo']);

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
        // Pour des raisons de rapidité, on travaille directement avec les
        // données brutes plutôt qu'avec les entités Reference : les entités
        // sont très pratiques, mais quand on réindexe des milliers de ref et
        // qu'il faut créer quasiment une centaine d'objets (champs, sous
        // champs, collections, ...) par référence, pour les détruire juste
        // après, php a un peu de mal...
        // Pour les mêmes raisons, on n'utilise pas get_posts() mais
        // directement wpdb.
        // Ces deux optimisations ont permis de ramener le temps de réindexation
        // de la base Prisme (75000 notices) de 865 secondes (14'25") à 132
        // secondes (2'12").
        // Même mieux : avec un bulkMaxDoc à 10000 : 61 secondes.

        global $wpdb;

        // Prépare la requête utilisée pour charger les posts par lots de $limit
        $type = $this->postType();
        $offset = 0;
        $limit = 1000;
        $sql = "SELECT ID, post_excerpt FROM $wpdb->posts
                WHERE post_type = %s AND post_status = 'publish'
                LIMIT %d OFFSET %d"; // @todo ORDER BY ID ASC ?

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
                $data = json_decode($post->post_excerpt, true);
                if (is_null($data)) {
                    $msg = __('Erreur lors du décodage des données JSON du post ID=%d.', 'docalist-biblio');
                    $msg = sprintf($msg, $post->ID);
                    printf('<p style="color: red; font-weight: bold">%s</p>', $msg);
                } else {
                    $indexer->index($type, $post->ID, $this->map($data));
                }
            }

            // Passe au lot suivant
            $offset += count($posts);

            // La ligne (commentée) suivante est pratique pour les tests
//             if ($offset >= 3000) break;
        }
        return;

/*
        // Version d'origine utilisant les entités
        // quatre à cinq fois plus lente ...
        $type = $this->postType();
        $offset = 0;
        $size = 1000;

        $query = new \WP_Query();

        $args = array(
            'fields' => 'ids',

            'post_type' => $type,
            // 'post_status' => 'publish',

            'offset' => $offset,
            'posts_per_page'=> $size,

            'orderby' => 'ID',
            'order' => 'ASC',

            'cache_results' => false,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,

            'no_found_rows' => true
        );

        while ($posts = $query->query($args)) {
//            echo "Query exécutée avec offset=", $args['offset'], ', result=', count($posts), '<br />';
            foreach($posts as $id) {
                $reference = $this->load($id);
                $indexer->index($type, $id, $this->map($reference));
            }
            $args['offset'] += count($posts);

            //if ($args['offset'] >= 10000) break;
        }
*/
    }

    /**
     * Retourne le libellé de la base
     *
     * @return string
     */
    public function label() {
        return $this->settings->label;
    }

    public function synchronize(WP_Post $post, EntityInterface $entity, $save = false) {
        parent::synchronize($post, $entity, $save);

        /* @var $entity Reference */

        // Liste des champs virtuels de la notice et chalo wp correspondant
        static $map = [
            'ref'        => 'post_name',
            'parent'     => 'post_parent',
            'title'      => 'post_title',
            'status'     => 'post_status',
         // 'userid'     => 'post_author',
            'creation'   => 'post_date',
            'lastupdate' => 'post_modified',
         // 'post_type'  => 'déjà fait par PostTypeRepository::synchronize()'
        ];

        // Sauvegarde d'une notice
        if ($save) {
            // Alloue un numéro de ref à la notice / met à jour notre séquence
            if (empty($entity->ref)) {
                $entity->ref = docalist('sequences')->increment($this->postType, 'ref');
            } else {
                docalist('sequences')->setIfGreater($this->postType, 'ref', $entity->ref);
            }

            // Recopie les champs virtuels de la notice dans le post wordpress
            foreach($map as $src => $dst) {
                if (isset($entity->$src)) {
                    $post->$dst = $entity->$src;
                    unset($entity->$src);
                }
            }
        }

        // Chargement d'une notice
        else {
            // Initialise les champs virtuels de la notice à partir du post wordpress
            foreach($map as $src => $dst) {
                $entity->$src = $post->$dst;
            }
        }
    }

    /**
     * Indique à Docalist Search les facettes diposnibles pour une notice
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

                'ref.creation.date' => array(
                    'label' => __('Année de création de la notice', 'docalist-biblio'),
                    'type' => 'date_histogram',
                    'facet' => array(
                        'field' => 'creation.date',
                        'interval' => 'year'
                    )
                ),

                'ref.lastupdate.date' => array(
                    'label' => __('Année de mise à jour de la notice', 'docalist-biblio'),
                    'type' => 'date_histogram',
                    'facet' => array(
                        'field' => 'lastupdate.date',
                        'interval' => 'year'
                    )
                ),

                // creation
                // lastupdate
                'ref.errors' => array(
                    //'state' => 'closed',
                    'label' => __('Erreurs détectées', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'errors.code',
                    )
                ),
            );

            return $facets;
        });
    }
}