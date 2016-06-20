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
namespace Docalist\Biblio\Field;

use Docalist\Type\Text;
use Docalist\Forms\EntryPicker;

/**
 * Un titre de périodique.
 */
class Journal extends Text
{
    public function getEditorForm($options = null)
    {
        return (new EntryPicker('journal'))->setOptions('index:journal.filter')->addClass('large-text');
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('journal')->text()->filter()->suggest();
    }

    public function mapData(array & $document) {
        $document['journal'] = $this->value();
    }
*/
}