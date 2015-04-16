<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
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

use Docalist\Table\TableManager;
use Docalist\Table\TableInfo;
use Docalist\Biblio\Reference;
use Docalist\Biblio\Settings\Settings;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Biblio\Pages\AdminDatabases;
use Exception;

/**
 * Plugin de gestion de notices bibliographiques.
 */
class Plugin {

    /**
     * La configuration du plugin.
     *
     * @var Settings
     */
    protected $settings;

    /**
     * La liste des bases
     *
     * @var Database[]
     */
    protected $databases;

    public function __construct() {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-biblio', false, 'docalist-biblio/languages');

        // Charge la configuration du plugin
        $this->settings = new Settings(docalist('settings-repository'));

        add_action('init', function() {

            // Crée les bases de données définies par l'utilisateur
            $this->databases = array();
            foreach ($this->settings->databases as $settings) {
                /* @var $settings DatabaseSettings */
                $database = new Database($settings);
                $this->databases[$database->postType()] = $database;
            }
        });

        // Crée la page Réglages » Docalist-Biblio
        add_action('admin_menu', function () {
            new AdminDatabases($this->settings);
        });

        // Nos filtres
        add_filter('docalist_biblio_get_reference', array($this, 'getReference'), 10, 2);

        // Liste des exporteurs définis dans ce plugin
        add_filter('docalist_biblio_get_export_formats', function(array $formats, Database $database) {
            $formats['docalist-json'] = [
                'label' => 'Compact',
                'converter' => 'Docalist\Biblio\Export\Converter',
                'exporter' => 'Docalist\Biblio\Export\Json',
            ];

            $formats['docalist-json-pretty'] = [
                'label' => 'Indenté',
                'converter' => 'Docalist\Biblio\Export\Converter',
                'exporter' => 'Docalist\Biblio\Export\Json',
                'exporter-settings' => [
                    'pretty' => true,
                ],
            ];

            $formats['docalist-xml'] = [
                'label' => 'Compact',
                'converter' => 'Docalist\Biblio\Export\Converter',
                'exporter' => 'Docalist\Biblio\Export\Xml',
            ];

            $formats['docalist-xml-pretty'] = [
                'label' => 'Indenté',
                'converter' => 'Docalist\Biblio\Export\Converter',
                'exporter' => 'Docalist\Biblio\Export\Xml',
                'exporter-settings' => [
                    'indent' => 4,
                ],
            ];

            return $formats;
        }, 10, 2);

        // Déclare les JS et les CSS prédéfinis inclus dans docalist-biblio
        add_action('init', function() {
            $this->registerAssets();
        });
    }

    /**
     * Retourne l'objet référence dont l'id est passé en paramètre.
     *
     * Implémentation du filtre 'docalist_biblio_get_reference'.
     *
     * @param string $id POST_ID de la référence à charger.
     * @param string|null $grid Grille à utiliser ou null pour retourner un
     * tableau contenant les données brutes.
     *
     * @return Reference|array Retourne un objet Reference si une grille a été
     * indiquée ; un tableau contenant les données de la notice sinon.
     *
     * @throws Exception
     */
    public function getReference($id = null, $grid = null) {
        is_null($id) && $id = get_the_ID();
        $type = get_post_type($id);

        if (! isset($this->databases[$type])) {
            $msg = __("Le post %s n'est pas une référence docalist (postype=%s)");
            throw new Exception(sprintf($msg, $id, $type));
        }

        $database = $this->databases[$type]; /* @var $database Database */
        return is_null($grid) ? $database->loadRaw($id) : $database->load($id, $grid);
    }

    /**
     * Déclare les scripts et styles standard de docalist-biblio.
     */
    protected function registerAssets() {
        $url = plugins_url('docalist-biblio');

        // Css pour EditReference (également utilisé dans le paramétrage de la grille de saisie)
        wp_register_style(
            'docalist-biblio-edit-reference',
            "$url/assets/edit-reference.css",
            ['wp-admin'],
            '140927'
        );
    }
}