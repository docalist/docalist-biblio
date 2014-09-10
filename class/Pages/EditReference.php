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
namespace Docalist\Biblio\Pages;

use Docalist\Biblio\Database;
use Docalist\Biblio\Reference;
use Docalist\Forms\Themes;
use Docalist\Utils;
use WP_Post;
use WP_Screen;
use Exception;

use Docalist\Forms\Fragment;

use Docalist\Http\ViewResponse;
use Docalist\Forms\Assets;
use Docalist\Schema\Field;

/**
 * Gère la page "création/modification d'une notice" d'une base documentaire.
 */
class EditReference {
    /**
     * Le nonce qui sera généré dans l'écran d'édition.
     */
    const NONCE = 'dcl_nonce';

    /**
     * La base de données documentaire.
     *
     * @var Database
     */
    protected $database;

    /**
     * Le post-type de la base.
     *
     * Equivalent à $this->database->postType().
     *
     * @var string
     */
    protected $postType;

    /**
     * La notice en cours d'édition.
     *
     * @var Reference
     */
    protected $reference;

    /**
     *
     * @param Database $database
     */
    public function __construct(Database $database) {
        $this->database = $database;
        $this->postType = $database->postType();

        // Demande à l'utilisateur le type de la notice à créer
        add_action('load-post-new.php', function() {
            $this->isMyPostType() && $this->create();
        });

        // Ajoute les metaboxes dans le formulaire de saisie
        add_action('add_meta_boxes_' . $this->postType, function(WP_Post $post) {
            $this->edit($post->ID);
        });

        // Enregistre les données transmises lorsque wordpress sauve le post
        global $pagenow;
        if ($pagenow === 'post.php' && $this->isMyPostType() && isset($_POST['action']) && $_POST['action'] === 'editpost') {
            add_filter('wp_insert_post_data', function(array $data, array $postarr) {
                return $this->save($data, $postarr);
            }, 10, 2);
        }
    }

    /**
     * Indique si la requête en cours concerne un enregistrement du post-type
     * géré par cette base.
     *
     * @return boolean
     */
    protected function isMyPostType() {
        global $typenow;

        return $typenow === $this->postType;
    }

    /**
     * Modifie le titre de l'écran d'édition en fonction du type de notice en
     * cours.
     *
     * @param string $type le type en cours
     * @param boolean $creation true s'il s'agit d'un nouvele enregistrement,
     * false s'il s'agit d'une mise à jour.
     */
    protected function setPageTitle($type, $creation = false) {
        // Variable globale wordpress utilisée pour le titre de la page
        // Définie dans post-new.php (entres autres) et affichée dans
        // edit-form-advanced...
        global $title;

//         if ($creation) {
//             $title = __("Base %s : créer une notice (%s)", 'docalist-biblio');
//         } else {
//             $title = __("Base %s : modifier une notice (%s)", 'docalist-biblio');
//         }

        $title = __('%s : %s', 'docalist-biblio');

        $title = sprintf(
            $title,
            $this->database->settings()->label(),
            lcfirst($this->database->settings()->types[$type]->label())
        );
    }

    /**
     * Affiche l'écran "choix du type de notice à créer".
     */
    protected function create(){
        // S'il n'y a qu'un seul type de notices, inutile de demander à l'utilisateur
        $types = $this->database->settings()->types;
        if (empty($_REQUEST['ref_type']) && count($types) === 1) {
            $_REQUEST['ref_type'] = $types->first()->name;
        }

        // On connaît le type de notice à créer
        if (isset($_REQUEST['ref_type'])) {
            // On va laisser WordPress continuer sont process
            // Il va créer un "auto-draft" et appeller wp_insert_post().
            // Juste avant l'insertion, il appelle le filtre wp_insert_post_data
            // avec les données initiales du post. C'est ce filtre qu'on intercepte
            // pour initialiser la notice.
            // Wordpress va ensuite enregistrer nos données puis afficher le
            // formulaire edit-form-advanced

            // Injecte les valeurs par défaut dans le draft qui va être créé
            add_filter('wp_insert_post_data', function(array $data) {
                // On conserve toutes les valeurs par défaut de wp pour le post
                // On se contente de stocker le type de la notice créée et de
                // vider le titre par défaut "brouillon auto".
                $data['post_excerpt'] = json_encode(['type' => $_REQUEST['ref_type']]);
                $data['post_title'] = '';

                return $data;

                /*
                  Ancienne version en appellant entityToPost (ne marche pas) :
                  - post_status est forcément à publish, on ne peut pas créer
                    de brouillon, la notice est publiée dès sa création. Il
                    faut garder le statut "auto-draft" fourni par wordpress.
                  - On alloue un numéro de ref, alors que le brouillon ne sera
                    pas forcément enregistré
                  - Les champs post_xxx_gmt doivent rester à 0

                $ref = new Reference();
                $ref->type = $_REQUEST['ref_type'];
                $data = $this->database->entityToPost($ref);

                return $data;
                */
            }, 1000); // on doit avoir une priorité > au filtre installé dans database.php

            // Adapte le titre de l'écran de saisie
            //$this->setPageTitle($_REQUEST['ref_type'], true);
            // TODO : ne sert à rien car edit() et appellé après et change le titre

            // Laisse wp afficher le formulaire
            return;
        }

        // On ne sait pas quel type de notice créer. Demande à l'utilisateur.

        // Indique à WP l'option de menu en cours
        // cf. wp-admin/post-new.php, lignes 28 et suivantes
        global $submenu_file;
        $submenu_file = "post-new.php?post_type=$this->postType";

        // Affiche la page "Choix du type de notice à créer"
        require_once('./admin-header.php');
        $view = new ViewResponse('docalist-biblio:reference/choose-type', [
            'database' => $this->database,
        ]);
        $view->sendContent();
        include('./admin-footer.php');

        // Empêche wp d'afficher le formulaire edit-form standard
        die();
    }

    /**
     * Ajoute une metabox de débogage qui affiche le contenu brut du post.
     */
    protected function addDebugMetabox(Reference $ref) {
        // @formatter:off
        add_meta_box(
            'dclrefdebug',                         // id metabox
            'Informations de debug de la notice',  // titre
            function () use ($ref) {                // Callback
                global $post;

                $data = $post->to_array();
                unset($data['post_excerpt']);
                $data = array_filter($data);

                echo "<h4>Propriétés du post WordPress :</h4><pre>";
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                echo "</pre>";

                echo "<h4>Contenu de la notice :</h4><pre>";
                echo json_encode($ref, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //echo $ref;
                echo "</pre>";

                echo "<h4>Mapping Docalist-Search</h4><pre>";
                echo json_encode($ref->map(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //echo $ref;
                echo "</pre>";
            },
            $this->postType,    // posttype
            'normal',           // contexte
            'high'              // priorité
        );
        // @formatter:on
    }

    /**
     * Paramètre le formulaire de saisie et ajoute les metaboxes correspondant
     * au type de la notice en cours.
     *
     * @param int $id ID du post à éditer.
     */
    protected function edit($id) {
        // Charge la notice à éditer
        $ref = $this->database->load($id, 'edit');

        // Adapte le titre de l'écran de saisie
        $this->setPageTitle($ref->type(), false);

        // Supprime la metabox "Identifiant"
        remove_meta_box('slugdiv', $this->postType, 'normal');

        add_action('edit_form_after_title', function() {
            $this->createNonce();
        });

        // Crée une metabox "debug" pour afficher le contenu brut du post
        $this->addDebugMetabox($ref);

        // Metabox normales pour la saisie
        $assets = new Assets();
        foreach($this->metaboxes($ref) as $form) {
            $id = $form->attribute('id');
            $title = $form->label();
            if (empty($title)) {
                $type = Reference::types()[$ref->type()];
                $title = $type::defaultSchema()->label();
            }

            // @formatter:off
            add_meta_box(
                $id,                        // id metabox
                $title,                     // titre
                function() use($form){                // Callback
                    // Le titre du formulaire a déjà été affiché par add_meta_box
                    $form->label(false);

                    // Affiche le formulaire
                    $form->render('wordpress');
                },
                $this->postType,    // posttype
                'normal',           // contexte
                'high'              // priorité
            );
            // @formatter:on

            $form->bind($ref);
            $assets->add($form->assets());
        }
        $assets->add(Themes::assets('wordpress'));
        // Insère tous les assets dans la page
        Utils::enqueueAssets($assets); // @todo : faire plutôt $assets->enqueue()

        wp_enqueue_style(
            'docalist-biblio-edit-css',
            plugins_url('docalist-biblio/assets/edit-reference.css'),
            array(),
            '20140627'
        );

        // Définit l'état initial des metaboxes (normal, replié, masqué)
        $hidden = ['authordiv', 'commentsdiv', 'commentstatusdiv', 'trackbacksdiv', 'revisionsdiv', 'dclrefdebug'];
        $collapsed = [];
        foreach($ref->schema()->fields() as $name => $field) { /* @var $field Field */
            if ($field->type() === 'Docalist\Biblio\Type\Group') {
                switch($field->state()) {
                    case 'hidden':
                        $hidden[] = $name;
                        break;
                    case 'collapsed':
                        $collapsed[] = $name;
                        break;
                    // default : affichage normal
                }
            }
        }

        add_filter('default_hidden_meta_boxes', function(array $result, WP_Screen $screen) use($hidden) {
            return $screen->id === $this->postType ? $hidden : $result;
        }, 10, 2);

        add_filter('get_user_option_closedpostboxes_dclrefprisme', function($result) use ($collapsed) {
            return $result === false ? $collapsed : $result;
        });
    }

    /**
     * Enregistre la notice.
     *
     * @param array $data Les données du post wordpress (wp_slashées).
     * @param array $postarr Les données transmises dans $_POST.
     *
     * @return array Les données à enregistrer dans le post.
     */
    protected function save($data, $postarr) {
        /*
         * Cette méthode est appellée par wp_insert_post() quand wordpress
         * s'apprête à enregistrer le post modifié dans la base.
         * Wordpress nous passe dans $data les nouvelles données du post.
         * A partir de ces données, on construit une référence (postToEntity)
         * On binde cette référence avec les données transmises dans $_POST
         * On met ensuite à jour $data à partir de la référence en appellant
         * entityToPost.
         * On retourne à wp le résultat obtenu.
         */

        // Si wordpress nous a appellé pour une révision, on ne change rien
        if ($data['post_type'] !== $this->postType) {
            return $data;
        }

        // Vérifie le nonce
        if (! $this->checkNonce()) {
            return;
        }

        // Crée une référence à partir des données du post
        // $data contient les données standard d'un post wordpress (post_author,
        // post_date, post_content, etc.)
        // Ce qui nous intéresse, c'est post_excerpt, qui contient le type actuel
        // de la notice.
        $data = $this->database->decode(wp_unslash($data), $postarr['ID']);

        // Récupère le type actuel de la notice
        if (! isset($data['type'])) {
            throw new \Exception("Pas de type dans data");
        }
        $type = $data['type'];
        $ref = Reference::create($type, $data);

//         if (! isset($postarr['ID'])) {
//             throw new \Exception("pas d'ID");
//         }
//         $id = (int) $postarr['ID'];
//         $ref = $this->database->load($id);

        // Binde la référence avec les données transmises dans $_POST
        $record = wp_unslash($_POST);
        foreach($this->metaboxes($ref) as $metabox) {
            $metabox->bind($record);
            $data = $this->filterEmpty($metabox->data());
            foreach($data as $key => $value) {
                $ref->$key = $value;
            }
        }

        $ref->beforeSave($this->database);

        // Récupère les données de la référence obtenue
        $data = $this->database->encode($ref->value());

        // Retourne le résultat à Wordpress
        $data = wp_slash($data);
// var_dump($data);
// die('jj');
        return $data;
    }

    /**
     * Supprime les valeurs vides du tableau passé en paramètre.
     *
     * Récursif : si un élément est un tableau qui ne contient que des valeurs
     * vides, il est supprimé.
     *
     * @param array $data
     *
     * @return array
     */
    private function filterEmpty(array $data) {
        foreach ($data as $key => $value) {
            is_array($value) && $data[$key] = $this->filterEmpty($data[$key]);
            if (empty($data[$key])){
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Génère un nonce WordPress lorsque l'écran d'édition du post type
     * est affiché.
     */
    protected function createNonce() {
        wp_nonce_field('edit-post', self::NONCE);
    }

    /**
     * Vérifie que $_POST contient le nonce créé par createNonce() et que
     * celui-ci est valide.
     *
     * @return bool
     */
    protected function checkNonce() {
        return isset($_POST[self::NONCE]) && wp_verify_nonce($_POST[self::NONCE], 'edit-post');
    }

    /**
     * Retourne les formulaires utilisés pour saisir une notice de ce type.
     *
     * @param string $type
     * @return Fragment[] Un tableau de la forme id metabox => form fragment
     */
    protected function metaboxes(Reference $ref) {
        $metaboxes = [];
        foreach($ref->schema()->fields() as $name => $field) { /* @var $field Field */
            if ($field->type() === 'Docalist\Biblio\Type\Group') {
                $box = new Fragment();
                $box->label($field->label())
                    ->description($field->description())
                    ->attribute('id', $name);

                $metaboxes[] = $box;
            } else {
                if (empty($metaboxes)){
//                     var_dump($ref->schema());
//                     throw new \Exception('le type ne commence pas par un groupe');
                    $box = new Fragment();
                    $box->label('')
                        ->attribute('id', 'defaultgroup');
                    $metaboxes[] = $box;
                }

                $box->add($ref->$name->editForm());
            }
        }

        return $metaboxes;
    }

    /**
     * Vérifie que les tables indiquées dans la définition d'un champ sont
     * correctes et qu'elles existent.
     *
     * Si ce n'est pas le cas, la définition du champ est temporairement
     * modifiée pour utiliser la table par défaut indiquée en paramètre et
     * une erreur "admin notice" est générée.
     *
     * @param Field $def La définition du champ à vérifier.
     * @param string $default Le nom de la première table par défaut.
     * @param string $default2 Le nom de laseconde table par défaut (si le champ
     * utilise deux tables, par exemple organization).
     */
//     protected function checkTables(Field $def, $default, $default2 = null) {
//         foreach(['table' => $default, 'table2' => $default2] as $table => $default) {
//             if ($table === 'table2' && !isset($def->$table)) {
//                 continue;
//             }

//             // Vérifie que la table indiquée existe
//             if (preg_match('~([a-z]+):([a-zA-Z0-9_-]+)~', $def->$table(), $match)) {
//                 if (docalist('table-manager')->info($match[2])) {
//                     continue;
//                 }
//             }

//             // Table incorrecte, affiche une admin notice
//             $msg = __("La table <code>%s</code> indiquée pour le champ <code>%s</code> n'est pas valide.", 'docalist-biblio');
//             $msg = sprintf($msg, $def->$table() ?: ' ', $def->name());
//             $msg .= '<br />';
//             $msg .= __('Vous devez corriger la grille de saisie', 'docalist-biblio');
//             add_action('admin_notices', function () use ($msg) {
//                 printf('<div class="error"><p>%s</p></div>', $msg);
//             });

//             // Et utilise la table par défaut
//             $def->$table = $default;
//         }
//     }

    /*
     * N'est plus utilisée mais peut reservir si on voulait générer un select
     * contenant toutes les entrées d'une table.
     */
//     protected function tableOptions($table, $fields = 'code,label') {
//         empty($table) && $table = 'countries';
//         /* @var $tableManager TableManager */
//         $tableManager = docalist('table-manager');
//         return $tableManager->get($table)->search($fields);
//     }
}