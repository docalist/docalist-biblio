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

class Biblio extends AbstractMetabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fragment();

        //@formatter:off
        $box->label(__('Informations bibliographiques', 'docalist-biblio'));

        $box->input('date')
            ->label(__('Date de publication', 'docalist-biblio'))
            ->addClass('span6')
            ->description(__('Date d\'édition ou de diffusion du document.', 'docalist-biblio'));

        $box->select('language')
            ->label(__('Langue du document', 'docalist-biblio'))
            ->repeatable(true)
            ->description(__('Langue(s) dans laquelle est écrit le document.', 'docalist-biblio'))
            ->addClass('span6')
            ->options($this->taxonomy('dcllanguage'));

        $box->input('pagination')
            ->label(__('Pagination', 'docalist-biblio'))
            ->addClass('span6')
            ->description(__('Pages de début et de fin (ex. 15-20) ou nombre de pages (ex. 10p.) du document.', 'docalist-biblio'));

        $box->input('format')
            ->label(__('Format du document', 'docalist-biblio'))
            ->addClass('span12')
            ->description(__('Caractéristiques matérielles du document : étiquettes de collation (tabl, ann, fig...), références bibliographiques, etc.', 'docalist-biblio'));
        //@formatter:on

        $this->form = $box;
    }
}