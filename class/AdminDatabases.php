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
            __('Docalist Biblio', 'docalist-biblio')    // libellé menu
        );
        // @formatter:on
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

        // Affiche le formulaire
        if (! $this->isPost()) {
            return $this->view('docalist-biblio:database/edit', [
                'database' => $database,
                'dbindex' => $dbindex,
            ]);
        }

        // Enregistre les paramètres de la base
        $_POST = wp_unslash($_POST);
        $database->name = $_POST['name'];
        $database->label = $_POST['label'];
        $database->description = $_POST['description'];
        $database->slug = $_POST['slug'];

        $this->settings->save();

        return $this->redirect($this->url('DatabasesList'), 303);
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

        return $this->redirect($this->url('DatabasesList'), 303);
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
        $types = apply_filters('docalist_biblio_get_types', array()); // code => class

        // Récupère la liste des types qui existent déjà dans la base
        $selected = $database->typeNames();

        // Ecran "choix du type"
        if (empty($name)) {

            // Construit la liste tous les types qui ne sont pas déjà dans la base
            foreach($types as $name => $class) {
                if (isset($selected[$name])) {
                    unset($types[$name]);
                } else {
                    $types[$name] = new $class();
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
        $class = $types[$name];
        $type = new $class();
        $database->types[] = $type->toArray();
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

        //TODO : if is post!!

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
    public function actionTypeDelete($dbindex, $typeindex) {
        $database = $this->database($dbindex);
        $type = $this->type($dbindex, $typeindex);
// TODO : confirmation
// TODO : toutes les actions devraient être en protected
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