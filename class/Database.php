<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package Docalist
 * @subpackage Biblio
 * @author Daniel Ménard <daniel.menard@laposte.net>
 * @version SVN: $Id$
 */
namespace Docalist\Biblio;

use Docalist\Data\Repository\PostTypeRepository;
use Docalist\Data\Entity\EntityInterface;
use Docalist\Biblio\Entity\Reference;
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
        parent::__construct('Entity\Reference', 'dclref' . $settings->name);

        // Stocke nos paramètres
        $this->settings = $settings;

        // Crée le custom post type WordPress
        $this->registerPostType();

        // Installe les hooks Docalist Search
        $this->docalistSearchHooks();

        // Déclare nos facettes
        $this->docalistSearchFacets();
    }

    /**
     * Crée un custom post type wordpress pour la base documentaire.
     *
     * @see http://codex.wordpress.org/Function_Reference/register_post_type
     */
    private function registerPostType() {
        // @formatter:off
        register_post_type($this->postType(), array(
            'labels' => $this->postTypelabels(),
            'public' => true,
            'rewrite' => array(
                'slug' => $this->settings->name,
                'with_front' => false,
            ),
            'capability_type' => 'post',
            'supports' => false, //array('excerpt'),
            'has_archive' => true,
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
            return $settings; // @todo
        });

        // Mappings
        add_filter("docalist_search_get_{$type}_mappings", function ($mappings) {
            return $this->mappings();
        });

        // Reindexation
        add_action("docalist_search_reindex_{$type}", function($indexer){
            $this->reindex($indexer);
        });
    }

    /**
     * Retourne les mappings Docalist Search à utiliser pour cette base de
     * données.
     *
     * @return array
     */
    protected function mappings() {
        $temp = require_once(__DIR__ . '/../mappings/dclref.php');
        return $temp['mappings']['dclref']; //@ todo réorganiser dclref
    }

    /**
     * Transforme une notice de la base en document Docalist Search.
     *
     * @return array
     */
    public function map(array $ref) {
        return $ref; // @todo à affiner
    }

    /**
     * Réindexe toutes les notices de la base dans Docalist Search.
     *
     * @return array
     */
    protected function reindex() {
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

        global $wpdb;

        // Prépare la requête utilisée pour charger les posts par lots de $limit
        $type = $this->postType();
        $offset = 0;
        $limit = 1000;
        $sql = "SELECT ID, post_excerpt FROM $wpdb->posts
                WHERE post_type = %s AND post_status = 'publish'
                LIMIT %d OFFSET %d"; // @todo ORDER BY ID ASC ?

        // Temps qu'on gagne, on joue
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
                if (is_null($data)) {echo "data empty<br />";}
                do_action('docalist_search_index', $type, $post->ID, $this->map($data)); // this map(data)
            }

            // Passe au lot suivant
            $offset += count($posts);

            // La ligne (commentée) suivante ets pratique pour les tests
            // if ($offset >= 3000) break;
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
                do_action('docalist_search_index', $type, $id, $this->map($reference));
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

    protected function synchronizePost(WP_Post & $post, EntityInterface $entity) {
        parent::synchronizePost($post, $entity);
        $post->post_name = $entity->ref;
        $post->post_title = $entity->title;
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
                        'field' => 'type.keyword',
                        //                    'order' => 'term',
                    )
                ),
                'ref.topic' => array(
                    'label' => __('Mot-clé', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'topic.term.keyword',
                        'size'  => 10,
                        //                    'order' => 'term',
                    )
                ),
                'ref.media' => array(
                    'label' => __('Support de document', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'media.keyword',
                    )
                ),
                'ref.journal' => array(
                    'label' => __('Revue', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'journal.keyword',
                    )
                ),
                'ref.author' => array(
                    'label' => __('Auteur', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'author.keyword',
                        'exclude' => array('et al.'),
                    )
                ),
                'ref.genre' => array(
                    'label' => __('Producteur de la notice', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'owner.keyword',
                    )
                ),
                'ref.owner' => array(
                    'label' => __('Genre de document', 'docalist-biblio'),
                    'facet' => array(
                        'field' => 'genre.keyword',
                    )
                ),
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