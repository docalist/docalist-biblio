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

use Docalist\AdminPage;
use Exception;

/**
 * Gestion des bases de données.
 */
class AdminDatabases extends AdminPage {
    /**
     * {@inheritdoc}
     */
    protected $defaultAction = 'DatabasesList';

    /**
     * {@inheritdoc}
     */
    protected $capability = [
        'default' => 'manage_options',
    ];

    /**
     * Les settings de docalist-biblio.
     *
     * @var Settings
     */
    protected $settings;

    /**
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;

        // @formatter:off
        parent::__construct(
            'docalist-biblio-databases',                // ID
            'options-general.php',                      // page parent
            __('Bases documentaires', 'docalist-biblio')    // libellé menu
        );
        // @formatter:on

        // Ajoute un lien "Réglages" dans la page des plugins
        $filter = 'plugin_action_links_docalist-biblio/docalist-biblio.php';
        add_filter($filter, function ($actions) {
            $action = sprintf(
                '<a href="%s" title="%s">%s</a>',
                esc_attr($this->url()),
                $this->menuTitle(),
                __('Réglages', 'docalist-biblio')
            );
            array_unshift($actions, $action);

            return $actions;
        });
    }

    /**
     * Retourne la base de données dont l'index est passé en paramètre.
     *
     * @param int $dbindex
     *
     * @return DatabaseSettings
     */
    protected function database($dbindex) {
        if (isset($this->settings->databases[$dbindex])) {
            return $this->settings->databases[$dbindex];
        }

        $title = __('Index de base de données invalide', 'docalist-biblio');
        $msg = __('La base %d n\'existe pas.', 'docalist-biblio');
        wp_die(sprintf($msg, $dbindex), $title);
    }

    /**
     * Retourne le type typeindex de la base de données dbindex.
     *
     * @param int $dbindex
     * @param int $typeindex
     *
     * @return TypeSettings
     */
    protected function type($dbindex, $typeindex) {
        $database = $this->database($dbindex);
        if (isset($database->types[$typeindex])) {
            return $database->types[$typeindex];
        }

        $title = __('Index de type invalide', 'docalist-biblio');
        $msg = __('Le type de notices %d n\'existe pas.', 'docalist-biblio');
        wp_die(sprintf($msg, $typeindex), $title);
    }

    /**
     * Met à jour les rewrite rules wordpress et retourne une redirection
     * vers l'action DatabasesList.
     *
     * Lorqu'une base de données est créée ou supprimée ou lorsque son slug
     * change, il faut mettre à jour les rewrite rules wordpress.
     *
     * Malheureusement, si une action appelle directement flush_rewrite_rules()
     * après avoir modifié les paramètres des bases, ça n'aura aucun effet car
     * wordpress a encore les "anciennes" rules : les objets Database ont déjà
     * été créés, les appels à register_post_type ont déjà été faits avec les
     * anciens slugs des bases, etc.
     *
     * Si on appelle flush_rewrite_rules() à ce stade, ça va regénérer
     * exactement les mêmes rules que celles qu'on avait avant de modifier les
     * paramètres des bases (ou alors il faudrait faire une espèce de
     * 'unregister_post_type" pour toutes les bases, puis les recréer, etc.)
     *
     * Pour régler le problème simplement, on passe par une redirection :
     * - Lorsqu'un slug est créé, modifié ou supprimé (actionDatabaseAdd,
     *   actionDatabaseEdit ou actionDatabaseDelete), on retourne une
     *   redirection vers actionRewriteRules().
     * - Lorsque actionRewriteRules() s'exécute, les "bons" settings sont relus,
     *   les objets Database sont recréés et les appels à registerPostType sont
     *   faits avec les bonnes valeurs.
     * - On se contente d'appeller flush_rewrite_rules() et on redirige vers
     *   actionDatabaseList().
     */
    public function actionRewriteRules() {
        flush_rewrite_rules(false);

        return $this->redirect($this->url('DatabasesList'), 303);
    }

    /**
     * Liste des bases de données.
     */
    public function actionDatabasesList() {
        return $this->view('docalist-biblio:database/list', [
            'databases' => $this->settings->databases
        ]);
    }

    /**
     * Nouvelle base de données.
     */
    public function actionDatabaseAdd() {
        $dbindex = count($this->settings->databases);
        $this->settings->databases[$dbindex] = array();

        return $this->actionDatabaseEdit($dbindex);
    }

    /**
     * Modifier les paramètres d'une base de données.
     *
     * @param int $dbindex Numéro de la base à éditer.
     */
    public function actionDatabaseEdit($dbindex) {
        // Vérifie que la base à éditer existe
        $database = $this->database($dbindex);

        // Requête POST : enregistre les paramètres de la base
        $error = '';
        if ($this->isPost()) {
            $oldSlug = $database->slug;

            // TODO: supprimer sequences si le nom a changé ?
            // ou plutôt : renommer ?
            try {
                $_POST = wp_unslash($_POST);
                $database->name = $_POST['name'];
                $database->label = $_POST['label'];
                $database->description = $_POST['description'];
                $database->slug = $_POST['slug'];

                $database->validate();
                $this->settings->save();

                // Met à jour les rewrite rules si le slug a changé
                if ($oldSlug !== $database->slug) {
                    return $this->redirect($this->url('RewriteRules'), 303);
                }

                return $this->redirect($this->url('DatabasesList'), 303);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Affiche le formulaire
        return $this->view('docalist-biblio:database/edit', [
            'database' => $database,
            'dbindex' => $dbindex,
            'error' => $error
        ]);
    }

    /**
     * Supprimer une base de données.
     *
     * @param int $dbindex Numéro de la base à supprimer.
     *
     * @param int $confirm
     */
    public function actionDatabaseDelete($dbindex, $confirm = false) {
        // Vérifie que la base à supprimer existe
        $database = $this->database($dbindex);

        // Demande confirmation
        if (! $confirm) {
            return $this->confirm(
                sprintf(__('La base de données <strong>%s (%s)</strong> va être supprimée.', 'docalist-biblio'), $database->label, $database->slug),
                __('Supprimer une base', 'docalist-biblio')
            );
        }

        // Supprime la base
        unset($this->settings->databases[$dbindex]);
        $this->settings->save();
//TODO : supprimer séquences
        // Met à jour les rewrite rules
        return $this->redirect($this->url('RewriteRules'), 303);
    }

    /**
     * Listes les types d'une base.
     *
     * @param int $dbindex Numéro de la base à éditer.
     */
    public function actionTypesList($dbindex) {
        // Vérifie que la base à modifier existe
        $database = $this->database($dbindex);

        // Liste des types
        return $this->view('docalist-biblio:type/list', [
            'database' => $database,
            'dbindex' => $dbindex
        ]);
    }

    /**
     * Ajoute un type dans une base.
     *
     * @param int $dbindex Numéro de la base à supprimer.
     * @param string $name Nom du type à ajouter
     */
    public function actionTypeAdd($dbindex, $name = null) {
        // Vérifie que la base à modifier existe
        $database = $this->database($dbindex);

        // Récupère la liste des types existants
        $types = apply_filters('docalist_biblio_get_types', array()); // code => defaults (array, path ou closure)

        // Récupère la liste des types qui existent déjà dans la base
        $selected = $database->typeNames();

        // Ecran "choix du type"
        if (empty($name)) {

            // Construit la liste tous les types qui ne sont pas déjà dans la base
            foreach($types as $name => $defaults) {
                if (isset($selected[$name])) {
                    unset($types[$name]);
                } else {
                    $types[$name] = apply_filters('docalist_biblio_get_type', $name);
                }
            }

            // Plus aucun type disponible
            if (empty($types)) {
                return $this->view('docalist-core:error', [
                    'h2' => __('Modifier une base', 'docalist-biblio'),
                    'h3' => __("Impossible d'ajouter un type de notice", 'docalist-biblio'),
                    'message' => __('La base contient déjà tous les types de notice possibles.', 'docalist-biblio'),
                ]);
            }

            // Choisit le type
            return $this->view('docalist-biblio:type/add', [
                'database' => $database,
                'dbindex' => $dbindex,
                'types' => $types,
            ]);
        }

        // Vérifie que le type choisi existe
        if (! isset($types[$name])) {
            $title = __('Type inexistant', 'docalist-biblio');
            $msg = __("Le type de notice %s n'existe pas.", 'docalist-biblio');
            wp_die(sprintf($msg, $name), $title);
        }

        // Vérifie que le type indiqué ne figure pas déjà dans la base
        if (isset($selected[$name])) {
            $title = __("Impossible d'ajouter ce type", 'docalist-biblio');
            $msg = __('Le type de notice %s est déjà dans la base.', 'docalist-biblio');
            wp_die(sprintf($msg, $name), $title);
        }

        // Ajoute le type
        $defaults = apply_filters('docalist_biblio_get_type', $name, false);
        $database->types[] = $defaults;
        $this->settings->save();

        return $this->redirect($this->url('TypesList', $dbindex), 303);
    }

    /**
     * Edite un type
     *
     * @param int $dbindex Base à éditer
     * @param int $typeindex Type à éditer
     */
    public function actionTypeEdit($dbindex, $typeindex) {
        $database = $this->database($dbindex);
        $type = $this->type($dbindex, $typeindex);

        if ($this->isPost()) {
            $_POST = wp_unslash($_POST);

            $type->label = $_POST['label'];
            $type->description = $_POST['description'];

            $this->settings->save();

            return $this->redirect($this->url('TypesList', $dbindex), 303);
        }

        return $this->view('docalist-biblio:type/edit', [
            'dbindex' => $dbindex,
            'typeindex' => $typeindex,
            'database' => $database,
            'type' => $type,
        ]);
    }

    /**
     * Supprime un type.
     *
     * @param int $dbindex Base à éditer
     * @param int $typeindex Type à supprimer
     */
    public function actionTypeDelete($dbindex, $typeindex, $confirm = false) {
        $database = $this->database($dbindex);
        $type = $this->type($dbindex, $typeindex);

        // Demande confirmation
        if (! $confirm) {
            return $this->confirm(
                sprintf(__('Le type <strong>%s</strong> va être supprimé de la base <strong>%s</strong>. Tous les paramètres de ce type (propriétés, grille de saisie...) vont être perdus.', 'docalist-biblio'), $type->label, $database->label),
                __('Supprimer un type', 'docalist-biblio')
            );
        }

        // Supprime le type
        unset($this->settings->databases[$dbindex]->types[$typeindex]);
        $this->settings->save();

        // Retourne à la liste des types
        return $this->redirect($this->url('TypesList', $dbindex), 303);
    }

    /**
     * Modifie la grille de saisie d'un type.
     *
     * @param int $dbindex Base à éditer
     * @param int $typeindex Type à éditer
     */
    public function actionTypeFields($dbindex, $typeindex) {
        $database = $this->database($dbindex);
        $type = $this->type($dbindex, $typeindex);

        if ($this->isPost()) {
            $type->fields = wp_unslash($_POST);
            $this->settings->save();

            return $this->redirect($this->url('TypesList', $dbindex), 303);
        }

        return $this->view('docalist-biblio:type/fields', [
            'dbindex' => $dbindex,
            'typeindex' => $typeindex,
            'database' => $database,
            'type' => $type,
        ]);
    }
}