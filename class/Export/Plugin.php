<?php
/**
 * This file is part of the "Docalist Biblio Export" plugin.
 *
 * Copyright (C) 2015-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */
namespace Docalist\Biblio\Export;

/**
 * Extension pour Docalist Biblio : génère des fichiers d'export et des
 * bibliographies.
 */
class Plugin {
    /**
     * Les paramètres du plugin.
     *
     * @var Settings
     */
    protected $settings;

    public function __construct() {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-biblio-export', false, 'docalist-biblio-export/languages');

        // Charge la configuration du plugin
//         $this->settings = new Settings(docalist('settings-repository'));

        // Crée la page de réglages du plugin
//         add_action('admin_menu', function() {
//             new SettingsPage($this->settings);
//         });

        // Déclare le widget "Export notices"
//         add_action('widgets_init', function() {
//             register_widget('Docalist\Biblio\Export\ExportWidget');
//         });

        // Déclare nos assets
//         require_once dirname(__DIR__) . '/assets/register.php';
    }
}