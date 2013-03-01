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

use Docalist\Metabox, Docalist\Forms\Fieldset;

class Type extends Metabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fieldset();

        //@formatter:off
        $box->label(__('Nature du document', 'docalist-biblio'));

        $box->select('type')
            ->label(__('Type de document', 'docalist-biblio'))
            ->options(array('article','livre','rapport'));

        $box->checklist('genre')
            ->label(__('Genre de document', 'docalist-biblio'))
            ->options(array('communication','decret','didacticiel','etat de l\'art'));

        $box->checklist('media')
            ->label(__('Support de document', 'docalist-biblio'))
            ->options(array('cd-rom','internet','papier','dvd','vhs'));
        //@formatter:on

        $this->form = $box;
    }
}