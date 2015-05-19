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

use WP_Query;
use Docalist\Search\SearchRequest;
use Docalist\Http\ViewResponse;

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

    /**
     * Nom du transient utilisé pour stocker la dernière requête docalist-search
     * exécutée.
     *
     * @var string
     */
    const TRANSIENT = 'docalist-biblio-export-last-request';

    public function __construct() {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-biblio-export', false, 'docalist-biblio-export/languages');

        // Charge la configuration du plugin
        $this->settings = new Settings(docalist('settings-repository'));

        // Crée la page de réglages du plugin
        add_action('admin_menu', function() {
            new SettingsPage($this->settings);
        });

        // Déclare le widget "Export notices"
        add_action('widgets_init', function() {
            register_widget('Docalist\Biblio\Export\ExportWidget');
        });

        add_filter('docalist_search_create_request', function(SearchRequest $request = null, WP_Query $query) {
            // Stocke la dernière requête exécutée par docalist-search
            if ($request && $request->isSearch()) {
                set_transient(self::TRANSIENT, $request, 600); // 10min
            }

            // Déclenche l'export si on est sur la page "export"
            if ($query->is_main_query() && $query->is_page && $query->get_queried_object_id() === $this->exportPage()) {
                $this->export();
            }

            return $request;
        }, 9999, 2); // priorité haute pour être le dernier

        // Déclare nos assets
//         require_once dirname(__DIR__) . '/assets/register.php';
    }

    /**
     * Retourne les paramètres du plugin.
     *
     * @return Settings
     */
    public function settings() {
        return $this->settings;
    }

    /**
     * Retourne l'ID de la page "export" indiquée dans les paramètres du plugin.
     *
     * @return int
     */
    public function exportPage() {
        return $this->settings->exportpage();
    }

    /**
     * Gère l'export.
     *
     * Teste si on a tous les paramètres requis, affiche le formulaire si ce
     * n'est pas le cas, lance l'export sinon.
     */
    public function export() {
        $ok = isset($_REQUEST['go']);
        if ($ok) {
            die('export généré');
        }
        add_filter('the_content', [$this, 'injectForm'], 9999); // priorité très haute pour ignorer wp_autop et cie.
        add_filter('the_excerpt', [$this, 'injectForm'], 9999); // priorité très haute pour ignorer wp_autop et cie.

    }

    /**
     * Injecte le formulaire d'export dans le contenu ou l'extrait passé en
     * paramètre.
     *
     * Cete méthode est appellée via the_content()/the_excerpt().
     *
     * @param string $content Le contenu de la page "export".
     *
     * @return string Le contenu de la page "export" si on n'a rien à exporter,
     * le formulaire d'export sinon.
     */
    public function injectForm($content) {
        global $post;

        // Vérifie que c'est bien notre page
        if ($post->ID !== $this->exportPage()) {
            return $content;
        }

        $request = get_transient(self::TRANSIENT);
        if ($request === false) {
            return $this->view('docalist-biblio-export:norequest');
        }

        var_dump($request);
        return 'formulaire export';
    }

    /**
     * Exécute la vue indiquée et retourne le contenu généré
     * @param string $view Nom de la vue.
     * @param array $viewArgs Paramètres à passer à la vue.
     */
    protected function view($view, array $viewArgs = []){
        !isset($viewArgs['this']) && $viewArgs['this'] = $this;

        $view = new ViewResponse($view, $viewArgs);

        return $view->getContent();
    }
}