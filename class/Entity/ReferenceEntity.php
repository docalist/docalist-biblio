<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio\Entity;

use Docalist\Data\Record;
use Docalist\Type\Any;
use Docalist\Biblio\Field\GenreField;
use Docalist\Biblio\Field\MediaField;
use Docalist\Biblio\Field\TitleField;
use Docalist\Biblio\Field\OtherTitleField;
use Docalist\Biblio\Field\TranslationField;
use Docalist\Biblio\Field\AuthorField;
use Docalist\Biblio\Field\CorporationField;
use Docalist\Biblio\Field\DateField;
use Docalist\Biblio\Field\JournalField;
use Docalist\Biblio\Field\NumberField;
use Docalist\Biblio\Field\LanguageField;
use Docalist\Biblio\Field\ExtentField;
use Docalist\Biblio\Field\FormatField;
use Docalist\Biblio\Field\EditorField;
use Docalist\Biblio\Field\CollectionField;
use Docalist\Biblio\Field\EditionField;
use Docalist\Biblio\Field\ContextField;
use Docalist\Biblio\Field\TopicField;
use Docalist\Biblio\Field\ContentField;
use Docalist\Biblio\Field\LinkField;
use Docalist\Biblio\Field\RelationField;
use Docalist\Data\GridBuilder\EditGridBuilder;

use Docalist\Search\MappingBuilder;
use Docalist\Tokenizer;

/**
 * Une référence documentaire.
 *
 * Le schéma d'une référence est fixe : les classes descendantes (Article, Book, ...) ne doivent pas créer
 * de nouveaux champs, elles peuvent juste paramétrer les champs existant ou les marquer "unused".
 *
 * @property GenreField[]       $genre          Genres.
 * @property MediaField[]       $media          Supports.
 * @property TitleField         $title          Titre du document.
 * @property OtherTitleField[]  $othertitle     Autres titres.
 * @property TranslationField[] $translation    Traductions du titre.
 * @property AuthorField[]      $author         Personnes auteurs (auteurs physiques).
 * @property CorporationField[] $corporation    Organismes auteurs (auteurs moraux).
 * @property DateField[]        $date           Dates du document.
 * @property JournalField       $journal        Périodique.
 * @property NumberField[]      $number         Numéros du document.
 * @property LanguageField[]    $language       Langues des textes.
 * @property ExtentField[]      $extent         Etendue.
 * @property FormatField[]      $format         Format et étiquettes de collation.
 * @property EditorField[]      $editor         Editeurs.
 * @property CollectionField[]  $collection     Collection et numéro dans la collection.
 * @property EditionField[]     $edition        Mentions d'édition.
 * @property ContextField       $context        Contexte dans lequel a été produit le document.
 * @property TopicField[]       $topic          Mots-clés.
 * @property ContentField[]     $content        Contenu du document.
 * @property LinkField[]        $link           Liens internet.
 * @property RelationField[]    $relation       Relations avec d'autres références.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ReferenceEntity extends Record
{
    public static function loadSchema()
    {
        return [
            'name' => 'reference',
            'label' => __('Référence', 'docalist-biblio'),
            'description' => __('Une référence documentaire.', 'docalist-biblio'),
            'fields' => [
                'genre'         => GenreField::class,
                'media'         => MediaField::class,
                'title'         => TitleField::class,
                'othertitle'    => OtherTitleField::class,
                'translation'   => TranslationField::class,
                'author'        => AuthorField::class,
                'corporation'   => CorporationField::class,
                'date'          => DateField::class,
                'journal'       => JournalField::class,
                'number'        => NumberField::class,
                'language'      => LanguageField::class,
                'extent'        => ExtentField::class,
                'format'        => FormatField::class,
                'editor'        => EditorField::class,
                'collection'    => CollectionField::class,
                'edition'       => EditionField::class,
                'context'       => ContextField::class,
                'topic'         => TopicField::class,
                'content'       => ContentField::class,
                'link'          => LinkField::class,
                'relation'      => RelationField::class,
            ],
        ];
    }

    /**
     * Compatibilité ascendante
     *
     * - Champ "corporation" : auparavant, le champ le champ s'appellait "organisation", on renomme à la volée.
     * - Champ "context" : auparavant, le champ le champ s'appellait "event", on renomme à la volée.
     * - Champ "imported" : le champ a été supprimé, on ignore les valeurs qu'on rencontre.
     * - Champ "errors" : le champ a été supprimé, on ignore les valeurs qu'on rencontre.
     * - Champ "relation" : Auparavant, c'était un multifield du style type+ref*. Désormais, c'est un TypedRelation,
     *   donc le champ ref n'existe plus et le champ value n'est pas répétable. Comme le champ n'était pas utilisé
     *   et que de toute façon ce serait difficile de gérer la compatibilité ascendante (il faudrait traduire
     *   les n° de réf en Post ID), on se contente de supprimer la valeur existante si on rencontre un champ
     *   relation qui a l'ancienne forme.
     *
     * {@inheritDoc}
     */
    public function assign($value)
    {
        ($value instanceof Any) && $value = $value->getPhpValue();

        // Le champ "corporation" s'appellait "organisation" avant (08/02/18)
        if (is_array($value) && isset($value['organisation'])) {
            $value['corporation'] = $value['organisation'];
            unset($value['organisation']);
        }

        // Le champ "context" s'appellait "event" avant (08/02/18)
        if (is_array($value) && isset($value['event'])) {
            $value['context'] = $value['event'];
            unset($value['event']);
        }

        // Le champ "imported" n'existe plus (08/02/18)
        unset($value['imported']);

        // Le champ "errors" n'existe plus (08/02/18)
        unset($value['errors']);

        // Ignore (efface) les relations qui utilisent l'ancien type de champ
        if (is_array($value) && isset($value['relation']) && isset(reset($value['relation'])['ref'])) {
            unset($value['relation']);
        }

        // Le champ "owner" est remplacé par le champ "source" (04/05/18)
        if (isset($value['owner'])) {
            foreach ((array) $value['owner'] as $owner) {
                (! empty($owner)) && $value['source'][] = ['type' => $owner];
            }
            unset($value['owner']);
        }

        return parent::assign($value);
    }

    /**
     * Initialise le champ post_title lorsque la notice est enregistrée.
     *
     * Par défaut, les post_title correspond simplement au champ title de la notice, mais les classes descendantes
     * peuvent surcharger la méthode pour avoir un titre plus spécifique (par exemple, pour un article de périodique,
     * on pourrait définir un post_title de la forme "title (in perio)".
     */
    protected function initPostTitle()
    {
        isset($this->title) && $this->posttitle = $this->title->getPhpValue();
    }

    /**
     * {@inheritDoc}
     */
    public static function getEditGrid()
    {
        $builder = new EditGridBuilder(static::class);

        $builder->addGroup(
            __('Nature du document', 'docalist-biblio'),
            'genre,media'
        );
        $builder->addGroup(
            __('Titres', 'docalist-biblio'),
            'title,othertitle,translation,context'
        );
        $builder->addGroup(
            __('Auteurs', 'docalist-biblio'),
            'author,corporation'
        );
        $builder->addGroup(
            __('Informations bibliographiques', 'docalist-biblio'),
            'journal,date,language,number,extent,format'
        );
        $builder->addGroup(
            __('Informations éditeur', 'docalist-biblio'),
            'editor,collection,edition'
        );
        $builder->addGroup(
            __('Indexation et résumé', 'docalist-biblio'),
            'topic,content'
        );
        $builder->addGroup(
            __('Liens et relations', 'docalist-biblio'),
            'link,relation'
        );
        $builder->addGroup(
            __('Informations de gestion', 'docalist-biblio'),
            'type,ref,source',
            'collapsed'
        );

        return $builder->getGrid();
    }

    protected function buildMapping(MappingBuilder $mapping)
    {
        // Le mapping des champs de base est construit par la classe parent
        $mapping = parent::buildMapping($mapping);

        // genre
        $mapping->addField('genre')->text()->filter();

        // media
        $mapping->addField('media')->text()->filter();

        // title
        $mapping->addField('title')->text();
        $mapping->addField('title-sort')->keyword();

        // othertitle
        $mapping->addField('othertitle')->text()
                ->addTemplate('othertitle-*')->copyFrom('othertitle')->copyDataTo('othertitle');

        // translation
        $mapping->addField('translation')->text()
                ->addTemplate('translation-*')->copyFrom('translation')->copyDataTo('translation');

        // author
        $mapping->addField('author')->literal()->filter()->suggest()
                ->addTemplate('author-*')->copyFrom('author')->copyDataTo('author');

        // corporation
        $mapping->addField('corporation')->text()->filter()->suggest()
                ->addTemplate('corporation-*')->copyFrom('corporation')->copyDataTo('corporation');
        // Exemples d'options pour l'indexation du champ 'corporation' :
        // indexer ou pas les organismes
        // faire ou pas un champ séparé pour chaque rôle (multifield)
        // indexer ville+pays dans un champ à part (pour faire du lookup sur index)
        // indexer pays dans un champ à part (pour faire du lookup sur index si on n'utilise pas la table pays)

        // date
        $mapping->addField('date')->date()
                ->addTemplate('date-*')->copyFrom('date')->copyDataTo('date');

        // journal
        $mapping->addField('journal')->text()->filter()->suggest();

        // number
        $mapping->addField('number')->literal()
                ->addTemplate('number-*')->copyFrom('number')->copyDataTo('number');

        // language
        $mapping->addField('language')->text()->filter();

        // extent : non indexé

        // format (demande de Marion, cf. mail NL 12/03/18)
        $mapping->addField('format')->text()->filter();

        // editor
        $mapping->addField('editor')->text()->filter()->suggest() // cc organisme
                ->addTemplate('editor-*')->copyFrom('editor')->copyDataTo('editor');

        // collection
        $mapping->addField('collection')->text()->filter();

        // edition
        $mapping->addField('edition')->text()->filter();

        // context
        $mapping->addField('context')->text()->filter();

        // topic
        $mapping->addField('topic')->text()->filter()->suggest()
                ->addTemplate('topic-*')->copyFrom('topic')->copyDataTo('topic');

        // topic-hierarchy : crée un champ 'hierarchy' pour tous les topics qui sont associés à une table de type thesaurus
        foreach($this->topic->getThesaurusTopics() as $topic) {
            $mapping->addField("topic-$topic-hierarchy")->hierarchy();
        }

        // content
        $mapping->addField('content')->text()
                ->addTemplate('content-*')->copyFrom('content')->copyDataTo('content');

        // link
        $mapping->addField('link')->url()
                ->addTemplate('link-*')->copyFrom('link')->copyDataTo('link');

        // relation
        $mapping->addField('relation')->integer()
                ->addTemplate('relation-*')->copyFrom('relation')->copyDataTo('relation');

        return $mapping;
    }

    public function map()
    {
        // Le mapping des champs de base est fait par la classe parent
        $document = parent::map();

        // genre
        if (isset($this->genre)) {
            $document['genre'] = $this->genre->map(function(GenreField $genre) { // Indexer le code
                return $genre->getEntryLabel();
            });
        }

        // media
        if (isset($this->media)) {
            $document['media'] = $this->media->map(function(MediaField $media) { // Indexer le code
                return $media->getEntryLabel();
            });
        }

        // title
        if (isset($this->title)) {
            $title = $this->title->getPhpValue();
            $document['title'] = $title;
            $document['title-sort'] = implode(' ', Tokenizer::tokenize($title));
        }

        // othertitle
        $this->mapMultiField($document, 'othertitle');

        // translation
        $this->mapMultiField($document, 'translation');

        // author
        $this->mapMultiField($document, 'author', function(AuthorField $aut) {
            return sprintf('%s¤%s', $aut->name->getPhpValue(), $aut->firstname->getPhpValue());
        });

        // corporation
        $this->mapMultiField($document, 'corporation', function(CorporationField $corp) {
            return sprintf(
                '%s¤%s¤%s¤%s',
                $corp->name->getPhpValue(),
                $corp->acronym->getPhpValue(),
                $corp->city->getPhpValue(),
                $corp->country->getPhpValue()
            );
         });

        // date
        $this->mapMultiField($document, 'date');

        // journal
        if (isset($this->journal)) {
            $document['journal'] = $this->journal->getPhpValue();
        }

        // number
        $this->mapMultiField($document, 'number');

        // language
        if (isset($this->language)) {
            $document['language'] = $this->language->map(function(LanguageField $language) { // Indexer le code
                return $language->getEntryLabel();
            });
        }

        // extent : non indexé

        // format (demande de Marion, cf. mail NL 12/03/18)
        if (isset($this->format)) {
            $document['format'] = $this->format->map(function(FormatField $format) { // Indexer le code ?
                return $format->getEntryLabel();
            });
        }

        // editor
        $this->mapMultiField($document, 'editor', function(EditorField $ed) { // cc organisme
            return sprintf(
                '%s¤%s¤%s¤%s',
                $ed->name->getPhpValue(),
                $ed->acronym->getPhpValue(),
                $ed->city->getPhpValue(),
                $ed->country->getPhpValue()
            );
        });

        // collection
        if (isset($this->collection)) {
            $document['collection'] = array_filter($this->collection->map(function(CollectionField $col) {
                return $col->name->getPhpValue();
            }));
        }

        // edition
        if (isset($this->edition)) {
            $document['edition'] = $this->edition->getPhpValue(); // multivalué, retourne un tableau
        }

        // context
        if (isset($this->context)) {
            $document['context'] = sprintf(
                '%s¤%s¤%s¤%s',
                $this->context->title->getPhpValue(),
                $this->context->date->getPhpValue(),
                $this->context->place->getPhpValue(),
                $this->context->number->getPhpValue()
            );
        }

        // topic
        $this->mapMultiField($document, 'topic');

        // topic-hierarchy : initialise le champ 'hierarchy' pour tous les topics qui sont associés à une table de type thesaurus
        foreach($this->topic->getThesaurusTopics() as $table => $topic) {
            if (isset($this->topic[$topic])) {
                $terms = $this->topic[$topic]->term->getPhpValue();
                $document["topic-$topic-hierarchy"] = $this->getTermsPath($terms, $table);
            }
        }

        // content
        $this->mapMultiField($document, 'content');

        // link
        $this->mapMultiField($document, 'link', 'url');

        // relation
        $this->mapMultiField($document, 'relation');

        // Ok
        return $document;
    }
}
