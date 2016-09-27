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
 */
namespace Docalist\Biblio;

use Docalist\Table\TableManager;
use Docalist\Table\TableInfo;

/**
 * Installation/désinstallation de docalist-biblio.
 */
class Installer {

    /**
     * Activation : enregistre les tables prédéfinies.
     *
     */
    public function activate() {
        $tableManager = docalist('table-manager'); /** @var TableManager $tableManager */

        // Enregistre les tables prédéfinies
        foreach($this->tables() as $name => $table) {
            $table['name'] = $name;
            $table['lastupdate'] = date_i18n('Y-m-d H:i:s', filemtime($table['path']));
            $tableManager->register(new TableInfo($table));
        }
    }

    /**
     * Désactivation : supprime les tables prédéfinies.
     */
    public function deactivate() {
        $tableManager = docalist('table-manager'); /** @var TableManager $tableManager */

        // Supprime les tables prédéfinies
        foreach(array_keys($this->tables()) as $table) {
            $tableManager->unregister($table);
        }
    }

    /**
     * Retourne la liste des tables prédéfinies.
     *
     * @return array
     */
    protected function tables() {
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tables'  . DIRECTORY_SEPARATOR;
        return [
            // Etiquettes de rôles
            'marc21-relators_fr' => [
                'path' => $dir . 'relators/marc21-relators_fr.txt',
                'label' => __('Etiquettes de rôles marc21 en français', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'roles',
                'creation' => '2014-03-14 11:04:05',
            ],
            'marc21-relators_en' => [
                'path' => $dir . 'relators/marc21-relators_en.txt',
                'label' => __('Etiquettes de rôles marc21 en anglais', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'roles',
                'creation' => '2014-03-14 11:04:46',
            ],
            'relators_unimarc-to-marc21' => [
                'path' => $dir . 'relators/relators_unimarc-to-marc21.txt',
                'label' => __('Table de conversion des codes de fonction Unimarc en relators code Marc21.', 'docalist-core'),
                'format' => 'conversion',
                'type' => 'roles',
                'creation' => '2014-03-17 07:42:03',
            ],

            // Exemple de thesaurus
            'thesaurus-example' => [
                'path' => $dir . 'thesaurus-example.txt',
                'label' => __('Exemple de table thesaurus', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'thesaurus',
                'creation' => '2014-03-17 07:57:35',
            ],

            // Supports
            'medias' => [
                'path' => $dir . 'medias.txt',
                'label' => __('Supports de documents', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'medias',
                'creation' => '2013-11-02 11:02:15',
            ],

            // Genres
            'genres' => [
                'path' => $dir . 'genres.txt',
                'label' => __('Genres de documents', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'genres',
                'creation' => '2014-07-16 09:41:59',
            ],

            // Numbers (types de numéros)
            'numbers' => [
                'path' => $dir . 'numbers.txt',
                'label' => __('Types de numéros', 'docalist-biblio'),
                'format' => 'table',
                'type' => 'numbers',
                'creation' => '2014-06-26 00:30:23',
            ],

            // Extent (types de pagination)
            'extent' => [
                'path' => $dir . 'extent.txt',
                'label' => __('Types de pagination', 'docalist-biblio'),
                'format' => 'table',
                'type' => 'extent',
                'creation' => '2014-06-12 11:47:35',
            ],

            // Format (étiquettes de collation)
            'format' => [
                'path' => $dir . 'format.txt',
                'label' => __('Etiquettes de format', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'format',
                'creation' => '2014-06-11 14:26:20',
            ],

            // Dates
            'dates' => [
                'path' => $dir . 'dates.txt',
                'label' => __('Types de dates', 'docalist-biblio'),
                'format' => 'table',
                'type' => 'dates',
                'creation' => '2014-06-03 08:01:53',
            ],

            // Types de titres
            'titles' => [
                'path' => $dir . 'titles.txt',
                'label' => __("Types de titres", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'titles',
                'creation' => '2013-11-04 01:28:47',
            ],

            // Vocabulaires disponibles
            'topics' => [
                'path' => $dir . 'topics.php',
                'label' => __("Liste des vocabulaires disponibles pour l'indexation", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'topics',
                'creation' => '2014-02-05 08:38:11',
            ],

            // Types de contenus
            'content' => [
                'path' => $dir . 'content.txt',
                'label' => __("Contenu", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'content',
                'creation' => '2013-11-04 03:25:53',
            ],

            // Types de liens
            'links' => [
                'path' => $dir . 'links.txt',
                'label' => __("Types de liens", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'links',
                'creation' => '2013-11-05 00:38:40',
            ],

            // Types de relations
            'relations' => [
                'path' => $dir . 'relations.txt',
                'label' => __("Types de relations", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'relations',
                'creation' => '2013-11-05 03:13:18',
            ],
        ];
    }
}