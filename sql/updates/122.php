<?php

AddColumn('needsrevision', 'stockdescriptiontranslations', 'TINYINT(1)', 'NOT NULL', '0', 'descriptiontranslation');
AddColumn('needsrevision', 'stocklongdescriptiontranslations', 'TINYINT(1)', 'NOT NULL', '0', 'longdescriptiontranslation');

NewScript('AutomaticTranslationDescriptions.php', '15');
NewMenuItem('Utilities', 'Transactions', _('Automatic Translation - Item descriptions'), '/AutomaticTranslationDescriptions.php', 6);

NewScript('RevisionTranslations.php', '15');
NewMenuItem('stock', 'Maintenance', _('Translated Descriptions Revision'), '/RevisionTranslations.php', 2);

UpdateDBNo(basename(__FILE__, '.php'));

?>