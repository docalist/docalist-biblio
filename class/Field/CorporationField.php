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

namespace Docalist\Biblio\Field;

use Docalist\Type\MultiField;
use Docalist\Type\Text;
use Docalist\Type\TableEntry;
use Docalist\Forms\Container;
use Docalist\Data\Indexable;
use Docalist\Data\Type\Collection\IndexableMultiFieldCollection;
use Docalist\Biblio\Indexer\CorporationFieldIndexer;

/**
 * Champ "corporation" : auteurs moraux ayant contribué au document catalogué.
 *
 * Ce champ répétable permet de saisir les organismes qui ont contribué à l'élaboration du document catalogué.
 *
 * On peut également indiquer pour chaque organisme auteur une étiquette de rôle qui précise la nature de sa
 * contribution (auteur, commanditaire, financeur...)
 *
 * Chaque occurence du champ corporation comporte cinq sous-champs :
 * - `name` : nom de l'organisme,
 * - `acronym` : sigle ou acronyme,
 * - `city` : ville,
 * - `country` : pays,
 * - `role` : étiquette de rôle.
 *
 * Le sous-champ `country` est associé à une table d'autorité qui contient les codes pays disponibles
 * (par défaut, il s'agit de la table des codes ISO à trois lettres).
 *
 * Le sous-champ `role` est associé à une table d'autorité qui contient les étiquettes de rôles disponibles
 * (par défaut, il s'agit de la table "marc21 relators").
 *
 * @property Text       $name       Nom de l'organisme.
 * @property Text       $acronym    Sigle ou acronyme.
 * @property Text       $city       Ville.
 * @property TableEntry $country    Pays.
 * @property TableEntry $role       Rôle.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class CorporationField extends MultiField implements Indexable
{
    /**
     * {@inheritdoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'corporation',
            'repeatable' => true,
            'label' => __('Organismes', 'docalist-biblio'),
            'description' => __(
                "Organismes qui ont contribué au document (organisme auteur, commanditaire, financeur...)",
                'docalist-biblio'
            ),
            'fields' => [
                'name' => [
                    'type' => Text::class,
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __("Nom de l'organisme", 'docalist-biblio'),
                ],
                'acronym' => [
                    'type' => Text::class,
                    'label' => __('Sigle', 'docalist-biblio'),
                    'description' => __("Sigle ou acronyme", 'docalist-biblio'),
                ],
                'city' => [
                    'type' => Text::class,
                    'label' => __('Ville', 'docalist-biblio'),
                    'description' => __('Ville du siège social', 'docalist-biblio'),
                ],
                'country' => [
                    'type' => TableEntry::class,
                    'table' => 'table:ISO-3166-1_alpha2_fr',
                    'label' => __('Pays', 'docalist-biblio'),
                    'description' => __('Pays du siège social', 'docalist-biblio'),
                ],
                'role' => [
                    'type' => TableEntry::class,
                    'table' => 'thesaurus:marc21-relators_fr',
                    'label' => __('Rôle', 'docalist-biblio'),
                    'description' => __('Fonction', 'docalist-biblio'),
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass(): string
    {
        return IndexableMultiFieldCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return CorporationFieldIndexer::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCategoryField(): TableEntry
    {
        return $this->role;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatSettingsForm(): Container
    {
        $form = parent::getFormatSettingsForm();

        $form->checkbox('searchlink')
        ->setLabel(__('Liens rebonds', 'docalist-core'))
        ->setDescription(__(
            "Génère un lien de recherche pour chaque organisme
            (l'attribut <code>filter.corporation</code> doit être actif).",
            'docalist-core')
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableFormats(): array
    {
        return [
            'n (a), t, c, r' => 'Nom (sigle), ville, pays, rôle',
            'n (a), t, c' => 'Nom (sigle), ville, pays',
            'name' => 'Nom ou sigle uniquement',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        switch ($format) {
            case 'n (a), t, c':
            case 'n (a), t, c, r':
                $name = $this->formatField('name', $options);
                $acronym = $this->formatField('acronym', $options);
                if (!empty($name) && !empty($acronym)) {
                    $result = $name . ' (' . $acronym . ')';
                } else {
                    $result = $name . $acronym; // l'un des deux est vide
                }

                $city = $this->formatField('city', $options);
                if (!empty($city)) {
                    $result && $result .= ', ';
                    $result .= $city;
                }

                $country = $this->formatField('country', $options);
                if (!empty($country)) {
                    $result && $result .= ', ';
                    $result .= $country;
                }

                if ($format === 'n (a), t, c, r') {
                    $role = $this->formatField('role', $options);
                    if (!empty($role)) {
                        $result && $result .= ' / '; // espaces insécables
                        $result .= $role;
                    }
                }
                break;

            case 'name':
                $result = $this->formatField('name', $options);
                empty($result) && $result = $this->formatField('acronym', $options);
                break;

            default:
                return parent::getFormattedValue($options);
        }

        if ($this->getOption('searchlink', $options)) {
            $url = apply_filters('docalist_search_get_search_page_url', '');
            if (!empty($url)) {
                $filter = sprintf(
                    '%s¤%s¤%s¤%s',
                    $this->name->getPhpValue(),
                    $this->acronym->getPhpValue(),
                    $this->city->getPhpValue(),
                    $this->country->getPhpValue()
                );
                $url .= '?filter.corporation=' . urlencode($filter);
                $title = __("Rechercher cet organisme", 'docalist-search');
                $result = sprintf(
                    '<a class="searchlink" href="%s" title="%s">%s</a>',
                    esc_attr($url),
                    esc_attr($title),
                    $result
                );
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function filterEmpty(bool $strict = true): bool
    {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a ni nom ni sigle
        return $this->filterEmptyProperty('name') && $this->filterEmptyProperty('acronym');
    }
}
