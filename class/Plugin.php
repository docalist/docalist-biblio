<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio;

use Docalist\Biblio\Entity\ArticleEntity;
use Docalist\Biblio\Entity\BookEntity;
use Docalist\Biblio\Entity\BookChapterEntity;
use Docalist\Biblio\Entity\DegreeEntity;
use Docalist\Biblio\Entity\FilmEntity;
use Docalist\Biblio\Entity\LegislationEntity;
use Docalist\Biblio\Entity\MeetingEntity;
use Docalist\Biblio\Entity\PeriodicalEntity;
use Docalist\Biblio\Entity\PeriodicalIssueEntity;
use Docalist\Biblio\Entity\ReportEntity;
use Docalist\Biblio\Entity\WebSiteEntity;
use Docalist\Data\Database;
use Docalist\Biblio\Import\Crossref\CrossrefImporter;

/**
 * Plugin de gestion de notices bibliographiques.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Plugin {
    /**
     * Initialise le plugin.
     */
    public function __construct() {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-biblio', false, 'docalist-biblio/languages');

        // Déclare la liste des types définis dans ce plugin
        add_filter('docalist_databases_get_types', function(array $types) {
            $types += [
                'article'           => ArticleEntity::class,
                'book'              => BookEntity::class,
                'book-chapter'      => BookChapterEntity::class,
                'degree'            => DegreeEntity::class,
                'film'              => FilmEntity::class,
                'legislation'       => LegislationEntity::class,
                'meeting'           => MeetingEntity::class,
                'periodical'        => PeriodicalEntity::class,
                'periodical-issue'  => PeriodicalIssueEntity::class,
                'report'            => ReportEntity::class,
                'website'           => WebSiteEntity::class,
            ];

            return $types;
        });

        // Déclare l'import Crossref
        add_filter('docalist_databases_get_importers', function(array $importers, Database $database) {
            $importers[CrossrefImporter::getID()] = CrossrefImporter::class;

            return $importers;
        }, 10, 2);
    }
}
