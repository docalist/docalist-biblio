<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Type;

use Docalist\Schema\Schema;
use Docalist\Type\Integer;
use Docalist\Forms\EntryPicker;
use InvalidArgumentException;

/**
 * Gère une relation vers un autre post WordPress.
 */
class Relation extends Integer
{
    public static function loadSchema()
    {
        return [
//            'reltype' => '',    // exemple : "Svb\Type\Event"
            'relfilter' => '*',  // exemple: "+type:event +database:dbevents"
        ];
    }

    public function __construct($value = null, Schema $schema = null)
    {
        parent::__construct($value, $schema);

        // Vérifie que la classe descendante a indiqué le type de relation
/*
        if (is_null($schema) || empty($schema->reltype())) {
            $field = $schema ? ($schema->name() ?: $schema->label()) : '';
            throw new InvalidArgumentException("Schema property 'reltype' is required for Relation field '$field'.");
        }
*/
        // Vérifie que la classe descendante a indiqué un filtre pour les lookups
        if (is_null($schema) || empty($schema->relfilter())) {
            $field = $schema ? ($schema->name() ?: $schema->label()) : '';
            throw new InvalidArgumentException("Schema property 'relfilter' is required for Relation field '$field'.");
        }
    }

    public static function getClassDefault()
    {
        // On surcharge car la valeur par défut d'un Integer est 0
        // On utilise null pour indiquer "pas de relation"
        return null;
    }

    public function assign($value)
    {
        // Un Integer ne peut pas être à null, par contre pour un type Relation, il faut accepter la valeur null
        if (is_null($value)) {
            $this->phpValue = null;

            return $this;
        }

        return parent::assign($value);
    }

    public function getSettingsForm()
    {
        // Récupère le formulaire par défaut
        $form = parent::getSettingsForm();

        // Indique le type de relation (input disabled contenant le nom de la classe php)
/*
        $form->input('reltype')
             ->setAttribute('disabled')
             ->addClass('code large-text')
             ->setLabel(__('Entité liée', 'docalist-biblio'))
             ->setDescription(__('Pour information, nom de classe des entités liées à ce champ.', 'docalist-biblio'));
*/
        $form->input('relfilter')
             ->addClass('code large-text')
             ->setLabel(__('Filtre de recherche', 'docalist-biblio'))
             ->setDescription(__(
                 'Equation utilisée pour filtrer les suggestions (lookups) en saisie.
                  Exemple : <code>type:mytype</code> ou <code>+type:mytype +database:mybase</code>.',
                 'docalist-biblio'
             ));

        return $form;
    }

    public function getAvailableEditors()
    {
        return [
            'lookup' => __('Lookup dynamique', 'docalist-biblio'),
            'input' => __('Saisie manuelle du POST ID', 'docalist-biblio'),
        ];
    }

    public function getEditorForm($options = null)
    {
        $editor = $this->getOption('editor', $options, $this->getDefaultEditor());

        switch ($editor) {
            case 'lookup':
                $editor = new EntryPicker();
                break;

            case 'input':
                return parent::getEditorForm($options);


            default:
                throw new InvalidArgumentException("Invalid TableEntry editor '$editor'");
        }

        return $editor
            ->setName($this->schema->name())
            ->setOptions('search:' . $this->schema->relfilter())
            ->setLabel($this->getOption('label', $options))
            ->setDescription($this->getOption('description', $options));
    }

    public function getAvailableFormats()
    {
        return [
            'id'            => __('Post ID', 'docalist-biblio'),
            'title'         => __('Titre', 'docalist-biblio'),
            'url'           => __('Permalien', 'docalist-biblio'),
            'link-id'       => __('Post ID cliquable', 'docalist-biblio'),
            'link-title'    => __('Titre cliquable', 'docalist-biblio'),
            'link-url'      => __('Permalien cliquable', 'docalist-biblio'),
        ];
    }

    public function getDefaultFormat()
    {
        return 'link-title';
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        switch ($format) {
            case 'id':
                return $this->phpValue;

            case 'title':
                return get_the_title($this->phpValue);

            case 'url':
                return get_post_permalink($this->phpValue);

            case 'link-id':
                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    esc_attr(get_post_permalink($this->phpValue)),
                    esc_attr(get_the_title($this->phpValue)),
                    esc_html($this->phpValue)
                );

            case 'link-title':
                return sprintf(
                    '<a href="%s">%s</a>',
                    esc_attr(get_post_permalink($this->phpValue)),
                    esc_html(get_the_title($this->phpValue))
                );

            case 'link-url':
                $url = get_post_permalink($this->phpValue);
                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    esc_attr($url),
                    esc_attr(get_the_title($this->phpValue)),
                    esc_html($url)
                );
        }

        throw new InvalidArgumentException("Invalid Relation format '$format'");
    }
}
