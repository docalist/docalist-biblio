<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio;

/**
 * Plugin de gestion de notices bibliographiques.
 */
class Plugin {
    /**
     * Initialise le plugin.
     */
    public function __construct() {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-biblio', false, 'docalist-biblio/languages');

        // Déclare la liste des types définis dans ce plugin
        add_filter('docalist_biblio_get_types', function(array $types) {
            $types += [
                'article'           => 'Docalist\Biblio\Reference\Article',
                'book'              => 'Docalist\Biblio\Reference\Book',
                'book-chapter'      => 'Docalist\Biblio\Reference\BookChapter',
                'degree'            => 'Docalist\Biblio\Reference\Degree',
                'film'              => 'Docalist\Biblio\Reference\Film',
                'legislation'       => 'Docalist\Biblio\Reference\Legislation',
                'meeting'           => 'Docalist\Biblio\Reference\Meeting',
                'periodical'        => 'Docalist\Biblio\Reference\Periodical',
                'periodical-issue'  => 'Docalist\Biblio\Reference\PeriodicalIssue',
                'report'            => 'Docalist\Biblio\Reference\Report',
                'website'           => 'Docalist\Biblio\Reference\WebSite',
            ];

            return $types;
        });
    }
}
