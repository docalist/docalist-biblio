# Abréviation des nombres ordinaux (premier, deuxième...) #

Sources d'informations :
-----------------------
- Ordinal Numbers in Various Languages : http://typophile.com/node/42577

- Abréviation des nombres ordinaux (premier, deuxième...) en français :
http://www.langue-fr.net/spip.php?article239

- Wikipedia :
http://fr.wikipedia.org/wiki/Adjectif_num%C3%A9ral#Abr.C3.A9viation_des_ordinaux.5B6.5D

En français :
-------------

    masculin singulier  masculin pluriel    féminin singulier   féminin pluriel
1   er  ers re  res
2   e   es  e   es
2   d   ds  de  des
3   e   es  e   es

la suite : comme 3

Utilisation en PHP :
--------------------

- aucun code "pur php" concluant sur internet
- meilleure approche -> ICU

Exemple :

    $formatter = new NumberFormatter('FR', NumberFormatter::ORDINAL);
    echo $formatter->format(1);
    // génère "1er". Remarque : "er" est un caractère unicode en exposant 
     
    $formatter = new NumberFormatter('EN', NumberFormatter::ORDINAL);
    echo $formatter->format(1);
    // 1st
    
 
On peut voir les règles utilisées en appellant getPattern() :

Par exemple, en français (locale = "FR"), on obtient :

    %%dord-mascabbrev:
        0: ᵉ;
        1: ᵉʳ;
        2: ᵉ;
    %digits-ordinal-masculine:
        0: =#,##0==%%dord-mascabbrev=;
        -x: −>%digits-ordinal-masculine>;
    %%dord-femabbrev:
        0: ᵉ;
        1: ʳᵉ;
        2: ᵉ;
    %digits-ordinal-feminine:
        0: =#,##0==%%dord-femabbrev=;
        -x: −>%digits-ordinal-feminine>;
    %digits-ordinal:
        0: =%digits-ordinal-masculine=;

En anglais : 

    %%digits-ordinal-indicator:
        0: ᵗʰ;
        1: ˢᵗ;
        2: ⁿᵈ;
        3: ʳᵈ;
        4: ᵗʰ;
        20: >%%digits-ordinal-indicator>;
        100: >%%digits-ordinal-indicator>;
    %digits-ordinal:
        0: =#,##0==%%digits-ordinal-indicator=;
        -x: −>%digits-ordinal>;

Les règles qui commencent par "%%" sont des règles internes.

Quand on indique NumberFormatter::ORDINAL, on dit en fait qu'on veut utiliser la règle "%digits-ordinal:"

On peut changer la règle par défaut utilisée en appellant : 

    $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%digits-ordinal-feminine");

Dans cas, en français, ça retournera "1ère" et non pas "1er".

On voit que les noms de règles ne sont pas normalisés. Par exemple, en anglais, la règle féminine n'existe pas.

On peut tester s'il y a eu une erreur en appellant :

    echo intl_get_error_message ();


Définir ses propres règles.
---------------------------

    $rules = '1:=#=ère; 2:=#=nde; 3:=#=ième;';
    $formatter = new NumberFormatter($locale, NumberFormatter::PATTERN_RULEBASED, $rules);

Génère :

    1ère
    2nde
    3ième
    4ième
    5ième

La doc sur la syntaxe des règles est ici :
http://icu-project.org/apiref/icu4j/com/ibm/icu/text/RuleBasedNumberFormat.html

