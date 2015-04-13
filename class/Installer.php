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

/**
 * Installation/désinstallation de docalist-biblio.
 */
class Installer {

    /**
     * Activation : enregistre les tables prédéfinies.
     *
     */
    public function activate() {
        $tableManager = docalist('table-manager'); /* @var $tableManager TableManager */

        // Enregistre les tables prédéfinies
        foreach($this->tables() as $name => $table) {
            $table['name'] = $name;
            $tableManager->register(new TableInfo($table));
        }
    }

    /**
     * Désactivation : supprime les tables prédéfinies.
     */
    public function deactivate() {
        $tableManager = docalist('table-manager'); /* @var $tableManager TableManager */

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
            ],
            'marc21-relators_en' => [
                'path' => $dir . 'relators/marc21-relators_en.txt',
                'label' => __('Etiquettes de rôles marc21 en anglais', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'roles',
            ],
            'relators_unimarc-to-marc21' => [
                'path' => $dir . 'relators/relators_unimarc-to-marc21.txt',
                'label' => __('Table de conversion des codes de fonction Unimarc en relators code Marc21.', 'docalist-core'),
                'format' => 'conversion',
                'type' => 'roles',
            ],

            // Exemple de thesaurus
            'thesaurus-example' => [
                'path' => $dir . 'thesaurus-example.txt',
                'label' => __('Exemple de table thesaurus', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'thesaurus',
            ],

            // Supports
            'medias' => [
                'path' => $dir . 'medias.txt',
                'label' => __('Supports de documents', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'medias',
            ],

            // Genres
            'genres' => [
                'path' => $dir . 'genres.txt',
                'label' => __('Genres de documents', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'genres',
            ],

            // Numbers (types de numéros)
            'numbers' => [
                'path' => $dir . 'numbers.txt',
                'label' => __('Types de numéros', 'docalist-biblio'),
                'format' => 'table',
                'type' => 'numbers',
            ],

            // Extent (types de pagination)
            'extent' => [
                'path' => $dir . 'extent.txt',
                'label' => __('Types de pagination', 'docalist-biblio'),
                'format' => 'table',
                'type' => 'extent',
            ],

            // Format (étiquettes de collation)
            'format' => [
                'path' => $dir . 'format.txt',
                'label' => __('Etiquettes de format', 'docalist-biblio'),
                'format' => 'thesaurus',
                'type' => 'format',
            ],

            // Dates
            'dates' => [
                'path' => $dir . 'dates.txt',
                'label' => __('Types de dates', 'docalist-biblio'),
                'format' => 'table',
                'type' => 'dates',
            ],

            // Anciennes tables
            'titles' => [
                'path' => $dir . 'titles.txt',
                'label' => __("Types de titres", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'titles',
            ],

            'topics' => [
                'path' => $dir . 'topics.php',
                'label' => __("Liste des vocabulaires disponibles pour l'indexation", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'topics',
            ],

            'content' => [
                'path' => $dir . 'content.txt',
                'label' => __("Contenu", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'content',
            ],

            'links' => [
                'path' => $dir . 'links.txt',
                'label' => __("Types de liens", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'links',
            ],

            'relations' => [
                'path' => $dir . 'relations.txt',
                'label' => __("Types de relations", 'docalist-biblio'),
                'format' => 'table',
                'type' => 'relations',
            ],
        ];

    }
}