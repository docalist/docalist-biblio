<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio\Metabox;

use Docalist\AbstractMetabox, Docalist\Forms\Fragment;

class Type extends AbstractMetabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fragment();

        //@formatter:off
        $box->label(__('Nature du document', 'docalist-biblio'));

        $box->select('type')
            ->label(__('Type de document', 'docalist-biblio'))
            ->options($this->taxonomy('dclreftype'));

        $box->checklist('genre')
            ->label(__('Genre de document', 'docalist-biblio'))
            ->options($this->taxonomy('dclrefgenre'));

        $box->checklist('media')
            ->label(__('Support de document', 'docalist-biblio'))
            ->options($this->taxonomy('dclrefmedia'));
        //@formatter:on

        $this->form = $box;
    }
}