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
use Docalist\Biblio\Indexer\AuthorFieldIndexer;

/**
 * Champ "author" : personnes physiques ayant contribué au document catalogué.
 *
 * Ce champ répétable permet de saisir les noms et prénoms des personnes qui ont contribué à
 * l'élaboration du document catalogué.
 *
 * On peut également indiquer pour chaque personne une étiquette de rôle qui précise la nature
 * de sa contribution (traducteur, auteur de la préface, illustrations...)
 *
 * Chaque occurence du champ author comporte trois sous-champs :
 * - `name` : nom de la personne,
 * - `firstname` : prénom ou initiale de la personne,
 * - `role` : étiquette de rôle éventuelle.
 *
 * Le sous-champ role est associé à une table d'autorité qui contient les étiquettes de rôles
 * disponibles (par défaut, il s'agit de la table "marc21 relators").
 *
 * @property Text       $name       Nom de la personne.
 * @property Text       $firstname  Prénom.
 * @property TableEntry $role       Rôle.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class AuthorField extends MultiField implements Indexable
{
    /**
     * {@inheritdoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'author',
            'repeatable' => true,
            'label' => __('Auteurs', 'docalist-biblio'),
            'description' => __(
                "Personnes qui ont contribué au document (auteur, coordonnateur, réalisateur...)",
                'docalist-biblio'
            ),
            'fields' => [
                'name' => [
                    'type' => Text::class,
                    'label' => __('Nom', 'docalist-biblio'),
                    'description' => __('Nom de la personne.', 'docalist-biblio'),
                ],
                'firstname' => [
                    'type' => Text::class,
                    'label' => __('Prénom', 'docalist-biblio'),
                    'description' => __('Prénom(s) ou initiales.', 'docalist-biblio'),
                ],
                'role' => [
                    'type' => TableEntry::class,
                    'label' => __('Rôle', 'docalist-biblio'),
                    'description' => __('Fonction', 'docalist-biblio'),
                    'table' => 'thesaurus:marc21-relators_fr',
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
        return AuthorFieldIndexer::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getCategoryField(): TableEntry
    {
        return $this->role;
    }

    /**
     * Retourne l'auteur "et al."
     *
     * @return AuthorField
     */
    public static function etal()
    {
        return new self(['name' => 'et al.']);
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatSettingsForm(): Container
    {
        $form = parent::getFormatSettingsForm();

        $form->checkbox('searchlink')
            ->setLabel(__('Liens rebonds', 'docalist-biblio'))
            ->setDescription(__(
                "Génère un lien de recherche pour chaque auteur
                (l'attribut <code>filter.author</code> doit être actif).",
                'docalist-biblio')
            );

        return $form;
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableFormats(): array
    {
        return [
            'f n (r)'   => __('Charlie Chaplin (Acteur)', 'docalist-biblio'),
            'f n'       => __('Charlie Chaplin', 'docalist-biblio'),
            'n (f) / r' => __('Chaplin (Charlie) / Acteur', 'docalist-biblio'),
            'n (f)'     => __('Chaplin (Charlie)', 'docalist-biblio'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFormattedValue($options = null): string|array
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        $name = $this->formatField('name', $options);
        $firstName = $this->formatField('firstname', $options);
        $t = [];
        switch ($format) {
            case 'f n (r)':
                $role = $this->formatField('role', $options);
                !empty($firstName) && $t[] = $firstName;
                !empty($name) && $t[] = $name;
                !empty($role) && $t[] =  '(' . $role . ')';
                break;

            case 'f n':
                !empty($firstName) && $t[] = $firstName;
                !empty($name) && $t[] = $name;
                break;

            case 'n (f) / r':
                $role = $this->formatField('role', $options);
                !empty($name) && $t[] = $name;
                !empty($firstName) && $t[] = '(' . $firstName . ')';
                !empty($role) && $t[] =  '/ ' . $role; // insécable après le slash
                break;

            case 'n (f)':
                !empty($name) && $t[] = $name;
                !empty($firstName) && $t[] = '(' . $firstName . ')';
                break;

            default:
                return parent::getFormattedValue($options);
        }

        $result = implode(' ', $t); // espace insécable

        if ($this->getOption('searchlink', $options)) {
            $url = apply_filters('docalist_search_get_search_page_url', '');
            if (!empty($url)) {
                $filter = $this->name->getPhpValue() . '¤' . $this->firstname->getPhpValue();
                $url .= '?filter.author=' . urlencode($filter);
                $title = __("Rechercher cet auteur", 'docalist-biblio');
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
     * {@inheritDoc}
     */
    public function filterEmpty(bool $strict = true): bool
    {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas de nom
        return $this->filterEmptyProperty('name');
    }
}
