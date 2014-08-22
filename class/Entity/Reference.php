<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
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

use Docalist\Type\Integer;
use Docalist\Type\String;
use Docalist\Type\Entity;
use Docalist\Type\Collection;
use Docalist\Biblio\TypeSettings;
use Docalist\Repository\Repository;
use Docalist\Repository\PostTypeRepository;

/**
 * Référence documentaire.
 *
 * @property Integer $ref
 * @property Integer $parent
 * @property String $title
 * @property String $status
 * @property String $creation
 * @property String $lastupdate
 * @property String $password
 * @property String $posttype
 * @property String $type
 * @property Collection $genre
 * @property Collection $media
 * @property Docalist\Biblio\Entity\Reference\Author[] $author
 * @property Docalist\Biblio\Entity\Reference\Organisation[] $organisation
 * @property Docalist\Biblio\Entity\Reference\OtherTitle[] $othertitle
 * @property Docalist\Biblio\Entity\Reference\Translation[] $translation
 * @property Docalist\Biblio\Entity\Reference\Date[] $date
 * @property String $journal
 * @property Docalist\Biblio\Entity\Reference\Number[] $number
 * @property Collection $language
 * @property Docalist\Biblio\Entity\Reference\Extent[] $extent
 * @property String $format
 * @property Docalist\Biblio\Entity\Reference\Editor[] $editor
 * @property Collection $edition
 * @property Docalist\Biblio\Entity\Reference\Collection[] $collection
 * @property Docalist\Biblio\Entity\Reference\Event $event
 * @property Docalist\Biblio\Entity\Reference\Topic[] $topic
 * @property Docalist\Biblio\Entity\Reference\Content[] $content
 * @property Docalist\Biblio\Entity\Reference\Link[] $link
 * @property Docalist\Biblio\Entity\Reference\Relation[] $relation
 * @property Collection $owner
 * @property String $imported
 * @property Docalist\Biblio\Entity\Reference\Error[] $errors
 */
class Reference extends Entity {
    static protected function loadSchema() {
        // @formatter:off
        return [
            'ref' => [         // Alias de post_name
                'type' => 'int',
                'label' => __('Numéro de référence', 'docalist-biblio'),
                'description' => __('Numéro unique identifiant la notice', 'docalist-biblio'),
            ],
            'parent' => [      // Alias de post_parent
                'type' => 'int',
                'label' => __('Notice parent', 'docalist-biblio'),
                'description' => __('Numéro de la référence parent', 'docalist-biblio'),
            ],
            'title' => [       // Alias de post_title
                'label' => __('Titre', 'docalist-biblio'),
                'description' => __('Titre original du document catalogué', 'docalist-biblio'),
            ],
            'status' => [      // Alias de post_status
                'label' => __('Statut', 'docalist-biblio'),
                'description' => __('Statut de la notice.', 'docalist-biblio'),
            ],
            'creation' => [    // Alias de post_date
                'label' => __('Création', 'docalist-biblio'),
                'description' => __('Date/heure de création de la notice.', 'docalist-biblio'),
            ],
            'lastupdate' => [  // Alias de post_modified
                'label' => __('Dernière modification', 'docalist-biblio'),
                'description' => __('Date/heure de dernière modification.', 'docalist-biblio'),
            ],
            'password' => [  // Alias de post_password
                'label' => __('Mot de passe', 'docalist-biblio'),
                'description' => __('Mot de passe de la notice.', 'docalist-biblio'),
            ],
            'posttype' => [  // Alias de post_type
                'label' => __('Post Type', 'docalist-biblio'),
            ],


            'type' => [
                'label' => __('Type de notice', 'docalist-biblio'),
                'description' => __('Code unique décrivant la forme du document catalogué', 'docalist-biblio'),
            ],
            'genre' => [
                'type' => 'string*',
                'label' => __('Genres', 'docalist-biblio'),
                'description' => __('Nature du document catalogué', 'docalist-biblio'),
            ],
            'media' => [
                'type' => 'string*',
                'label' => __('Supports', 'docalist-biblio'),
                'description' => __('Support physique du document (papier, dvd, etc.)', 'docalist-biblio'),
            ],
            'author' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Author*',
                'label' => __('Auteurs', 'docalist-biblio'),
//                 'description' => __('Liste des personnes physiques auteurs du document', 'docalist-biblio'),
            ],
            'organisation' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Organisation*',
                'label' => __('Organismes', 'docalist-biblio'),
//                 'description' => __('Liste des auteurs moraux : organismes, collectivités auteurs, commanditaires, etc.', 'docalist-biblio'),
            ],
            'othertitle' => [
                'type' => 'Docalist\Biblio\Entity\Reference\OtherTitle*',
                'label' => __('Autres titres', 'docalist-biblio'),
//                 'description' => __("Titre de l'ensemble, du dossier, du supplément, etc.", 'docalist-biblio'),
            ],
            'translation' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Translation*',
                'label' => __('Traductions', 'docalist-biblio'),
//                 'description' => __('Traduction en une ou plusieurs langue du titre original qui figure dans Titre.', 'docalist-biblio'),
            ],
            'date' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Date*',
                'label' => __('Date', 'docalist-biblio'),
                'description' => __("Dates du document au format <code>AAAAMMJJ</code>, éventuellement complété (2009→2009<b>0101</b>). La première date saisie sera utilisée pour le tri.", 'docalist-biblio'),
            ],
            'journal'=> [
                'label' => __('Périodique', 'docalist-biblio'),
                'description' => __('Nom du journal (revue, magazine, périodique, etc.) dans lequel a été publié le document.', 'docalist-biblio'),
            ],
            'number' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Number*',
                'label' => __('Numéros', 'docalist-biblio'),
                'description' => __('Numéros du document (ISSN, ISBN, volume, fascicule, ...)', 'docalist-biblio'),
            ],
            'language' => [
                'type' => 'string*',
                'label' => __('Langues', 'docalist-biblio'),
                'description' => __("Langues des textes qui figurent dans le document.", 'docalist-biblio'),
            ],
            'extent' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Extent*',
                'label' => __('Etendue', 'docalist-biblio'),
                'description' => __("Pagination, nombre de pages, durée, etc.", 'docalist-biblio'),
            ],
            'format' => [
                'type' => 'string*',
                'label' => __('Format', 'docalist-biblio'),
                'description' => __('Caractéristiques matérielles du document : étiquettes de collation (tabl, ann, fig...), références bibliographiques, etc.', 'docalist-biblio'),
            ],
            'editor' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Editor*',
                'label' => __("Editeurs", 'docalist-biblio'),
                'description' => __("Société ou organisme délégué par l'auteur pour assurer la diffusion du document.", 'docalist-biblio'),
            ],
            'edition' => [
                'type' => 'string*',
                'label' => __("Mentions d'édition", 'docalist-biblio'),
                'description' => __("Nouvelle édition, périodicité, etc.", 'docalist-biblio'),
            ],
            'collection' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Collection*',
                'label' => __('Collection', 'docalist-biblio'),
                'description' => __('Collection et numéro dans la collection, sous-collection et numéro dans la sous-collection, etc.', 'docalist-biblio'),
            ],
            'event' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Event',
                'label' => __("Evènement", 'docalist-biblio'),
                'description' => __('Evènement (congrès, colloque, manifestation, soutenance de thèse, etc.) qui a donné lieu au document', 'docalist-biblio'),
            ],
            'topic' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Topic*',
                'label' => __('Indexation', 'docalist-biblio'),
//                 'description' => __('Liste de listes de mots-clés.', 'docalist-biblio'),
            ],
            'content' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Content*',
                'label' => __('Contenu du document', 'docalist-biblio'),
//                 'description' => __('Notes, remarques et informations supplémentaires sur le document.', 'docalist-biblio'),
            ],
            'link' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Link*',
                'label' => __('Liens internet', 'docalist-biblio'),
//                 'description' => __("Liste de liens relatifs au document.", 'docalist-biblio'),
            ],
            'relation' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Relation*',
                'label' => __("Relations avec d'autres notices", 'docalist-biblio'),
//                 'description' => __("Relations entre la notice cataloguée et d'autres notices de la même base.", 'docalist-biblio'),
            ],
            'owner' => [
                'type' => 'string*',
                'label' => __('Producteur de la notice', 'docalist-biblio'),
                'description' => __('Personne ou organisme producteur de la notice.', 'docalist-biblio'),
            ],

            // Les champs qui suivent ne font pas partie du format docalist

            'imported' => [
                'label' => __('Notice importée', 'docalist-biblio'),
            ],
            'errors' => [
                'type' => 'Docalist\Biblio\Entity\Reference\Error*',
                'label' => __('Erreurs()', 'docalist-biblio'),
            ],
        ];
        // @formatter:on
    }

    /**
     * Attribue un numéro de la ref à la notice avant de l'enregistrer si elle
     * n'en a pas déjà un.
     */
    public function beforeSave(Repository $repository) {
        // Vérifie qu'on peut accéder à $repository->postType()
        if (! $repository instanceof PostTypeRepository) {
            throw new \Exception("Les notices ne peuvent enregistrées que dans un PostTypeRepository");
        }

        // Met à jour la séquence si on a déjà un numéro de ref
        if (isset($this->ref)) {
            docalist('sequences')->setIfGreater($repository->postType(), 'ref', $this->ref());
        }

        // Sinon, alloue un numéro à la notice
        else {
            $this->ref = docalist('sequences')->increment($repository->postType(), 'ref');
        }
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
            if (isset($this->$field)) {
                $field = $this->$field;
                return $field instanceof Collection ? $field->first() : $field;
            }
        }

        return null;
    }

    /**
     * Formatte le champ date.
     *
     * @param string $format
     * @return string
     */
    public function formatDate($format = 'j F Y') {
        $date = $this->date() ?: $this->creation->date();

        return date_i18n('F Y', strtotime($date));
    }

    /**
     * Indique si un champ est filtrable (pour l'affichage avec Formatter) et
     * retourne le nom de la sous-zone utilisable comme filtre.
     *
     * Exemples :
     * - les champs simples ne sont pas filtrables, la méthode retournera false
     *   si elle est appellée avec des champs comme ref, title, type, media...
     * - le champ author est filtrable par étiquette de rôle,
     *   filterable('author') retourne 'role'.
     * - le champ translation est filtrable par langue,
     *   filterable('translation') retourne 'language'.
     * - le champ content est filtrable par type,
     *   filterable('content') retourne 'type'.
     * - etc.
     *
     * @param string $field Le nom du champ à tester.
     *
     * @return false|string Retourne le nom de la sous-zone utilisable comme
     * filtre ou false si le champ n'est pas filtrable.
     */
    public function filterable($field) {
        // Liste des champs filtrables (champ => sous-zone)
        static $filterable = [
            'author' => 'role',
            'organisation' => 'role',
            'othertitle' => 'type',
            'translation' => 'language',
            'date' => 'type',
            'number' => 'type',
            'extent' => 'type',
            'editor' => 'role',
            'topic' => 'type',
            'content' => 'type',
            'link' => 'type',
            'relation' => 'type',
        ];

        // remarque : collection et event sont des entités mais ne sont pas filtrables

        return isset($filterable[$field]) ? $filterable[$field] : false;
    }

    /**
     * Filtre un champ.
     *
     * @param string $field Nom du champ à filtrer.
     * @param string|array $value valeur ou liste des valeurs à conserver.
     * @param bool $reverse Par défaut, seuls les valeurs qui ont $value sont
     * retournées. Si $reverse est à true, le test est inversé et seules les
     * valeurs qui ne correspondent pas à $value seront retournées.
     *
     * @return Collection
     */
    public function filter($name, $value, $reverse = false) {
        if (false === $key = $this->filterable($name)) {
            throw new \Exception("Le champ $name n'est pas filtrable");
        }

        if (is_string($value)) {
            $value = [$value => true];
        } else {
            $value = array_flip($value);
        }

        $result = new Collection($this->schema($name));
        if (isset($this->$name)) {
            foreach($this->$name as $field) {
                if ($reverse xor isset($value[$field->$key()])) {
                    $result[] = $field;
                }
            }
        }

        return $result;
    }

    public function label($field) {
        $label = $this->schema->field($field)->label();
        if (is_null($this->repository) || !isset($this->type)) {
            return $label;
        }

        $type = $this->type();

        /* @var $type TypeSettings */
        $type = $this->repository->settings()->types[$type];
        if (is_null($type)) {
            return $label;
        }

        /* @var $field FieldSettings */
        //var_dump($type->__get('fields')[$field]);
        //$field = $type->fields[$field];
        $field = $type->__get('fields')[$field];
        if (is_null($field)) {
            return $label;
        }

        if ($field->label) {
            $label = $field-> label;
        }

        return $label;
    }
}