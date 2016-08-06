<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Pages;

use Docalist\Biblio\Database;
use Docalist\Biblio\Reference;
use Docalist\AdminPage;
use Docalist\Schema\Schema;
use Docalist\Http\ViewResponse;
use Docalist\Http\CallbackResponse;
use Docalist\Search\SearchRequest;
use Docalist\Biblio\Reference\ReferenceIterator;
use Docalist\Biblio\Export\Converter;
use Docalist\Biblio\Export\Exporter;

/**
 * Page "Importer" d'une base
 */
class ImportPage extends AdminPage {

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
        parent::__construct(
            'import-' . $database->postType(),              // ID
            'edit.php?post_type=' . $database->postType(),  // Page parent
            __('Gérer', 'docalist-biblio')                  // Libellé du menu
        );
        $this->database = $database;
    }

    /**
     * Importe un ou plusieurs fichiers dans la base.
     *
     * Ce module utilise le gestionnaire de médias de WordPress. La page
     * affichée permet à l'utilisateur de choisir un fichier existant depuis
     * la bibliothèque de médias ou de télécharger un nouveau fichier.
     *
     * L'utilisateur peut ajouter plusieurs fichiers à charger. Il indique pour
     * chaque fichier le convertisseur à utiliser et lance l'import.
     *
     * @param array $ids Tableau contenant les ID (dans la bibliothèque de
     * médias de WordPress) des fichiers à importer dans la base.
     *
     * @param array $formats Tableau indiquant, pour chaque fichier, le nom de
     * code du convertisseur à utiliser.
     *
     * @param array $options Un tableau d'options
     *
     * @return ViewResponse|CallBackResponse
     */
    public function actionImport(array $ids = null, array $formats = null, array $options = []) {
        // Récupère la liste des importeurs disponibles
        // Le filtre retourne un tableau de la forme
        // Nom de code de l'importeur => libellé de l'importeur
        $importers = apply_filters('docalist_biblio_get_importers', [], $this->database);
        if (empty($importers)) {
            return $this->view('docalist-core:error', [
                'h2' => __('Importer un fichier', 'docalist-biblio'),
                'h3' => __("Aucun importeur disponible", 'docalist-biblio'),
                'message' => sprintf(__("Aucun format d'import n'est disponible.", 'docalist-biblio')),
            ]);
        }

        // Permet à l'utilisateur d'uploader et de choisir les fichiers à charger
        if (empty($ids)) {
            return $this->view('docalist-biblio:import/choose', [
                'database' => $this->database,
                'settings' => $this->database->settings(),
                'converters' => $importers,
            ]);
        }

        // Vérifie les fichiers indiqués
        $files = [];
        foreach($ids as $index => $id) {
            // Récupère le path du fichier attaché
            $path = get_attached_file($id);
            if (empty($path) || ! file_exists($path)) {
                return $this->view('docalist-core:error', [
                    'h2' => __('Importer un fichier', 'docalist-biblio'),
                    'h3' => __("Fichier non trouvé", 'docalist-biblio'),
                    'message' => sprintf(__("Le fichier %s n'existe pas.", 'docalist-biblio'), $id),
                ]);
            }

            // Vérifie le format indiqué
            if (empty($formats[$index]) || !isset($importers[$formats[$index]])) {
                return $this->view('docalist-core:error', [
                    'h2' => __('Importer un fichier', 'docalist-biblio'),
                    'h3' => __("Convertisseur incorrect", 'docalist-biblio'),
                    'message' => sprintf(__("Le convertisseur indiqué pour le fichier %s n'est pas valide.", 'docalist-biblio'), $id),
                ]);
            }

            $files[$path] = $formats[$index];
        }

        // Vérifie les options
        $options['simulate'] = isset($options['simulate']);
        !isset($options['status']) && $options['status'] = 'pending';
        $options['importref'] = isset($options['importref']) && $options['importref'] === '1';
        $options['limit'] = isset($options['limit']) ? (int) $options['limit'] : 0;

        // On retourne une réponse de type "callback" qui lance l'import
        // lorsqu'elle est générée (import_start, error, progress, done)
        $response = new CallbackResponse(function() use($files, $options) {
            // Permet au script de s'exécuter longtemps
            ignore_user_abort(true);
            set_time_limit(3600);

            // Supprime la bufferisation pour voir le suivi en temps réel
            while(ob_get_level()) ob_end_flush();

//             Susceptible d'être plus rapide avec une base innodb, à tester.
//             global $wpdb;
//             $wpdb->query('SET autocommit=0');
//             wp_suspend_cache_addition(true);
//             wp_suspend_cache_invalidation(true);

            // Pour suivre le déroulement de l'import, on affiche une vue qui
            // installe différents filtres sur les événements déclenchés
            // pendant l'import.
            $this->view('docalist-biblio:import/import')->sendContent();

            // Début de l'import
            do_action('docalist_biblio_before_import', $files, $this->database, $options);

            // Importe tous les fichiers dans l'ordre indiqué
            foreach($files as $file => $importer) {
                // Début de l'import du fichier
                do_action('docalist_biblio_import_start', $file, $options);

                // Détermine l'action à invoquer pour cet importeur
                $tag = "docalist_biblio_import_{$importer}";

                // Vérifie qu'il y a bien un callback derrière
                if (! has_action($tag)) {
                    $msg = __("L'importeur %s n'est pas installé correctement, impossible d'importer le fichier.", 'docalist-biblio');
                    do_action('docalist_biblio_import_error', sprintf($msg, $importer));
                }

                // Lance l'importeur
                else {
                    do_action($tag, $file, $this->database, $options);
                }

                // Fin de l'import du fichier
                do_action('docalist_biblio_import_done', $file, $options);
            }

            // Fin de l'import
            do_action('docalist_biblio_after_import', $files, $this->database, $options);
        });

        // Indique que notre réponse doit s'afficher dans le back-office wp
        $response->adminPage(true);

        // Terminé
        return $response;
    }

    public function actionDeleteAll($confirm = false) {
        if (! $confirm) {
            return $this->view(
                'docalist-biblio:delete-all/confirm',
                [ 'database' => $this->database ]
            );
        }

        // On retourne une réponse de type "callback" qui lance la suppression
        // lorsqu'elle est générée.
        $response = new CallbackResponse(function() {
            // Permet au script de s'exécuter longtemps
            ignore_user_abort(true);
            set_time_limit(3600);

            // Supprime la bufferisation pour voir le suivi en temps réel
            while(ob_get_level()) ob_end_flush();

            // Pour suivre le déroulement, on affiche une vue qui installe
            // différents filtres sur les événements déclenchés pendant la
            // suppression.
            $this->view('docalist-biblio:delete-all/delete-all')->sendContent();

            // Lance la suppression
            $this->database->deleteAll();

        });

        // Indique que notre réponse doit s'afficher dans le back-office wp
        $response->adminPage(true);

        // Terminé
        return $response;
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

        $this->doc($schema->getFields(), 0, $maxlevel);
        echo '</table>';
    }

    protected function doc(array $fields, $level, $maxlevel) {
        // var_dump($schema);

        /* @var Schema $field */
        foreach($fields as $field) {
            echo '<tr>';

            //$level && printf('<td colspan="%d">x</td>', $level);
            for ($i = 0; $i < $level; $i++) {
                echo '<td></td>';
            }

            $repeat = $field->repeatable() ? __('<b>Répétable</b>', 'docalist-biblio') : __('Monovalué', 'docalist-biblio');
            $msg = '<th colspan="%1$d"><h%2$d style="margin: 0">%3$s</h%2$d></th><td class="row-title">%4$s</td><td><i>%5$s</i></td><td>%6$s</td><td>%7$s</td>';
            printf($msg,
                $maxlevel - $level,     // %1
                $level + 3,             // %2
                $field->name(),         // %3
                $field->label(),        // %4
                $field->description(),  // %5
                $field->type(),         // %6
                $repeat // %7
            );

            echo '</tr>';

            $subfields = $field->getFields();
            $subfields && $this->doc($subfields, $level + 1, $maxlevel);
        }
    }

    /**
     * Exporte un lot de notices.
     *
     * @param array $queryArgs Paramètres de la requête Docalist Search à
     * exécuter.
     * @param string $format Nom du format d'export à utiliser
     */
    public function actionExport($format = null, $mode = 'download', $zip = false) {
        // Essaie de construire une requête avec les arguments en cours
        $args = $_REQUEST;
        unset($args['page']); // nom de la page admin / numéro de page de résultats
        unset($args['m']); // nom de l'action
        unset($args['post_type']); // type
        unset($args['format']); // nom du format
        unset($args['mode']); // download, display ou mail
        unset($args['zip']); // faire un zip

        $request = new SearchRequest($args);

        // Si la requête est vide, demande à l'utilisateur de saisir une équation
        if (empty($args)) {
            return $this->view('docalist-biblio:export/choose-refs', [
                'database' => $this->database,
                'format' => $format,
            ]);
        }

        // Ajoute le filtre type dans la requête
        $type = $this->database->postType();
        if (! in_array($type, (array) $request->filter('_type'))) {
            $request->filter('_type', $type);
        }

        // Exécute la requête
        $request->size(1);
        $results = $request->execute('count');

        // Si on a zéro réponses, corrige l'équation de recherche
        if (0 === $results->getHitsCount()) {
            return $this->view('docalist-biblio:export/choose-refs', [
                'database' => $this->database,
                'format' => $format,
                'error'    => __("Aucune notice ne correspond aux critères de recherche indiqués.", 'docalist-biblio')
            ]);
        }

        // Récupère la liste des exporteurs disponibles
        $formats = apply_filters('docalist_biblio_get_export_formats', [], $this->database);
        if (empty($formats)) {
            return $this->view('docalist-core:error', [
                'h2' => __('Exporter des notices', 'docalist-biblio'),
                'h3' => __("Aucun format d'export disponible", 'docalist-biblio'),
                'message' => sprintf(__("Aucun format d'export n'est disponible.", 'docalist-biblio')),
            ]);
        }

        // Permet à l'utilisateur de choisir le format d'export
        if (empty($format)) {
            return $this->view('docalist-biblio:export/choose-exporter', [
                'database' => $this->database,
                'formats' => $formats,
                'args' => $args,
            ]);
        }

        // Vérifie que le format d'export indiqué existe
        if (!isset($formats[$format])) {
            return $this->view('docalist-core:error', [
                'h2' => __('Exporter des notices', 'docalist-biblio'),
                'h3' => __("Format d'export incorrect", 'docalist-biblio'),
                'message' => sprintf(__("Le format d'export indiqué (%s) n'est pas valide.", 'docalist-biblio'), $format),
            ]);
        }
        $name = $format;
        $format = $formats[$format];

        // Vérifie que le format indique le nom du convertisseur à utiliser
        if (!isset($format['converter'])) {
            return $this->view('docalist-core:error', [
                'h2' => __('Exporter des notices', 'docalist-biblio'),
                'h3' => __("Format d'export incorrect", 'docalist-biblio'),
                'message' => sprintf(__("Aucun convertisseur indiqué dans le format %s.", 'docalist-biblio'), $name),
            ]);
        }
        $converter = $format['converter'];

        // Vérifie que le format indique le nom de l'exporter à utiliser
        if (!isset($format['exporter'])) {
            return $this->view('docalist-core:error', [
                'h2' => __('Exporter des notices', 'docalist-biblio'),
                'h3' => __("Format d'export incorrect", 'docalist-biblio'),
                'message' => sprintf(__("Aucune exporteur indiqué dans le format %s.", 'docalist-biblio'), $name),
            ]);
        }
        $exporter = $format['exporter'];

        // Crée le convertisseur
        $settings = isset($format['converter-settings']) ? $format['converter-settings'] : [];
        $converter = new $converter($settings); /* @var Converter $converter */

        // Crée l'exporteur
        $settings = isset($format['exporter-settings']) ? $format['exporter-settings'] : [];
        $exporter = new $exporter($converter, $settings); /* @var Exporter $exporter */

        // Crée l'itérateur
        $iterator = new ReferenceIterator($request);

        // Crée une réponse de type "callback" qui lancera l'export
        $response = new CallbackResponse(function() use($exporter, $iterator) {
            // Permet au script de s'exécuter longtemps
            set_time_limit(3600);

            // Exporte les notices
            $exporter->export($iterator);
        });

        $disposition = ($mode==='display') ? 'inline' : 'attachment';

        $response->headers->set('Content-Type', $exporter->contentType());
        $response->headers->set('Content-disposition', $exporter->contentDisposition($disposition));
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    public function actionShowSettings() {
        return $this->view('docalist-core:info', [
            'h2' => __('Settings', 'docalist-biblio'),
//            'message' => '<pre>' . (string)$this->database->settings() . '</pre>',
            'message' => '<pre>' . var_export($this->database->settings(),true) . '</pre>',
        ]);
    }
}