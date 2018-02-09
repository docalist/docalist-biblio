<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\Text;
use Docalist\Forms\EntryPicker;

/**
 * Champ "journal" : titre du périodique dans lequel a été publié le document.
 *
 * Ce champ n'est pas répétable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class JournalField extends Text
{
    public static function loadSchema()
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

    public function getEditorForm($options = null)
    {
        return (new EntryPicker('journal'))->setOptions('index:journal')->addClass('large-text');
    }
}
