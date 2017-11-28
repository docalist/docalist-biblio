<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Pages;

use Docalist\AdminPage;
use Exception;
use Docalist\Biblio\Database;
use Docalist\Biblio\Settings\Settings;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Biblio\Settings\TypeSettings;
use Docalist\Schema\Schema;
use WP_Role;
use Docalist\Biblio\Type;
use Docalist\Biblio\Grid;

/**
 * Gestion des bases de données.
 */
class AdminDatabases extends AdminPage
{
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

    protected function getDefaultAction()
    {
        return 'DatabasesList';
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
        $msg = __('La base <b>%s</b> n\'existe pas.', 'docalist-biblio');
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
        $msg = __(
            'Le type de notices <b>%s</b> n\'existe pas dans la base <b>%s</b>.',
            'docalist-biblio'
        );
        wp_die(sprintf($msg, $typeindex, $dbindex), $title);
    }

    /**
     * Met à jour les rewrite rules wordpress et retourne une redirection
     * vers l'action DatabasesList.
     *
     * Lorqu'une base de données est créée ou supprimée ou lorsque sa page
     * d'accueil change, il faut mettre à jour les rewrite rules wordpress.
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
     * - Lorsqu'un base est créée, modifiée ou supprimée (actionDatabaseAdd,
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
        $name = ''; // n'existe pas déjà car on ne peut pas enregistrer une base avec un nom vide
        $this->settings->databases[] = ['name' => $name];
        return $this->actionDatabaseEdit($name);
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
            $oldHome = $database->homepage();
            $oldMode = $database->homemode();

            // TODO: supprimer sequences si le nom a changé ?
            // ou plutôt : renommer ?
            try {
                $_POST = wp_unslash($_POST);
                $database->name = $_POST['name'];
                $database->label = $_POST['label'];
                $database->description = $_POST['description'];
                $database->homepage = (int) $_POST['homepage'];
                $database->homemode = $_POST['homemode'];
                $database->searchpage = (int) $_POST['searchpage'];
                $database->stemming = $_POST['stemming'];
                $database->icon = $_POST['icon'];
                $database->notes = $_POST['notes'];
                $database->thumbnail = (bool)$_POST['thumbnail'];
                $database->revisions = (bool)$_POST['revisions'];
                $database->comments = (bool)$_POST['comments'];
                empty($database->creation) && $database->creation = date_i18n('Y/m/d H:i:s');
                $database->lastupdate = date_i18n('Y/m/d H:i:s');

                $database->validate();

                // vérifie unicité nom/homepage (https://github.com/daniel-menard/prisme/issues/181)
                foreach ($this->settings->databases as $name => $db) { /** @var Database $db */
                    if ($name === $dbindex) {
                        continue;
                    }

                    if ($database->name() === $db->name()) {
                        $msg = __('Il existe déjà une base avec le nom "%s"', 'docalist-biblio');
                        throw new Exception(sprintf($msg, $db->name()));
                    }

                    if ($database->homepage() && $database->homepage() === $db->homepage()) {
                        $msg = __('La page d\'accueil indiquée est déjà utilisée par "%s"', 'docalist-biblio');
                        throw new Exception(sprintf($msg, $db->label()));
                    }

                    if (trim(strtolower($database->label())) === trim(strtolower($db->label()))) {
                        $msg = __('Il existe déjà une base avec le même libellé (%s), vous allez vous mélanger les pinceaux !', 'docalist-biblio');
                        throw new Exception(sprintf($msg, $db->label()));
                    }
                }

                $this->settings->save(); // refreshKeys

                // Attribue au groupe Administrateurs tous les droits sur cette base
                if ($dbindex === '') { // nouvelle base
                    $this->setupCapacities($database, true);
                }

                // TODO : modifier les droits si le nom a changé ?
                // elseif ($name !== $dbindex) {
                // - Faire un tableau de conversion "ancienne cap" => "new cap"
                // - parcourir tous les rôles (et les utilisateurs ?)
                // - mettre à jour les caps
                // }
                // Ou alors : afficher une alerte/une info à l'administrateur ?

                // Met à jour les rewrite rules si la homepage a changé
                if ($oldHome !== $database->homepage() || $oldMode != $database->homemode()) {
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
     * Accorde ou enlève les droits sur la base au groupe Administrateurs.
     *
     * @param DatabaseSettings $database La base pour laquelle il faut accorder
     * les droits.
     * @param bool $grant true : ajoute les droits, false : les supprime
     */
    private function setupCapacities(DatabaseSettings $database, $grant = true) {
        // Récupère la liste des droits à attribuer
        $capabilities = $database->capabilities();

        // Détermine les rôles auxquels on va accorder ou enlever ces droits
        $roles = ['administrator'];

        // Récupère le suffixe utilisé pour les primary capabilities
        $primary = $database->capabilitySuffix() . 's';

        /*
         * Explication :
         * On ne veut changer que les capacités propres à docalist-biblio (par
         * exemple, on ne veut pas modifier le droit standard "read" de WP), et
         * uniquement celles qui sont des "primitive capacities" (il ne faut
         * pas attribuer une "meta capability" directement à un rôle).
         * Pour cela, on ne traiter que celles qui se terminent par le préfixe
         * des primary capabilities (par exemple "*dbprisme_refs").
         */

        // Attribue les droits à tous les rôles indiqués
        foreach ($roles as $role) {
            $role = get_role($role); /** @var WP_Role $role */
            foreach($capabilities as $capability) {
                if (substr($capability, -strlen($primary)) === $primary) {
                    $grant ? $role->add_cap($capability) : $role->remove_cap($capability);
                }
            }
        }
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
            $msg = __(
                'La base de données <strong>%s (%s)</strong> va être supprimée.',
                'docalist-biblio'
            );
            return $this->confirm(
                sprintf($msg, $database->label(), $database->slug()),
                __('Supprimer une base', 'docalist-biblio')
            );
        }

        // Supprime la base
        unset($this->settings->databases[$dbindex]);
        $this->settings->save();

        // Supprimer la séquence utilisée pour cette base
        docalist('sequences')->clear($database->postType());

        // Supprime les droits accordés lors de la création de la base
        // NB : Si l'utilisateur a ensuite accordé les droits à d'autres groupes
        // ou à d'autres utilisateurs, il doit les supprimer manuellement.
        $this->setupCapacities($database, false);

        // Met à jour les rewrite rules
        return $this->redirect($this->url('RewriteRules'), 303);
    }

    /**
     * Exporte (affiche) les paramètres complets d'une base.
     *
     * @param string $dbindex
     */
    public function actionDatabaseExportSettings($dbindex, $pretty = false) {
        // Vérifie que la base à éditer existe
        $database = $this->database($dbindex);

        // Affiche le formulaire
        return $this->view('docalist-biblio:database/export-settings', [
            'database' => $database,
            'dbindex' => $dbindex,
            'pretty' => $pretty
        ]);
    }

    /**
     * Importe les paramètres d'une base.
     *
     * @param string $dbindex
     */
    public function actionDatabaseImportSettings($dbindex, $settings = null, $types = null) {
        // Vérifie que la base à éditer existe
        $database = $this->database($dbindex);

        // Requête POST : enregistre les paramètres de la base
        if ($this->isPost()) {
            $settings = json_decode(wp_unslash($settings), true);
            if (! is_array($settings) || ! isset($settings['name'])) {
                return $this->view('docalist-core:error', [
                    'h2' => __('Importer des paramètres', 'docalist-biblio'),
                    'h3' => __("Paramètres incorrects", 'docalist-biblio'),
                    'message' => __("Le code que vous avez fourni n'est pas valide, vérifiez que vous avez bien collé la totalité des paramètres.", 'docalist-biblio'),
                ]);
            }

            if (empty($settings['types'])) {
                return $this->view('docalist-core:info', [
                    'h2' => __('Importer des paramètres', 'docalist-biblio'),
                    'h3' => __("Aucun type", 'docalist-biblio'),
                    'message' => __("Le code que vous avez fourni est valide mais ne contient aucun type, impossible d'importer quoi que ce soit.", 'docalist-biblio'),
                ]);
            }

            // Affiche le formulaire permettant de choisir les types à importer
            if (empty($types)) {
                return $this->view('docalist-biblio:database/import-settings-confirm', [
                    'database' => $database,
                    'dbindex' => $dbindex,
                    'settings' => $settings,
                ]);
            }

            foreach($settings['types'] as $type) {
                $name = $type['name'];
                if (! in_array($name, $types)) {
//                    echo "ne pas importer $name<br />";
                    continue;
                }
//                 echo "importer $name<br />";
                if (isset($database->types[$name])) {
//                     echo "Le type $name existe déjà<br />";
                    $database->types[$name] = $type;
                } else {
//                     echo "Le type $name n'existe pas encore<br />";
                    $database->types[$name] = $type;
                }
            }

            $this->settings->save();

            return $this->redirect($this->url('DatabasesList'), 303);
        }

        // Affiche le formulaire permettant de coller le code
        return $this->view('docalist-biblio:database/import-settings', [
            'database' => $database,
            'dbindex' => $dbindex
        ]);
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
     * @param string|array $name Nom du type à ajouter
     */
    public function actionTypeAdd($dbindex, $name = null) {
        // Vérifie que la base à modifier existe
        $database = $this->database($dbindex);

        // Récupère la liste des types disponibles
        $types = Database::getAvailableTypes();

        // Récupère la liste des types qui existent déjà dans la base
        $selected = $database->types;

        // Ecran "choix du type"
        if (empty($name)) {

            // Construit la liste tous les types qui ne sont pas déjà dans la base
            foreach($types as $name => $class) {
                if (isset($selected[$name])) {
                    unset($types[$name]);
                } else {
                    $types[$name] = $class::getDefaultSchema();
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

        foreach((array)$name as $name) {
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

            // Initialise les différentes grilles du type
            $class = $types[$name]; /** @var Type $class */

            $base    = new Grid($class::getBaseGrid());
            $edit    = new Grid($class::getEditGrid());
            $content = new Grid($class::getContentGrid());
            $excerpt = new Grid($class::getExcerptGrid());

            $edit->initSubfields($base);
            $content->initSubfields($base);
            $excerpt->initSubfields($base);

            // Crée le type
            $database->types[] = new TypeSettings([
                'name' => $name,
                'label' => $base->label(),
                'description' => $base->description(),
                'grids' => [ $base, $edit, $content, $excerpt]
            ]);
        }

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
            $msg = __(
                'Le type <b>%s</b> va être supprimé de la base <b>%s</b>.
                Tous les paramètres de ce type (propriétés, grille de saisie...)
                vont être perdus.',
                'docalist-biblio'
            );
            return $this->confirm(
                sprintf($msg, $type->label(), $database->label()),
                __('Supprimer un type', 'docalist-biblio')
            );
        }

        // Supprime le type
        unset($this->settings->databases[$dbindex]->types[$typeindex]);
        $this->settings->save();

        // Retourne à la liste des types
        return $this->redirect($this->url('TypesList', $dbindex), 303);
    }

    public function actionTypeRecreate($dbindex, $typeindex, $confirm = false) {
        unset($this->settings->databases[$dbindex]->types[$typeindex]);

        return $this->actionTypeAdd($dbindex, $typeindex);
    }

    /**
     * Liste les grilles d'un type.
     *
     * @param int $dbindex Base à éditer
     * @param int $typeindex Type à éditer
     */
    public function actionGridList($dbindex, $typeindex) {
        // Vérifie les paramètres
        $database = $this->database($dbindex);
        $type = $this->type($dbindex, $typeindex);

        // Liste des grilles
        return $this->view('docalist-biblio:grid/list', [
            'database' => $database,
            'dbindex' => $dbindex,
            'type' => $type,
            'typeindex' => $typeindex,
        ]);
    }

    /**
     * Edite les paramètres d'une grille (label, description...)
     *
     * @param int $dbindex Base à éditer
     * @param int $typeindex Type à éditer
     * @param string $gridname Nom de la grille à éditer.
     */
    public function actionGridSettings($dbindex, $typeindex, $gridname) {
        $database = $this->database($dbindex);
        $type = $this->type($dbindex, $typeindex);

        $grid = $type->grids[$gridname]; /** @var Schema $grid */

        if ($this->isPost()) {
            $_POST = wp_unslash($_POST);

            $grid->label = $_POST['label'];
            $grid->description = $_POST['description'];

            $this->settings->save();

            return $this->redirect($this->url('GridList', $dbindex, $typeindex), 303);
        }

        return $this->view('docalist-biblio:grid/settings', [
            'database' => $database,
            'dbindex' => $dbindex,
            'type' => $type,
            'typeindex' => $typeindex,
            'grid' => $grid,
            'gridname' => $gridname
        ]);
    }

    /**
     * Edite les champs d'une grille (ordre, libellés, groupes, etc.)
     *
     * @param int $dbindex Base à éditer
     * @param int $typeindex Type à éditer
     * @param string $gridname Nom de la grille à éditer.
     */
    public function actionGridEdit($dbindex, $typeindex, $gridname) {
        $debug = false;

        $database = $this->database($dbindex);
        $type = $this->type($dbindex, $typeindex);
        $grid = $type->grids[$gridname]; /** @var Schema $grid */

        // Enregistre la grille si on est en POST
        if ($this->isPost()) {
            $data = wp_unslash($_POST);

            if ($debug) {
                $old = '<?php ' . var_export($grid, true);
                $grid = $grid->mergeWith($data);
                $type->grids[$gridname] = $grid;
                $new = '<?php ' . var_export($grid, true);

                header('content-type: text/html; charset=UTF-8');
                echo '<pre style="width: 49%; float: left; border:1px;">', htmlspecialchars($old), '</pre>';
                echo '<pre style="width: 49%; float: left; border:1px;">', htmlspecialchars($new), '</pre>';
                file_put_contents('d:/old.txt', $old);
                file_put_contents('d:/new.txt', $new);
                die();
                $this->settings->save();
            }

            // Renumérote les groupes
            $fields = [];
            $groupNumber = 1;
            foreach($data['fields'] as $name => $field) {
                if (isset($field['type']) && $field['type'] === 'Docalist\Biblio\Type\Group') {
                    $name = 'group' . $groupNumber;
                    $groupNumber++;
                }
                $fields[$name] = $field;
            }
            $data['fields'] = $fields;

            // Met à jour la grille et enregistre les settings
            $type->grids[$gridname] = $grid->mergeWith($data);
            $this->settings->save();

            return $this->redirect($this->url('GridList', $dbindex, $typeindex), 303);
        }

        return $this->view('docalist-biblio:grid/edit', [
            'database' => $database,
            'dbindex' => $dbindex,
            'type' => $type,
            'typeindex' => $typeindex,
            'grid' => $grid,
            'gridname' => $gridname
        ]);
    }

    /**
     * Duplique une grille.
     *
     * @param int $dbindex Base à éditer
     * @param int $typeindex Type à éditer
     * @param string $gridname Nom de la grille à copier.
     */
    public function actionGridCopy($dbindex, $typeindex, $gridname) {
        return $this->info('Pas encore implémenté', 'Dupliquer une grille');
    }

    /**
     * Supprime une grille
     *
     * @param int $dbindex Base à éditer
     * @param int $typeindex Type à éditer
     * @param string $gridname Nom de la grille à éditer.
     */
    public function actionGridDelete($dbindex, $typeindex, $gridname) {
        return $this->info('Pas encore implémenté', 'Supprimer une grille');
    }

    public function actionGridToPhp($dbindex, $typeindex, $gridname, $diffonly = false) {
        $database = $this->database($dbindex);
        $type = $this->type($dbindex, $typeindex);
        $grid = $type->grids[$gridname]; /** @var Schema $grid */

        // recrée la grille telle que'elle était initialement pour
        // que la vue tophp puisse indiquer les modifications apportées
        $types = Database::getAvailableTypes();

        if ($diffonly) {
            $method = 'get' . $gridname . 'Grid';
            $base = $types[$typeindex]::$method();
            $base->name = $gridname;
        } else {
            $base = $types[$typeindex]::getBaseGrid();
        }

        return $this->view('docalist-biblio:grid/tophp', [
            'database' => $database,
            'dbindex' => $dbindex,
            'type' => $type,
            'typeindex' => $typeindex,
            'grid' => $grid,
            'gridname' => $gridname,
            'base' => $base,
            'diffonly' => $diffonly
        ]);
    }
}
