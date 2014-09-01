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
namespace Docalist\Biblio;

use Docalist\Type\Entity;
use Docalist\Repository\Repository;
use Docalist\Repository\PostTypeRepository;
use Docalist\Schema\Schema;
use Docalist\Schema\Field;

/**
 * Référence documentaire.
 *
 * @property Docalist\Biblio\Type\Integer $ref
 * @property Docalist\Biblio\Type\Integer $parent
 * @property Docalist\Biblio\Entity\Reference\Title $title
 * @property Docalist\Biblio\Type\String $status
 * @property Docalist\Biblio\Type\String $creation
 * @property Docalist\Biblio\Type\String $lastupdate
 * @property Docalist\Biblio\Type\String $password
 * @property Docalist\Biblio\Type\String $posttype
 * @property Docalist\Biblio\Type\String $type
 * @property Docalist\Biblio\Entity\Reference\Genres $genre
 * @property Docalist\Biblio\Entity\Reference\Medias $media
 * @property Docalist\Biblio\Entity\Reference\Authors $author
 * @property Docalist\Biblio\Entity\Reference\Organisations $organisation
 * @property Docalist\Biblio\Entity\Reference\OtherTitles $othertitle
 * @property Docalist\Biblio\Entity\Reference\Translations $translation
 * @property Docalist\Biblio\Entity\Reference\Dates $date
 * @property Docalist\Biblio\Type\String $journal
 * @property Docalist\Biblio\Entity\Reference\Numbers $number
 * @property Docalist\Biblio\Entity\Reference\Languages $language
 * @property Docalist\Biblio\Entity\Reference\Extents $extent
 * @property Docalist\Biblio\Entity\Reference\Formats $format
 * @property Docalist\Biblio\Entity\Reference\Editors $editor
 * @property Docalist\Biblio\Entity\Reference\Editions $edition
 * @property Docalist\Biblio\Entity\Reference\Collections $collection
 * @property Docalist\Biblio\Entity\Reference\Event $event
 * @property Docalist\Biblio\Entity\Reference\Topics $topic
 * @property Docalist\Biblio\Entity\Reference\Contents $content
 * @property Docalist\Biblio\Entity\Reference\Links $link
 * @property Docalist\Biblio\Entity\Reference\Relations $relation
 * @property Docalist\Biblio\Entity\Reference\Owner $owner
 * @property Docalist\Biblio\Entity\Reference\Imported $imported
 * @property Docalist\Biblio\Entity\Reference\Errors $errors
 */
class Reference extends Entity {
    /**
     * La liste des types de notices prédéfinis.
     *
     * @var array Un tableau de la forme type => classe du type.
     */
    static protected $types = [
        'article'           => 'Docalist\Biblio\Entity\Article',
        'book'              => 'Docalist\Biblio\Entity\Book',
        'degree'            => 'Docalist\Biblio\Entity\Degree',
        'book-chapter'      => 'Docalist\Biblio\Entity\BookChapter',
        'legislation'       => 'Docalist\Biblio\Entity\Legislation',
        'meeting'           => 'Docalist\Biblio\Entity\Meeting',
        'periodical-issue'  => 'Docalist\Biblio\Entity\PeriodicalIssue',
        'periodical'        => 'Docalist\Biblio\Entity\Periodical',
        'report'            => 'Docalist\Biblio\Entity\Report',
        'website'           => 'Docalist\Biblio\Entity\WebSite',
    ];

    /**
     * Retourne la liste des types de notices prédéfinis.
     *
     * @return array Un tableau de la forme type => classe du type.
     */
    static public function types() {
        return self::$types;
    }

    /**
     * Enregistre un nouveau type de notice.
     *
     * Si le type existe déjà, il est écrasé, ce qui permet à un plugin de
     * changer la classe responsable d'un type prédéfini.
     *
     * @param string $name Nom du type.
     * @param string $class Nom complet de la classe du type.
     */
    static public function registerType($name, $class) {
        self::$types[$name] = $class;
    }

    /**
     * Crée une notice du type indiqué.
     *
     * @param string $type
     * @param array $value
     * @param Schema $schema
     * @param string $id
     *
     * @return Reference
     * @throws \Exception
     */
    static public function create($type, array $value = null, Schema $schema = null, $id = null) {
        if (! isset(self::$types[$type])) {
            throw new \Exception("Type de notice inexistant : $type");
        }
        return new self::$types[$type]($value, $schema, $id);
    }

    /**
     * Retourne la grille 'edit'.
     *
     * @return Schema
     */
    static public function editGrid() {
        $grid = static::defaultSchema();
        $grid->name = 'edit';
        $grid->description = sprintf(
            __("Grille utilisée pour la saisie et la modification d'une notice de type %s.", 'docalist-biblio'),
            lcfirst($grid->label())
        );
        $grid->label = __('Formulaire de saisie', 'docalist-biblio');

        return $grid;
    }

    /**
     * Retourne la grille 'content'.
     *
     * @return Schema
     */
    static public function contentGrid() {
        $grid = static::defaultSchema();
        $grid->name = 'content';
        $grid->description = sprintf(
            __("Grille utilisée pour l'affichage détaillé d'une notice complète de type %s.", 'docalist-biblio'),
            lcfirst($grid->label())
        );
        $grid->label = __('Affichage long', 'docalist-biblio');

        return $grid;
    }

    /**
     * Retourne la grille 'excerpt'.
     *
     * @return Schema
     */
    static public function excerptGrid() {
        $grid = static::defaultSchema();
        $grid->name = 'excerpt';
        $grid->description = sprintf(
            __("Grille utilisée pour l'affichage court d'une notice de type %s dans une liste de réponses.", 'docalist-biblio'),
            lcfirst($grid->label())
        );
        $grid->label = __('Affichage court', 'docalist-biblio');

        return $grid;
    }

    static protected function loadSchema() {
        // @formatter:off
        $schema = [
            'name' => 'reference',
            'label' => __('Référence', 'docalist-biblio'),
            'description' => __('Décrit une notice documentaire.', 'docalist-biblio'),
            'fields' => [
                'ref' => [         // Alias de post_name
                    'type' => 'Docalist\Biblio\Type\Integer',
                    'label' => __('Numéro de référence', 'docalist-biblio'),
                    'description' => __('Numéro unique identifiant la notice', 'docalist-biblio'),
                ],
                'parent' => [      // Alias de post_parent
                    'type' => 'Docalist\Biblio\Type\Integer',
                    'label' => __('Notice parent', 'docalist-biblio'),
                    'description' => __('Numéro de la référence parent', 'docalist-biblio'),
                ],
                'title' => [       // Alias de post_title
                    'type' => 'Docalist\Biblio\Entity\Reference\Title',
                    'label' => __('Titre', 'docalist-biblio'),
                    'description' => __('Titre original du document catalogué', 'docalist-biblio'),
                ],
                'status' => [      // Alias de post_status
                    'type' => 'Docalist\Biblio\Type\String',
                    'label' => __('Statut', 'docalist-biblio'),
                    'description' => __('Statut de la notice.', 'docalist-biblio'),
                ],
                'creation' => [    // Alias de post_date
                    'type' => 'Docalist\Biblio\Type\String',
                    'label' => __('Création', 'docalist-biblio'),
                    'description' => __('Date/heure de création de la notice.', 'docalist-biblio'),
                ],
                'lastupdate' => [  // Alias de post_modified
                    'type' => 'Docalist\Biblio\Type\String',
                    'label' => __('Dernière modification', 'docalist-biblio'),
                    'description' => __('Date/heure de dernière modification.', 'docalist-biblio'),
                ],
                'password' => [  // Alias de post_password
                    'type' => 'Docalist\Biblio\Type\String',
                    'label' => __('Mot de passe', 'docalist-biblio'),
                    'description' => __('Mot de passe de la notice.', 'docalist-biblio'),
                ],
                'posttype' => [  // Alias de post_type
                    'type' => 'Docalist\Biblio\Type\String',
                    'label' => __('Post Type', 'docalist-biblio'),
                ],


                'type' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Type',
                    'label' => __('Type de notice', 'docalist-biblio'),
                    'description' => __('Code unique décrivant la forme du document catalogué', 'docalist-biblio'),
                ],
                'genre' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Genres',
                    'label' => __('Genres', 'docalist-biblio'),
                    'description' => __('Nature du document catalogué', 'docalist-biblio'),
                    'table' => 'thesaurus:genres',
                ],
                'media' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Medias',
                    'label' => __('Supports', 'docalist-biblio'),
                    'description' => __('Support physique du document (papier, dvd, etc.)', 'docalist-biblio'),
                    'table' => 'thesaurus:medias',
                ],
                'author' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Authors',
                    'label' => __('Auteurs', 'docalist-biblio'),
    //                 'description' => __('Liste des personnes physiques auteurs du document', 'docalist-biblio'),
                    'table' => 'thesaurus:marc21-relators_fr',
                ],
                'organisation' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Organisations',
                    'label' => __('Organismes', 'docalist-biblio'),
    //                 'description' => __('Liste des auteurs moraux : organismes, collectivités auteurs, commanditaires, etc.', 'docalist-biblio'),
                    'table1' => 'table:ISO-3166-1_alpha2_fr',
                    'table2' => 'thesaurus:marc21-relators_fr',
                ],
                'othertitle' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\OtherTitles',
                    'label' => __('Autres titres', 'docalist-biblio'),
    //                 'description' => __("Titre de l'ensemble, du dossier, du supplément, etc.", 'docalist-biblio'),
                    'table' => 'table:titles',
                ],
                'translation' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Translations',
                    'label' => __('Traductions', 'docalist-biblio'),
    //                 'description' => __('Traduction en une ou plusieurs langue du titre original qui figure dans Titre.', 'docalist-biblio'),
                    'table' => 'table:ISO-639-2_alpha3_EU_fr',
                ],
                'date' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Dates',
                    'label' => __('Date', 'docalist-biblio'),
                    'description' => __("Dates du document au format <code>AAAAMMJJ</code>, éventuellement complété (2009→2009<b>0101</b>). La première date saisie sera utilisée pour le tri.", 'docalist-biblio'),
                    'table' => 'table:dates',
                ],
                'journal'=> [
                    'type' => 'Docalist\Biblio\Entity\Reference\Journal',
                    'label' => __('Périodique', 'docalist-biblio'),
                    'description' => __('Nom du journal (revue, magazine, périodique, etc.) dans lequel a été publié le document.', 'docalist-biblio'),
                ],
                'number' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Numbers',
                    'label' => __('Numéros', 'docalist-biblio'),
                    'description' => __('Numéros du document (ISSN, ISBN, volume, fascicule, ...)', 'docalist-biblio'),
                    'table' => 'table:numbers',
                ],
                'language' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Languages',
                    'label' => __('Langues', 'docalist-biblio'),
                    'description' => __("Langues des textes qui figurent dans le document.", 'docalist-biblio'),
                    'table' => 'table:ISO-639-2_alpha3_EU_fr',
                ],
                'extent' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Extents',
                    'label' => __('Etendue', 'docalist-biblio'),
                    'description' => __("Pagination, nombre de pages, durée, etc.", 'docalist-biblio'),
                    'table' => 'table:extent',
                ],
                'format' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Formats',
                    'label' => __('Format', 'docalist-biblio'),
                    'description' => __('Caractéristiques matérielles du document : étiquettes de collation (tabl, ann, fig...), références bibliographiques, etc.', 'docalist-biblio'),
                    'table' => 'thesaurus:format',
                ],
                'editor' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Editors',
                    'label' => __("Editeurs", 'docalist-biblio'),
                    'description' => __("Société ou organisme délégué par l'auteur pour assurer la diffusion du document.", 'docalist-biblio'),
                    'table1' => 'table:ISO-3166-1_alpha2_fr',
                    'table2' => 'thesaurus:marc21-relators_fr',
                ],
                'edition' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Editions',
                    'label' => __("Mentions d'édition", 'docalist-biblio'),
                    'description' => __("Nouvelle édition, périodicité, etc.", 'docalist-biblio'),
                ],
                'collection' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Collections',
                    'label' => __('Collection', 'docalist-biblio'),
                    'description' => __('Collection et numéro dans la collection, sous-collection et numéro dans la sous-collection, etc.', 'docalist-biblio'),
                ],
                'event' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Event',
                    'label' => __("Evènement", 'docalist-biblio'),
                    'description' => __('Evènement (congrès, colloque, manifestation, soutenance de thèse, etc.) qui a donné lieu au document', 'docalist-biblio'),
                ],
                'topic' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Topics',
                    'label' => __('Indexation', 'docalist-biblio'),
    //                 'description' => __('Liste de listes de mots-clés.', 'docalist-biblio'),
                    'table' => 'table:topics',
                ],
                'content' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Contents',
                    'label' => __('Contenu du document', 'docalist-biblio'),
    //                 'description' => __('Notes, remarques et informations supplémentaires sur le document.', 'docalist-biblio'),
                    'table' => 'table:content',
                ],
                'link' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Links',
                    'label' => __('Liens internet', 'docalist-biblio'),
    //                 'description' => __("Liste de liens relatifs au document.", 'docalist-biblio'),
                    'table' => 'table:links',
                ],
                'relation' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Relations',
                    'label' => __("Relations avec d'autres notices", 'docalist-biblio'),
    //                 'description' => __("Relations entre la notice cataloguée et d'autres notices de la même base.", 'docalist-biblio'),
                    'table' => 'table:relations',
                ],
                'owner' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Owners',
                    'label' => __('Producteur de la notice', 'docalist-biblio'),
                    'description' => __('Personne ou organisme producteur de la notice.', 'docalist-biblio'),
                ],

                // Les champs qui suivent ne font pas partie du format docalist

                'imported' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Imported',
                    'label' => __('Notice importée', 'docalist-biblio'),
                ],
                'errors' => [
                    'type' => 'Docalist\Biblio\Entity\Reference\Errors',
                    'label' => __('Erreurs()', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on

        // simplifie les schémas dans les types
        foreach($schema['fields'] as $name => & $field) {
            $field['name'] = $name;
        }

        return $schema;
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
        $ref = $this->ref();
        if (! empty($ref)) {
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

        /* @var $type Schema */
        $type = $this->repository->settings()->types[$type];
        if (is_null($type)) {
            return $label;
        }

        /* @var $field Field */
        $field = $type->__get('fields')[$field];
        if (is_null($field)) {
            return $label;
        }

        if ($field->label) {
            $label = $field->label;
        }

        return $label;
    }
}