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
use Docalist\Forms\TableLookup;

/**
 * Une collection de supports de documents.
 */
class Medias extends Repeatable {
    use SettingsFormTrait;

    static protected $type = 'Media';

    public function editForm() {
        $field = new TableLookup($this->schema->name(), $this->schema->table());
        $field->multiple(true);

        return $field;
    }

    public function settingsForm() {
        $form = parent::settingsForm();
        $form->select('table')
             ->label(__("Table d'autorité", 'docalist-biblio'))
             ->options($this->tablesOfType('medias'));

        return $form;
    }
}