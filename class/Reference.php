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

use Docalist\Type\Entity;
use Docalist\Type\Collection; // uniquement pour filter()
use Docalist\Repository\Repository;
use Docalist\Repository\PostTypeRepository;
use Docalist\Schema\Schema;
use Docalist\Schema\Field;
use Docalist\Biblio\Type\BiblioField;
use Exception;

/**
 * Référence documentaire.
 *
 * @property Docalist\Biblio\Field\Ref $ref
 * @property Docalist\Biblio\Type\Integer $parent
 * @property Docalist\Biblio\Field\Title $title
 * @property Docalist\Biblio\Field\Status $status
 * @property Docalist\Biblio\Field\Creation $creation
 * @property Docalist\Biblio\Field\CreatedBy $createdBy
 * @property Docalist\Biblio\Field\LastUpdate $lastupdate
 * @property Docalist\Biblio\Type\String $password
 * @property Docalist\Biblio\Field\PostType $posttype
 * @property Docalist\Biblio\Field\Type $type
 * @property Docalist\Biblio\Field\Genres $genre
 * @property Docalist\Biblio\Field\Medias $media
 * @property Docalist\Biblio\Field\Authors $author
 * @property Docalist\Biblio\Field\Organisations $organisation
 * @property Docalist\Biblio\Field\OtherTitles $othertitle
 * @property Docalist\Biblio\Field\Translations $translation
 * @property Docalist\Biblio\Field\Dates $date
 * @property Docalist\Biblio\Field\Journal $journal
 * @property Docalist\Biblio\Field\Numbers $number
 * @property Docalist\Biblio\Field\Languages $language
 * @property Docalist\Biblio\Field\Extents $extent
 * @property Docalist\Biblio\Field\Formats $format
 * @property Docalist\Biblio\Field\Editors $editor
 * @property Docalist\Biblio\Field\Editions $edition
 * @property Docalist\Biblio\Field\Collections $collection
 * @property Docalist\Biblio\Field\Event $event
 * @property Docalist\Biblio\Field\Topics $topic
 * @property Docalist\Biblio\Field\Contents $content
 * @property Docalist\Biblio\Field\Links $link
 * @property Docalist\Biblio\Field\Relations $relation
 * @property Docalist\Biblio\Field\Owner $owner
 * @property Docalist\Biblio\Field\Imported $imported
 * @property Docalist\Biblio\Field\Errors $errors
 */
class Reference extends Entity {
    /**
     * La liste des types de notices prédéfinis.
     *
     * @var array Un tableau de la forme type => classe du type.
     */
    static protected $types = [
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
     * @throws Exception
     */
    static public function create($type, array $value = null, Schema $schema = null, $id = null) {
        if (! isset(self::$types[$type])) {
            throw new Exception("Type de notice inexistant : $type");
        }
        $ref = new self::$types[$type]($value, $schema, $id); /* @var $ref Reference */
        $ref->type = $type;

        return $ref;
    }

    /**
     * Retourne la grille 'base'.
     *
     * @return Schema
     */
    static public function baseGrid() {
        $grid = static::defaultSchema();
        $grid->description = sprintf(
            __("Liste des champs et valeurs par défaut pour le type %s.", 'docalist-biblio'),
            lcfirst($grid->label())
        );
        $grid->label = __('Grille de base', 'docalist-biblio');

        return $grid;
    }

    /**
     * Retourne la grille 'edit'.
     *
     * @return Schema
     */
    static public function editGrid() {
        $grid = static::defaultSchema();
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
        $schema = static::loadSchema();

        $label = __('Affichage long', 'docalist-biblio');
        $description = sprintf(
            __("Affichage détaillé d'une notice de type %s.", 'docalist-biblio'),
            lcfirst($schema['label'])
        );

        $fields = array_keys($schema['fields']);
        $hidden = ['title', 'posttype', 'password', 'parent', 'slug', 'imported', 'errors'];
        $last = ['owner', 'status', 'creation', 'createdBy', 'lastupdate', 'ref'];
        $fields = array_diff($fields, $hidden, $last);

        $group1 = [ 'group1' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Champs affichés', 'docalist-biblio'), 'before' => '<dl>', 'format' => '<dt>%label</dt><dd>%content</dd>', 'after' => '</dl>' ] ];
        $group2 = [ 'group2' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Champs non affichés', 'docalist-biblio') ] ];

        $fields = array_merge($group1, $fields, $last, $group2, $hidden);

        return new Schema([
            'label' => $label,
            'description' => $description,
            'fields' => $fields
        ]);
    }

    /**
     * Retourne la grille 'excerpt'.
     *
     * @return Schema
     */
    static public function excerptGrid() {
        $schema = static::loadSchema();

        $label = __('Affichage court', 'docalist-biblio');
        $description = sprintf(
            __("Affichage court d'une notice de type %s dans une liste de réponses.", 'docalist-biblio'),
            lcfirst($schema['label'])
        );

        $fields = array_keys($schema['fields']);
        $show1 = [
            'group1' => [ 'label' => __('Source du document', 'docalist-biblio'), 'before' => '<p>', 'format' => '%content', 'after' => '.</p>', 'type' => 'Docalist\Biblio\Type\Group' ],
            'type',
            'author' => [ 'before' => ' de ', 'limit' => 1, 'format' => 'f n' ],
        ];

        if (in_array('journal', $fields)) {
            $show2 = [
                'journal' => [ 'before' => ', ' ],
                'number' => [ 'format' => 'format', 'before' => ', ' ],
                'extent' => [ 'format' => 'format', 'before' => ', ' ],
                'date' => [ 'format' => 'date', 'before' => ', ', 'limit' => 1 ],
            ];
        } elseif (in_array('editor', $fields)) {
            $show2 = [
                'editor' => [ 'before' => ', ' ],
                'date' => [ 'format' => 'year', 'before' => ', ', 'limit' => 1 ],
            ];
        } else {
            $show2 = [
                'othertitle' => [ 'before' => ', ', 'limit' => 1 ],
                'date' => [ 'format' => 'year', 'before' => ', ', 'limit' => 1 ],
            ];
        }

        $show3= [
            'group2' => [ 'label' => __('Contenu', 'docalist-biblio'), 'format' => '<blockquote>%content</blockquote>', 'type' => 'Docalist\Biblio\Type\Group' ],
            'content' => [ 'format' => 'v', 'limit' => 1, 'maxlen' => 220 ],
            'group3' => [ 'label' => __('Indexation', 'docalist-biblio'), 'format' => '<p>%content</p>', 'type' => 'Docalist\Biblio\Type\Group' ],
            'topic' => [ 'format' => 'v', 'sep' => ', ' ],
            'group5' => [ 'label' => __('Liens disponibles', 'docalist-biblio'), 'before' => '<p>', 'format' => '%content', 'after' => '</p>', 'sep' => ', ', 'type' => 'Docalist\Biblio\Type\Group' ],
            'link' => [ 'format' => 'link' ],
            'group6' => [ 'type' => 'Docalist\Biblio\Type\Group', 'label' => __('Champs non affichés', 'docalist-biblio') ],
        ];
        // Liens disponibles

        $fields = array_diff_key($schema['fields'], $show1, $show2, $show3);

        $fields = array_merge($show1, $show2, $show3, $fields);

        return new Schema([
            'label' => $label,
            'description' => $description,
            'fields' => $fields
        ]);
    }

    static protected function loadSchema() {
        // @formatter:off
        $schema = [
            'name' => 'reference',
            'label' => __('Référence', 'docalist-biblio'),
            'description' => __('Décrit une notice documentaire.', 'docalist-biblio'),
            'fields' => [
                'posttype' => [  // Alias de post_type
                    'type' => 'Docalist\Biblio\Field\PostType',
                    'label' => __('Post Type', 'docalist-biblio'),
                ],
                'status' => [      // Alias de post_status
                    'type' => 'Docalist\Biblio\Field\Status',
                    'label' => __('Statut', 'docalist-biblio'),
                    'description' => __('Statut de la notice.', 'docalist-biblio'),
                ],
                'creation' => [    // Alias de post_date
                    'type' => 'Docalist\Biblio\Field\Creation',
                    'label' => __('Création', 'docalist-biblio'),
                    'description' => __('Date/heure de création de la notice.', 'docalist-biblio'),
                ],
                'createdBy' => [      // Alias de post_author
                    'type' => 'Docalist\Biblio\Field\CreatedBy',
                    'label' => __('Créé par', 'docalist-biblio'),
                    'description' => __('Auteur de la notice.', 'docalist-biblio'),
                ],
                'lastupdate' => [  // Alias de post_modified
                    'type' => 'Docalist\Biblio\Field\LastUpdate',
                    'label' => __('Dernière modification', 'docalist-biblio'),
                    'description' => __('Date/heure de dernière modification.', 'docalist-biblio'),
                ],
                'password' => [  // Alias de post_password
                    'type' => 'Docalist\Biblio\Type\String',
                    'label' => __('Mot de passe', 'docalist-biblio'),
                    'description' => __('Mot de passe de la notice.', 'docalist-biblio'),
                ],
                'parent' => [      // Alias de post_parent
                    'type' => 'Docalist\Biblio\Type\Integer',
                    'label' => __('Notice parent', 'docalist-biblio'),
                    'description' => __('Numéro de référence de la notice parent.', 'docalist-biblio'),
                ],
                'slug' => [  // Alias de post_name
                    'type' => 'Docalist\Biblio\Type\String',
                    'label' => __('Slug de la notice', 'docalist-biblio'),
                ],
                'ref' => [         // Alias de post_name
                    'type' => 'Docalist\Biblio\Field\Ref',
                    'label' => __('Numéro de référence', 'docalist-biblio'),
                    'description' => __('Numéro unique identifiant la notice.', 'docalist-biblio'),
                ],
                'type' => [
                    'type' => 'Docalist\Biblio\Field\Type',
                    'label' => __('Type de notice', 'docalist-biblio'),
                    'description' => __('Code indiquant le type de notice.', 'docalist-biblio'),
                ],
                'genre' => [
                    'type' => 'Docalist\Biblio\Field\Genres',
                    'label' => __('Genres', 'docalist-biblio'),
                    'description' => __('Nature du document catalogué.', 'docalist-biblio'),
                    'table' => 'thesaurus:genres',
                ],
                'media' => [
                    'type' => 'Docalist\Biblio\Field\Medias',
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
                    'type' => 'Docalist\Biblio\Field\OtherTitles',
                    'label' => __('Autres titres', 'docalist-biblio'),
                    'description' => __("Autres titres du document : sigle, variante, titre du dossier, du numéro, du diplôme...)", 'docalist-biblio'),
                    'table' => 'table:titles',
                ],
                'translation' => [
                    'type' => 'Docalist\Biblio\Field\Translations',
                    'label' => __('Traductions', 'docalist-biblio'),
                    'description' => __('Traduction en une ou plusieurs langues du titre original du document.', 'docalist-biblio'),
                    'table' => 'table:ISO-639-2_alpha3_EU_fr',
                ],
                'author' => [
                    'type' => 'Docalist\Biblio\Field\Authors',
                    'label' => __('Auteurs', 'docalist-biblio'),
                    'description' => __("Liste des personnes qui ont contribué à l'élaboration du document : auteur, coordonnateur, réalisateur...", 'docalist-biblio'),
                    'table' => 'thesaurus:marc21-relators_fr',
                ],
                'organisation' => [
                    'type' => 'Docalist\Biblio\Field\Organisations',
                    'label' => __('Organismes', 'docalist-biblio'),
                    'description' => __("Liste des organismes qui ont contribué à l'élaboration du document : organisme considéré comme auteur, organisme commanditaire, financeur...", 'docalist-biblio'),
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'table2' => 'thesaurus:marc21-relators_fr',
                    'sep' => ' ; ', // sép par défaut à l'affichage, espace insécable avant ';'
                ],
                'date' => [
                    'type' => 'Docalist\Biblio\Field\Dates',
                    'label' => __('Date', 'docalist-biblio'),
                    'description' => __("Dates du document au format <code>AAAAMMJJ</code> : date de publication, date d'enregistrement...", 'docalist-biblio'),
                    'table' => 'table:dates',
                ],
                'journal'=> [
                    'type' => 'Docalist\Biblio\Field\Journal',
                    'label' => __('Périodique', 'docalist-biblio'),
                    'description' => __('Nom du journal (revue, magazine, périodique...) dans lequel a été publié le document.', 'docalist-biblio'),
                ],
                'number' => [
                    'type' => 'Docalist\Biblio\Field\Numbers',
                    'label' => __('Numéros', 'docalist-biblio'),
                    'description' => __('Numéros du document : DOI, ISSN, ISBN, numéro de volume, numéro de fascicule...', 'docalist-biblio'),
                    'table' => 'table:numbers',
                ],
                'language' => [
                    'type' => 'Docalist\Biblio\Field\Languages',
                    'label' => __('Langues', 'docalist-biblio'),
                    'description' => __("Langues des textes qui figurent dans le document catalogué.", 'docalist-biblio'),
                    'table' => 'table:ISO-639-2_alpha3_EU_fr',
                ],
                'extent' => [
                    'type' => 'Docalist\Biblio\Field\Extents',
                    'label' => __('Etendue', 'docalist-biblio'),
                    'description' => __("Pagination, nombre de pages, durée, dimensions...", 'docalist-biblio'),
                    'table' => 'table:extent',
                ],
                'format' => [
                    'type' => 'Docalist\Biblio\Field\Formats',
                    'label' => __('Format', 'docalist-biblio'),
                    'description' => __("Etiquettes de collation utilisées pour décrire ce que l'on trouve dans le document catalogué : tableaux, annexes, références bibliographiques...", 'docalist-biblio'),
                    'table' => 'thesaurus:format',
                ],
                'editor' => [
                    'type' => 'Docalist\Biblio\Field\Editors',
                    'label' => __("Editeurs", 'docalist-biblio'),
                    'description' => __("Société ou organisme délégué par l'auteur pour assurer la diffusion et la distribution du document.", 'docalist-biblio'),
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'table2' => 'thesaurus:marc21-relators_fr',
                ],
                'collection' => [
                    'type' => 'Docalist\Biblio\Field\Collections',
                    'label' => __('Collection', 'docalist-biblio'),
                    'description' => __("Collection, sous-collection et numéro au sein de la collection de l'éditeur.", 'docalist-biblio'),
                ],
                'edition' => [
                    'type' => 'Docalist\Biblio\Field\Editions',
                    'label' => __("Mentions d'édition", 'docalist-biblio'),
                    'description' => __("Mentions utilisées pour décrire le type de l'édition : nouvelle édition, édition revue et corrigée, périodicité...", 'docalist-biblio'),
                ],
                'event' => [
                    'type' => 'Docalist\Biblio\Field\Event',
                    'label' => __("Evènement", 'docalist-biblio'),
                    'description' => __("Description de l'évènement à l'origine du document : congrès, colloque, manifestation, soutenance de thèse...", 'docalist-biblio'),
                ],
                'topic' => [
                    'type' => 'Docalist\Biblio\Field\Topics',
                    'label' => __('Indexation', 'docalist-biblio'),
                    'description' => __("Mots-clés décrivant le contenu du document. Les mots-clés utilisés peuvent provenir d'un ou plusieurs vocabulaires différents.", 'docalist-biblio'),
                    'table' => 'table:topics',
                ],
                'content' => [
                    'type' => 'Docalist\Biblio\Field\Contents',
                    'label' => __('Contenu du document', 'docalist-biblio'),
                    'description' => __('Description du contenu du document : résumé, présentation, critique, remarques...', 'docalist-biblio'),
                    'table' => 'table:content',
                ],
                'link' => [
                    'type' => 'Docalist\Biblio\Field\Links',
                    'label' => __('Liens internet', 'docalist-biblio'),
                    'description' => __("Liens associés au document : site de l'auteur, accès au texte intégral, site de l'éditeur...", 'docalist-biblio'),
                    'table' => 'table:links',
                ],
                'relation' => [
                    'type' => 'Docalist\Biblio\Field\Relations',
                    'label' => __("Relations avec d'autres notices", 'docalist-biblio'),
                    'description' => __("Relations entre ce document et d'autres documents déjà catalogués : voir aussi, nouvelle édition, erratum...", 'docalist-biblio'),
                    'table' => 'table:relations',
                ],
                'owner' => [
                    'type' => 'Docalist\Biblio\Field\Owners',
                    'label' => __('Producteur de la notice', 'docalist-biblio'),
                    'description' => __('Personne ou organisme producteur de la notice.', 'docalist-biblio'),
                ],

                // Les champs qui suivent ne font pas partie du format docalist

                'imported' => [
                    'type' => 'Docalist\Biblio\Field\Imported',
                    'label' => __('Notice importée', 'docalist-biblio'),
                ],
                'errors' => [
                    'type' => 'Docalist\Biblio\Field\Errors',
                    'label' => __('Erreurs()', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on

        // simplifie les schémas dans les types
        foreach($schema['fields'] as $name => & $field) {
            $field['name'] = $name;
        }
        unset($field);

        return $schema;
    }

    /**
     * Attribue un numéro de la ref à la notice avant de l'enregistrer si elle
     * n'en a pas déjà un.
     */
    public function beforeSave(Repository $repository) {
        // Vérifie qu'on peut accéder à $repository->postType()
        if (! $repository instanceof PostTypeRepository) {
            throw new Exception("Les notices ne peuvent enregistrées que dans un PostTypeRepository");
        }

        // Met à jour la séquence si on a déjà un numéro de ref
        $ref = $this->ref();
        if (! empty($ref)) {
            docalist('sequences')->setIfGreater($repository->postType(), 'ref', $this->ref());
        }

        // Sinon, alloue un numéro à la notice
        else {
            // On n'alloue un n° de ref qu'au notices publiées (#322)
            if ($this->status() === 'publish') {
                $this->ref = docalist('sequences')->increment($repository->postType(), 'ref');
            }

            // Remarque : dans wp_insert_post, WP fait le test suivant :
            // if ( !in_array( $post_status, array( 'draft', 'pending', 'auto-draft' ) ) )
            //
            // Autre solution : tester si la notice a un statut public
            // $publicStatuses = get_post_stati(['public' => true], 'names')
            // if (isset($publicStatuses[$this->status()])) { /* alloc ref */ }
            //
            // Remarque : ne fonctionne pas pour un post 'future' car beforeSave
            // n'est pas rappellée dans ce cas.
        }

        // Alloue une slug à la notice

        // slug de la forme 'ref'
        $slug = $this->ref();

        // slug de la forme 'ref-mots-du-titre'
        // $slug = $this->ref() . '-' . sanitize_title($this->title(), '', 'save');

        $this->slug = $slug;
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
        $date = $this->date->first() ?: $this->creation->date();

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
    public function filter($name, $value, $reverse = false) { // Encore utilisé ?
        if (false === $key = $this->filterable($name)) {
            throw new Exception("Le champ $name n'est pas filtrable");
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

    public function format() {
        // Variables utilisées
        $group = null;  // Le groupe en cours
        $format = null; // Le format du groupe en cours
        $fields = [];   // Les champs générés par le groupe en cours
        $result = '';   // Le résultat final
        $hasGroupCap = null; // True si l'utilisateur a la cap requise pour le groupe en cours

        // Si la grille ne commence pas par un groupe, créé un groupe par défaut
        if (current($this->schema()->fields())->type !== 'Docalist\Biblio\Type\Group') {
            $group = new Field([
                'type' => 'Docalist\Biblio\Type\Group',
                'format' => '<p><b>%label : </b>%content</p>',
            ]);
            $format = $group->format;
            $hasGroupCap = true;
        }

        // Construit la notice
        foreach($this->schema()->fields() as $name => $field) { /* @var $field BiblioField */
            // Si c'est un groupe, cela devient le nouveau groupe courant
            if ($field->type === 'Docalist\Biblio\Type\Group') {
                // Génère le groupe précédent s'il n'est pas vide
                if ($fields) {
                    $result .= $group->before;
                    $result .= implode($group->sep, $fields);
                    $result .= $group->after;

                    $fields = [];
                }

                // Teste si on a les droits requis pour le nouveau groupe
                $cap = $field->capability();

                if ($cap && ! current_user_can($cap)) {
                    $hasGroupCap = false;
                    unset($group); // secu : $group n'existe pas si $hasGroupCap est à false

                    continue;
                }

                // Nouveau groupe en cours
                $hasGroupCap = true;
                $group = $field;
                $format = $group->format;
                continue;
            }

            // C'est un champ

            // Si on n'a pas la cap du groupe en cours, on ne va pas plus loin
            if (! $hasGroupCap) {
                continue;
            }

            // Un groupe sans format n'affiche rien, inutile d'aller plus loin
            if (empty($format)) {
                continue;
            }

            // Si le champ est vide, passe au suivant
            if (! isset($this->value[$name])) {
                continue;
            }

            // Si on n'a pas la cap du champ, inutile d'aller plus loins
            $cap = $field->capability();
            if ($cap && ! current_user_can($cap)) {
                continue;
            }

            // Formatte le contenu du champ
            $content = $this->value[$name]->format();

            // Champ renseigné mais format() n'a rien retourné, passe au suivant
            if (empty($content)) {
                continue;
            }

            // format() nous a retourné soit un tableau de champs (vue éclatée)
            // soit une simple chaine avec le contenu formatté du champ.
            // Si c'est une chaine, on le gère comme un tableau en utilisant
            // le libellé du champ.
            ! is_array($content) && $content = [($field->labelspec() ?: $field->label()) => $content];

            // Stocke le champ (ou les champs en cas de vue éclatée)
            foreach ($content as $label => $content) {
                $content = $field->before . $content . $field->after;
                $fields[] = strtr($format, ['%label' => $label, '%content' => $content]);
            }
        }

        // Si on a un groupe non vide en cours, envoie group.after
        if ($fields) {
            $result .= $group->before;
            $result .= implode($group->sep, $fields);
            $result .= $group->after;
        }

        // Ok
        return $result;
    }
}