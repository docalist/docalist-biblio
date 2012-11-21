<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Setting:     docalist-options
 * Tab:         Biblio
 * Title:       Labels
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Docalist;

$plugin = Docalist::plugin('Biblio');

_e('<p>Utilisez les options ci-dessous pour modifier les libellés utilisés pour désigner la base et son contenu.</p>', 'docalist-biblio');

$labels = array(
    'menu' => array(
        __('Nom de la base', 'docalist-biblio'),
        __('Libellé du menu principal.', 'docalist-biblio')
    ),
    'name' => array(
        __('Nom au singulier', 'docalist-biblio'),
        __('Option "nouveau" dans l\'admin bar de Wordpress.', 'docalist-biblio')
    ),
    'all' => array(
        __('Tous les enregistrements', 'docalist-biblio'),
        __('Première option du menu.', 'docalist-biblio')
    ),
    'new' => array(
        __('Créer un enregistrement', 'docalist-biblio'),
        __('Seconde option du menu.', 'docalist-biblio')
    ),
    'edit' => array(
        __('Modifier', 'docalist-biblio'),
        __('Utilisé à divers endroits.', 'docalist-biblio')
    ),
    'view' => array(
        __('Afficher', 'docalist-biblio'),
        __('Utilisé à divers endroits.', 'docalist-biblio')
    ),
    'search' => array(
        __('Rechercher', 'docalist-biblio'),
        __('Libellé du bouton rechercher.', 'docalist-biblio')
    ),
    'notfound' => array(
        __('Aucune réponse trouvée.', 'docalist-biblio'),
        __('En cas de recherche infructueuse.', 'docalist-biblio')
    ),
);

foreach ($labels as $name => $desc) {
    $fullName = 'Biblio.' . $name;

    piklist('field', array(
        'type' => 'text',
        'field' => $fullName,
        'label' => isset($desc[0]) ? $desc[0] : ucfirst($name),
        'description' => isset($desc[1]) ? $desc[1] : '',
        'value' => $plugin->defaultOption($fullName),
        'attributes' => array('class' => 'regular-text'),
        'required' => true, // @todo : ne fonctionne pas avec le piklist actuel
    ));
}
