<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package Docalist
 * @subpackage Biblio
 * @author Daniel Ménard <daniel.menard@laposte.net>
 * @version SVN: $Id$
 */
namespace Docalist\Biblio;

use Docalist\Forms\Fragment;
use Docalist\Forms\Themes;
use Docalist\Utils;
use WP_Post;

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
     *
     * @param Database $database
     */
    public function __construct(Database $database) {
        $this->database = $database;
        $this->id = 'edit-' . $database->postType();

        add_action('add_meta_boxes_' . $database->postType(), function(WP_Post $post) {
            $this->edit($post->ID);
        });

        add_action('post_updated', function($id, WP_Post $post, WP_Post $previous) {
            if ($post->post_type === $this->database->postType()) {
                $this->save($post->ID);
            }
        }, 10, 3);
    }

    protected function save($id) {
        // Vérifie le nonce
        if (! $this->checkNonce()) {
            return;
        }

        // Charge la notice à mettre à jour
        $reference = $this->database->load($id);

        // Met à jour la notice à partir des données transmises dans $_POST
        $record = wp_unslash($_POST);
        foreach($this->metaboxes() as $id => $metabox) {
            $metabox->bind($record);
            foreach($metabox->data() as $key => $data) {
                $reference->$key = $data;
            }
        }

        // Enregistre la notice modifiée
        $this->database->store($reference);
    }


    protected function edit($id) {
        // Supprime la metabox "Identifiant"
        remove_meta_box('slugdiv', $this->database->postType(), 'normal');

        add_action('edit_form_after_title', function() {
            $this->createNonce();
        });

        // Charge la notice à éditer
        $reference = $this->database->load($id);
        $assets = Themes::assets('wordpress');
        foreach($this->metaboxes() as $id => $form) {
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
                $this->database->postType(),// posttype
                'normal',                   // contexte
                'default'                   // priorité
            );
            // @formatter:on

            $form->bind($reference);
            $assets->add($form->assets());
        }

        // Insère tous les assets dans la page
        Utils::enqueueAssets($assets); // @todo : faire plutôt $assets->enqueue()
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

    protected function metaboxes() {
        /*
        $metaboxes = wp_cache_get(__METHOD__);

        if ($metaboxes !== false) {
            echo 'metaboxes chargées depuis le cache wp<br />';
            return $metaboxes;
        }
        */
        $metaboxes = array();
        // @formatter:off

        // Type, Genre, Media
        $box = new Fragment();
        $box->label(__('Nature du document', 'docalist-biblio'));
        $box->select('type')
            ->options($this->taxonomy('dclreftype'));
        $box->select('genre')
            ->options($this->taxonomy('dclrefgenre'));
        $box->select('media')
            ->options($this->taxonomy('dclrefmedia'));
        $metaboxes['dclreftype'] = $box;

        // Title, OtherTitle, Translation
        $box = new Fragment();
        $box->label(__('Titre du document', 'docalist-biblio'));
        $box->input('title')
            ->addClass('large-text')
            ->attribute('id', 'DocTitle');
        $box->table('othertitle')
                ->select('type')
                ->options($this->taxonomy('dclreftitle'))
            ->parent()
                ->input('title');
        $box->table('translation')
                ->select('language')
                ->options($this->taxonomy('dcllanguage'))
            ->parent()
                ->input('title');
        $metaboxes['dclreftitles'] = $box;

        // Author, Organisation
        $box = new Fragment();
        $box->label(__('Auteurs', 'docalist-biblio'));
        $box->table('author')
                ->input('name')
            ->parent()
                ->input('firstname')
            ->parent()
                ->select('role')
                ->options($this->taxonomy('dclrefrole'));
        $box->table('organisation')
                ->input('name')
            ->parent()
                ->input('city')
            ->parent()
                ->select('country')
                ->options($this->taxonomy('dclcountry'))
            ->parent()
                ->select('role')
                ->options($this->taxonomy('dclrefrole'));
        $metaboxes['dclrefauthors'] = $box;

        // Journal, Issn, Volume, Issue
        $box = new Fragment();
        $box->label(__('Journal / périodique', 'docalist-biblio'));
        $box->input('journal')
            ->attribute('class', 'large-text');
        $box->input('issn');
        $box->input('volume');
        $box->input('issue');
        $metaboxes['dclrefjournal'] = $box;

        // Date / language / pagination / format
        $box = new Fragment();
        $box->label(__('Informations bibliographiques', 'docalist-biblio'));
        $box->input('date');
        $box->select('language')
            ->options($this->taxonomy('dcllanguage'));
        $box->input('pagination');
        $box->input('format');
        $metaboxes['dclrefbiblio'] = $box;

        // Editor / Collection / Edition / Isbn
        $box = new Fragment();
        $box->label(__('Informations éditeur', 'docalist-biblio'));
        $box->table('editor')
                ->input('name')
            ->parent()
                ->input('city')
            ->parent()
                ->select('country')
                ->options($this->taxonomy('dclcountry'));
        $box->table('collection')
                ->input('name')
            ->parent()
                ->input('number');
        $box->table('edition')
                ->input('type')
            ->parent()
                ->input('value');
        $box->input('isbn');
        $metaboxes['dclrefeditor'] = $box;

        // Event / Degree
        $box = new Fragment();
        $box->label(__('Congrès et diplômes', 'docalist-biblio'));
        $box->table('event')
                ->input('title')
            ->parent()
                ->input('date')
            ->parent()
                ->input('place')
            ->parent()
                ->input('number');
        $box->table('degree')
                ->select('level')
                ->options(array('licence','master','doctorat'))
            ->parent()
                ->input('title');
        $metaboxes['dclrefevent'] = $box;

        // Topic / Abstract / Note
        $box = new Fragment();
        $box->label(__('Indexation et résumé', 'docalist-biblio'));
        $box->table('topic')
                ->select('type')
                ->options(array('prisme', 'names', 'geo', 'free'))
            ->parent()
                ->input('term');

        $box->table('abstract')
                ->select('language')
                ->options($this->taxonomy('dcllanguage'))
            ->parent()
                ->textarea('content');
        $box->table('note')
                ->select('type')
                ->options($this->taxonomy('dclrefnote'))
            ->parent()
                ->textarea('content');

        $metaboxes['dclreftopics'] = $box;

        // Ref / Owner / Creation / Lastupdate
        $box = new Fragment();
        $box->label(__('Informations de gestion', 'docalist-biblio'));
        $box->input('ref');
        $box->input('owner');
        $box->table('creation')
                ->input('date')
            ->parent()
                ->input('by');
        $box->table('lastupdate')
                ->input('date')
            ->parent()
                ->input('by');
        $metaboxes['dclrefmanagement'] = $box;

        //@formatter:on
/*
        $box = new Fragment();
        $box->label(__('Notice brute', 'docalist-biblio'));
        $box->tag('pre', 'content');
        $metaboxes['dclrefjson'] = $box;
*/
        /*
        wp_cache_set(__METHOD__, $metaboxes);
        echo 'metaboxes enregistrées dans le cache wp<br />';
        */

        return $metaboxes;
    }

    protected function taxonomy($name) {
        $terms = get_terms($name, array(
            'hide_empty' => false,
        ));

        $result = array();
        foreach ($terms as $term) {
            $result[$term->slug] = $term->name;
        }

        return $result;
    }
}