<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio;

use Docalist\Data\Record;
use Docalist\Biblio\Field\Genre;
use Docalist\Biblio\Field\Media;
use Docalist\Biblio\Field\Title;
use Docalist\Biblio\Field\OtherTitle;
use Docalist\Biblio\Field\Translation;
use Docalist\Biblio\Field\Author;
use Docalist\Biblio\Field\Organisation;
use Docalist\Biblio\Field\Date;
use Docalist\Biblio\Field\Journal;
use Docalist\Biblio\Field\Number;
use Docalist\Biblio\Field\Language;
use Docalist\Biblio\Field\Extent;
use Docalist\Biblio\Field\Format;
use Docalist\Biblio\Field\Editor;
use Docalist\Biblio\Field\Collection;
use Docalist\Biblio\Field\Edition;
use Docalist\Biblio\Field\Event;
use Docalist\Data\Type\Topic;
use Docalist\Biblio\Field\Content;
use Docalist\Data\Type\Link;
use Docalist\Biblio\Field\Relation;
use Docalist\Biblio\Field\Owner;
use Docalist\Biblio\Field\Imported;
use Docalist\Biblio\Field\Error;

use Docalist\Search\MappingBuilder;
use Docalist\Tokenizer;

/**
 * Une référence documentaire.
 *
 * Le schéma d'une référence est fixe : les classes descendantes (Article, Book, ...) ne doivent pas créer
 * de nouveaux champs, elles peuvent juste paramétrer les champs existant ou les marquer "unused".
 *
 * @property Genre[]        $genre          Genres.
 * @property Media[]        $media          Supports.
 * @property Title          $title          Titre du document.
 * @property OtherTitle[]   $othertitle     Autres titres.
 * @property Translation[]  $translation    Traductions du titre.
 * @property Author[]       $author         Personnes auteurs.
 * @property Organisation[] $organisation   Organismes auteurs.
 * @property Date[]         $date           Dates du document.
 * @property Journal        $journal        Périodique.
 * @property Number[]       $number         Numéros du document.
 * @property Language[]     $language       Langues des textes.
 * @property Extent[]       $extent         Etendue.
 * @property Format[]       $format         Format et étiquettes de collation.
 * @property Editor[]       $editor         Editeurs.
 * @property Collection[]   $collection     Collection et numéro dans la collection.
 * @property Edition[]      $edition        Mentions d'édition.
 * @property Event          $event          Événement à l'origine du document.
 * @property Topic[]        $topic          Mots-clés.
 * @property Content[]      $content        Contenu du document.
 * @property Link[]         $link           Liens internet.
 * @property Relation[]     $relation       Relations avec d'autres références.
 * @property Owner[]        $owner          Producteur de la notice.
 *
 * @property Imported       $imported       Notice importée.
 * @property Error[]        $errors         Erreurs.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Reference extends Record
{
    public static function loadSchema()
    {
        return [
            'name' => 'reference',
            'label' => __('Référence', 'docalist-biblio'),
            'description' => __('Une référence documentaire.', 'docalist-biblio'),
            'fields' => [
                'genre'         => 'Docalist\Biblio\Field\Genre*',
                'media'         => 'Docalist\Biblio\Field\Media*',
                'title'         => 'Docalist\Biblio\Field\Title',
                'othertitle'    => 'Docalist\Biblio\Field\OtherTitle*',
                'translation'   => 'Docalist\Biblio\Field\Translation*',
                'author'        => 'Docalist\Biblio\Field\Author*',
                'organisation'  => 'Docalist\Biblio\Field\Organisation*',
                'date'          => 'Docalist\Biblio\Field\Date*',
                'journal'       => 'Docalist\Biblio\Field\Journal',
                'number'        => 'Docalist\Biblio\Field\Number*',
                'language'      => 'Docalist\Biblio\Field\Language*',
                'extent'        => 'Docalist\Biblio\Field\Extent*',
                'format'        => 'Docalist\Biblio\Field\Format*',
                'editor'        => 'Docalist\Biblio\Field\Editor*',
                'collection'    => 'Docalist\Biblio\Field\Collection*',
                'edition'       => 'Docalist\Biblio\Field\Edition*',
                'event'         => 'Docalist\Biblio\Field\Event',
                'topic'         => 'Docalist\Data\Type\Topic*',
                'content'       => 'Docalist\Biblio\Field\Content*',
                'link'          => 'Docalist\Data\Type\Link*',
                'relation'      => 'Docalist\Biblio\Field\Relation*',
                'owner'         => 'Docalist\Biblio\Field\Owner*',

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

    /**
     * Compatibilité : le type du champ relation a changé.
     *
     * Auparavant, c'était un multifield du style type+ref*. Désormais, c'est un TypedRelation, le champ ref
     * n'existe plus et le champ value n'est pas répétable.
     *
     * Comme le champ n'était pas utilisé et que de toute façon ce serait difficile de gérer la compatibilité
     * ascendante (il faudrait traduire les n° de réf en Post ID), on se contente de supprimer la valeur
     * existante si on rencontre un champ relation qui a l'ancienne forme.
     *
     * {@inheritDoc}
     */
    public function assign($value)
    {
        ($value instanceof Any) && $value = $value->getPhpValue();

        // Ignore (efface) les relations qui utilisent l'ancien type de champ
        if (is_array($value) && isset($value['relation']) && isset(reset($value['relation'])['ref'])) {
            unset($value['relation']);
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


    protected static function buildEditGrid(array $groups)
    {
        $allFields = static::getDefaultSchema()->getFields();
        $grid = [];
        $groupNumber = 1;
        foreach ($groups as $label => $fields) {
            // Pour chaque groupe de champs, la liste de champs est une chaine ou un tableau
            is_string($fields) && $fields = explode(',', $fields);

            // Crée le groupe
            $group = 'group' . $groupNumber++;
            $grid[$group] = [
                'type' => 'Docalist\Data\Type\Group',
                'label' => $label
            ];

            // Ajoute tous les champs de ce groupe
            foreach ($fields as $field) {
                // La chaine '-' est utilisée pour indiquer une boite "collapsed"
                if ($field==='-') {
                    $grid[$group]['state'] = 'collapsed';
                    continue;
                }
                // Vérifie que le champ existe et qu'il n'apparait qu'une seule fois dans la grille
                $field = trim($field);
                if (!isset($allFields[$field])) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" not in schema or defined twice', $field));
                }
                if ($allFields[$field]->unused()) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" is marked "unused" in schema', $field));
                }
                unset($allFields[$field]);

                // Ajoute le champ
                $grid[] = $field;
            }
        }

        // Ajoute tous les champs qui ne sont pas listés dans un groupe caché "champs non utilisés"
        /*
         Désactivé car ça crée un conflit entre l'UI wordpress et la notre (les notices restent en auto-draft)
        if ($allFields) {
            $group = 'group' . $groupNumber++;
            $grid[$group] = [
                'type' => 'Docalist\Data\Type\Group',
                'label' => __('Champs non utilisés', 'docalist-biblio'),
                'state' => 'hidden',
                'description' => __(
                    '<b>ATTENTION</b> : les champs suivants ne sont pas utilisés ou sont des champs de
                    gestion gérés directement par WordPress. <b>VOUS NE DEVRIEZ PAS LES MODIFIER<b>.',
                    'docalist-biblio'
                )
            ];
            $grid = array_merge($grid, array_keys($allFields));
        }
        */

        // Construit la grille finale
        return [
            'name' => 'edit',
            'gridtype' => 'edit',
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            //'description' => $description,
            'fields' => $grid,
        ];
    }

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Nature du document', 'docalist-biblio')             => 'genre,media',
            __('Titres', 'docalist-biblio')                         => 'title,othertitle,translation',
            __('Auteurs', 'docalist-biblio')                        => 'author,organisation',
            __('Informations bibliographiques', 'docalist-biblio')  => 'journal,date,language,number,extent,format',
            __('Informations éditeur', 'docalist-biblio')           => 'editor,collection,edition',
            __('Congrès et diplômes', 'docalist-biblio')            => 'event',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
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

        // organisation
        $mapping->addField('organisation')->text()->filter()->suggest()
                ->addTemplate('organisation-*')->copyFrom('organisation')->copyDataTo('organisation');
        // Exemples d'options pour l'indexation du champ 'organisme' :
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

        // format : non indexé

        // editor
        $mapping->addField('editor')->text()->filter()->suggest() // cc organisme
                ->addTemplate('editor-*')->copyFrom('editor')->copyDataTo('editor');

        // collection
        $mapping->addField('collection')->text()->filter();

        // edition
        $mapping->addField('edition')->text()->filter();

        // event
        $mapping->addField('event')->text()->filter();

        // topic
        $mapping->addField('topic')->text()->filter()->suggest()
                ->addTemplate('topic-*')->copyFrom('topic')->copyDataTo('topic');

        // topic-hierarchy : crée un champ 'hierarchy' pour tous les topics qui sont associés à une table de type thesaurus
        foreach($this->topic->getThesaurusTopics() as $topic) {
            $mapping->addField("topic-$topic-hierarchy")->text('hierarchy')->setProperty('search_analyzer', 'keyword');
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

        // owner
        $mapping->addField('owner')->text()->filter();

        // imported
        // errors

        return $mapping;
    }

    public function map()
    {
        // Le mapping des champs de base est fait par la classe parent
        $document = parent::map();

        // genre
        if (isset($this->genre)) {
            $document['genre'] = $this->genre->map(function(Genre $genre) { // Indexer le code
                return $genre->getEntryLabel();
            });
        }

        // media
        if (isset($this->media)) {
            $document['media'] = $this->media->map(function(Media $media) { // Indexer le code
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
        $this->mapMultiField($document, 'author', function(Author $aut) {
            return sprintf('%s¤%s', $aut->name->getPhpValue(), $aut->firstname->getPhpValue());
        });

        // organisation
        $this->mapMultiField($document, 'organisation', function(Organisation $org) {
            return sprintf(
                '%s¤%s¤%s¤%s',
                $org->name->getPhpValue(),
                $org->acronym->getPhpValue(),
                $org->city->getPhpValue(),
                $org->country->getPhpValue()
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
            $document['language'] = $this->language->map(function(Language $language) { // Indexer le code
                return $language->getEntryLabel();
            });
        }

        // extent : non indexé

        // format : non indexé

        // editor
        $this->mapMultiField($document, 'editor', function(Editor $org) { // cc organisme
            return sprintf(
                '%s¤%s¤%s¤%s',
                $org->name->getPhpValue(),
                $org->acronym->getPhpValue(),
                $org->city->getPhpValue(),
                $org->country->getPhpValue()
            );
        });

        // collection
        if (isset($this->collection)) {
            $document['collection'] = array_filter($this->collection->map(function(Collection $col) {
                return $col->name->getPhpValue();
            }));
        }

        // edition
        if (isset($this->edition)) {
            $document['edition'] = $this->edition->getPhpValue(); // multivalué, retourne un tableau
        }

        // event
        if (isset($this->event)) {
            $document['event'] = sprintf(
                '%s¤%s¤%s¤%s',
                $this->event->title->getPhpValue(),
                $this->event->date->getPhpValue(),
                $this->event->place->getPhpValue(),
                $this->event->number->getPhpValue()
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

        // owner
        if (isset($this->owner)) {
            $document['owner'] = $this->owner->getPhpValue();
        }

        // imported
        // errors

        // Ok
        return $document;
    }
}
