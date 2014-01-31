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
        $this->id = 'edit-' . $this->postType;

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
        $this->chooseType();
        include('./admin-footer.php');

        // Empêche wp d'afficher le formulaire edit-form standard
        die();
    }

    /**
     * Permet à l'utilisateur de choisir le type de référence à créer.
     *
     * Liste tous les types enregistrés pour la base, génère des liens qui
     * permettent à l'utilisateur de choisir et rappelle la page
     * wp-admin/post-new.php en passant l'argument ref_type en paramètre.
     *
     * @throws Exception
     */
    protected function chooseType() {
        $postType = get_post_type_object($this->postType);

        // Début de page
        echo '<div class="wrap">';

        // Icone et titre de la page
        screen_icon();
        $title = $this->database->settings()->label . ' - ' . $postType->labels->add_new_item;
        printf('<h2>%s</h2>', $title);

        // Texte d'intro
        $msg = __('Choisissez le type de notice à créer :', 'docalist-biblio');
        printf('<p>%s</p>', $msg);

        // Table widefat avec la liste des types disponibles
        echo '<table class="widefat">';
        foreach($this->database->settings()->types as $i => $type) {
            $class = $i % 2 ? 'alternate' : '';
            $link = add_query_arg('ref_type', $type->name);
            printf('<tr class="%s"><td class="row-title"><a href="%s">%s</a></td><td class="desc">%s</td></tr>', $class, $link, $type->label, $type->description);
        }

        if (!isset($i)) {
            $msg = __('Aucun type de notices dans cette base.', 'docalist-biblio');
            printf('<tr><td class="desc" colspan="2">%s</td></tr>', $msg);
        }

        echo '</table>';

        // Fin de page
        echo '</div>';
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

        $assets = Themes::assets('wordpress');
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
                'default'           // priorité
            );
            // @formatter:on

            $form->bind($this->reference);
            $assets->add($form->assets());
        }

        // Insère tous les assets dans la page
        Utils::enqueueAssets($assets); // @todo : faire plutôt $assets->enqueue()

        wp_enqueue_style(
            'docalist-biblio-edit-css',
            plugins_url('docalist-biblio/assets/edit-reference.css'),
            array(),
            '20131107'
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
                $table = $def->table;
                $field = new Select($name);
                $field->options($this->tableOptions($table));
                break;

            case 'media':
                $table = $def->table;
                $field = new Select($name);
                $field->options($this->tableOptions($table));
                break;

            case 'author':
                $roles = $def->table;

                $field = new Table($name);
                $field->input('name')->addClass('name');
                $field->input('firstname')->addClass('firstname');
                $field->select('role')
                      ->options($this->tableOptions($roles))
                      ->addClass('role');

                break;

            case 'organisation':
                $countries = $def->table;
                $roles = $def->table2;

                $field = new Table($name);
                $field->input('name')->addClass('name');
                $field->input('city')->addClass('city');
                $field->select('country')
                      ->options($this->tableOptions($countries))
                      ->addClass('country');
                $field->select('role')
                      ->options($this->tableOptions($roles))
                      ->addClass('role');
                break;

            case 'title':
                $field = new Input($name);
                $field->addClass('large-text');//->attribute('id', 'DocTitle');
                break;

            case 'othertitle':
                $titles = $def->table;

                $field = new Table($name);
                $field->select('type')
                      ->options($this->tableOptions($titles))
                      ->addClass('type');
                $field->input('title')->addClass('title');
                break;

            case 'translation':
                $languages = $def->table;

                $field = new Table($name);
                $field->select('language')
                      ->options($this->tableOptions($languages))
                      ->addClass('language');
                $field->input('title')->addClass('title');
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
                $languages = $def->table;

                $field = new Select($name);
                $field->options($this->tableOptions($languages));
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
                $countries = $def->table;

                $field = new Table($name);
                $field->input('name')->addClass('name');
                $field->input('city')->addClass('city');
                $field->select('country')
                      ->options($this->tableOptions($countries))
                      ->addClass('country');
                break;

            case 'edition':
                $field = new Table($name);
                $field->input('type')->addClass('type');
                $field->input('value')->addClass('value');
                break;

            case 'collection':
                $field = new Table($name);
                $field->input('name')->addClass('name');
                $field->input('number')->addClass('number');
                break;

            case 'event':
                $field = new Table($name);
                $field->input('title')->addClass('title');
                $field->input('date')->addClass('date');
                $field->input('place')->addClass('place');
                $field->input('number')->addClass('number');
                break;

            case 'degree':
                $field = new Table($name);
                $field->input('title')->addClass('title');
                $field->input('level')->addClass('level');
                break;

            case 'abstract':
                $languages = $def->table;

                $field = new Table($name);
                $field->select('language')
                      ->options($this->tableOptions($languages))
                      ->addClass('language');
                $field->textarea('content')->addClass('content');
                break;

            case 'topic':
                // $table = $def->table; // TODO mettre la bonne table
                $field = new Table($name);
                $field->select('type')
                    //->options($def->table->toArray())
                      ->addClass('type');
                $field->input('term')->addClass('term');
                break;

            case 'note':
                $notes = $def->table;

                $field = new Table($name);
                $field->select('type')
                      ->options($this->tableOptions($notes))
                      ->addClass('type');
                $field->textarea('content')->addClass('content');
                break;

            case 'link':
                $links = $def->table;

                $field = new Table($name);
                $field->input('url')->addClass('url');
                $field->select('type')
                      ->options($this->tableOptions($links))
                      ->addClass('type');
                $field->input('label')->addClass('label');
                $field->input('date')->addClass('date');
                break;

            case 'doi':
                $field = new Input($name);
                break;

            case 'relations':
                $relations = $def->table;

                $field = new Table($name);
                $field->select('type')
                      ->options($this->tableOptions($relations))
                      ->addClass('type');
                $field->input('ref')->addClass('ref');
                break;

            case 'owner':
                $field = new Input($name);
                break;

            case 'creation':
                $field = new Table($name);
                $field->input('date')->addClass('date');
                $field->input('by')->addClass('by');
                break;

            case 'lastupdate':
                $field = new Table($name);
                $field->input('date')->addClass('date');
                $field->input('by')->addClass('by');
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

    protected function tableOptions($table, $fields = 'code,label') {
        empty($table) && $table = 'countries';
        /* @var $tableManager TableManager */
        $tableManager = docalist('table-manager');
        return $tableManager->get($table)->search($fields);
    }
}