<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Biblio\Type\Repeatable;
use Docalist\Biblio\Type\SettingsFormTrait;
use Docalist\Forms\Table;

/**
 * Une collection de liens.
 */
class Links extends Repeatable {
    use SettingsFormTrait;

    static protected $type = 'Link';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->input('url')->addClass('url');
        $field->TableLookup('type', $this->schema->table())
              ->addClass('link-type');
        $field->input('label')->addClass('link-label');
        $field->input('date')->addClass('link-date');

        return $field;
    }

    public function settingsForm() {
        $form = parent::settingsForm();
        $form->select('table')
             ->label(__("Table des types de liens", 'docalist-biblio'))
             ->options($this->tablesOfType('links'));

        return $form;
    }
}