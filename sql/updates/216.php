<?php
CreateTable('care_group', "CREATE TABLE `care_group` (
  `nr` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `name` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `LD_var` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `description` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `status` varchar(25) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `modify_id` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `modify_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_id` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`nr`)
)");

InsertRecord('care_group', array('nr'), array(1), array('nr', 'id', 'name', 'LD_var', 'description', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(1, 'pregnancy', _('Pregnancy'), 'LDPregnancy', '', '', '', '0000-00-00 00:00:00', '', date('Y-m-d H:i:s')));
InsertRecord('care_group', array('nr'), array(2), array('nr', 'id', 'name', 'LD_var', 'description', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(2, 'neonatal', _('Neonatal'), 'LDNeonatal', '', '', '', '0000-00-00 00:00:00', '', date('Y-m-d H:i:s')));
InsertRecord('care_group', array('nr'), array(3), array('nr', 'id', 'name', 'LD_var', 'description', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(3, 'encounter', _('Encounter'), 'LDEncounter', '', '', '', '0000-00-00 00:00:00', '', date('Y-m-d H:i:s')));
InsertRecord('care_group', array('nr'), array(4), array('nr', 'id', 'name', 'LD_var', 'description', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(4, 'op', _('OP'), 'LDOP', '', '', '', '0000-00-00 00:00:00', '', date('Y-m-d H:i:s')));
InsertRecord('care_group', array('nr'), array(5), array('nr', 'id', 'name', 'LD_var', 'description', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(5, 'anesthesia', _('Anesthesia'), 'LDAnesthesia', '', '', '', '0000-00-00 00:00:00', '', date('Y-m-d H:i:s')));
InsertRecord('care_group', array('nr'), array(6), array('nr', 'id', 'name', 'LD_var', 'description', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(6, 'prescription', _('Prescription'), 'LDPrescription', '', '', '', '0000-00-00 00:00:00', '', date('Y-m-d H:i:s')));

CreateTable('care_yellow_paper', "CREATE TABLE `care_yellow_paper` (
  `encounter_nr` bigint(20) NOT NULL,
  `staff_name` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `location_id` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `history` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `create_id` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sunto_anamnestico` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `stato_presente` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `altezza` double(15,3) DEFAULT NULL,
  `peso` double(15,3) DEFAULT NULL,
  `norm` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `dati_urine` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `dati_sangue` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `dati_altro` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `diagnosi` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `terapia` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `malattie_ereditarie` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `padre` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `madre` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `fratelli` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `coniuge` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `figli` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `paesi_esteri` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `abitazione` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `lavoro_pregresso` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `lavoro_presente` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `lavoro_attuale` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `ambiente_lavoro` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `gas_lavoro` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `tossiche_lavoro` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `conviventi` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `prematuro` varchar(4) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `eutocico` varchar(4) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `fisiologici_normali` varchar(4) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `fisiologici_tardivi` varchar(4) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `mestruazione` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `gravidanze` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `militare` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `alcolici` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `caffe` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `fumo` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `droghe` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `sete` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `alvo` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `diuresi` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `anamnesi_remota` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `anamnesi_prossima` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `nr` bigint(20) NOT NULL AUTO_INCREMENT,
  `modify_id` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `modify_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`nr`),
  UNIQUE KEY `nr` (`nr`)
)");

CreateTable('care_target_test', "CREATE TABLE `care_target_test` (
  `nr` bigint(20) NOT NULL AUTO_INCREMENT,
  `encounter_nr` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `staff_nr` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `staff_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `location_id` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `history` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `modify_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `modify_time` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `create_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_costituzionale` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `condizioni_generali` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `stato_nutrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `decubito` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `psiche` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `cute` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `descrizione_mucose` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `annessi_cutanei` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `edemi` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `sottocutaneo_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `temperatura` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `polso_battiti` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `polso` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `pressione_max` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `pressione_min` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `linfoghiandolare_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `capo_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `globi_oculari` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `sclere_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `pupille` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `riflesso_corneale` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `orecchie` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `naso` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `cavo_orofaringeo` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `lingua` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `dentatura` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `tonsille` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `collo_forma` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `mobilita` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `atteggiamento` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `giugulari_turgide` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `tiroide_normale` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `collo_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `mammelle` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `torace_forma` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_torace` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `ispezione_respiratoria` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `palpazione_respiratoria` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `percussione_respiratoria` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `ascoltazione_respiratoria` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_respiratoria` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `fegato_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `epatomegalia` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `murphy` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `colecisti_palpabile` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_fegato` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `milza_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_milza` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `urogenitale_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `esplorazione_vaginale` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_genitale` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `osteoarticolare_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `muscolare_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_muscolare` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `nervoso_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `nervi_cranici` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `riflessi_superficiali` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_nervoso` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `ispezione_cuore` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `palpazione_cuore` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `percussione_cuore` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `ascoltazione_cuore` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_cuore` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `vasi_periferici_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `arterie` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `vene` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_vasi` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `addome_descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `addome_ispezione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `addome_palpazione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `addome_percussione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `addome_ascoltazione` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `rettale` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `reperti_addome` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`nr`),
  UNIQUE KEY `nr` (`nr`)
)");

NewModule('hospsetup', 'hspsu', _('Hospital Setup'), 15);

RemoveMenuItem('system', 'Transactions', 'Hospital Configuration Options', '/KCMCHospitalConfiguration.php');
NewMenuItem('hospsetup', 'Transactions', _('Hospital Configuration Options'), '/KCMCHospitalConfiguration.php', 1);

NewScript('KCMCSelectPatient.php', 13);
NewMenuItem('hospital', 'Reports', _('Select Patient'), '/KCMCSelectPatient.php', 2);

CreateTable('care_billable_items', "CREATE TABLE `care_billable_items` (
  `nr` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `price_list` varchar(4) NOT NULL DEFAULT '',
  `is_billed` tinyint(1) NOT NULL DEFAULT 0,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `modify_id` varchar(35) NOT NULL DEFAULT '',
  `modify_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_id` varchar(35) NOT NULL DEFAULT '',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`nr`)
)");

CreateTable('care_type_discharge', "CREATE TABLE `care_type_discharge` (
  `nr` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `LD_var` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `status` varchar(25) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `modify_id` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `modify_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_id` varchar(35) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`nr`)
)");

InsertRecord('care_type_discharge', array('nr'), array(1), array('nr', 'type', 'name', 'LD_var', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(1, 'regular', _('Regular discharge'), 'LDRegularRelease', '', '', '2003-04-15 00:05:55', '', '2003-04-13 11:12:26'));
InsertRecord('care_type_discharge', array('nr'), array(2), array('nr', 'type', 'name', 'LD_var', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(2, 'own', _('Patient left hospital on his own will'), 'LDSelfRelease', '', '', '2003-04-15 00:06:06', '', '2003-04-13 11:13:17'));
InsertRecord('care_type_discharge', array('nr'), array(3), array('nr', 'type', 'name', 'LD_var', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(3, 'emergency', _('Emergency discharge'), 'LDEmRelease', '', '', '2003-04-15 00:06:17', '', '2003-04-13 11:14:52'));
InsertRecord('care_type_discharge', array('nr'), array(4), array('nr', 'type', 'name', 'LD_var', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(4, 'change_ward', _('Change of ward'), 'LDChangeWard', '', '', '0000-00-00 00:00:00', '', '2003-04-13 11:15:26'));
InsertRecord('care_type_discharge', array('nr'), array(5), array('nr', 'type', 'name', 'LD_var', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(5, 'change_room', _('Change of room'), 'LDChangeRoom', '', '', '2003-04-15 00:06:59', '', '0000-00-00 00:00:00'));
InsertRecord('care_type_discharge', array('nr'), array(6), array('nr', 'type', 'name', 'LD_var', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(6, 'change_bed', _('Change of bed'), 'LDChangeBed', '', '', '2003-04-14 23:09:42', '', '2003-04-13 11:16:19'));
InsertRecord('care_type_discharge', array('nr'), array(7), array('nr', 'type', 'name', 'LD_var', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(7, 'death', _('Death of patient'), 'LDPatientDied', '', '', '2003-04-15 00:06:42', '', '0000-00-00 00:00:00'));
InsertRecord('care_type_discharge', array('nr'), array(8), array('nr', 'type', 'name', 'LD_var', 'status', 'modify_id', 'modify_time', 'create_id', 'create_time'), array(8, 'change_dept', _('Change of department'), 'LDChangeDept', '', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00'));

NewScript('KCMCYellowPaper.php', 13);

NewScript('KCMCDailyWardNotes.php', 13);

NewScript('KCMCTransferBeds.php', 13);

NewScript('KCMCDischargePatient.php', 13);

NewScript('KCMCDischargeTypes.php', 13);
NewMenuItem('hospsetup', 'Maintenance', _('Maintain Discharge Types'), '/KCMCDischargeTypes.php', 1);

UpdateDBNo(basename(__FILE__, '.php'));

?>