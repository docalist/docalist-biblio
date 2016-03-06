<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
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
use InvalidArgumentException;
use Docalist\Forms\EntryPicker;

/**
 * Gère une relation vers un autre post WordPress.
 */
class Relation extends Integer
{
    public function __construct($value = null, Schema $schema = null)
    {
        parent::__construct($value, $schema);

        // Vérifie que la classe descendante a indiqué le type de relation
        if (is_null($schema) || empty($schema->reltype())) {
            $field = $schema ? ($schema->name() ?: $schema->label()) : '';
            throw new InvalidArgumentException("Schema property 'reltype' is required for Relation field '$field'.");
        }

        // Vérifie que la classe descendante a indiqué un filtre pour les lookups
        if (is_null($schema) || empty($schema->relfilter())) {
            $field = $schema ? ($schema->name() ?: $schema->label()) : '';
            throw new InvalidArgumentException("Schema property 'relfilter' is required for Relation field '$field'.");
        }
    }

    static public function loadSchema() {
        return [
            'reltype' => '',    // exemple : "Svb\Type\Event"
            'relfilter' => '',  // exemple: "+type:event +database:dbevents"
        ];
    }

    public function getSettingsForm()
    {
        // Récupère le formulaire par défaut
        $form = parent::getSettingsForm();

        // Indique le type de relation (input disabled contenant le nom de la classe php)
        $form->input('reltype')
             ->setAttribute('disabled')
             ->addClass('code large-text')
             ->setLabel(__('Entité liée', 'docalist-biblio'))
             ->setDescription(__('Pour information, nom de classe des entités liées à ce champ.', 'docalist-biblio'));

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
}
