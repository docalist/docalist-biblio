<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Import\Crossref;

use Docalist\Data\Import\Converter;
use Docalist\Biblio\Entity\ReferenceEntity;
use Exception;
use InvalidArgumentException;

/**
 * Convertit une notice du format Crossref vers le format Reference Docalist-Biblio.
 *
 * @see https://github.com/CrossRef/rest-api-doc/blob/master/api_format.md#work
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class CrossrefConverter implements Converter
{
    public function __invoke(array $data)
    {
        try {
            $ref = new ReferenceEntity();           // Champs Crossref traités
            $this->doType($data, $ref);             // type
            $this->doSource($data, $ref);           //
            $this->doTitles($data, $ref);           // title, original-title, short-title, subtitle
            $this->doContainerTitle($data, $ref);   // container-title, short-container-title, group-title
            $this->doAuthors($data, $ref);          // author, editor, chair, translator
            $this->doCorporates($data, $ref);       // funder
            $this->doPublisher($data, $ref);        // publisher
            $this->doNumbers($data, $ref);          // volume, issue, DOI, issn-type, ISSN (ignoré), ISBN
            $this->doDates($data, $ref);            // created, deposited, indexed, issued, posted, accepted, print, online
            $this->doPage($data, $ref);             // page
            $this->doAbstract($data, $ref);         // abstract
            $this->doTopic($data, $ref);            // subject

            // Champs non traités :
            // - reference, reference-count, references-count, is-referenced-by-count.
            // - TODO source : contient 'crossref', voir comment on pourrait gèrer ça dans Docalist.
            // - prefix : déjà dans le champ DOI (partie avant le slash).
            // - article-number : déjà dans le champ DOI (partie après le slash).
            // - URL : contient la version url du DOI, mais c'est l'ancienne adresse (http + dx)
            // - member : numéro crossref de l'organization qui a déposé les meta données du document.
            // - archive : contient un code (un nom). Endroit ou on a une archive du doc ?
            // - license : pas stockable dans une ref docalist (ajouter un type de lien 'licence' ?)
            // - relation : travaux cités, toujours vide sur jeu d'essai.
            // - update-to : signale que c'est une correction d'un autre DOI, pas stockable dans docalist.
            // - update-policy : url vers page de l'éditeur sur politique de correction, pas utile.
            // - content-domain : pas compris ce que c'était
            // - alternative-id : contient souvent une copie du DOI, sinon numéros internes à l'éditeur
            // - clinical-trial-number : pas rempli dans jeu d'essai, infos sur les essais cliniques
            // - TODO assertion : contient des infos de copyright et de peer-review.
            // - TODO link : la majorité des liens sont soient rompus, soit inutilisables.
        } catch (Exception $e) {
            printf('<span style="color:red">ERREUR: %s</span><br />', $e->getMessage());

            return null;
        }

        $ref->filterEmpty(false);

        return $ref;
    }

    protected function doType(array $data, ReferenceEntity $ref)
    {
        // type (String, required) : Enumeration, one of the type ids from https://api.crossref.org/v1/types

        // Liste des types Crossref : https://api.crossref.org/types
        $types = [
        //  'book-section'          => '?',             // Book Section (0)
            'monograph'             => 'book',          // Monograph (11)
            'report'                => 'report',        // Report (5)
        //  'book-track'            => '?',             // Book Track (0)
            'journal-article'       => 'article',       // Journal Article (1642)
        //  'book-part'             => '?',             // Part (0)
            'other'                 => '?',             // Other (1)
            'book'                  => 'book',          // Book (16)
        //  'journal-volume'        => '?',             // Journal Volume (0)
        //  'book-set'              => '?',             // Book Set (0)
            'reference-entry'       => '?',             // Reference Entry (35)
            'proceedings-article'   => '?',             // Proceedings Article (5)
        //  'journal'               => '?',             // Journal (0)
        //  'component'             => '?',             // Component (0)
            'book-chapter'          => 'book-chapter',  // Book Chapter (316)
        //  'report-series'         => '?',             // Report Series (0)
        //  'proceedings'           => '?',             // Proceedings (0)
        //  'standard'              => '?',             // Standard (0)
            'reference-book'        => '?',             // Reference Book (1)
            'posted-content'        => '?',             // Posted Content (1) : contient résumé (champ "abstract")
        //  'journal-issue'         => '?',             // Journal Issue (0)
            'dissertation'          => '?',             // Dissertation (10)
            'dataset'               => '?',             // Dataset (65)
        //  'book-series'           => '?',             // Book Series (0)
        //  'edited-book'           => '?',             // Edited Book (0)
        //  'standard-series'       => '?',             // Standard Series (0)
        ];

        if (! isset($data['type'])) {
            throw new InvalidArgumentException('Pas de champ "type" dans la notice Crossref');
        }
        $type = $data['type'];

        if (! isset($types[$type]) || $types[$type] === '?') {
            throw new InvalidArgumentException('Type de notice Crossref non géré : ' . $type);
        }

        $ref->type = $types[$type];
    }

    protected function doSource(array $data, ReferenceEntity $ref)
    {
        $source = ['type' => 'crossref','value' => date('Y-m-d')];
        if (isset($data['DOI'])) {
            $source['url'] = 'https://api.crossref.org/v1/works/' . $data['DOI'];
        }
        $ref->source = [$source];
    }

    protected function doTitles(array $data, ReferenceEntity $ref)
    {
        // title            (Array of String, required) : Work titles, including translated titles
        // original-title   (Array of String, optional) : Work titles in the work's original publication language
        // short-title      (Array of String, optional) : Short or abbreviated work titles

        if (isset($data['title'])) {
            $ref->title = array_shift($data['title']);
        }

        // 'original-title' : aucun trouvé dans jeu d'essai
        // 'short-title' : aucun trouvé dans jeu d'essai
        // 'title' multivalué : aucun trouvé dans jeu d'essai
        // 'subtitle' : dans la très grande majorité des cas, figure déjà dans title, donc fait double emploi
    }

    protected function doContainerTitle(array $data, ReferenceEntity $ref)
    {
        if (isset($data['container-title'])) {
            $type = $data['type'] ?? '';
            switch ($type) {
                case 'journal-article':
                    $ref->journal = array_shift($data['container-title']);
                    break;

                case 'book-chapter': // TODO : revoir le type
                    $ref->othertitle[] = ['type' => 'common', 'value' => array_shift($data['container-title'])];
                    break;

                case 'book':
                    // Pour un livre, ça pourrait contenir le nom de la collection, mais
                    // dans la pratique on a un copier/coller du nom de l'éditeur, donc on ignore.
                    break;

                default:
                    echo "Champ 'container-title' non géré pour les notices de type '$type'<br />";
            }
        }

        // short-container-title : non traité

        if (isset($data['group-title'])) {
            $ref->othertitle[] = ['type' => 'common', 'value' => $data['container-title']]; // TODO : revoir le type
        }
    }

    protected function doAuthors(array $data, ReferenceEntity $ref)
    {
        $fields = [
            // champ crossref   => étiquette de rôle (marc21-relator)
            'author'            => '',      // auteur (mettre "/aut" ?)
            'editor'            => 'edt',   // éditeur intellectuel
            'chair'             => 'pra',   // présidence
            'translator'        => 'trl',   // traducteur
        ];

        foreach($fields as $field => $role) {
            if (isset($data[$field])) {
                foreach ($data[$field] as $data) {
                    // cf. https://github.com/CrossRef/rest-api-doc/blob/master/api_format.md#contributor
                    $ref->author[] = [
                        'name'      => $data['family'] ?? '',
                        'firstname' => $data['given'] ?? '',
                        'role'      => $role,
                    ];
                    // non traités : ORCID, authenticated-orcid, affiliation
                }
            }
        }

        // 'name' peut contenir : "No authorship indicated"
    }

    protected function doCorporates(array $data, ReferenceEntity $ref)
    {
        //funder (Array of Funder, optional)
        $fields = [
            // champ crossref   => étiquette de rôle (marc21-relator)
            'funder'            => 'fnd',   // Bailleur de fonds
        ];

        foreach($fields as $field => $role) {
            if (isset($data[$field])) {
                foreach ($data[$field] as $data) {
                    // cf. https://github.com/CrossRef/rest-api-doc/blob/master/api_format.md#contributor
                    $ref->corporation[] = [
                        'name' => $data['name'] ?? '',
                        'role' => $role,
                    ];
                    // non traités : DOI, award, doi-asserted-by
                }
            }
        }
    }

    protected function doPublisher(array $data, ReferenceEntity $ref)
    {
        if (isset($data['publisher'])) {
            $ref->editor[] = ['name' => $data['publisher']]; // TODO: ville, pays
        }
    }

    protected function doNumbers(array $data, ReferenceEntity $ref)
    {
        // volume (String, optional) : Volume number of an article's journal
        if (isset($data['volume'])) {
            $ref->number[] = ['type' => 'volume-no', 'value' => $data['volume']];
        }

        // issue (String, optionale) : Issue number of an article's journal
        if (isset($data['issue'])) {
            $ref->number[] = ['type' => 'issue-no', 'value' => $data['issue']];
        }

        // DOI (String, required) : DOI of the work
        if (isset($data['DOI'])) {
            // On le stocke comme un lien, pas comme un numéro
            $ref->link[] = ['type' => 'doi', 'url' => 'https://doi.org/' . $data['DOI']];
        }

        // issn-type (Array of ISSN with Type, optional) : List of ISSNs with ISSN type information
        if (isset($data['issn-type'])) {
            foreach ($data['issn-type'] as $issn) {
                switch ($issn['type']) {
                    case 'print':
                        $type = 'issn-p';
                        break;

                    case 'electronic':
                        $type = 'issn-e';
                        break;

                    case '??': // quel est le code utilisé par Crossref pour "issn de liaison" ?
                        $type = 'issn-l';

                    default:
                        $type = 'issn'; // générique
                        echo "Type d'ISSN crossref non géré : ", $issn['type'], "<br />";

                }
                $ref->number[] = ['type' => $type, 'value' => $issn['value']];
            }
        }

        // ISBN	(Array of String, optional)
        if (isset($data['ISBN'])) {
            foreach($data['ISBN'] as $isbn) {
                $ref->number[] = ['type' => 'isbn', 'value' => $isbn];
            }
        }

        // prefix : déjà dans DOI
        // article-number : déjà dans DOI
    }

    protected function doDates(array $data, ReferenceEntity $ref)
    {
        // created (Date, required) : Date on which the DOI was first registered
        // deposited (Date, required) : Date on which the work metadata was most recently updated
        // indexed (Date, required) : Date on which the work metadata was most recently indexed. Re-indexing does not imply a metadata change, see deposited for the most recent metadata change date
        //
        // issued (Partial Date, required) : Eariest of published-print and published-online
        // posted (Partial Date, optional) : Date on which posted content was made available online
        // accepted (Partial Date, optional) : Date on which a work was accepted, after being submitted, during a submission process
        // published-print (Partial Date, optional) : Date on which the work was published in print
        // published-online (Partial Date, optional) : Date on which the work was published online

        // Récupère les dates qui figurent dans la notice Crossref
        // 'created', 'deposited', 'indexed' : ignorés
        $issued = isset($data['issued']) ? $this->partialDate($data['issued']) : null;
        $posted = isset($data['posted']) ? $this->partialDate($data['posted']) : null;
//        $accepted = isset($data['accepted']) ? $this->partialDate($data['accepted']) : null;
        $print = isset($data['print']) ? $this->partialDate($data['print']) : null;
        $online = isset($data['online']) ? $this->partialDate($data['online']) : null;

        // Si on a une date 'posted' et pas de 'issued', on swappe
        if (is_null($issued) && !is_null($posted)) {
            $issued = $posted;
            $posted = null;
        }

        // Stocke les dates
        $issued && $ref->date[] = ['type' => 'publication', 'value' => $issued];
        $posted && $ref->date[] = ['type' => 'first-publication', 'value' => $posted]; // TODO : revoir type
//      $accepted && $ref->date[] = ['type' => 'first-publication', 'value' => $accepted]; // TODO : pas dans la table
        $print && $ref->date[] = ['type' => 'print', 'value' => $print];
        $online && $ref->date[] = ['type' => 'first-publication', 'value' => $online]; // TODO : revoir type
    }

    private function partialDate(array $date)
    {
        if (! isset($date['date-parts'])) {
            echo 'Partial Date sans clé "date-parts"<br />';
            return null;
        }

        $date = $date['date-parts'];

        // date-parts est un tableau de tableaux, on a un étage en trop
        $date = reset($date);

        $result = $date[0]; // year
        isset($date[1]) && $result .= substr('0' . $date[1], -2);
        isset($date[2]) && $result .= substr('0' . $date[2], -2);

        return $result;
    }

    protected function doPage(array $data, ReferenceEntity $ref)
    {
        // page (String, optional) : Pages numbers of an article within its journal
        if (isset($data['page'])) {
            $page = $data['page'];
            $type = (strpos($page, '-') === false) ? 'page' : 'page-range';
            $ref->extent[] = ['type' => $type, 'value' => $page];
        }
    }

    protected function doAbstract(array $data, ReferenceEntity $ref)
    {
        // abstract (XML String, Optional) : Abstract as a JSON string or a JATS XML snippet encoded into a JSON string
        // JATS : cf. https://en.wikipedia.org/wiki/Journal_Article_Tag_Suite

        if (isset($data['abstract'])) {
            $abstract = $data['abstract'];
            $abstract = strip_tags($abstract);
            $ref->content[] = ['type' => 'abstract', 'value' => $abstract];
        }
    }

    protected function doTopic(array $data, ReferenceEntity $ref)
    {
        // subject (Array of String, optional) : Subject category names, a controlled vocabulary from Sci-Val. Available for most journal articles
        if (isset($data['subject'])) {
            $ref->topic[] = ['type' => 'free', 'value' => $data['subject']];
        }
    }
}
