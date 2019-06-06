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
use Docalist\Data\Type\Collection\IndexableTypedValueCollection;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Biblio\Field\TranslationField;

/**
 * Indexeur pour le champ "translation".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TranslationFieldIndexer extends FieldIndexer
{
    /**
     * {@inheritDoc}
     *
     * @var IndexableTypedValueCollection
     */
    protected $field;

    /**
     * {@inheritDoc}
     *
     * @param IndexableTypedValueCollection $field
     */
    public function __construct(IndexableTypedValueCollection $field)
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
                return __(
                    'Recherche sur les traductions du titre original des documents catalogués.',
                    'docalist-biblio'
                );
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
                return __('Recherche fulltext sur les titres traduits.', 'docalist-biblio');
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
                ->setLabel($this->getAttributeLabel('search'))
                ->setDescription($this->getAttributeDescription('search'));
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
        foreach ($this->field as $translation) { /** @var TranslationField $otherTitle */
            isset($attr['search']) && $data[$attr['search']][] = $translation->value->getPhpValue();
        }

        // Ok
        return $data;
    }
}
