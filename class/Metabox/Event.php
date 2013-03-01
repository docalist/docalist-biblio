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

class Event extends Metabox {
    /**
     * @inheritdoc
     */
    public function __construct() {
        $box = new Fieldset();

        //@formatter:off
        $box->label(__('Congrès et diplômes', 'docalist-biblio'));

        $box->table('event')
            ->label(__('Informations sur l\'événement', 'docalist-biblio'))
            ->description(__('Congrès, colloques, manifestations, soutenances de thèse, etc.', 'docalist-biblio'))
                ->input('title')
                ->label(__('Titre', 'docalist-biblio'))
                ->addClass('span5')
            ->parent()
                ->input('date')
                ->label(__('Date', 'docalist-biblio'))
                ->addClass('span2')
            ->parent()
                ->input('place')
                ->label(__('Lieu', 'docalist-biblio'))
                ->addClass('span3')
            ->parent()
                ->input('number')
                ->label(__('N°', 'docalist-biblio'))
                ->addClass('span2');

        $box->table('degree')
            ->label(__('Diplôme', 'docalist-biblio'))
            ->description(__('Description des titres universitaires et professionnels.', 'docalist-biblio'))
                ->select('level')
                ->label(__('Niveau', 'docalist-biblio'))
                ->addClass('span3')
                ->options(array('licence','master','doctorat'))
            ->parent()
                ->input('title')
                ->label(__('Intitulé', 'docalist-biblio'))
                ->addClass('span9');
        //@formatter:on

        $this->form = $box;
    }
}