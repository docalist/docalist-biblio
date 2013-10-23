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
        $base = $this->database->settings()->label;

        $type = $type ? $this->database->classForType($type, true) : 'Type inconnu';
        $type = $type->label;

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
        $type = $this->database->classForType($this->reference->type, true);

        $assets = Themes::assets('wordpress');
        foreach($type->metaboxes() as $id => $form) {
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
        $type = $this->database->classForType($record['type'], true);

        // Met à jour la notice
        $record = wp_unslash($_POST);
        foreach($type->metaboxes() as $id => $metabox) {
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
}