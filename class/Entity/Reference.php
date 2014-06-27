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
namespace Docalist\Biblio\Entity;

use Docalist\Data\Entity\AbstractEntity;

/**
 * Référence documentaire.
 *
 * @property long $ref
 * @property string $type
 * @property string[] $genre
 * @property string[] $media
 * @property Docalist\Biblio\Entity\Reference\Author[] $author
 * @property Docalist\Biblio\Entity\Reference\Organisation[] $organisation
 * @property string $title
 * @property Docalist\Biblio\Entity\Reference\OtherTitle[] $othertitle
 * @property Docalist\Biblio\Entity\Reference\Translation[] $translation
 * @property Docalist\Biblio\Entity\Reference\Date[] $date
 * @property string $journal
 * @property Docalist\Biblio\Entity\Reference\Number[] $number
 * @property string $issn
 * @property string $volume
 * @property string $issue
 * @property string[] $language
 * @property Docalist\Biblio\Entity\Reference\Extent[] $extent
 * @property string $format
 * @property string $isbn
 * @property Docalist\Biblio\Entity\Reference\Editor[] $editor
 * @property string[] $edition
 * @property Docalist\Biblio\Entity\Reference\Collection[] $collection
 * @property Docalist\Biblio\Entity\Reference\Event $event
 * @property Docalist\Biblio\Entity\Reference\Topic[] $topic
 * @property Docalist\Biblio\Entity\Reference\Content[] $content
 * @property Docalist\Biblio\Entity\Reference\Link[] $link
 * @property string $doi
 * @property Docalist\Biblio\Entity\Reference\Relation[] $relation
 * @property string[] $owner
 * @property string $creation
 * @property string $lastupdate
 * @property string $status
 * @property string $imported
 * @property Entity[] $errors
 */
class Reference extends AbstractEntity {
    protected function loadSchema() {
        // @formatter:off
        return array(
            'ref' => array(         // Alias de post_name
                'type' => 'long',
                'label' => __('Numéro de référence', 'docalist-biblio'),
                'description' => __('Numéro unique identifiant la notice', 'docalist-biblio'),
            ),
            'parent' => array(      // Alias de post_parent
                'type' => 'long',
                'label' => __('Notice parent', 'docalist-biblio'),
                'description' => __('Numéro de la référence parent', 'docalist-biblio'),
            ),
            'title' => array(       // Alias de post_title
                'label' => __('Titre', 'docalist-biblio'),
                'description' => __('Titre original du document catalogué', 'docalist-biblio'),
            ),
            'status' => array(      // Alias de post_status
                'label' => __('Statut', 'docalist-biblio'),
                'description' => __('Statut de la notice.', 'docalist-biblio'),
            ),
            'creation' => array(    // Alias de post_date
                'label' => __('Création', 'docalist-biblio'),
                'description' => __('Date/heure de création de la notice.', 'docalist-biblio'),
            ),
            'lastupdate' => array(  // Alias de post_modified
                'label' => __('Dernière modification', 'docalist-biblio'),
                'description' => __('Date/heure de dernière modification.', 'docalist-biblio'),
            ),
            'password' => array(  // Alias de post_password
                'label' => __('Mot de passe', 'docalist-biblio'),
                'description' => __('Mot de passe de la notice.', 'docalist-biblio'),
            ),


            'type' => array(
                'label' => __('Type de notice', 'docalist-biblio'),
                'description' => __('Code unique décrivant la forme du document catalogué', 'docalist-biblio'),
            ),
            'genre' => array(
                'type' => 'string*',
                'label' => __('Genres', 'docalist-biblio'),
                'description' => __('Nature du document catalogué', 'docalist-biblio'),
            ),
            'media' => array(
                'type' => 'string*',
                'label' => __('Supports', 'docalist-biblio'),
                'description' => __('Support physique du document (papier, dvd, etc.)', 'docalist-biblio'),
            ),
            'author' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Author*',
                'label' => __('Auteurs', 'docalist-biblio'),
//                 'description' => __('Liste des personnes physiques auteurs du document', 'docalist-biblio'),
            ),
            'organisation' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Organisation*',
                'label' => __('Organismes', 'docalist-biblio'),
//                 'description' => __('Liste des auteurs moraux : organismes, collectivités auteurs, commanditaires, etc.', 'docalist-biblio'),
            ),
            'othertitle' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\OtherTitle*',
                'label' => __('Autres titres', 'docalist-biblio'),
//                 'description' => __("Titre de l'ensemble, du dossier, du supplément, etc.", 'docalist-biblio'),
            ),
            'translation' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Translation*',
                'label' => __('Traductions', 'docalist-biblio'),
//                 'description' => __('Traduction en une ou plusieurs langue du titre original qui figure dans Titre.', 'docalist-biblio'),
            ),
            'date' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Date*',
                'label' => __('Date', 'docalist-biblio'),
                'description' => __("Dates du document au format <code>AAAAMMJJ</code>, éventuellement complété (2009→2009<b>0101</b>). La première date saisie sera utilisée pour le tri.", 'docalist-biblio'),
            ),
            'journal'=> array(
                'label' => __('Périodique', 'docalist-biblio'),
                'description' => __('Nom du journal (revue, magazine, périodique, etc.) dans lequel a été publié le document.', 'docalist-biblio'),
            ),
            'number' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Number*',
                'label' => __('Numéros', 'docalist-biblio'),
                'description' => __('Numéros du document (ISSN, ISBN, volume, fascicule, ...)', 'docalist-biblio'),
            ),
            'issn' => array(
                'label' => __('ISSN', 'docalist-biblio'),
                'description' => __('International Standard Serial Number (identifiant du périodique) au format 1234-567X.', 'docalist-biblio'),
            ),
            'volume' => array(
                'label' => __('Volume', 'docalist-biblio'),
                'description' => __("Numéro de volume du périodique ou numéro de tome de l'ouvrage.", 'docalist-biblio'),
            ),
            'issue' => array(
                'label' => __('Fascicule', 'docalist-biblio'),
                'description' => __("Numéro du périodique de la revue dans lequel l'article a été publié.", 'docalist-biblio'),
            ),
            'language' => array(
                'type' => 'string*',
                'label' => __('Langues', 'docalist-biblio'),
                'description' => __("Langues des textes qui figurent dans le document.", 'docalist-biblio'),
            ),
            'extent' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Extent*',
                'label' => __('Etendue', 'docalist-biblio'),
                'description' => __("Pagination, nombre de pages, durée, etc.", 'docalist-biblio'),
            ),
            'format' => array(
                'type' => 'string',
                'label' => __('Format', 'docalist-biblio'),
                'description' => __('Caractéristiques matérielles du document : étiquettes de collation (tabl, ann, fig...), références bibliographiques, etc.', 'docalist-biblio'),
            ),
            'isbn' => array(
                'label' => __('ISBN', 'docalist-biblio'),
                'description' => __('International Standard Book Number (identifiant du livre) composé de 13 chiffres (10 pour des ouvrages anciens).', 'docalist-biblio'),
            ),
            'editor' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Editor*',
                'label' => __("Editeurs", 'docalist-biblio'),
                'description' => __("Société ou organisme délégué par l'auteur pour assurer la diffusion du document.", 'docalist-biblio'),
            ),
            'edition' => array(
                'type' => 'string*',
                'label' => __("Mentions d'édition", 'docalist-biblio'),
                'description' => __("Nouvelle édition, périodicité, etc.", 'docalist-biblio'),
            ),
            'collection' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Collection*',
                'label' => __('Collection', 'docalist-biblio'),
                'description' => __('Collection et numéro dans la collection, sous-collection et numéro dans la sous-collection, etc.', 'docalist-biblio'),
            ),
            'event' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Event',
                'label' => __("Evènement", 'docalist-biblio'),
                'description' => __('Evènement (congrès, colloque, manifestation, soutenance de thèse, etc.) qui a donné lieu au document', 'docalist-biblio'),
            ),
            'topic' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Topic*',
                'label' => __('Indexation', 'docalist-biblio'),
//                 'description' => __('Liste de listes de mots-clés.', 'docalist-biblio'),
            ),
            'content' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Content*',
                'label' => __('Contenu du document', 'docalist-biblio'),
//                 'description' => __('Notes, remarques et informations supplémentaires sur le document.', 'docalist-biblio'),
            ),
            'link' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Link*',
                'label' => __('Liens internet', 'docalist-biblio'),
//                 'description' => __("Liste de liens relatifs au document.", 'docalist-biblio'),
            ),
            'doi' => array(
                'label' => __('DOI', 'docalist-biblio'),
                'description' => __('Digital Object Identifier : identifiant unique de la ressource électronique.', 'docalist-biblio'),
            ),
            'relation' => array(
                'type' => 'Docalist\Biblio\Entity\Reference\Relation*',
                'label' => __("Relations avec d'autres notices", 'docalist-biblio'),
//                 'description' => __("Relations entre la notice cataloguée et d'autres notices de la même base.", 'docalist-biblio'),
            ),
            'owner' => array(
                'type' => 'string*',
                'label' => __('Producteur de la notice', 'docalist-biblio'),
                'description' => __('Personne ou organisme producteur de la notice.', 'docalist-biblio'),
            ),

            // Les champs qui suivent ne font pas partie du format docalist

            'imported' => array(
                'label' => __('Notice importée', 'docalist-biblio'),
            ),
            'errors' => array(
                'repeatable' => true,
                'label' => __('Erreurs()', 'docalist-biblio'),
                'fields' => array('code', 'value', 'message'),
            ),
        );
        // @formatter:on
    }

    /**
     * Retourne la première valeur du premier des champs qui est renseigné.
     *
     * @param string $field ... Le ou les champs à examiner.
     *
     * @return string
     */
    public function first($field) {
        foreach(func_get_args() as $field) {
            $field = $this->$field;
            if (is_scalar($field)) {
                if (! empty($field)) {
                    return $field;
                }
            }
            elseif ($field->count()) {
                return (string) $field[0];
            }
        }

        return '';
    }

    /**
     * Formatte le champ date.
     *
     * @param string $format
     * @return string
     */
    public function formatDate($format = 'j F Y') {
        $date = $this->date ?: $this->creation->date;

        return date_i18n('F Y', strtotime($this->date));
    }
}