<?php
/**
 * This file is part of the 'Docalist Biblio Export' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id: Xml.php 1919 2015-01-05 10:30:57Z daniel.menard.35@gmail.com $
 */
namespace Docalist\Biblio\Export;

use Docalist\Biblio\Reference\ReferenceIterator;
use XMLWriter;

/**
 * Un exporteur au format XML.
 */
class Xml extends Exporter {
    protected static $defaultSettings = [
        // Surcharge les paramètres hérités
        'mime-type' => 'application/xml',
        'extension' => '.xml',

        // Taille de l'indentation ou zéro ou false pour générer un code compact
        'indent' => 4,
        'binary' => true,
    ];

    public function export(ReferenceIterator $references) {
        $xml = new XMLWriter();
        $xml->openURI('php://output');

        if ($indent = $this->get('indent')) {
            $xml->setIndentString(str_repeat(' ', $indent));
            $xml->setIndent(true);
        }
        $xml->startDocument('1.0', 'utf-8', 'yes');
        //  $xml->writeComment('test');
            $xml->startElement('references');
            $xml->writeAttribute('count', $references->count());
            $xml->writeAttribute('datetime', date('Y-m-d H:i:s'));
            $xml->writeAttribute('query', $references->searchRequest()->asEquation());
            foreach($references as $reference) {
                $data = $this->converter->convert($reference);
                $xml->startElement('reference');
                    $this->outputArray($xml, $data);
                $xml->endElement();
            }
            $xml->endElement();
        $xml->endDocument();

        $xml->flush();
    }

    /**
     * Exporte le tableau passé en paramètre en xml.
     *
     * Le mappage est très simple :
     * - chaque élément du tableau devient un élément xml.
     * - si la clé est numérique, un noeud "item" est généré.
     * - sinon le nom du noeud correspond au nom de la clé.
     * - si l'élément du tableau est un scalaire, il est écrit tel quel
     * - si c'est un tableau, on récursive.
     *
     * @param XMLWriter $xml
     * @param array $data
     */
    protected function outputArray(XMLWriter $xml, array $data) {
        foreach ($data as $key => $value) {
            is_int($key) && $key = 'item';
            $xml->startElement($key);
            if (is_scalar($value)) {
                $xml->text($value);
            } else {
                $this->outputArray($xml, $value);
            }
            $xml->endElement();
        }
    }

    public function label() {
        return 'XML';
    }

    public function description() {
        return __('<a href="http://fr.wikipedia.org/wiki/Extensible_Markup_Language">Extensible Markup Language</a> : fichier texte dans lequel les données sont encadrées par des &lt;balises&gt;.', 'docalist-biblio');
    }
}