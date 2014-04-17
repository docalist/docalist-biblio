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
use Exception;

use Docalist\Forms\Fragment;
use Docalist\Forms\Table;
use Docalist\Forms\Input;
use Docalist\Forms\Select;
//use Docalist\Forms\Hidden;

use Docalist\Table\TableManager;
use Docalist\Http\ViewResponse;
use Docalist\Forms\TableLookup;
use Docalist\Forms\Assets;

/**
 * La page "création/modification d'une notice" d'une base documentaire.
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

        add_action('load-post.php', function() {
            if (! $this->isMyPostType()) {
                return;
            }

            if (isset($_GET['action']) && $_GET['action'] === 'edit') {
                $this->reference = $this->database->load($_GET['post']);
                $this->setPageTitle($this->reference->type, false);
            }
        });

        add_action('load-post-new.php', function() {
            $this->isMyPostType() && $this->create();
        });

        add_action('add_meta_boxes_' . $this->postType, function(WP_Post $post) {
            $this->edit($post->ID);
        });

        add_action('post_updated', function($id, WP_Post $post, WP_Post $previous) {
            if ($post->post_type === $this->postType) {
                $this->save($post->ID);
            }
        }, 10, 3);
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
        // Détermine le libellé du type
        foreach($this->database->settings()->types as $item){
            if ($item->name === $type) {
                $type = $item->label;
                break;
            }
        };
        // TODO : indexer les types par nom pour permettre una ccès direct au label

        $base = $this->database->settings()->label;

        if ($creation) {
            $op = __('création', 'docalist-biblio');
            $label = 'add_new_item';
        } else {
            $op = __('édition', 'docalist-biblio');
            $label = 'edit_item';
        }

        $title = sprintf('%s - %s : %s', $base, $type, $op);

        get_post_type_object($this->postType)->labels->$label = $title;
    }

    /**
     * Affiche l'écran "choix du type de notice à créer".
     */
    protected function create(){
        // S'il n'y a qu'un seul type de notices, inutile de demander à l'utilisateur
        if (empty($_REQUEST['ref_type']) && count($this->database->settings()->types) === 1) {
            $_REQUEST['ref_type'] = $this->database->settings()->types[0]->name;
        }

        // Si le type de ref a déjà été indiqué, laisse wp faire son job
        if (isset($_REQUEST['ref_type'])) {
            return $this->setPageTitle($_REQUEST['ref_type'], true);
        }

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

    protected function addDebugMetabox() {
        // @formatter:off
        add_meta_box(
            'dclref-debug',                        // id metabox
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

        // Debug toujours cachée, il faut aller dans options pour l'afficher
        add_filter('hidden_meta_boxes', function(array $hidden, $screen) {
            // @see https://trepmal.com/2011/03/31/change-which-meta-boxes-are-shown-or-hidden-by-default/
            if ($screen->id === $this->postType) {
                $hidden[] = 'dclref-debug';
            }

            return $hidden;
        }, 10, 2);
    }

    protected function edit($id) {
        // Supprime la metabox "Identifiant"
        remove_meta_box('slugdiv', $this->postType, 'normal');

        add_action('edit_form_after_title', function() {
            $this->createNonce();
        });

        // Charge la notice à éditer
        if (! isset($this->reference)) {
            $this->reference = $this->database->load($id);
        }

        // Détermine la grille de saisie à utiliser
        isset($_REQUEST['ref_type']) && $this->reference->type = $_REQUEST['ref_type'];

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
     * @param int $id ID de la notice à enregistrer.
     */
    protected function save($id) {
        // Vérifie le nonce
        if (! $this->checkNonce()) {
            return;
        }

        // Récupère les données transmises dans $_POST
        $record = wp_unslash($_POST);

        // Charge la notice à mettre à jour
        $this->reference = $this->database->load($id);
        if (! isset($record['type'])) {
            throw new Exception('Pas de type de notice dans $_POST');
        }
        $type = $record['type'];

        // Met à jour la notice
        $record = wp_unslash($_POST);
        foreach($this->metaboxes($type) as $id => $metabox) {
            $metabox->bind($record);
            foreach($metabox->data() as $key => $data) {
                $this->reference->$key = $data;
            }
        }

        // Enregistre la notice modifiée
        $this->database->store($this->reference);
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
            die("Impossible de trouver le type $type");
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
                $field = new Input($name);
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
                $this->checkTables($def, 'table:ISO-3166-1_alpha2_fr');
                $field = new Table($name);
                $field->input('name')->addClass('editor-name');
                $field->input('city')->addClass('editor-city');
                $field->TableLookup('country', $def->table)
                      ->addClass('editor-country');
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

            case 'abstract':
                $this->checkTables($def, 'table:ISO-639-2_alpha3_EU_fr');
                $field = new Table($name);
                $field->TableLookup('language', $def->table)
                      ->addClass('abstract-language');
                $field->textarea('content')->addClass('abstract-content');
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

            case 'note':
                $this->checkTables($def, 'table:notes');
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

            case 'relations':
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