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
use Docalist\Biblio\Field\OtherTitleField;

/**
 * Indexeur pour le champ "othertitle".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class OtherTitleFieldIndexer extends FieldIndexer
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
                return __('Recherche sur les titres secondaires des documents catalogués.', 'docalist-biblio');
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
                return __('Recherche fulltext sur les autres titres.', 'docalist-biblio');
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
                ->text($attr['search'])
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
        foreach ($this->field as $otherTitle) { /** @var OtherTitleField $otherTitle */
            isset($attr['search']) && $data[$attr['search']][] = $otherTitle->value->getPhpValue();
        }

        // Ok
        return $data;
    }
}
