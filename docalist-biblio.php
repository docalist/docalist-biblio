<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Plugin Name: Docalist Biblio
 * Plugin URI:  http://docalist.org/
 * Description: Docalist: bibliographic data manager.
 * Version:     0.3
 * Author:      Daniel Ménard
 * Author URI:  http://docalist.org/
 * Text Domain: docalist-biblio
 * Domain Path: /languages
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */

namespace Docalist\Biblio;

// Définit une constante pour indiquer que ce plugin est activé
define('DOCALIST_BIBLIO', __FILE__);

/**
 * Initialise le plugin.
 */
add_action('plugins_loaded', function () {
    // Auto désactivation si les plugins dont on a besoin ne sont pas activés
    $dependencies = ['DOCALIST_CORE', 'DOCALIST_SEARCH'];
    foreach($dependencies as $dependency) {
        if (! defined($dependency)) {
            return add_action('admin_notices', function() use ($dependency) {
                deactivate_plugins(plugin_basename(__FILE__));
                unset($_GET['activate']); // empêche wp d'afficher "extension activée"
                $dependency = ucwords(strtolower(strtr($dependency, '_', ' ')));
                $plugin = get_plugin_data(__FILE__, true, false)['Name'];
                echo "<div class='error'><p><b>$plugin</b> has been deactivated because it requires <b>$dependency</b>.</p></div>";
            });
        }
    }

    // Ok
    docalist('autoloader')->add(__NAMESPACE__, __DIR__ . '/class');
    docalist('services')->add('docalist-biblio', new Plugin());
});

/**
 * Activation du plugin.
 */
add_action('activate_docalist-biblio/docalist-biblio.php', function() {
    // Si docalist-core n'est pas dispo, on ne peut rien faire
    if (defined('DOCALIST_CORE')) {
        // plugins_loaded n'a pas encore été lancé, donc il faut initialiser
        // notre autoloader
        docalist('autoloader')->add(__NAMESPACE__, __DIR__ . '/class');
        (new Installer)->activate();
    }
});

/**
 * Désactivation du plugin.
*/
add_action('deactivate_docalist-biblio/docalist-biblio.php', function() {
    // Si docalist-core n'est pas dispo, on ne peut rien faire
    if (defined('DOCALIST_CORE')) {
        docalist('autoloader')->add(__NAMESPACE__, __DIR__ . '/class');
        (new Installer)->deactivate();
    }
});