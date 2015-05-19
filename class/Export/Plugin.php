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
use Docalist\Search\SearchResults;
use RuntimeException;

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
                set_transient(self::TRANSIENT, $request, 3600); // 10min
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

        // Affiche un message si on n'a aucune requête en cours
        $request = get_transient(self::TRANSIENT); /* @var $request SearchRequest */
        if ($request === false) {
            return $this->view('docalist-biblio-export:norequest');
        }

        // Exécute la requête
        $request->facet('_type', 100);
        $results = $request->execute('count'); /* @var $results SearchResults */

        // Affiche un message si on a aucune réponse
        if ($results->total() === 0) {
            return $this->view('docalist-biblio-export:nohits');
        }

        // Détermine la liste des types de notices qu'on va exporter
        $countByType = $types = [];
        foreach($results->facet('_type')->terms as $term) {
            $types[] = $term->term;
            $label = apply_filters('docalist_search_get_facet_label', $term->term, '_type');
            $countByType[$label] = $term->count;
        }

        // Récupère la liste des formats d'export possibles
        $formats = $this->formats($types);
        if (empty($formats)) {
            return $this->view('docalist-biblio-export:noformat', [
                'types' => $countByType,
                'total' => $results->total(),
                'max' => 100,
            ]);
        }

        // Initialise les options
        $mail = isset($_REQUEST['mail']) && $_REQUEST['mail'] === '1';
        $zip  = isset($_REQUEST['zip']) && $_REQUEST['zip'] === '1';
        $format = isset($_REQUEST['format']) ? $_REQUEST['format'] : null;
        !isset($formats[$format]) && $format = null; // vérifie que le format existe
        is_null($format) && $format=key($formats);

        // Affiche le texte d'introduction (nb de hits, types, limites, etc.)
        $content = $this->view('docalist-biblio-export:form', [
            'types' => $countByType,
            'total' => $results->total(),
            'max' => 100,
            'formats' => $formats,
            'format' => is_null($format) ? key($formats) : $format, // le premier par défaut
            'mail' => $mail,
            'zip' => $zip,
        ]);

        return $content;
    }

    protected function formats(array $types) {
        // Dans une table 'export-formats', on aura :
        // 'formats' => liste des formats définis = ['name', 'converter', 'converter-settings', 'exporter', 'exporter-settings']
        // Dans les settings, on aura pour chaque type indexé par docalist-search :
        // 'formats' => liste des formats autorisés pour ce type = ['format1', 'format2', ...]

        // Récupère la liste des formats d'export définis
        // TODO : depuis la table
        $formats = apply_filters('docalist_biblio_get_export_formats', []);
        if (empty($formats)) {
            throw new \RuntimeException(__("Aucun format d'export disponible", 'docalist-biblio'));
        }

        // on récupère un tableau de la forme
        // $formats['code-format'] = ['label' => ..., 'converter' => ..., 'exporter' => ... ]

        // Liste des formats autorisés pour chaque type
        // TODO : depuis les settings
        $formatsByType = [
            'post' => [
                'docalist-json-pretty',
                'docalist-xml',
                'docalist-xml-pretty',
            ],
            'page' => [
                'docalist-json-pretty',
//                 'docalist-xml-pretty',
            ],
            'dclrefprisme' => [
                'docalist-json',
                'docalist-json-pretty',
                'docalist-xml',
                'docalist-xml-pretty',
                'prisme2014-json',
                'prisme2014-json-pretty',
                'prisme2014-uppercase-json',
                'prisme2014-uppercase-json-pretty',
                'prisme2014-xml',
                'prisme2014-xml-pretty',
                'prisme2014-uppercase-xml',
                'prisme2014-uppercase-xml-pretty'
            ],
        ];

        foreach($types as $type) {
            if (! isset($formatsByType[$type])) {
                $formats = [];
                break;
            }
            $formats = array_intersect_key($formats, array_flip($formatsByType[$type]));
        }

        return $formats;
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