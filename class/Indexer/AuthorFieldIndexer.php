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

namespace Docalist\Biblio\Indexer;

use Docalist\Data\Indexer\FieldIndexer;
use Docalist\Data\Type\Collection\IndexableMultiFieldCollection;
use Docalist\Biblio\Field\AuthorField;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Search\Mapping\Field\Parameter\IndexOptions;

/**
 * Indexeur pour le champ "author".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class AuthorFieldIndexer extends FieldIndexer
{
    /**
     * {@inheritDoc}
     *
     * @var IndexableMultiFieldCollection
     */
    protected $field;

    /**
     * {@inheritDoc}
     *
     * @param IndexableMultiFieldCollection $field
     */
    public function __construct(IndexableMultiFieldCollection $field)
    {
        parent::__construct($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeLabel(string $attribute, string $type = ''): string
    {
        switch ($attribute) {
            case 'search':
                return __('Recherche sur les auteurs physiques des références docalist.', 'docalist-biblio');

            case 'filter':
                return __('Filtre sur les auteurs physiques des références docalist.', 'docalist-biblio');

            case 'suggest':
                return __('Autocomplete sur les auteurs physiques des références docalist.', 'docalist-biblio');

            case 'sort':
                return __('Tri sur le premier auteur physique des références docalist.', 'docalist-biblio');
        }

        return parent::getAttributeLabel($attribute, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeDescription(string $attribute, string $type = ''): string
    {
        switch ($attribute) {
            case 'search':
                return __(
                    'Exemples : <code>author:nom</code>, <code>author:prénom</code>, <code>author:no*</code>
                    <code>author:"nom prénom"</code>, <code>author:"prénom nom"</code>.',
                    'docalist-biblio'
                );

            case 'filter':
                return __(
                    'Contient des valeurs structurées de la forme "Nom¤Prénom".',
                    'docalist-biblio'
                );

            case 'suggest':
                return __(
                    'Contient des valeurs structurées de la forme "Nom¤Prénom".',
                    'docalist-biblio'
                );

            case 'sort':
                return __(
                    'Version en minuscules sans accents ni signes de ponctuation du nom et du prénom
                    du premier auteur physique indiqué dans la référence docalist.',
                    'docalist-biblio'
                );
        }

        return parent::getAttributeDescription($attribute, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexSettingsForm(): Container
    {
        $form = parent::getIndexSettingsForm();

        $form->checkbox()
            ->setName('search')
            ->setLabel($this->getAttributeName('search'))
            ->setDescription($this->getAttributeLabel('search'));

        $form->checkbox()
            ->setName('filter')
            ->setLabel($this->getAttributeName('filter'))
            ->setDescription($this->getAttributeLabel('filter'));

        $form->checkbox()
            ->setName('suggest')
            ->setLabel($this->getAttributeName('suggest'))
            ->setDescription($this->getAttributeLabel('suggest'));

        $form->checkbox()
            ->setName('sort')
            ->setLabel($this->getAttributeName('sort'))
            ->setDescription($this->getAttributeLabel('sort'));

        return $form;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(): Mapping
    {
        $mapping = parent::getMapping();

        $attr = $this->getAttributes();

        if (isset($attr['search'])) {
            $mapping
                ->literal($attr['search'])
                ->setFeatures(Mapping::FULLTEXT)
                ->setIndexOptions(IndexOptions::INDEX_DOCS)
                ->setLabel($this->getAttributeLabel('search'))
                ->setDescription($this->getAttributeDescription('search'));
        }

        if (isset($attr['filter'])) {
            $mapping
                ->keyword($attr['filter'])
                ->setFeatures(Mapping::AGGREGATE | Mapping::FILTER)
                ->setLabel($this->getAttributeLabel('filter'))
                ->setDescription($this->getAttributeDescription('filter'));
        }

        if (isset($attr['suggest'])) {
            $mapping
                ->suggest($attr['suggest'])
                ->setFeatures(Mapping::LOOKUP)
                ->setLabel($this->getAttributeLabel('suggest'))
                ->setDescription($this->getAttributeDescription('suggest'));
        }

        if (isset($attr['sort'])) {
            $mapping
                ->keyword($attr['sort'])
                ->setFeatures(Mapping::SORT)
                ->setLabel($this->getAttributeLabel('sort'))
                ->setDescription($this->getAttributeDescription('sort'));
        }

        return $mapping;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexData(): array
    {
        // Récupère la liste des attributs à générer
        $attr = $this->getAttributes();

        // Si le champ n'est pas indexé ou que la collection est vide, terminé
        if (empty($attr) || 0 === $this->field->count()) {
            return [];
        }

        // Indexe toute les entrées
        $data = [];
        foreach ($this->field as $author) { /** @var AuthorField $author */
            $value = $author->name->getPhpValue() . '¤' . $author->firstname->getPhpValue();

            isset($attr['search']) && $data[$attr['search']][] = $value;
            isset($attr['filter']) && $data[$attr['filter']][] = $value;
            isset($attr['suggest']) && $data[$attr['suggest']][] = $value;

            if (isset($attr['sort']) && empty($data[$attr['sort']])) { // le premier uniquement
                $data[$attr['sort']][] = $this->getSortKey($value);
            }
        }

        // Ok
        return $data;
    }
}
