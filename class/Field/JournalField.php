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

use Docalist\Type\Text;
use Docalist\Forms\Container;
use Docalist\Forms\Element;
use Docalist\Forms\EntryPicker;
use Docalist\Data\Indexable;
use Docalist\Biblio\Indexer\JournalFieldIndexer;

/**
 * Champ "journal" : titre du périodique dans lequel a été publié le document.
 *
 * Ce champ n'est pas répétable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class JournalField extends Text implements Indexable
{
    /**
     * {@inheritdoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'journal',
            'label' => __('Périodique', 'docalist-biblio'),
            'description' => __(
                'Nom du journal (revue, magazine, périodique...) dans lequel a été publié le document.',
                'docalist-biblio'
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorForm($options = null): Element
    {
        $name = $this->schema->name() ?? '';

        $form = new EntryPicker($name);
        $form->setOptions('index:' . $name);
        $form->addClass('large-text');

        return $this->configureEditorForm($form, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return JournalFieldIndexer::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatSettingsForm(): Container
    {
        $form = parent::getFormatSettingsForm();

        $form->checkbox('searchlink')
            ->setLabel(__('Lien rebond', 'docalist-biblio'))
            ->setDescription(__(
                "Génère un lien de recherche pour le périodique
                (l'attribut <code>filter.journal</code> doit être actif).",
                'docalist-biblio')
            );

        return $form;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormattedValue($options = null): string
    {
        $result = $this->getPhpValue();

        if ($this->getOption('searchlink', $options)) {
            $url = apply_filters('docalist_search_get_search_page_url', '');
            if (!empty($url)) {
                $url .= '?filter.journal=' . urlencode($result);
                $title = __("Rechercher ce périodique", 'docalist-biblio');
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
}
