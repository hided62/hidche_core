<?php

# Czech language, encoding by ISO 8859-2 charset (Iso Latin-2)
# For convert to MS Windows use shell command:
#    iconv -f ISO_8859-2 -t CP1250 < adodb-cz.inc.php
# For convert to ASCII use shell command:
#    unaccent ISO_8859-2 < adodb-cz.inc.php
# v1.0, 19.06.2003 Kamil Jakubovic <jake@host.sk>

$ADODB_LANG_ARRAY = array (
            'LANG'                      => 'cz',
            DB_ERROR                    => 'nezn??chyba',
            DB_ERROR_ALREADY_EXISTS     => 'ji? existuje',
            DB_ERROR_CANNOT_CREATE      => 'nelze vytvo?it',
            DB_ERROR_CANNOT_DELETE      => 'nelze smazat',
            DB_ERROR_CANNOT_DROP        => 'nelze odstranit',
            DB_ERROR_CONSTRAINT         => 'poru?en?omezuj??podm?ky',
            DB_ERROR_DIVZERO            => 'd?len?nulou',
            DB_ERROR_INVALID            => 'neplatn?,
            DB_ERROR_INVALID_DATE       => 'neplatn?datum nebo ?as',
            DB_ERROR_INVALID_NUMBER     => 'neplatn???lo',
            DB_ERROR_MISMATCH           => 'nesouhlas?,
            DB_ERROR_NODBSELECTED       => '??n?datab?e nen?vybr?a',
            DB_ERROR_NOSUCHFIELD        => 'pole nenalezeno',
            DB_ERROR_NOSUCHTABLE        => 'tabulka nenalezena',
            DB_ERROR_NOT_CAPABLE        => 'nepodporov?o',
            DB_ERROR_NOT_FOUND          => 'nenalezeno',
            DB_ERROR_NOT_LOCKED         => 'nezam?eno',
            DB_ERROR_SYNTAX             => 'syntaktick?chyba',
            DB_ERROR_UNSUPPORTED        => 'nepodporov?o',
            DB_ERROR_VALUE_COUNT_ON_ROW => '',
            DB_ERROR_INVALID_DSN        => 'neplatn?DSN',
            DB_ERROR_CONNECT_FAILED     => 'p?ipojen?selhalo',
            0	                        => 'bez chyb', // DB_OK
            DB_ERROR_NEED_MORE_DATA     => 'm?o zdrojov?h dat',
            DB_ERROR_EXTENSION_NOT_FOUND=> 'roz??en?nenalezeno',
            DB_ERROR_NOSUCHDB           => 'datab?e neexistuje',
            DB_ERROR_ACCESS_VIOLATION   => 'nedostate?n?pr?a'
);
?>
