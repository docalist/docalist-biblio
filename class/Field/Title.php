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
use Docalist\Biblio\DatabaseIndexer;

/**
 * Le titre de la notice.
 */
class Title extends Text {
    public function getEditorForm($options = null)
    {
        return parent::getEditorForm($options)->addClass('large-text');
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        DatabaseIndexer::standardMapping('post_title', $mapping);
    }

    public function mapData(array & $document) {
        DatabaseIndexer::standardMap('post_title', $this->value(), $document);
    }
*/
}