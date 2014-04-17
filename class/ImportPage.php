<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio;

use Docalist\Biblio\Entity\Reference;
use Docalist\AdminPage;
use Docalist\Data\Schema\Schema;
use Docalist\Data\Schema\Field;
use Docalist\Utils;
use Docalist\Http\ViewResponse;
use Docalist\Http\CallbackResponse;

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

        // On retourne une réponse de type "callback" qui lance l'import
        // lorsqu'elle est générée (import_start, error, progress, done)
        $response = new CallbackResponse(function() use($files, $options) {
            // Permet au script de s'exécuter longtemps
            ignore_user_abort(true);
            set_time_limit(3600);

            // Supprime la bufferisation pour voir le suivi en temps réel
            while(ob_get_level()) ob_end_flush();

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
            return $this->confirm('Toutes les notices vont être supprimées.');
        }

        echo __('<p>Suppression en cours...</p>', 'docalist-search');

        $count = $this->database->deleteAll();

        $msg = __('<p>%d notice(s) supprimée(s).</p>', 'docalist-search');

        printf($msg, $count);
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

        $this->doc($schema->fields(), 0, $maxlevel);
        echo '</table>';
    }

    protected function doc(array $fields, $level, $maxlevel) {
        // var_dump($schema);

        /* @var $field Field */
        foreach($fields as $field) {
            echo '<tr>';

            //$level && printf('<td colspan="%d">x</td>', $level);
            for ($i = 0; $i < $level; $i++) {
                echo '<td></td>';
            }

            $repeat = $field->repeatable() ? __('<b>Répétable</b>', 'docalist-biblio') : __('Monovalué', 'docalist-biblio');
            $type = $field->entity() ? Utils::classname($field->entity()) : $field->type();
            $msg = '<th colspan="%1$d"><h%2$d style="margin: 0">%3$s</h%2$d></th><td class="row-title">%4$s</td><td><i>%5$s</i></td><td>%6$s</td><td>%7$s</td>';
            printf($msg,
                $maxlevel - $level,     // %1
                $level + 3,             // %2
                $field->name(),         // %3
                $field->label(),        // %4
                $field->description(),  // %5
                $type,         // %6
                $repeat // %7
            );

            echo '</tr>';

            $subfields = $field->fields();
            $subfields && $this->doc($subfields, $level + 1, $maxlevel);
        }
    }
}