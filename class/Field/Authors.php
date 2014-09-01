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
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\Repeatable;
use Docalist\Biblio\Type\SettingsFormTrait;
use Docalist\Forms\Table;

/**
 * Une collection d'auteurs physiques.
 */
class Authors extends Repeatable {
    use SettingsFormTrait;

    static protected $type = 'Author';

    public function editForm() {
        $field = new Table($this->schema->name());

        $field->input('name')->addClass('author-name');
        $field->input('firstname')->addClass('author-firstname');
        $field->TableLookup('role', $this->schema->table())
              ->addClass('author-role');

        return $field;
    }

    public function settingsForm() {
        $form = parent::settingsForm();
        $form->select('table')
             ->label(__("Table des rôles", 'docalist-biblio'))
             ->options($this->tablesOfType('roles'));

        return $form;
    }
}