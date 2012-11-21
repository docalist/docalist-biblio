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
use Docalist\Core\AbstractPlugin;

/**
 * Plugin de gestion de notices bibliographiques.
 */
class Plugin extends AbstractPlugin {
    /**
     * @inheritdoc
     */
    protected function defaultOptions() {
        return array(
            'Biblio.menu' => __('Base biblio', 'docalist-biblio'),
            'Biblio.name' => __('Notice', 'docalist-biblio'),
            'Biblio.all' => __('Liste des notices', 'docalist-biblio'),
            'Biblio.new' => __('Créer une notice', 'docalist-biblio'),
            'Biblio.edit' => __('Modifier', 'docalist-biblio'),
            'Biblio.view' => __('Afficher', 'docalist-biblio'),
            'Biblio.search' => __('Rechercher', 'docalist-biblio'),
            'Biblio.notfound' => __('Aucune réponse trouvée.', 'docalist-biblio'),
        );
    }


}
