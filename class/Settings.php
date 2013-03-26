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
use Docalist\AbstractSettings;

/**
 * Options de configuration du plugin de gestion de notices bibliographiques.
 */
class Settings extends AbstractSettings {
    /**
     * @inheritdoc
     */
    public function __construct() {
        /*
         * Principe : nos settings contiennent (une partie) des arguments qui
         * seront passés à register_post_type() : slug utilisé, libellés, etc.
         * A terme, l'utilisateur pourra ainsi configurer comme il le souhaite
         * les libellés utilisés.
         * Des valeurs par défaut sont proposées et sont prévues pour être
         * traduites. Pour éviter d'avoir trop de chaines de traduction,
         * certains libellés sont réutilisés. (8 chaines à traduire qui sont
         * encuite utilisées pour génrére 14 libellés).
         */

        // translators: Nom de la base de données tel qu'affiché dans les menus
        $menu = __('Base biblio', 'docalist-biblio');

        // translators: Libellé utilisé pour désigner une notice bibliographique
        $name = __('Notice', 'docalist-biblio');

        // translators: Libellé utilisé pour une liste de notices
        $all = __('Liste des notices', 'docalist-biblio');

        // translators: Libellé utilisé pour créer une notice
        $new = __('Créer une notice', 'docalist-biblio');

        // translators: Libellé utilisé pour modifier une notice
        $edit = __('Modifier', 'docalist-biblio');

        // translators: Libellé utilisé pour afficher une notice
        $view = __('Afficher', 'docalist-biblio');

        // translators: Libellé utilisé pour rechercher des notices
        $search = __('Rechercher', 'docalist-biblio');

        // translators: Libellé utilisé pour indiquer aucune réponse trouvée
        $notfound = __('Aucune notice trouvée.', 'docalist-biblio');

        $this->defaults = array(
            // pour le formatter, en attendant qu'il y ait d'autrs options
            'nu' => '',

            // Options du custom post type "references"
            'ref' => array(
                // Slug de la base (register_post_type : $args.rewrite.slug)
                'slug' => 'base',

                // Libellés utilisés (register_post_type : $args.labels)
                'labels' => array(
                    'name' => $menu,
                    'singular_name' => $name,
                    'menu_name' => $menu,
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
                    'name_admin_bar' => $name,
                )
            ),

            'import' => array(
                // Lors d'un import, appeller wp_cache_init toutes les x notices
                // pour éviter les erreurs "memory exhausted"
                'reinitcache' => 100,
            )
        );
    }

}
