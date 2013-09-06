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
 * @property string $date
 * @property string $journal
 * @property string $issn
 * @property string $volume
 * @property string $issue
 * @property string[] $language
 * @property string[] $pagination
 * @property string $format
 * @property string $isbn
 * @property Docalist\Biblio\Entity\Reference\Editor[] $editor
 * @property Docalist\Biblio\Entity\Reference\Edition[] $edition
 * @property Docalist\Biblio\Entity\Reference\Collection[] $collection
 * @property Docalist\Biblio\Entity\Reference\Event $event
 * @property Docalist\Biblio\Entity\Reference\Degree $degree
 * @property Docalist\Biblio\Entity\Reference\AbstractField[] $abstract
 * @property Docalist\Biblio\Entity\Reference\Topic[] $topic
 * @property Docalist\Biblio\Entity\Reference\Note[] $note
 * @property Docalist\Biblio\Entity\Reference\Link[] $link
 * @property string $doi
 * @property Docalist\Biblio\Entity\Reference\Relation[] $relations
 * @property string[] $owner
 * @property Docalist\Biblio\Entity\Reference\DateBy $creation
 * @property Docalist\Biblio\Entity\Reference\DateBy $lastupdate
 * @property string[] $status
 */
class Reference extends AbstractEntity {
    protected function loadSchema() {
        // @formatter:off
        return array(
            'ref' => array(
                'type' => 'long',
                'label' => __('Numéro de référence', 'docalist-biblio'),
                'description' => __('Numéro unique identifiant la notice', 'docalist-biblio'),
            ),
            'type' => array(
                'label' => __('Type de document', 'docalist-biblio'),
                'description' => __('Code unique décrivant la forme du document catalogué', 'docalist-biblio'),
            ),
            'genre' => array(
                'type' => 'string*',
                'label' => __('Genre de document', 'docalist-biblio'),
                'description' => __('Nature du document catalogué', 'docalist-biblio'),
            ),
            'media' => array(
                'type' => 'string*',
                'label' => __('Support du document', 'docalist-biblio'),
                'description' => __('Support physique du document (papier, dvd, etc.)', 'docalist-biblio'),
            ),
            'author' => array(
                'type' => 'Reference\Author*',
                'label' => __('Auteur(s) du document', 'docalist-biblio'),
                'description' => __('Liste des personnes physiques auteurs du document', 'docalist-biblio'),
            ),
            'organisation' => array(
                'type' => 'Reference\Organisation*',
                'label' => __('Organisme(s) auteur(s) du document', 'docalist-biblio'),
                'description' => __('Liste des auteurs moraux : organismes, collectivités auteurs, commanditaires, etc.', 'docalist-biblio'),
            ),
            'title' => array(
                'label' => __('Titre du document', 'docalist-biblio'),
                'description' => __('Titre original du document catalogué', 'docalist-biblio'),

            ),
            'othertitle' => array(
                'type' => 'Reference\OtherTitle*',
                'label' => __('Autre(s) titre(s)', 'docalist-biblio'),
                'description' => __("Titre de l'ensemble, du dossier, du supplément, etc.", 'docalist-biblio'),
            ),
            'translation' => array(
                'type' => 'Reference\Translation*',
                'label' => __('Traduction(s) du titre', 'docalist-biblio'),
                'description' => __('Traduction en une ou plusieurs langue du titre original qui figure dans Titre.', 'docalist-biblio'),
            ),
            'date' => array(
                'label' => __('Date de publication', 'docalist-biblio'),
                'description' => __("Date d'édition ou de diffusion du document AAAA[MM[JJ]].", 'docalist-biblio'),
            ),
            'journal'=> array(
                'label' => __('Titre de périodique', 'docalist-biblio'),
                'description' => __('Nom du journal (revue, magazine, périodique, etc.) dans lequel a été publié le document.', 'docalist-biblio'),
            ),
            'issn' => array(
                'label' => __('ISSN', 'docalist-biblio'),
                'description' => __('International Standard Serial Number (identifiant du périodique) au format 1234-567X.', 'docalist-biblio'),
            ),
            'volume' => array(
                'label' => __('Numéro de volume', 'docalist-biblio'),
                'description' => __("Numéro de volume du périodique ou numéro de tome de l'ouvrage.", 'docalist-biblio'),
            ),
            'issue' => array(
                'label' => __('Numéro de fascicule', 'docalist-biblio'),
                'description' => __("Numéro du périodique de la revue dans lequel l'article a été publié.", 'docalist-biblio'),
            ),
            'language' => array(
                'type' => 'string*',
                'label' => __('Langue(s) du document', 'docalist-biblio'),
                'description' => __("Langues des textes qui figurent dans le document.", 'docalist-biblio'),
            ),
            'pagination' => array(
                'type' => 'string', // @todo mettre en multivalué
                'label' => __('Pagination', 'docalist-biblio'),
                'description' => __("Pour un ouvrage : nombre de pages (180p.), pour un article : numéro de page (10) ou pages de début et de fin (10-15).", 'docalist-biblio'),
            ),
            'format' => array(
                'type' => 'string',
                'label' => __('Format du document', 'docalist-biblio'),
                'description' => __('Caractéristiques matérielles du document : étiquettes de collation (tabl, ann, fig...), références bibliographiques, etc.', 'docalist-biblio'),
            ),
            'isbn' => array(
                'label' => __('ISBN', 'docalist-biblio'),
                'description' => __('International Standard Book Number (identifiant du livre) composé de 13 chiffres (10 pour des ouvrages anciens).', 'docalist-biblio'),
            ),
            'editor' => array(
                'type' => 'Reference\Editor*',
                'label' => __("Editeur et lieu d'édition", 'docalist-biblio'),
                'description' => __("Société ou organisme délégué par l'auteur pour assurer la diffusion du document.", 'docalist-biblio'),
            ),
            'edition' => array(
                'type' => 'Reference\Edition*',
                'label' => __("Mentions d'édition", 'docalist-biblio'),
                'description' => __("Mentions d'édition et numéros divers qui ne font pas l'objet d'un champ spécifique.", 'docalist-biblio'),
            ),
            'collection' => array(
                'type' => 'Reference\Collection*',
                'label' => __('Collection', 'docalist-biblio'),
                'description' => __('Collection et numéro dans la collection, sous-collection et numéro dans la sous-collection, etc.', 'docalist-biblio'),
            ),
            'event' => array(
                'type' => 'Reference\Event',
                'label' => __("Informations sur l'évènement", 'docalist-biblio'),
                'description' => __('Evènement (congrès, colloque, manifestation, soutenance de thèse, etc.) qui a donné lieu au document', 'docalist-biblio'),
            ),
            'degree' => array(
                'type' => 'Reference\Degree',
                'label' => __('Diplôme', 'docalist-biblio'),
                'description' => __("Nom du diplôme pour les documents donnant lieu à l'attribution d'un titre universitaire ou professionnel.", 'docalist-biblio'),
            ),
            'abstract' => array(
                'type' => 'Reference\AbstractField*',
                'label' => __('Résumé', 'docalist-biblio'),
                'description' => __('Résumé du document et langue du résumé.', 'docalist-biblio'),
            ),
            'topic' => array(
                'type' => 'Reference\Topic*',
                'label' => __('Indexation', 'docalist-biblio'),
                'description' => __('Liste de listes de mots-clés.', 'docalist-biblio'),
            ),
            'note' => array(
                'type' => 'Reference\Note*',
                'label' => __('Notes', 'docalist-biblio'),
                'description' => __('Notes, remarques et informations supplémentaires sur le document.', 'docalist-biblio'),
            ),
            'link' => array(
                'type' => 'Reference\Link*',
                'label' => __('Liens internet', 'docalist-biblio'),
                'description' => __("Liste de liens relatifs au document.", 'docalist-biblio'),
            ),
            'doi' => array(
                'label' => __('DOI', 'docalist-biblio'),
                'description' => __('Digital Object Identifier : identifiant unique de la ressource électronique.', 'docalist-biblio'),
            ),
            'relations' => array(// TODO : au singulier
                'type' => 'Reference\Relation*',
                'label' => __('Notices liées', 'docalist-biblio'),
                'description' => __("Relations entre la notice cataloguée et d'autres notices de la même base.", 'docalist-biblio'),
            ),
            'owner' => array(
                'type' => 'string*',
                'label' => __('Propriétaires de la notice', 'docalist-biblio'),
                'description' => __('Personne ou organisme producteur de la notice.', 'docalist-biblio'),
            ),
            'creation' => array(
                'type' => 'Reference\DateBy',
                'label' => __('Date de création', 'docalist-biblio'),
                'description' => __('Date de création de la notice et agent.', 'docalist-biblio'),
            ),
            'lastupdate' => array(
                'type' => 'Reference\DateBy',
                'label' => __('Mise à jour', 'docalist-biblio'),
                'description' => __('Date de dernière mise à jour de la notice et agent.', 'docalist-biblio'),
            ),
            'status' => array(
                'type' => 'string*',
                'label' => __('Statut', 'docalist-biblio'),
                'description' => __('Mots-clés décrivant le statut actuel de la notice.', 'docalist-biblio'),
            ),
//            'statusdate', On conserve ?

            // Les champs qui suivent ne font pas partie du format docalist

            'imported',
            'errors' => array(
                'repeatable' => true,
                'fields' => array('code', 'value', 'message'),
            ),
            'todo',
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