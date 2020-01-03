<?php

/*
 * This file is part of Oveleon Object Type Entity.
 *
 * (c) https://www.oveleon.de/
 */

// Back end modules
array_insert($GLOBALS['BE_MOD']['system']['objectTypes'], 1, array
(
    'importObjectTypes' => array('\\ContaoEstateManager\\ObjectTypeEntityOnOffice\\OnOfficeObjectType', 'setupImport')
));
