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

/**
 * Page "Liste des notices" d'une base
 */
class ListReferences{
    /**
     * La base de données documentaire.
     *
     * @var Database
     */
    protected $database;

    /**
     * Le post-type de la base.
     *
     * Equivalent à $this->database->postType().
     *
     * @var string
     */
    protected $postType;

    /**
     *
     * @param Database $settings
     */
    public function __construct(Database $database) {
        $this->database = $database;
        $this->postType = $database->postType();

        $this->setupColumns();
        $this->removeMonthsFilter();
        $this->setupFilters();
    }

    /**
     * Ajoute les colonnes ref et type.
     *
     * La colonne type ne s'affiche que si on a au moins deux types de notices
     * différents dans la base.
     */
    protected function setupColumns() {
        add_filter("manage_edit-{$this->postType}_columns", function($columns) {
            $result = [];
            $position = 0;
            foreach($columns as $key => $label) {
                ++$position;
                $position === 2 && $t['ref'] = __('Ref', 'docalist-biblio');

//                 if (count($this->types) > 1) {
                    $position === 3 && $t['type'] = __('Type', 'docalist-biblio');
//                 }

                $t[$key] = $label;
            }

            echo
            '<style type="text/css">
                    .widefat .column-ref  { width: 6%; text-align: center }
                    .column-type { width: 10% }
                </style>';
            return $t;
        });

        add_action("manage_{$this->postType}_posts_custom_column", function($column, $post_id) {
            /* @var $ref Reference */

    // Version 1 avec les entités
            static $ref = null;

            if ($column !== 'ref' && $column !== 'type') {
                return;
            }

            if (is_null($ref) || $ref->primarykey() !== $post_id) {
                $ref = $this->database->load($post_id);
            }

            switch ( $column ) {
                case 'ref' :
                    echo $ref->ref;
                    break;

                case 'type' :
                    echo $ref->type;
//                    echo ' : ', get_post_field('post_mime_type', $post_id);
                    break;
            }

            return;
/*

    // Version 2 lecture direct de post_excerpt
            static $last, $ref;
            global $post;

            if ($column !== 'ref' && $column !== 'type') {
                return;
            }

            if (is_null($last) || $last !== $post_id) {
                $last = $post_id;
                $ref = json_decode($post->post_excerpt, true);
            }
            echo isset($ref[$column]) ? $ref[$column] : '';
*/
        }, 10, 2 );
    }

    /**
     * Ajoute un filtre supplémentaire (par type de notice).
     *
     * Le filtre ne s'affiche que si on a au moins deux types de notices
     * différents dans la base.
     *
     * @param array $types La liste des types présents dans la base, telle que
     * retournée par countTypes().
     */
    protected function setupFilters() {

        // Nos filtres sont désactivés pour l'instant, c'est beaucoup trop lent
        // (une seconde de délai en plus). cf. docalist-biblio#8.

        // Pour le moment, on se contente de masquer le bouton "filtrer"
        // comme on n'a plus aucun filtre
        add_action('restrict_manage_posts', function() {
            echo '<style type="text/css">#post-query-submit {display: none;}</style>';
        });

        return;

        add_action('restrict_manage_posts', function() {
            global $typenow;

//             if ($typenow !== $this->postType || count($this->types) < 2) {
//                 return;
//             }

            if ($typenow !== $this->postType) {
                return;
            }

            // Filtre par année de création de la notice (année d'édition ?)
            $years = $this->countYears();
            $current = empty($_GET['year']) ? '' : $_GET['year'];

            echo '<select name="year">';
            printf(
                "<option%s value=''>%s</option>",
                selected($current, '', false),
                __('Filtrer par année', 'docalist-biblio')
            );

            foreach($years as $year) {
                printf(
                    "<option%s value='%s'>%s</option>",
                    selected($current, $year->year, false),
                    esc_attr($year->year),
                    sprintf('%s (%d)', $year->year, $year->count)
                );

            }
            echo '</select>';

            // Filtre par type de notice
            $types = $this->countTypes();
            $current = empty($_GET['dclreftype']) ? '' : $_GET['dclreftype'];

            echo '<select name="dclreftype">';
            printf(
                "<option%s value=''>%s</option>",
                selected($current, '', false),
                __('Filtrer par type', 'docalist-biblio')
            );

            foreach($types as $type) {
                printf(
                    "<option%s value='%s'>%s</option>",
                    selected($current, $type->type, false),
                    esc_attr($type->type),
                    sprintf('%s (%d)', $type->label, $type->count)
                );

            }
            echo '</select>';
        });

        add_filter('parse_query', function(\WP_Query $wp_query) {
            global $pagenow, $typenow;

            if ($pagenow=='edit.php' && $typenow === $this->postType) {
                if (!empty($_GET['year'])) {
                    $wp_query->query_vars['year'] = $_GET['year'];
                }
                if (!empty($_GET['dclreftype'])) {
                    $wp_query->query_vars['post_mime_type'] = 'dclref/' . $_GET['dclreftype'];
                }
            }
        });
    }

    /**
     * Supprime le filtre par année/mois standard de wordpress qu'on va
     * remplacer par un filtre par année dans setupFilters().
     *
     * Raisons :
     * 1. pas dynamique, ne tient pas compte des autres filtres
     * 2. n'affiche pas le nombre de notices
     * 3. pas très utile de filtrer par mois, beaucoup trop d'entrées
     *   (plus de 400 avec la base Prisme qui couvre 40 ans de littérature).
     */
    protected function removeMonthsFilter() {
        // Empêche l'affichage du filtre (cf. WP_List_Table::months_dropdown()).
        add_filter('months_dropdown_results', '__return_empty_array');

        // Le filtre ci-dessus évite l'affichage du dropdown, mais la requête
        // sql correspondante est tout de même exécutée par wordpress. Avec le
        // Nombre d'entrée qu'on a, c'est une "slow query" (0,30 sec sur mon pc)
        // Pour l'empêcher, on fait un hack qui neutralise la requête
        // directement dans wpdb.
        global $pagenow, $typenow;

        if ($pagenow === 'edit.php' && $typenow === $this->postType) {
            add_filter('query', function($query) {
                $sql = 'SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month';
                if (strpos($query, $sql) !== false) {
                    return 'DO 0'; // il faut que ce soit du sql valide
                }
                return $query;
            });
        }
    }

    /**
     * Compte les différents types de notices qui existent dans la base.
     *
     * @return array Un tableau d'objets avec les propriétés :
     * - type : le nom du type (par exemple "article")
     * - post_mime_type : le type mime du type (par exemple "dclref/article"),
     * - count : le nombre de notices de ce type,
     * - label : le libellé du type (par exemple "Article de périodique").
     */
    protected function countTypes() {
        static $cache;

        global $wpdb;

        if ($cache) {
            return $cache;
        }

        // Version dynamique qui tient compte des critères de recherche actuels
        if (true) {
            /* @var $wp_query WP_Query */
            global $wp_query;

            $sql = $wp_query->request;

            $pt = strpos($sql, ' FROM ');
            $sql = substr($sql, $pt + 6);

            $pt = strpos($sql, ' ORDER BY ');
            $sql = substr($sql, 0, $pt);

            $sql = "SELECT `post_mime_type`, count(*) AS `count` FROM $sql ";
            $sql.= "GROUP BY `post_mime_type` ";
            $sql.= "ORDER BY `count` DESC";
        }

        // Version non dynamique (même liste quels que soient les critères)
        else {
            $sql = "SELECT `post_mime_type`, count(*) AS `count` FROM `$wpdb->posts` ";
            $sql.= "WHERE `post_type`='$this->postType' ";
            $sql.= "GROUP BY `post_mime_type` ";
            $sql.= "ORDER BY `count` DESC";
        }

        $cache = $wpdb->get_results($sql);
        if ($cache) {
            $types = $this->database->settings()->typeNames();
            foreach ($cache as $type) {
                list($dclref, $code) = explode('/', $type->post_mime_type);
                $label = isset($types[$code]) ? $types[$code] : $code;

                $type->type = $code;
                $type->label = $label;
            }
        }

        return $cache;
    }

    /**
     * Compte les différents années de création qui existent dans la base.
     *
     * @return array Un tableau d'objets avec les propriétés :
     * - year : l'année
     * - count : le nombre de notices créé pour cette année,
     */
    protected function countYears() {
        static $cache;

        global $wpdb;

        if ($cache) {
            return $cache;
        }

        // Version dynamique qui tient compte des critères de recherche actuels
        if (true) {
            /* @var $wp_query WP_Query */
            global $wp_query;

            $sql = $wp_query->request;

            $pt = strpos($sql, ' FROM ');
            $sql = substr($sql, $pt + 6);

            $pt = strpos($sql, ' ORDER BY ');
            $sql = substr($sql, 0, $pt);

            $sql = "SELECT YEAR(post_date) AS `year`, count(*) AS `count` FROM $sql ";
            $sql.= "GROUP BY `year` ";
            $sql.= "ORDER BY `year` DESC";
        }

        // Version non dynamique (même liste quels que soient les critères)
        else {
            $sql = "SELECT YEAR(post_date) AS `year`, count(*) AS `count` FROM `$wpdb->posts` ";
            $sql.= "WHERE `post_type`='$this->postType' ";
            $sql.= "GROUP BY `post_mime_type` ";
            $sql.= "ORDER BY `count` DESC";
        }

        $cache = $wpdb->get_results($sql);

        return $cache;
    }
}
