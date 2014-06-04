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
use Docalist\Forms\Themes;
use Docalist\Utils;
use WP_Post;
use WP_Screen;
use Exception;

use Docalist\Forms\Fragment;
use Docalist\Forms\Table;
use Docalist\Forms\Input;
use Docalist\Forms\Select;

use Docalist\Table\TableManager;
use Docalist\Http\ViewResponse;
use Docalist\Forms\TableLookup;
use Docalist\Forms\Assets;

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

        // Définit les metaboxes qui sont cachées par défaut
        add_filter('default_hidden_meta_boxes', function(array $hidden, WP_Screen $screen) {
            if ($screen->id === $this->postType) {
                $hidden = ['authordiv', 'commentsdiv', 'commentstatusdiv', 'trackbacksdiv', 'revisionsdiv', 'dclrefdebug'];
            }
            return $hidden;
        }, 10, 2);
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

        // Détermine le libellé du type
        foreach($this->database->settings()->types as $item){
            if ($item->name === $type) {
                $type = $item->label;
                break;
            }
        };
        // TODO : indexer les types par nom pour permettre un accès direct au label

        $base = $this->database->settings()->label;

        if ($creation) {
            $title = __("Base %s : créer une notice (%s)", 'docalist-biblio');
        } else {
            $title = __("Base %s : modifier une notice (%s)", 'docalist-biblio');
        }

        $title = sprintf($title, $base, lcfirst($type));
    }

    /**
     * Affiche l'écran "choix du type de notice à créer".
     */
    protected function create(){
        // S'il n'y a qu'un seul type de notices, inutile de demander à l'utilisateur
        if (empty($_REQUEST['ref_type']) && count($this->database->settings()->types) === 1) {
            $_REQUEST['ref_type'] = $this->database->settings()->types[0]->name;
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
            $this->setPageTitle($_REQUEST['ref_type'], true);

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
    protected function addDebugMetabox() {
        // @formatter:off
        add_meta_box(
            'dclrefdebug',                         // id metabox
            'Informations de debug de la notice',  // titre
            function() {                // Callback
                global $post;

                $data = $post->to_array();
                unset($data['post_excerpt']);
                $data = array_filter($data);

                echo "<h4>Propriétés du post WordPress :</h4><pre>";
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                echo "</pre>";

                $data = $this->reference->toArray();
                echo "<h4>Contenu de la notice :</h4><pre>";
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
        $this->reference = $this->database->load($id);

        // Adapte le titre de l'écran de saisie
        $this->setPageTitle($this->reference->type, false);

        // Supprime la metabox "Identifiant"
        remove_meta_box('slugdiv', $this->postType, 'normal');

        add_action('edit_form_after_title', function() {
            $this->createNonce();
        });

        // Crée une metabox "debug" pour afficher le contenu brut du post
        $this->addDebugMetabox();

        // Metabox normales pour la saisie
        $assets = new Assets();
        foreach($this->metaboxes($this->reference->type) as $id => $form) {
            $title = $form->label() ?: $id;

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

            $form->bind($this->reference);
            $assets->add($form->assets());
        }
        $assets->add(Themes::assets('wordpress'));
        // Insère tous les assets dans la page
        Utils::enqueueAssets($assets); // @todo : faire plutôt $assets->enqueue()

        wp_enqueue_style(
            'docalist-biblio-edit-css',
            plugins_url('docalist-biblio/assets/edit-reference.css'),
            array(),
            '20140326'
        );
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
        $data = wp_unslash($data);
        $ref = new Reference($this->database->postToEntity($data));

        // Récupère le type de référence
        $type = $ref->type;

        // Binde la référence avec les données transmises dans $_POST
        $record = wp_unslash($_POST);
        foreach($this->metaboxes($type) as $id => $metabox) {
            $metabox->bind($record);
            foreach($metabox->data() as $key => $value) {
                $ref->$key = $value;
            }
        }

        // Récupère les données de la référence obtenue
        $data = $this->database->entityToPost($ref);

        // Retourne le résultat à Wordpress
        $data = wp_slash($data);

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
    protected function metaboxes($type) {
        $type = strtolower($type); // TODO : prisme contient 'Article' au lieu de 'article'

        // Récupère la grille de saisie de ce type
        // TODO : indexer les types par nom, on ne peut pas accéder directement aux settings d'un type
        $fields = null;
        foreach($this->database->settings()->types as $t) {
            if ($t->name === $type) {
                $fields = $t->fields;
                break;
            }
        }
        if (is_null($fields)) {
            echo __METHOD__, "<br />";
            echo "Impossible de trouver le type $type";
//            var_dump($this->database->settings()->types);
            foreach($this->database->settings()->types as $t) {
                var_dump($t->name);
            }
            echo '<pre>';
            debug_print_backtrace();
            die();
        }
        $metaboxes = array();
        $box = new Fragment();
        foreach($fields as $field) {
            // Nouvelle métabox. Sauve la courante si non vide et crée une nouvelle
            if ($field->name === 'group') {
                if (count($box->fields()) !== 0) {
                    $id = $type . '-' . $box->fields()[0]->name();
                    $metaboxes[$id] = $box;
                }

                $box = new Fragment();
                $box->label($field->label)->description($field->description);
            } else {
                $field = $this->createField($field);
                // $field->label($def->label)->description($def->label);
                $box->add($field);
            }
        }

        if (count($box->fields()) !== 0) {
            $id = $type . '-' . $box->fields()[0]->name();
            $metaboxes[$id] = $box;
        }
//         var_dump($metaboxes);
//         die();
        return $metaboxes;

    }
    protected function createField(FieldSettings $def) {
        $name = $def->name;
        switch($name) {
            case 'ref':
                $field = new Input($name);
                break;

            case 'type':
                $types = apply_filters('docalist_biblio_get_types', array()); // code => class
                $types = array_keys($types);

                $field = new Select($name);
                $field->options($types);
                break;

            case 'genre':
                $this->checkTables($def, 'table:genres-articles');
                $field = new TableLookup($name, $def->table);
                $field->multiple(true);
                break;

            case 'media':
                $this->checkTables($def, 'table:medias');
                $field = new TableLookup($name, $def->table);
                $field->multiple(true);
                break;

            case 'author':
                $this->checkTables($def, 'thesaurus:marc21-relators_fr');
                $field = new Table($name);
                $field->input('name')->addClass('author-name');
                $field->input('firstname')->addClass('author-firstname');
                $field->TableLookup('role', $def->table)
                      ->addClass('author-role');
                break;

            case 'organisation':
                $this->checkTables($def, 'table:ISO-3166-1_alpha2_fr', 'thesaurus:marc21-relators_fr');
                $field = new Table($name);
                $field->input('name')->addClass('organisation-name');
                $field->input('city')->addClass('organisation-city');
                $field->TableLookup('country', $def->table)
                      ->addClass('organisation-country');
                $field->TableLookup('role', $def->table2)
                      ->addClass('organisation-role');
                break;

            case 'title':
                $field = new Input($name);
                $field->addClass('large-text');//->attribute('id', 'DocTitle');
                break;

            case 'othertitle':
                $this->checkTables($def, 'table:titles');
                $field = new Table($name);
                $field->TableLookup('type', $def->table)
                      ->addClass('othertitle-type');
                $field->input('title')->addClass('othertitle-title');
                break;

            case 'translation':
                $this->checkTables($def, 'table:ISO-639-2_alpha3_EU_fr');
                $field = new Table($name);
                $field->TableLookup('language', $def->table)
                      ->addClass('translation-language');
                $field->input('title')->addClass('translation-title');
                break;

            case 'date':
                $this->checkTables($def, 'table:dates');
                $field = new Table($name);
                $field->TableLookup('type', $def->table)
                      ->addClass('date-type');
                $field->input('date')->addClass('date-date');
                break;

            case 'journal':
                $field = new Input($name);
                $field->addClass('large-text');
                break;

            case 'issn':
                $field = new Input($name);
                break;

            case 'volume':
                $field = new Input($name);
                break;

            case 'issue':
                $field = new Input($name);
                break;

            case 'language':
                $this->checkTables($def, 'table:ISO-639-2_alpha3_EU_fr');
                $field = new TableLookup($name, $def->table);
                $field->multiple(true);
                break;

            case 'pagination':
                $field = new Input($name);
                break;

            case 'format':
                $field = new Input($name);
                break;

            case 'isbn':
                $field = new Input($name);
                break;

            case 'editor':
                $this->checkTables($def, 'table:ISO-3166-1_alpha2_fr', 'thesaurus:marc21-relators_fr');
                $field = new Table($name);
                $field->input('name')->addClass('editor-name');
                $field->input('city')->addClass('editor-city');
                $field->TableLookup('country', $def->table)
                      ->addClass('editor-country');
                $field->TableLookup('role', $def->table2)
                      ->addClass('editor-role');
                break;

            case 'edition':
                $field = new Table($name);
                $field->input('type')->addClass('edition-type');
                $field->input('value')->addClass('edition-value');
                break;

            case 'collection':
                $field = new Table($name);
                $field->input('name')->addClass('collection-name');
                $field->input('number')->addClass('collection-number');
                break;

            case 'event':
                $field = new Table($name);
                $field->input('title')->addClass('event-title');
                $field->input('date')->addClass('event-date');
                $field->input('place')->addClass('event-place');
                $field->input('number')->addClass('event-number');
                break;

            case 'degree':
                $field = new Table($name);
                $field->input('title')->addClass('degree-title');
                $field->input('level')->addClass('degree-level');
                break;

            case 'topic':
                $this->checkTables($def, 'table:topics');
                $field = new Table($name);
                $field->TableLookup('type', $def->table)
                      ->addClass('topic-type');
              //$field->input('term')->addClass('topic-term');

                switch ($this->database->settings()->slug) {
                    case 'infolegis':
                        $table = 'thesaurus:domaines-test';
                        break;
                    case 'annuairesites':
                        $table = 'thesaurus:prisme-web-content';
                        break;
                    default:
                        $table = 'thesaurus:thesaurus-prisme-2013';
                }
                $field->TableLookup('term', $table)
                      ->multiple(true)
                      ->addClass('topic-term');
                break;

            case 'content':
                $this->checkTables($def, 'table:content');
                $field = new Table($name);
                $field->TableLookup('type', $def->table)
                      ->addClass('note-type');
                $field->textarea('content')->addClass('note-content');
                break;

            case 'link':
                $this->checkTables($def, 'table:links');
                $field = new Table($name);
                $field->input('url')->addClass('url');
                $field->TableLookup('type', $def->table)
                      ->addClass('link-type');
                $field->input('label')->addClass('link-label');
                $field->input('date')->addClass('link-date');
                break;

            case 'doi':
                $field = new Input($name);
                break;

            case 'relation':
                $this->checkTables($def, 'table:relations');
                $field = new Table($name);
                $field->TableLookup('type', $def->table)
                      ->addClass('relations-type');
                $field->input('ref')->addClass('relations-ref');
                break;

            case 'owner':
                $field = new Input($name);
                break;

            case 'creation':
                $field = new Table($name);
                $field->input('date')->addClass('creation-date');
                $field->input('by')->addClass('creation-by');
                break;

            case 'lastupdate':
                $field = new Table($name);
                $field->input('date')->addClass('lastupdate-date');
                $field->input('by')->addClass('lastupdate-by');
                break;

            case 'status':
                $field = new Input($name);
                break;

            default:
                throw new Exception("Champ inconnu : '$name'");
        }
        $field->addClass($name);
        $field->label($def->label)->description($def->description);

        return $field;
    }

    /**
     * Vérifie que les tables indiquées dans la définition d'un champ sont
     * correctes et qu'elles existent.
     *
     * Si ce n'est pas le cas, la définition du champ est temporairement
     * modifiée pour utiliser la table par défaut indiquée en paramètre et
     * une erreur "admin notice" est générée.
     *
     * @param FieldSettings $def La définition du champ à vérifier.
     * @param string $default Le nom de la première table par défaut.
     * @param string $default2 Le nom de laseconde table par défaut (si le champ
     * utilise deux tables, par exemple organization).
     */
    protected function checkTables(FieldSettings $def, $default, $default2 = null) {
        foreach(['table' => $default, 'table2' => $default2] as $table => $default) {
            if ($table === 'table2' && empty($def->$table)) {
                continue;
            }

            // Vérifie que la table indiquée existe
            if (preg_match('~([a-z]+):([a-zA-Z0-9_-]+)~', $def->$table, $match)) {
                if (docalist('table-manager')->info($match[2])) {
                    continue;
                }
            }

            // Table incorrecte, affiche une admin notice
            $msg = __("La table <code>%s</code> indiquée pour le champ <code>%s</code> n'est pas valide.", 'docalist-biblio');
            $msg = sprintf($msg, $def->$table ?: ' ', $def->name);
            $msg .= '<br />';
            $msg .= __('Vous devez corriger la grille de saisie', 'docalist-biblio');
            add_action('admin_notices', function () use ($msg) {
                printf('<div class="error"><p>%s</p></div>', $msg);
            });

            // Et utilise la table par défaut
            $def->$table = $default;
        }
    }

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