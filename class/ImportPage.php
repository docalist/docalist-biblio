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

        $path = $uploadDir . '/prisme/BaseV5/PrismeV5-22-07-13.txt';
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
}