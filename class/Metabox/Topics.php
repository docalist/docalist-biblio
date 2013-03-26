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

class Topics extends Metabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fragment();

        //@formatter:off
        $box->label(__('Indexation et résumé', 'docalist-biblio'));

        $box->table('topic')
            ->label(__('Mots-clés', 'docalist-biblio'))
            ->description(__('Indexation du document : mots-clés matières, mots outils, noms propres, description géographique, période historique, candidats descripteurs, etc.', 'docalist-biblio'), false)
            ->repeatable(true)
                ->select('type')
                ->label(__('Thesaurus', 'docalist-biblio'))
                ->addClass('span2')
                ->options(array('prisme', 'names', 'geo', 'free'))
            ->parent()
                ->Div()
                ->attribute('style', 'border: 1px solid red')
                ->label(__('Termes', 'docalist-biblio'))
                ->addClass('span12')
                    ->input('terms')
                    ->addClass('span2')
                    ->repeatable(true);

        $box->table('abstract')
            ->label(__('Résumé', 'docalist-biblio'))
            ->description(__('Résumé du document et langue du résumé.', 'docalist-biblio'))
            ->repeatable(true)
                ->select('language')
                ->label(__('Langue du résumé', 'docalist-biblio'))
                ->addClass('span2')
                ->options($this->taxonomy('dcllanguage'))
            ->parent()
                ->textarea('content')
                ->label(__('Résumé', 'docalist-biblio'))
                ->addClass('span10');

        $box->table('note')
            ->label(__('Notes', 'docalist-biblio'))
            ->description(__('Remarques, notes et informations complémentaires sur le document.', 'docalist-biblio'))
            ->repeatable(true)
                ->select('type')
                ->label(__('Type de note', 'docalist-biblio'))
                ->addClass('span2')
                ->options(array('note visible','note interne','avertissement','objectifs pédagogiques','publics concernés','pré-requis', 'modalités d\'accès', 'copyright'))
            ->parent()
                ->textarea('content')
                ->label(__('Contenu de la note', 'docalist-biblio'))
                ->addClass('span10');

        //@formatter:on

        $this->form = $box;
    }
}