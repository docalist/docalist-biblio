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
namespace Docalist\Biblio\Pages;

use Docalist\Biblio\Database;
use Docalist\Biblio\Reference;
use WP_Post;
use DateTime, DateInterval;
use Exception;

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
        // Définit la liste des colonnes à afficher
        add_filter("manage_{$this->postType}_posts_columns", function($columns) {
            echo
                '<style type="text/css">
                    .widefat .column-ref  { width: 3em; text-align: right }
                    .column-type { width: 5em }
                    .column-creation { width: 9em }
                    .column-lastupdate { width: 9em }
                </style>';

            return [
                'cb' => $columns['cb'],
                'ref' => __('Ref', 'docalist-biblio'),
                'type' => __('Type', 'docalist-biblio'),
                'title' => $columns['title'],
                'creation' => __('Création', 'docalist-biblio'),
                'lastupdate' => __('Mise à jour', 'docalist-biblio'),
//                 'author' => $columns['author'],
                'comments' => $columns['comments'],
//                 'date' => $columns['date'],
            ];
        });

        // Fournit le contenu des colonnes personnalisées pour chaque notice
        add_action("manage_{$this->postType}_posts_custom_column", function($column, $post_id) {
            /* @var $ref Reference */
            static $ref = null;

            /* @var $post WP_Post */
            global $post;

            if (is_null($ref) || $ref->id() !== $post_id) {
                try {
                    $ref = $this->database->load($post_id);
                } catch (Exception $e) {
                    return;
                }
            }

            switch ( $column ) {
                case 'ref' :
                    echo $ref->ref();
                    break;

                case 'type' :
                    $types = $this->database->settings()->types;
                    $type = $ref->type();
                    if (isset($types[$type])) {
                        echo $types[$type]->label();
                    } else {
                        $title = __("Le type %s n'existe pas dans la base %s.", 'docalist-biblio');
                        $title = sprintf($title, $type, $this->database->settings()->label());
                        printf('<span style="color:red;" title="%s">%s</span>', $title, $type);
                    }
                    break;

                case 'creation':
                    $date = $post->post_date;
                    $author = get_user_by('id', $post->post_author); /* @var $author WP_User */
                    printf('%s<br/><a href="%s">%s</a>',
                        $this->formatDate($date),
                        esc_url(add_query_arg(['author' => $author ? $author->ID : 0])),
                        $author ? $author->display_name : 'n/a'
                    );
                    break;

                case 'lastupdate':
                    $date = $post->post_modified;
                    if ($date !== $post->post_date) {
                        $id = get_post_meta($post->ID, '_edit_last', true);
                        if ($id /* && $id !== $post->post_author */) {
                            $author = get_user_by('id', $id); /* @var $author WP_User */
                            printf('%s<br/><a href="%s">%s</a>',
                                $this->formatDate($date),
                                esc_url(add_query_arg(['author' => $author ? $author->ID : 0])),
                                $author ? $author->display_name : 'n/a'
                            );
                        }
                    }
                    break;
            }
        }, 10, 2 );

        add_filter("manage_edit-{$this->postType}_sortable_columns", function($columns) {
            /*
             * On a plusieurs possibilités pour trier sur "la date de dernière
             * modification" et peu de doc. Examen des requêtes SQL générées:
             * - orderby=last_modified -> ORDER BY wp_posts.post_date DESC
             * - orderby=modified      -> ORDER BY wp_posts.post_modified DESC
             * - orderby=post_modified -> ORDER BY wp_posts.post_modified DESC
             *
             * Conclusion :
             * - last_modified est vraiment trompeur : ça trie sur la date de
             *   création et non pas la date de mise à jour !
             * - modified est juste un alias de post_modified
             * - orderby=post_modified est l'option la plus lisible
             *
             * Ordre par défaut :
             * - si l'utilisateur demande à trier par "date de modif", il veut
             *   en général les derniers posts modifiés
             * - donc : order=desc
             *
             * Tri secondaire :
             * - les posts qui n'ont jamais été modifiés arrivent dans un
             *   ordre quelconque.
             * - Il faut un tri secondaire : post_date.
             * - Pb : comment indiquer ça ?
             * - Par essais successifs, il semble que la syntaxe champ1+champ2
             *   fonctionne (non documenté).
             *    -> orderby=post_modified+post_date&order=desc
             *    -> ORDER BY wp_posts.post_modified DESC, wp_posts.post_date DESC
             * - Seule limite : l'ordre de tri ne s'inverse pas quand on
             *   reclique sur la colonne lastupdate (pas grave).
             *
             * Performances :
             * - dans la base wordpress, le champ post_modified n'est pas indexé
             * - du coup, le tri par date de dernière modification est très lent
             *   (la slow query générée dure environ 1 seconde).
             * - ajouter un index ? utiliser ES ?
             */
            $columns['creation']   = ['post_date', true];
            $columns['lastupdate'] = ['post_modified+post_date', true];

            return $columns;
        });
    }

    private function formatDate($date) {
        global $wp_locale;
        global $mode; // "list" ou "excerpt"

        $date = new DateTime($date);
        $now = new DateTime();
        $excerpt = $mode === 'excerpt';

        // Aujourd'hui
        if ($date->format('Ymd') === $now->format('Ymd')) {
            return $date->format('H:i:s');
        }

        // Un des jours précédents : affiche hier, mardi, lundi, etc.
        $oneDay = new DateInterval('P1D');
        for ($i = 0; $i < 6; $i++) {
            $now->sub($oneDay);
            if ($date->format('Ymd') === $now->format('Ymd')) {
                $day = $i ? $wp_locale->get_weekday($date->format('w')) : __('hier', 'docalist-biblio');
                $format = $excerpt ? '%s %s' : '<abbr title="%2$s">%1$s</abbr>';
                return sprintf($format, $day, $date->format('H:i:s'));
            }
        }

        // Même année : affiche jour/mois
        $now = new DateTime();
        if ($date->format('Y') === $now->format('Y')) {
            $format = $excerpt ? '%s %s' : '<abbr title="%2$s">%1$s</abbr>';
            return sprintf($format, $date->format('d/m'), $date->format('H:i:s'));
        }

        // Autre date
        $format = $excerpt ? '%s %s' : '<abbr title="%2$s">%1$s</abbr>';
        return sprintf($format, $date->format('d/m/Y'), $date->format('H:i:s'));
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
        // Pour le moment, on se contente de masquer le bouton "filtrer"
        // comme on n'a plus aucun filtre
        add_action('restrict_manage_posts', function() {
            echo '<style type="text/css">#post-query-submit {display: none;}</style>';
        });

        // Nos filtres sont désactivés pour l'instant, c'est beaucoup trop lent
        // (une seconde de délai en plus). cf. docalist-biblio#8.
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
            $types = $this->database->settings()->types;
            foreach ($cache as $type) {
                list($dclref, $code) = explode('/', $type->post_mime_type);
                $label = isset($types[$code]) ? $types[$code]->label : $code;

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
