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

use Docalist\Biblio\Entity\Reference;
use Docalist\AbstractAdminPage;
use Docalist\Data\Schema\Schema;
use Docalist\Data\Schema\Field;
use Docalist\Utils;
/**
 * Page "Importer" d'une base
 */
class ImportPage extends AbstractAdminPage {

    /**
     *
     * @var Database
     */
    protected $database;

    /**
     *
     * @param Database $database
     */
    public function __construct(Database $database) {
//         echo "Création de la page 'liste des notices' pour la base ", $database->settings()->name, '<br />';
        $parent = 'edit.php?post_type=' . $database->postType();
        $title = $database->label();
        $menu = __('Gérer', 'docalist-biblio');
        parent::__construct($parent, $title, $menu);
        $this->database = $database;
        $this->id = 'import-' . $database->postType();
    }

    /**
     * Import de fichier
     */
    public function actionImport() {
        $uploads = wp_upload_dir();
        $uploadDir = $uploads['basedir'];
        $uploadUrl = $uploads['baseurl'];

        $path = $uploadDir . '/prisme/1309/201309_BDD PRISME_septembre 2013.TXT';
        $class= 'Docalist\Biblio\Import\Prisme';

        $msg = 'Le fichier <code>%s</code> va être importé dans la base <b>%s</b>.';
        $msg = sprintf($msg, realpath($path), $this->database->label());
        if (! $this->confirm($msg, "Lancer l'import")) {
            return;
        }

        $records = new $class($path, true);

        $time = microtime(true);
        set_time_limit(3600);
        ignore_user_abort(true);
        while(ob_get_level()) ob_end_flush();
        echo 'Temps écoulé (sec) ; Nb de notices chargées ; memory_get_usage() ; memory_get_usage(true) ; memory_get_peak_usage() ; memory_get_peak_usage(true)', '<br />';
        $nb = 0;
        foreach($records as $record) {
            ++$nb;

            // Appelles wp_cache_init toutes les reinitcache notices
//             if (0 === $nb % $reinitCache) {
//                 wp_cache_init();
//             }

            // Toutes les 500 notices, stats sur le temps et la mémoire utilisée
            if (0 === $nb % 500) {
                echo round(microtime(true)-$time,2), ' ; ', $nb, ' ; ', memory_get_usage(), ' ; ', memory_get_usage(true), ' ; ', memory_get_peak_usage(), ' ; ', memory_get_peak_usage(true), '<br />';
                flush();
            }
            $record = (array) $record;
            $reference = new Reference($record);
            $this->database->store($reference);

//           if ($nb >= 1000) break;
        }
        echo '<br />';
        echo "<p>Temps total : ", round(microtime(true)-$time,2), ' secondes</p>';
        echo "<p>Notices chargées : $nb</p>";
        echo '</div>'; // .wrap

    }

    public function actionDeleteAll() {
        if (! $this->confirm('Toutes les notices vont être supprimées.')) {
            return;
        }
        echo __('<p>Suppression en cours...</p>', 'docalist-search');

        $count = $this->database->deleteAll();

        $msg = __('<p>%d notice(s) supprimée(s).</p>', 'docalist-search');

        printf($msg, $count);
    }

    /**
     * Taxonomies
     */
    public function actionTaxonomies() {
        // $posttype = $this->plugin()->get('references')->id();
        // $taxonomies = get_taxonomies(array('object_type' => array($posttype)), 'objects');
        $taxonomies = get_taxonomies(array(), 'objects');

        echo '<ul class="ul-disc">';
        foreach($taxonomies as $taxonomy) {
/*
            //@formatter:off
            $url = admin_url(sprintf(
                    'edit-tags.php?taxonomy=%s&post_type=%s',
                    $taxonomy->name,
                    $posttype
            ));
            //@formatter:off
*/

            //@formatter:off
            $url = admin_url(sprintf(
                    'edit-tags.php?taxonomy=%s',
                    $taxonomy->name
            ));
            //@formatter:off
            printf('<li><a href="%s">%s</a></li>', $url, $taxonomy->label);
        }
        echo '</ul>';
    }

    /**
     * Format de la base.
     *
     * Documentation sur le format de la base documentaire.
     */
    public function actionDoc() {
        // Récupère le type des entités
        $class = $this->database->type();

        // Récupère le schéma
        /* @var $ref Reference */
        $ref = new $class;
        $schema = $ref->schema();

        $maxlevel = 4;

        $msg = '<table class="widefat"><thead><tr><th colspan="%d">%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr></thead>';
        printf($msg,
            $maxlevel,
            __('Nom du champ', 'docalist-biblio'),
            __('Libellé', 'docalist-biblio'),
            __('Description', 'docalist-biblio'),
            __('Type', 'docalist-biblio'),
            __('Répétable', 'docalist-biblio')
        );

        $this->doc($schema->fields(), 0, $maxlevel);
        echo '</table>';
    }

    protected function doc(array $fields, $level, $maxlevel) {
        // var_dump($schema);

        /* @var $field Field */
        foreach($fields as $field) {
            echo '<tr>';

            //$level && printf('<td colspan="%d">x</td>', $level);
            for ($i = 0; $i < $level; $i++) {
                echo '<td></td>';
            }

            $repeat = $field->repeatable() ? __('<b>Répétable</b>', 'docalist-biblio') : __('Monovalué', 'docalist-biblio');
            $type = $field->entity() ? Utils::classname($field->entity()) : $field->type();
            $msg = '<th colspan="%1$d"><h%2$d style="margin: 0">%3$s</h%2$d></th><td class="row-title">%4$s</td><td><i>%5$s</i></td><td>%6$s</td><td>%7$s</td>';
            printf($msg,
                $maxlevel - $level,     // %1
                $level + 3,             // %2
                $field->name(),         // %3
                $field->label(),        // %4
                $field->description(),  // %5
                $type,         // %6
                $repeat // %7
            );

            echo '</tr>';

            $subfields = $field->fields();
            $subfields && $this->doc($subfields, $level + 1, $maxlevel);
        }
    }
}