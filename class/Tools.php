<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Exception;
use Docalist\AbstractAdminPage;

/**
 * Outils pour la base bibliographique.
 *
 * fsdfsfsd
 *
 */
class Tools extends AbstractAdminPage {
    /**
     * @inheritdoc
     */
    protected $parentPage = 'tools.php';

    /**
     * Vider la base
     *
     * Supprime toutes les notices de la base.
     */
    public function actionDeleteAll() {
        global $wpdb;

        if (! $this->confirm('Toutes les notices vont être supprimées.')) {
            return;
        }

        echo '<p>Suppression en cours...</p>';
        set_time_limit(3600);
        while(ob_get_level()) ob_end_flush();
        flush();

        $postType = $this->plugin()->get('references')->id();

        // Adapté de http://wpquestions.com/question/show/id/1363 (John Cotton)
        echo "<p>Suppression des posts de type $postType...</p>";
        flush();
        $posts = $wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_type='$postType'");

        echo "<p>Suppression des postmeta qui ne sont pas associés à un post...</p>";
        flush();
        $metas = $wpdb->query("DELETE FROM `$wpdb->postmeta` WHERE post_id NOT IN (SELECT ID FROM `$wpdb->posts`)");

        echo "<p>Suppression des term_relationships qui ne sont pas associés à un post...</p>";
        flush();
        $terms = $wpdb->query("DELETE FROM `$wpdb->term_relationships` WHERE object_id NOT IN (SELECT ID FROM `$wpdb->posts`)");

        // Remarque : dans l'ordre où on le fait, on peut supprimer des metas
        // et des term_relations qui n'appartiennent pas à des notices car on
        // supprime tout ce qui n'est pas rattaché à un post.
        //
        // On pourrait faire autrement et remplacer la clause where par :
        // WHERE ... IN (SELECT ID FROM wp_posts where post_type=dclref)
        // et dans ce cas on supprimerait les posts en dernier.
        //
        // Je laisse comme c'est car :
        // - ça permet de faire le ménage dans la base
        // - les requêtes sql sont plus simples.

        echo '<ul class="ul-disc">';
        echo "<li>$posts notice(s) supprimée(s)</li>";
        echo "<li>$metas meta(s) supprimé(s)</li>";
        echo "<li>$terms relation(s) de termes supprimée(s)</li>";
        echo '</ul>';

        echo '<p>La base documentaire a été vidée.</p>';
    }

    /**
     * Import de fichiers
     *
     * Importe le fichier Prisme dans la base.
     */
    public function actionImport() {
        $uploads = wp_upload_dir();
        $uploadDir = $uploads['basedir'];
        $uploadUrl = $uploads['baseurl'];

        if (true) {
//          $path = 'd:/prisme/BASE PRISME NOUVEAU FORMAT.TXT';
//            $path = $uploadDir . '/prisme/BaseV3/BDD PRISME separateur habituel.txt';
            $path = $uploadDir . '/prisme/BaseV4/BDDPrismeModifie.txt';
            $class= 'Docalist\Biblio\Import\Prisme';
        } else {
            $path = $uploadDir . '/BDSP/Bdsp.csv';
            $class= 'Docalist\Biblio\Import\BdspCsv';
        }

        echo "<p>Le fichier suivant va être importé : <code>", realpath($path), "</code></p>";
        if (! $this->confirm('Etes-vous sur ?', "Lancer l'import")) {
            return;
        }

        $references = $this->plugin()->get('references');
        $reinitCache = $this->setting('import.reinitcache');
        $records = new $class($path, true);

        $time = microtime(true);
        set_time_limit(3600);
        while(ob_get_level()) ob_end_flush();
        echo 'Temps écoulé (sec) ; Nb de notices chargées ; memory_get_usage() ; memory_get_usage(true) ; memory_get_peak_usage() ; memory_get_peak_usage(true)', '<br />';
        $nb = 0;
        foreach($records as $record) {
            ++$nb;

            // Appelles wp_cache_init toutes les reinitcache notices
            if (0 === $nb % $reinitCache) {
                wp_cache_init();
            }

            // Toute sles 500 notices, stats sur le temps et la mémoire utilisée
            if (0 === $nb % 500) {
                echo round(microtime(true)-$time,2), ' ; ', $nb, ' ; ', memory_get_usage(), ' ; ', memory_get_usage(true), ' ; ', memory_get_peak_usage(), ' ; ', memory_get_peak_usage(true), '<br />';
                flush();
            }

            $references->store($record);
//echo "<pre>"; print_r($record); echo "</pre>";
//            if ($nb >= 1000) break;
        }
        echo '<br />';
        echo "<p>Temps total : ", round(microtime(true)-$time,2), ' secondes</p>';
        echo "<p>Notices chargées : $nb</p>";
        echo '</div>'; // .wrap
    }

    /**
     * Taxonomies
     *
     * Gestion des tables d'autorité.
     */
    public function actionTaxonomies() {
        $posttype = $this->plugin()->get('references')->id();
        $taxonomies = get_taxonomies(array('object_type' => array($posttype)), 'objects');

        echo '<ul class="ul-disc">';
        foreach($taxonomies as $taxonomy) {
            //@formatter:off
            $url = admin_url(sprintf(
                'edit-tags.php?taxonomy=%s&post_type=%s',
                $taxonomy->name,
                $posttype
            ));
            //@formatter:off
            printf('<li><a href="%s">%s</a></li>', $url, $taxonomy->label);
        }
        echo '</ul>';
    }
}
