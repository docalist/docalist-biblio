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

use Docalist\Forms\Table;

/**
 * Une collection de topics d'indexation.
 */
class Topics extends Repeatable {
    use SettingsFormTrait;

    static protected $type = 'Topic';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->TableLookup('type', $this->schema->table())
              ->addClass('topic-type');
        //$field->input('term')->addClass('topic-term');
/*
        switch ($this->database->settings()->slug) {
            case 'infolegis':
                $table = 'thesaurus:domaines-test';
                break;
            case 'annuairesites':
                $table = 'thesaurus:prisme-web-content';
                break;
            default:
                $table = 'thesaurus:thesaurus-prisme-2013';
        }
*/
        $table = 'thesaurus:thesaurus-prisme-2013';
        $field->TableLookup('term', $table)
              ->multiple(true)
              ->addClass('topic-term');

        return $field;
    }

    public function settingsForm() {
        $form = parent::settingsForm();
        $form->select('table')
             ->label(__("Table des vocabulaires", 'docalist-biblio'))
             ->options($this->tablesOfType('topics'));

        return $form;
    }
}