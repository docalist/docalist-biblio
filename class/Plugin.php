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

use Docalist\AbstractPlugin;
use Docalist\Biblio\Repository\References;
use Docalist\Utils;
use Docalist\Biblio\Entity\Reference;
use Docalist\Forms\Themes;
use Docalist\Data\Repository\RepositoryInterface;

/**
 * Plugin de gestion de notices bibliographiques.
 */
class Plugin extends AbstractPlugin {

    /**
     * Les taxonomies créées par ce plugin
     *
     * @var Taxonomies
     */
    protected $taxonomies;

    /**
     * Les bases documentaires définies par l'utilisateur
     *
     * @var Database[]
     */
    protected $databases;


    public function register() {
        // Charge la configuration du plugin
        $settings = new Settings('docalist-biblio');
        $this->add($settings);

        // Crée les bases de données définies par l'utilisateur
        foreach ($settings->databases as $databaseSettings) {
            $database = new Database($databaseSettings);
            $this->databases[$database->postType()] = $database;
        }

        // Crée les taxonomies
        $this->registerTaxonomies();

        // Ajoute les pages d'administration dans le back-office de wordpress
        add_action('admin_menu', function () {
            foreach($this->databases as $database) {
                new ListReferences($database);
                new EditReference($database);
                $this->add(new ImportPage($database)); // @todo : enlever le add() quand AdminPage ne sera plus un registrable
            }
        });

        // Nos filtres
        add_filter('docalist_biblio_get_reference', array($this, 'getReference'), 10, 1);

        add_filter('get_the_excerpt', function($content) {
            global $post;

            // Récupère le type du post en cours
            $type = $post->post_type;

            // Vérifie que c'est une notices
            if (! isset($this->databases[$type])) {
                return $content;
            }

            // Construit un extrait de la notice
            $excerpt = 'un extrait de ma notice ' . $type . ' ' . $post->ID;

            return $excerpt;
        }, 11);

    }

    /**
     * Retourne l'objet référence dont l'id est passé en paramètre.
     *
     * Implémentation du filtre 'docalist_biblio_get_reference'.
     *
     * @param string $id
     * @throws Exception
     */
    public function getReference($id = null) {
        is_null($id) && $id = get_the_ID();
        $type = get_post_type($id);

        if (! isset($this->databases[$type])) {
            throw new Exception("Ce n'est pas une Reference"); // @todo
        }

        return $this->databases[$type]->load($id);
    }

    /**
     * Déclare dans WordPress les taxonomies utilisées.
     */
    protected function registerTaxonomies() {
        // Paramètres communs à toutes les taxonomies
        // @formatter:off
        $args = array(
            'hierarchical' => false,
            'show_ui' => true,
            'query_var' => false,
            'rewrite' => false,
        );
        // @formatter:on

        // Codes pays
        $args['label'] = __('Codes pays', 'docalist-biblio');
        register_taxonomy('dclcountry', array(), $args);

        // Codes langues
        $args['label'] = __('Codes langues', 'docalist-biblio');
        register_taxonomy('dcllanguage', array(), $args);

        // Types de documents
        $args['label'] = __('Types de documents', 'docalist-biblio');
        register_taxonomy('dclreftype', array(), $args);

        // Genres de documents
        $args['label'] = __('Genres de documents', 'docalist-biblio');
        register_taxonomy('dclrefgenre', array(), $args);

        // Supports de documents
        $args['label'] = __('Supports de documents', 'docalist-biblio');
        register_taxonomy('dclrefmedia', array(), $args);

        // Etiquettes de rôle
        $args['label'] = __('Etiquettes de rôle', 'docalist-biblio');
        register_taxonomy('dclrefrole', array(), $args);

        // Types de titres
        $args['label'] = __('Types de titres', 'docalist-biblio');
        register_taxonomy('dclreftitle', array(), $args);

        // Types de notes
        $args['label'] = __('Types de notes', 'docalist-biblio');
        register_taxonomy('dclrefnote', array(), $args);
    }
}