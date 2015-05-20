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

    /**
     * La requête docalist-search contenant les notices à exporter.
     *
     * Initialisé par checkParams().
     *
     * @var SearchRequest
     */
    protected $request;

    /**
     * Le format d'export à utiliser.
     *
     * Initialisé par checkParams().
     *
     * @var Format
     */
    protected $format;

    /**
     * Indique s'il faut compresser le fichier généré.
     *
     * Initialisé par checkParams().
     *
     * @var boolean
     */
    protected $zip;

    /**
     * Indique s'il faut envoyer le fichier par e-mail.
     *
     * Initialisé par checkParams().
     *
     * @var boolean
     */
    protected $mail;


    /**
     * Initialise le plugin.
     */
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

        // Stocke la dernière requête exécutée par docalist-search dans un
        // transient. On utilise une priorité haute pour laisser la possibilité
        // à tous les autres plugins de créer la requête.
        // On en profite pour tester si on est sur la page "export" (c'est plus
        // simple de le faire içi plutôt que d'intercepter parse_query et ça
        // fait un filtre en moins) et si c'est le cas, on vérifie les
        // paramètres indiqués et on déclenche l'export.
        add_filter('docalist_search_create_request', function(SearchRequest $request = null, WP_Query $query) {
            // Stocke la SearchRequest
            if ($request && $request->isSearch()) {
                set_transient(self::TRANSIENT, $request, 24 * HOUR_IN_SECONDS);
            }

            // Déclenche l'export si on est sur la page "export"
            if ($query->is_main_query() && $query->is_page && $query->get_queried_object_id() === $this->exportPage()) {
                $view = $this->checkParams(); // true si ok, une View sinon
                if ($view === true) {
                    $this->export();
                } else {
                    $this->showView($view);
                }
            }

            return $request;
        }, 9999, 2);
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
     * Teste si on a tous les paramètres requis pour pouvoir lancer l'export.
     *
     * @return true|ViewResponse Retourne true si on a tout ce qu'il faut et
     * que tout est ok, sinon retourne une vue contenant le formulaire "choix
     * du format d'export" ou un message à afficher à l'utilisateur.
     */
    protected function checkParams() {
        // Affiche un message si on n'a aucune requête en cours
        $request = get_transient(self::TRANSIENT); /* @var $request SearchRequest */
        if ($request === false) {
            return $this->view('docalist-biblio-export:norequest');
        }

        // Exécute la requête
        $request->facet('_type', 100);
        $request->size(2);
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

        // Récupère les options transmises en paramètres
        $mail = isset($_REQUEST['mail']) && $_REQUEST['mail'] === '1';
        $zip  = isset($_REQUEST['zip']) && $_REQUEST['zip'] === '1';
        $format = isset($_REQUEST['format']) ? $_REQUEST['format'] : null;
        $go  = isset($_REQUEST['go']) && $_REQUEST['go'] === '1';

        // Vérifie que le format indiqué figure dans la liste des formats possibles
        isset($format) && !isset($formats[$format]) && $format = null;

        // Si tout est ok, retourne true
        if (isset($format) && $go) {
            $this->request = $request;
            $this->mail = $mail;
            $this->zip = $zip;
            $this->format = $formats[$format];

            return true;
        }

        // Sinon, affiche le formulaire "choix du format"
        return $this->view('docalist-biblio-export:form', [
            'types' => $countByType,
            'total' => $results->total(),
            'max' => 100,
            'formats' => $formats,
            'format' => is_null($format) ? key($formats) : $format, // le premier par défaut
            'mail' => $mail,
            'zip' => $zip,
        ]);
    }

    /**
     * Injecte le contenu généré par la vue passée en paramètres dans la page
     * "export".
     *
     * @param ViewResponse $view La vue à exécutr.
     */
    protected function showView(ViewResponse $view) {
        $injectView = function ($content) use ($view) {
            global $post;

            // Vérifie que c'est bien notre page
            if ($post->ID !== $this->exportPage()) {
                return $content;
            }

            // Exécute la vue et retourne le contenu généré
            return $view->getContent();
        };

        // On ne sait pas si le thème utilise the_content() ou the_excerpt()
        // donc on intercepte les deux, en utilisant une priorité très haute
        // pour court-circuiter wp_autop et compagnie.
        add_filter('the_content', $injectView, 9999);
        add_filter('the_excerpt', $injectView, 9999);
    }

    /**
     * Lance l'export.
     *
     * Teste si on a tous les paramètres requis, affiche le formulaire si ce
     * n'est pas le cas, lance l'export sinon.
     */
    protected function export() {
        // Permet au script de s'exécuter longtemps
        set_time_limit(3600);

        $mode='display'; // TODO
        $disposition = ($mode==='display') ? 'inline' : 'attachment';

        $this->format->export($this->request, $disposition);

        die('export généré');
    }

    /**
     * Retourne la liste des formats disponibles pour les types indiqués.
     *
     * @param array $types La liste des types.
     *
     * @throws RuntimeException
     *
     * @return Format[]
     */
    protected function formats(array $types) {
        // Dans une table 'export-formats', on aura :
        // 'formats' => liste des formats définis = ['name', 'converter', 'converter-settings', 'exporter', 'exporter-settings']
        // Dans les settings, on aura pour chaque type indexé par docalist-search :
        // 'formats' => liste des formats autorisés pour ce type = ['format1', 'format2', ...]

        // Récupère la liste des formats d'export définis
        // TODO : depuis la table
        $formats = apply_filters('docalist_biblio_get_export_formats', []);
        if (empty($formats)) {
            throw new RuntimeException(__("Aucun format d'export disponible", 'docalist-biblio'));
        }
        // on récupère un tableau de la forme 'format' => params

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
                'docalist-xml-pretty',
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

        // Conserve uniquement les formats qui sont communs à tous les types qu'on a
        foreach($types as $type) {
            if (! isset($formatsByType[$type])) {
                $formats = [];
                break;
            }
            $formats = array_intersect_key($formats, array_flip($formatsByType[$type]));
        }

        // Instancie les formats qui restent
        foreach($formats as $name => & $format) {
            $format = new Format($name, $format);
        }

        // Ok
        return $formats;
    }

    /**
     * Exécute la vue indiquée et retourne le contenu généré.
     *
     * @param string $view Nom de la vue.
     * @param array $viewArgs Paramètres à passer à la vue.
     *
     * @return string
     */
    protected function view($view, array $viewArgs = []){
        !isset($viewArgs['this']) && $viewArgs['this'] = $this;

        return new ViewResponse($view, $viewArgs);
    }
}