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
use Docalist\Biblio\Field\JournalField;
use Docalist\Forms\Container;
use Docalist\Search\Mapping;
use Docalist\Search\Mapping\Field\Parameter\IndexOptions;

/**
 * Indexeur pour le champ "journal".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class JournalFieldIndexer extends FieldIndexer
{
    /**
     * {@inheritDoc}
     *
     * @var JournalField
     */
    protected $field;

    /**
     * {@inheritDoc}
     *
     * @param JournalField $field
     */
    public function __construct(JournalField $field)
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
                return __('Recherche sur le titre de périodique des articles catalogués.', 'docalist-biblio');

            case 'filter':
                return __('Filtre sur le titre de périodique des articles catalogués.', 'docalist-biblio');

            case 'suggest':
                return __('Autocomplete sur le titre de périodique des articles catalogués.', 'docalist-biblio');

            case 'sort':
                return __('Tri sur le titre de périodique des articles catalogués.', 'docalist-biblio');
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
                    'Recherche par mot ou par expression sans tenir compte de l\'ordre et de la fréquence des mots.',
                    'docalist-biblio'
                );

            case 'filter':
                return __(
                    'Titre exact du périodique tel que saisi dans la référence.',
                    'docalist-biblio'
                );

            case 'suggest':
                return __(
                    'Permet de faire des lookups sur les titres de périodiques déjà saisis.',
                    'docalist-biblio'
                );

            case 'sort':
                return __(
                    'Version en minuscules sans accents ni signes de ponctuation du titre de périodique.',
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

        // Si le champ n'est pas indexé ou qu'il est vide, terminé
        $value = $this->field->getPhpValue();
        if (empty($attr) || empty($value)) {
            return [];
        }

        // Indexe le champ
        $data = [];

        isset($attr['search']) && $data[$attr['search']] = $value;
        isset($attr['filter']) && $data[$attr['filter']] = $value;
        isset($attr['suggest']) && $data[$attr['suggest']] = $value;
        isset($attr['sort']) && $data[$attr['sort']] = $this->getSortKey($value);

        // Ok
        return $data;
    }
}
