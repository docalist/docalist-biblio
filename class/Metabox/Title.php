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

use Docalist\Metabox, Docalist\Forms\Fragment;

class Title extends Metabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fragment();

        //@formatter:off
        $box->label(__('Titre du document', 'docalist-biblio'));

        $box->input('title')
            ->addClass('large-text')
            ->attribute('id', 'DocTitle')
            ->label('Titre principal');

        $box->table('othertitle')
            ->label('Autres titres')
            ->repeatable(true)
                ->select('type')
                ->label('Type de titre')
                ->options(array('serie','dossier','special'))
                ->addClass('span4')
            ->parent()
                ->input('title')
                ->label('Autre titre')
                ->description('En minuscules, svp')
                ->addClass('span8');

        $box->table('translation')
            ->label('Traduction du titre')
            ->repeatable(true)
                ->select('language')
                ->label('Langue')
                ->options(array('fre','eng','ita','spa','deu'))
                ->addClass('span4')
            ->parent()
                ->input('title')
                ->label('Titre traduit')
                ->addClass('span8');
        //@formatter:on

        $this->form = $box;
    }
}