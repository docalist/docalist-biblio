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

namespace Docalist\Biblio\Entity;

use Docalist\Data\Entity\ContentEntity;
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
use Docalist\Biblio\Field\LinkField;
use Docalist\Biblio\Field\RelationField;

use Docalist\Type\Collection;
use Docalist\Type\Collection\MultiFieldCollection;
use Docalist\Type\Collection\TypedValueCollection;
use Docalist\Data\Type\Collection\IndexableCollection;
use Docalist\Data\Type\Collection\IndexableTypedValueCollection;
use Docalist\Data\Type\Collection\IndexableMultiFieldCollection;
use Docalist\Data\Type\Collection\TypedRelationCollection;

use Docalist\Data\GridBuilder\EditGridBuilder;

/**
 * Une référence documentaire.
 *
 * Le schéma d'une référence est fixe : les classes descendantes (Article, Book, ...) ne doivent pas créer
 * de nouveaux champs, elles peuvent juste paramétrer les champs existant ou les marquer "unused".
 *
 * @property IndexableCollection            $genre          Genres.
 * @property IndexableCollection            $media          Supports.
 * @property TitleField                     $title          Titre du document.
 * @property IndexableTypedValueCollection  $othertitle     Autres titres.
 * @property IndexableTypedValueCollection  $translation    Traductions du titre.
 * @property IndexableMultiFieldCollection  $author         Personnes auteurs (auteurs physiques).
 * @property IndexableMultiFieldCollection  $corporation    Organismes auteurs (auteurs moraux).
 * @property TypedValueCollection           $date           Dates du document.
 * @property JournalField                   $journal        Périodique.
 * @property TypedValueCollection           $number         Numéros du document.
 * @property IndexableCollection            $language       Langues des textes.
 * @property TypedValueCollection           $extent         Etendue.
 * @property IndexableCollection            $format         Format et étiquettes de collation.
 * @property IndexableMultiFieldCollection  $editor         Editeurs.
 * @property Collection                     $collection     Collection et numéro dans la collection.
 * @property Collection                     $edition        Mentions d'édition.
 * @property Collection                     $context        Contexte dans lequel a été produit le document.
 * @property MultiFieldCollection           $link           Liens internet.
 * @property TypedRelationCollection        $relation       Relations avec d'autres références.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ReferenceEntity extends ContentEntity
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
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
                'topic'         => [], // Hérité de ContentEntity
                'content'       => [], // Hérité de ContentEntity
                'link'          => LinkField::class,
                'relation'      => RelationField::class,
            ],
        ];
    }

    /**
     * Compatibilité ascendante
     *
     * - Champ "corporation" : auparavant, le champ s'appellait "organisation", on renomme à la volée.
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
    public function assign($value): void
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

        parent::assign($value);
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
        $builder = new EditGridBuilder(self::class);

        $builder->setProperty('stylesheet', 'docalist-biblio-edit-reference');

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
}
