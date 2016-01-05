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

use Docalist\Schema\Schema;
use Exception;

/**
 * Une référence documentaire.
 */
class Reference extends Type
{
    /**
     * Crée une notice du type indiqué.
     *
     * @param string $type
     * @param array $value
     * @param Schema $schema
     * @param string $id
     *
     * @return Reference
     * @throws Exception
     */
    public static function create($type, array $value = null, Schema $schema = null, $id = null)
    {
        $types = apply_filters('docalist_biblio_get_types', []);
        if (! isset($types[$type])) {
            throw new Exception("Type de notice inexistant : $type");
        }
        $class = $types[$type];

        $ref = new $class($value, $schema, $id); /* @var $ref Reference */
        $ref->type = $type;

        return $ref;
    }


    public static function loadSchema()
    {
        return [
            'name' => 'reference',
            'label' => __('Référence', 'docalist-biblio'),
            'description' => __('Une notice documentaire.', 'docalist-biblio'),
            'fields' => [
                'genre' => [
                    'type' => 'Docalist\Biblio\Field\Genre*',
                    'label' => __('Genres', 'docalist-biblio'),
                    'description' => __('Nature du document catalogué.', 'docalist-biblio'),
                    'table' => 'thesaurus:genres',
                ],
                'media' => [
                    'type' => 'Docalist\Biblio\Field\Media*',
                    'label' => __('Supports', 'docalist-biblio'),
                    'description' => __('Support physique du document : document imprimé, document numérique, dvd...', 'docalist-biblio'),
                    'table' => 'thesaurus:medias',
                ],
                'title' => [       // Alias de post_title
                    'type' => 'Docalist\Biblio\Field\Title',
                    'label' => __('Titre', 'docalist-biblio'),
                    'description' => __('Titre original du document catalogué.', 'docalist-biblio'),
                ],
                'othertitle' => [
                    'type' => 'Docalist\Biblio\Field\OtherTitle*',
                    'label' => __('Autres titres', 'docalist-biblio'),
                    'description' => __('Autres titres du document : sigle, variante, titre du dossier, du numéro, du diplôme...)', 'docalist-biblio'),
                    'table' => 'table:titles',
                    'explode' => true,
                    'format' => 'v (t)',
                ],
                'translation' => [
                    'type' => 'Docalist\Biblio\Field\Translation*',
                    'label' => __('Traductions', 'docalist-biblio'),
                    'description' => __('Traduction en une ou plusieurs langues du titre original du document.', 'docalist-biblio'),
                    'table' => 'table:ISO-639-2_alpha3_EU_fr',
                    'explode' => true,
                ],
                'author' => [
                    'type' => 'Docalist\Biblio\Field\Author*',
                    'label' => __('Auteurs', 'docalist-biblio'),
                    'description' => __("Liste des personnes qui ont contribué à l'élaboration du document : auteur, coordonnateur, réalisateur...", 'docalist-biblio'),
                    'table' => 'thesaurus:marc21-relators_fr',
                ],
                'organisation' => [
                    'type' => 'Docalist\Biblio\Field\Organisation*',
                    'label' => __('Organismes', 'docalist-biblio'),
                    'description' => __("Liste des organismes qui ont contribué à l'élaboration du document : organisme considéré comme auteur, organisme commanditaire, financeur...", 'docalist-biblio'),
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'table2' => 'thesaurus:marc21-relators_fr',
                    'sep' => ' ; ', // sép par défaut à l'affichage, espace insécable avant ';'
                ],
                'date' => [
                    'type' => 'Docalist\Biblio\Field\Date*',
                    'label' => __('Date', 'docalist-biblio'),
                    'description' => __("Dates du document au format <code>AAAAMMJJ</code> : date de publication, date d'enregistrement...", 'docalist-biblio'),
                    'table' => 'table:dates',
                ],
                'journal' => [
                    'type' => 'Docalist\Biblio\Field\Journal',
                    'label' => __('Périodique', 'docalist-biblio'),
                    'description' => __('Nom du journal (revue, magazine, périodique...) dans lequel a été publié le document.', 'docalist-biblio'),
                ],
                'number' => [
                    'type' => 'Docalist\Biblio\Field\Number*',
                    'label' => __('Numéros', 'docalist-biblio'),
                    'description' => __('Numéros du document : DOI, ISSN, ISBN, numéro de volume, numéro de fascicule...', 'docalist-biblio'),
                    'table' => 'table:numbers',
                ],
                'language' => [
                    'type' => 'Docalist\Biblio\Field\Language*',
                    'label' => __('Langues', 'docalist-biblio'),
                    'description' => __('Langues des textes qui figurent dans le document catalogué.', 'docalist-biblio'),
                    'table' => 'table:ISO-639-2_alpha3_EU_fr',
                ],
                'extent' => [
                    'type' => 'Docalist\Biblio\Field\Extent*',
                    'label' => __('Etendue', 'docalist-biblio'),
                    'description' => __('Pagination, nombre de pages, durée, dimensions...', 'docalist-biblio'),
                    'table' => 'table:extent',
                ],
                'format' => [
                    'type' => 'Docalist\Biblio\Field\Format*',
                    'label' => __('Format', 'docalist-biblio'),
                    'description' => __("Etiquettes de collation utilisées pour décrire ce que l'on trouve dans le document catalogué : tableaux, annexes, références bibliographiques...", 'docalist-biblio'),
                    'table' => 'thesaurus:format',
                ],
                'editor' => [
                    'type' => 'Docalist\Biblio\Field\Editor*',
                    'label' => __('Editeurs', 'docalist-biblio'),
                    'description' => __("Société ou organisme délégué par l'auteur pour assurer la diffusion et la distribution du document.", 'docalist-biblio'),
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'table2' => 'thesaurus:marc21-relators_fr',
                ],
                'collection' => [
                    'type' => 'Docalist\Biblio\Field\Collection*',
                    'label' => __('Collection', 'docalist-biblio'),
                    'description' => __("Collection, sous-collection et numéro au sein de la collection de l'éditeur.", 'docalist-biblio'),
                ],
                'edition' => [
                    'type' => 'Docalist\Biblio\Field\Edition*',
                    'label' => __("Mentions d'édition", 'docalist-biblio'),
                    'description' => __("Mentions utilisées pour décrire le type de l'édition : nouvelle édition, édition revue et corrigée, périodicité...", 'docalist-biblio'),
                ],
                'event' => [
                    'type' => 'Docalist\Biblio\Field\Event',
                    'label' => __('Evènement', 'docalist-biblio'),
                    'description' => __("Description de l'évènement à l'origine du document : congrès, colloque, manifestation, soutenance de thèse...", 'docalist-biblio'),
                ],
                'topic' => [
                    'type' => 'Docalist\Biblio\Field\Topic*',
                    'label' => __('Indexation', 'docalist-biblio'),
                    'description' => __("Mots-clés décrivant le contenu du document. Les mots-clés utilisés peuvent provenir d'un ou plusieurs vocabulaires différents.", 'docalist-biblio'),
                    'table' => 'table:topics',
                ],
                'content' => [
                    'type' => 'Docalist\Biblio\Field\Content*',
                    'label' => __('Contenu du document', 'docalist-biblio'),
                    'description' => __('Description du contenu du document : résumé, présentation, critique, remarques...', 'docalist-biblio'),
                    'table' => 'table:content',
                ],
                'link' => [
                    'type' => 'Docalist\Biblio\Field\Link*',
                    'label' => __('Liens internet', 'docalist-biblio'),
                    'description' => __("Liens associés au document : site de l'auteur, accès au texte intégral, site de l'éditeur...", 'docalist-biblio'),
                    'table' => 'table:links',
                ],
                'relation' => [
                    'type' => 'Docalist\Biblio\Field\Relation*',
                    'label' => __("Relations avec d'autres notices", 'docalist-biblio'),
                    'description' => __("Relations entre ce document et d'autres documents déjà catalogués : voir aussi, nouvelle édition, erratum...", 'docalist-biblio'),
                    'table' => 'table:relations',
                ],
                'owner' => [
                    'type' => 'Docalist\Biblio\Field\Owner*',
                    'label' => __('Producteur de la notice', 'docalist-biblio'),
                    'description' => __('Personne ou organisme producteur de la notice.', 'docalist-biblio'),
                ],

                // Les champs qui suivent ne font pas partie du format docalist

                'imported' => [
                    'type' => 'Docalist\Biblio\Field\Imported',
                    'label' => __('Notice importée', 'docalist-biblio'),
//                    'editor' => 'textarea',
                ],
                'errors' => [
                    'type' => 'Docalist\Biblio\Field\Error*',
                    'label' => __('Erreurs()', 'docalist-biblio'),
                ],
            ],
        ];
    }
}
