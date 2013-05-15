<?php
/**
* @package     Prisme
* @subpackage  modules
* @author      Daniel Ménard <daniel.menard.35@gmail.com>
* @version     SVN: $Id$
*/
namespace Docalist\Biblio\Import;

use Iterator;
use stdClass;

/**
 * Classe permettant de lire les fichiers Prisme au format texte tabulé.
 *
 * Cette classe implémente l'interface Iterator. L'utilisation classique est une simple boucle :
 * <code>
 * foreach(new PrismeReader($fileToLoad) as $index => $record)
 * {
 *     // chargement du index-ième enregistrement
 *     // $record est un tableau de la forme nom de champ => contenu du champ
 * }
 *
 * </code>
 *
 * @package     Prisme
 * @subpackage  modules
 * @author      Daniel Ménard <daniel.menard.35@gmail.com>
 */
class Prisme implements Iterator
{
    /**
     * Délimiteur utilisé pour marquer les fins de ligne (les fins d'enregistrement).
     *
     * @var string
     */
    const RECORD_DELIMITER = '+++';

    /**
     * Délimiteur utilisé entre les champs.
     *
     * @var string
     */
    const FIELD_DELIMITER = ';;';

    /**
     * Longueur maximale d'un enregistrement.
     *
     * @var int
     */
    const MAX_LINE_LENGTH = 65536;

    /**
     * Path du fichier en cours.
     *
     * @var string
     */
    private $path;

    /**
     * Handle du fichier en cours.
     *
     * @var resource
     */
    protected $file;

    /**
     * Noms des champs.
     *
     * @var array
     */
    protected $headers;

    /**
     * Données de l'enregistrement en cours.
     *
     * @var array
     */
    protected $data;

    /**
     * Numéro de l'enregistrement en cours.
     *
     * @var int
     */
    protected $recordNumber;

    /**
     * Flag indiquant si la fin de fichier a été atteinte
     *
     * @var bool
     */
    protected $eof;

    /**
     * Indique s'il faut convertir les données de l'ancien format
     * (éclatement des champs Url, So, etc.)
     *
     * @var bool
     */
    protected $conversion = true;

    protected $fieldIndex;

    /**
     *
     * @var array
     */
    protected $handles = array();

    protected $repeatable = array(
        'AU' => true,
        'AUCO' => true,
        'AS' => true,
        'GO' => true,
        'HI' => true,
        'DENP' => true,
        'DE' => true,
        'CD' => true,
    );

    protected $rename = array(
//      'REF' => 'REF',
//      'OP' => 'OP',
//      'DS' => 'DS',
        'TY-TYP' => 'TY',
//      'GEN' => 'GEN',
        'TY-SUP' => 'SUP',
//      'URL' => 'URL',
        'URL-DATE' => 'DC',
//      'AU' => 'AU',
//      'AUCO' => 'AUCO',
//      'AS' => 'AS',
//      'TI' => 'TI',
//      'TN' => 'TN',
//      'COL' => 'COL',
        'TP-TIT' => 'TP',
        'TP-SSTIT' => 'STP',
        'VOLUME' => 'VOL',
        'NUMERO' => 'NUM',
        'AUTRES' => 'DATE',
        'ED-LIEU' => 'VILLE',
        'EDIT' => 'ED',
        'ED-COL' => 'COLL',
        'ED-RED' => 'REED',
//      'ISBN' => 'ISBN',
//      'DP' => 'DP',
//      'ND' => 'ND',
//      'DATRI' => 'DATRI',
        'PAGES' => 'PAG',
        'RESTE' => 'NO',
//      'GO' => 'GO',
//      'HI' => 'HI',
//      'DENP' => 'DENP',
//      'DE' => 'DE',
//      'CD' => 'CD',
//      '' => 'LA',
//      '' => 'DOM',
//      'RESU' => 'RESU',

    );

    protected $cache;
    /**
     *
     * @var int numéro de la ref en cours de conversion, pour les logs
     */
    protected $ref;

    // workaround pour le bug #63240 de php sur stream_get_line
    protected function stream_get_line() {
        while (false === $pt = strpos($this->cache, self::RECORD_DELIMITER)) {
            if (feof($this->file)) {
                $result = $this->cache;
                $this->cache = '';

                return $result;
            }

            $this->cache .= fread($this->file, self::MAX_LINE_LENGTH);
        }

        $result = substr($this->cache, 0, $pt);
        $this->cache = substr($this->cache, $pt + strlen(self::RECORD_DELIMITER));

        return $result;
    }

    /**
     * Constructeur. Ouvre le fichier passé en paramètre.
     *
     * @param string $path
     */
    public function __construct($path, $conversion = true, $fieldIndex = false)
    {
        $this->conversion = $conversion;
        $this->path = $path;
        $this->file = fopen($path, 'rb');
        $this->fieldIndex = $fieldIndex;
    }


    /**
     * Destructeur. Ferme le fichier en cours.
     */
    public function __destruct()
    {
        fclose($this->file);
        foreach($this->handles as $file) {
            fclose($file);
        }
    }


    /**
     * Lit une ligne du fichier et retourne un tableau contenant les données.
     *
     * @return array|false retourne un tableau contenant les données ou false si la fin
     * du fichier est atteinte (dans ce cas, la propriété $eof est mise à true).
     */
    protected function read()
    {
        if (feof($this->file))
        {
            $this->eof = true;
            return false;
        }
//        $data = stream_get_line($this->file, self::MAX_LINE_LENGTH, self::RECORD_DELIMITER);
        $data = $this->stream_get_line();
        $data = utf8_encode($data);
        $data = explode(self::FIELD_DELIMITER, $data);
        return $data;
    }


    /**
     * Interface Iterator. Initialise une boucle foreach.
     *
     * Charge la ligne d'entête du fichier, initialise le numéro de l'enregistrement en cours et
     * le flag de fin de fichier puis charge le premier enregistrement.
     */
    function rewind()
    {
        fseek($this->file, 0);
        $this->headers = $this->read();
        $this->recordNumber = 0;
        $this->eof = false;
        $this->next();
    }


    /**
     * Interface Iterator. Charge le prochain enregistrement du fichier.
     *
     */
    function next()
    {
        $this->data = $this->read();
        if ($this->data === false) return;

        // Détection d'erreurs
        if (count($this->data) !== count($this->headers)) {
            echo "<p>Notice erronnée dans le fichier CSV : le nombre de champs dans la notice ne correspond pas au nombre de champs dans la ligne d'entête</p>";
            echo '<table border="1"><tr><th>Entête</th><th>Notice</th></tr>';
            $max = max(count($this->data), count($this->headers));
            for($i=0; $i < $max; $i++) {
                echo '<tr>';
                echo '<th>', isset($this->headers[$i]) ? $this->headers[$i] : '-', '</th>';
                echo '<td>', isset($this->data[$i])    ? $this->data[$i]    : '-', '</td>';
                echo '</tr>';
            }
            echo '</table>';
            return $this->next();
        }

        $this->data = array_combine($this->headers, $this->data);
        ++$this->recordNumber;

        // Supprime les champs vides
        $this->data = array_map('trim', $this->data); // trim all
        $this->data = array_filter($this->data, 'strlen'); // ne garde que ceux dont len !== 0

        if ($this->conversion) {
            $record = new stdClass();
            $imported = '';
            foreach($this->data as $name => $value)
            {
                $value = trim($value);
                if (empty($value))
                {
                    continue;
                }

                if (isset($this->rename[$name])) {
                    $name = $this->rename[$name];
                }

                $imported .= "$name:$value\n";

                if (isset($this->repeatable[$name]))
                {
                    $value = array_map('trim', explode(',', $value));
                }

                $record->$name = $value;
/*
                if ($this->fieldIndex) {
                    if (! isset($this->handles[$name])) {
                        $path = $this->path . "-$name.txt";
                        $this->handles[$name] = fopen($path, 'w');
                    }
                    $ref = $record->REF;
                    foreach((array) $value as $item) {
                        fwrite($this->handles[$name], "$ref\t$item\r\n");
                    }
                }
*/
            }
            $this->data = $this->convert($record);

            $this->data->imported = $imported;
        }
    }

    protected function log(array $old, array $new) {
        $filename = key($old);
        if (! isset($this->handles[$filename])) {
            $path = $this->path . "-$filename.txt";
            $this->handles[$filename] = fopen($path, 'w');
            $header = 'REF';
            foreach($old as $key => $value) {
                $header .= "\t$key";
            }
            foreach($new as $key => $value) {
                $header .= "\t$key";
            }
            $header .= "\n";
            fwrite($this->handles[$filename], $header);
        }

        $data = $this->ref;
        foreach($old as $key => $value) {
            is_array($value) && $value = implode('¤', $value);
            $data .= "\t$value";
        }
        foreach($new as $key => $value) {
            is_array($value) && $value = implode('¤', $value);
            $data .= "\t$value";
        }
        $data .= "\n";
        fwrite($this->handles[$filename], $data);
    }

    /**
     * Interface Iterator. Indique si la fin de fichier a été atteinte.
     *
     * @return bool
     */
    function valid()
    {
        return ! $this->eof;
    }


    /**
     * Interface Iterator. Retourne le numéro d'enregistrement en cours.
     *
     * @return int
     */
    function key()
    {
        return $this->recordNumber;
    }


    /**
     * Interface Iterator. Retourne les données de l'enregistrement en cours.
     *
     *  @return array
     */
    function current()
    {
        return $this->data;
    }

    protected $errors;

    protected function error($field, $value = null, $message = null) {
        $error = array('code' => $field . 'Err');
        if (! is_null($value)) $error['value'] = $value;
        if (! is_null($message)) $error['message'] = $message;

        $this->errors[] = $error;
    }

    protected function missing($field) {
        $error = array('code' => $field . 'Missing');

        $this->errors[] = $error;
    }

    protected function authors(stdClass $data) {
        $reEtAl='~\s*et\s*al[\s\.]*~i';
        $etal = false;
        foreach(array('AU' => '', 'AS' => 'interviewer') as $field => $defaultRole) {
            if (isset($data->$field)) {
                foreach($data->$field as $au) {
                    if (trim($au) === '') continue;
                    $error = '';
                    if (preg_match($reEtAl, $au)) {
                        $etal = $au;
                    }
                    else {
                        if (preg_match('~^([A-Z\' -]+)\s*\(([^\)]+)\)*(.*)$~', $au, $match)) {
                            $aut = array(
                                'name' => $match[1],
                                'firstname' => trim($match[2]),
                            );
                            $match[3] = trim($match[3]);
                            if (!$match[3] && $defaultRole) $match[3] = $defaultRole;
                            $match[3] && $aut['role'] = $match[3];

                            $doc->author[] = $aut;
                        } else {
                            $error = 'Auteur physique syntaxe invalide';
                            $aut = array('name' => $au);
                            $doc->author[] = $aut;
                            $this->error($field, $au, $error);
                        }
                        $this->log(array($field => $au), $aut + array('firstname'=>'', 'role' => '', 'error' => $error));
                    }
                }
            }
        }

        if ($etal) {
            $aut = array('name' => 'et al.');
            $doc->author[] = $aut;
            $this->log(array('AU' => $etal), $aut + array('firstname'=>'', 'role' => '', 'error' => $error));
        }

        unset($data->AU, $data->AS);
    }

    public function convert(stdClass $data)
    {
        // Réinitialise a liste des erreurs rencontrées
        $this->errors = array();

        // Commence à créer l'enreg
        $doc = new StdClass;

        // REF - Numéro de référence
        if (isset($data->REF)) {
            $doc->ref = (int) $data->REF;
            if ($doc->ref != $data->REF) {
                $this->error('REF', $data->REF, 'entier attendu');
            }
            unset($data->REF);
            $this->ref = $doc->ref;
        } else {
            $this->missing('REF');
            $this->ref = 'n/a';
        }

        // OP - Organisme Producteur et DS - Date de Saisie
        $op = '';
        if (isset($data->OP)) {
            $op = $data->OP;
        } else {
            $this->missing('OP');
        }

        $ds = '';
        if (isset($data->DS)) {
            $ds = $data->DS;
            if (preg_match('~^(\d{4})[\.-](\d{2})[\.-](\d{2})$~', $ds, $match)) {
                $ds = $match[1] . $match[2] . $match[3];
            } else {
                $this->error('DS', $ds, 'Date invalide');
                $ds = '';
            }
        } else {
            $this->missing('DS');
        }

        ($ds || $op) && $doc->creation = array('date' => $ds, 'by' => $op);
        $op && $doc->owner = array($op);
        unset($data->OP, $data->DS);

        // TY - Type de document
        if (isset($data->TY)) {
            $doc->type = $data->TY;
            unset($data->TY);
        } else {
            $this->missing('TY');
        }

        // GEN - Genre du document
        if (isset($data->GEN)) {
            $doc->genre[] = $data->GEN;
            unset($data->GEN);
        } else {
//            $this->missing('GEN');
        }

        // SUP - Support du document
        if (isset($data->SUP)) {
            $doc->media[] = $data->SUP;
            unset($data->SUP);
        } else {
//            $this->missing('SUP');
        }

        // URL, DC
        $url = '';
        if (isset($data->URL)) {
            $url = $data->URL;
            unset($data->URL);

            if (false !== $pt = strpos($url, ' ')) {
                $url = substr($url, $pt - 1);
                $url = trim($url, ',');
            }
            $doc->link[] = array('type' => 'url', 'url' => $url);
        }
        // TODO: DC (absent du fichier actuel)

        // AU - Auteurs et AS - Auteurs secondaires
        $this->authors($data);

        // AUCO - Collectivités Auteurs
        if (isset($data->AUCO)) {
            foreach($data->AUCO as $aut) {
                // Ressemble à un auteur physique ?
                if (preg_match('~^([A-Z -]+)\s*\(([A-Z][^\)]+)\)$~', $aut, $match)) {
                    $aut = array(
                        'name' => $match[1],
                        'firstname' => trim($match[2]),
                        'role' => 'dir.',
                    );

                    $doc->author[] = $aut;

                } else {
                    $doc->organisation[] = array('name' => $data->AUCO);
                }
            }
            unset($data->AUCO);
        }

        // TI - Titre
        if (isset($data->TI)) {
            $doc->title = $data->TI;
            unset($data->TI);
        } else {
            $this->missing('TI');
        }

        // TN - Titre du Numéro (= titre du dossier)
        if (isset($data->TN)) {
            $doc->othertitle[] = array('type' => 'dossier', 'title' => $data->TN);
            unset($data->TN);
        }

        // COL - Titre du colloque, congrès, conférence
        if (isset($data->COL)) {
            $doc->event[] = array('title' => $data->COL); // todo date, place, number
            // event multivalué. Utile ?
            unset($data->COL);
        }

        // TP - Titre du Périodique
        if (isset($data->TP)) {
            $doc->journal = $data->TP;
            unset($data->TP);
        }

        // TODO : STP

        // VOL - Volume de parution
        if (isset($data->VOL)) {
            $doc->volume = $data->VOL;
            unset($data->VOL);
        }

        // NUM - Numéro de fascicule
        if (isset($data->NUM)) {
            $doc->issue = $data->NUM;
            unset($data->NUM);
        }

        // DATE - "périodicité"
        if (isset($data->DATE)) {
            $doc->date = $data->DATE;
            // strptime ?
            unset($data->DATE);
        }

        // ED, VILLE - Editeur et lieu d'édition
        $ed = '';
        if (isset($data->ED)) {
            $ed = $data->ED;
            unset($data->ED);
        }
        $ville = '';
        if (isset($data->VILLE)) {
            $ville = $data->VILLE;
            unset($data->VILLE);
        }
        if ($ed || $ville) {
            $doc->editor[] = array('name' => $ed, 'city' => $ville);
        }

        // COLL - Collection (de l'éditeur)
/*
        n.7
        n.13822
        n° 69
        79
        coll." recherches n."90
        coll.  topo-guide 2000
        coll. les documents d'information de l'assemblée nationale n.2521 28 juin 2000
        coll. n.2542/dian 52/2000
        coll. théories et pratiques dans l'enseignement n.IX-699
        coll. les actes;9
        coll. 10/18;n.134
        coll. enjeux;n.12
        coll. se former : s22
        coll. se former+ : s30
        oll. le livre de poche/references inedit n.lp 12/514
        coll. écoles n° IX-68
*/
        if (isset($data->COLL)) {
            $doc->collection[] = array('name' => $data->COLL);
            unset($data->COLL);
        }

        // Mentions d'édition
        if (isset($data->REED)) {
            $doc->edition[] = array('type' => $data->REED);
            unset($data->REED);
        }

        // ISBN
        if (isset($data->ISBN)) {
            $isbn = $data->ISBN;
            $isbn = strtr($data->ISBN, array('-'=>'', ' '=>''));
            $doc->isbn[] = $isbn;
            unset($data->ISBN);

            if (!preg_match('~\d{9}[\dxX]~', $isbn) && ! preg_match('~\d{13}~', $isbn)) {
                $this->error('ISBN', $isbn, 'Isbn Incorrect');
            }
        }

        // DP - Date de publication
        // TODO: année uniquement. Peut être multivalué ?

        // DATRI

        // ND - Nom du diplôme
        // Forme : Mémoire DEES : Paris : IRTS : 2003
        if (isset($data->ND)) {
            $doc->degree[] = array('title' => $data->ND);
            unset($data->ND);
        }

        // PAG - Pagination
        if (isset($data->PAG)) {
            // pagination de type analytique : "p. x", "p. x-y", "pp. x-y", etc.
            if (preg_match('~^p+[.\s]*(\d+(?:\s*-\s*\d+)?)$~', $data->PAG, $match)) {
                $doc->pagination = $match[1];
            }

            // pagination de type monographique (x p.)
            elseif (preg_match('~^(\d+)\s*p+[.\s]*$~', $data->PAG, $match)) {
                $doc->pagination = $match[1] . 'p';
            }

            // pagination non reconnue / erreur
            else {
                $this->error('PAG');
                $doc->pagination = $data->PAG;
            }
            unset($data->PAG);
        } else {
            $this->missing('PAG');
        }

        // NO - Etiquettes de collation (autres caractéristiques matérielles)
        if (isset($data->NO)) {
            $doc->format = $data->NO;
            unset($data->NO);
        }

        // GO - Descripteurs Géographiques
        if (isset($data->GO)) {
            $doc->topic[] = array('type' => 'geo', 'term' => $data->GO);
            unset($data->GO);
        }

        // HI - Descripteurs Période Historique
        if (isset($data->HI)) {
            $doc->topic[] = array('type' => 'period', 'term' => $data->HI);
            unset($data->HI);
        }

        // DENP - Descripteurs Noms Propres
        if (isset($data->DENP)) {
            $doc->topic[] = array('type' => 'names', 'term' => $data->DENP);
            unset($data->DENP);
        }

        // DE - Descripteurs
        if (isset($data->DE)) {
            $doc->topic[] = array('type' => 'prisme', 'term' => $data->DE);
            unset($data->DE);
        } else {
            $this->missing('DE');
        }

        // CD - Candidats Descripteurs
        if (isset($data->CD)) {
            $doc->topic[] = array('type' => 'free', 'term' => $data->CD);
            unset($data->CD);
        }

        // LA - Langue
        if (isset($data->LA)) {
            $doc->language[] = $la;
            unset($data->RESU);
        } else {
            $doc->language[] = 'fre';
        }

        // RESU - Résumé
        if (isset($data->RESU)) {
            $doc->abstract[] = array('language' => 'fre', 'content' => $data->RESU);
            unset($data->RESU);
        }

        // Terminé
        if ($this->errors) {
            $doc->errors = $this->errors;
        }

        unset($data->SO, $data->DATRI, $data->NO);

        $data=(array)$data;
        if ($data) $doc->todo = array_keys($data);

        return $doc;
    }
}