<?php

/* mysql specific functions for the database upgrade script
 */

function CharacterSet($table, $db) {
	$sql="SELECT TABLE_COLLATION
		FROM information_schema.tables
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$table."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	return $myrow['TABLE_COLLATION'];
}

function NewScript($ScriptName, $PageSecurity, $db) {
	/*Is page already in table */
	$sql = "SELECT script FROM scripts WHERE script='" . $ScriptName . "'";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)==0) {
		$sql = "INSERT INTO `scripts` VALUES('" . $ScriptName . "', '" . $PageSecurity . "', '')";
		$response = executeSQL($sql, $db, False);
		if ($response==0) {
			OutputResult( _('The script').' '.$ScriptName.' '._('has been inserted') , 'success');
		} else {
			OutputResult( _('The script').' '.$ScriptName.' '._('could not be inserted') . '<br />' . $sql, 'error');
		}
	} else {
		OutputResult( _('The script').' '.$ScriptName.' '._('already exists') , 'info');
	}
}

function RemoveScript($ScriptName, $db) {
	/*Is page already in table */
	$sql = "SELECT script FROM scripts WHERE script='" . $ScriptName . "'";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)!=0) {
		$sql = "DELETE FROM `scripts` WHERE script='" . $ScriptName . "'";
		$response = executeSQL($sql, $db, False);
		if ($response==0) {
			OutputResult( _('The script').' '.$ScriptName.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The script').' '.$ScriptName.' '._('could not be removed') , 'error');
		}
	} else {
		OutputResult( _('The script').' '.$ScriptName.' '._('does not exist') , 'info');
	}
}

function AddColumn($Column, $Table, $Type, $Null, $Default, $After, $db) {
	global $SQLFile;
	if (DB_table_exists($Table, $db)) {
		$sql="desc ".$Table." ".$Column;
		$result = DB_query($sql, $db);
		if (isset($SQLFile) or DB_num_rows($result)==0) {
			if ($Type=='text') {
				$response=executeSQL("ALTER TABLE `".$Table."` ADD COLUMN `".$Column."` ".$Type." ".$Null.
					" AFTER `".$After."`", $db, False);
			} else {
				$response=executeSQL("ALTER TABLE `".$Table."` ADD COLUMN `".$Column."` ".$Type." ".$Null." DEFAULT '".$Default.
					"' AFTER `".$After."`", $db, False);
			}
			$SQL="ALTER TABLE `".$Table."` ADD COLUMN `".$Column."` ".$Type." ".$Null." DEFAULT '".$Default.
					"' AFTER `".$After."`";
			if ($response==0) {
				OutputResult( _('The column').' '.$Column.' '._('has been inserted') , 'success');
			} else {
				OutputResult( _('The column').' '.$Column.' '._('could not be inserted') . '<br />' . $SQL , 'error');
			}
		} else {
			OutputResult( _('The column').' '.$Column.' '._('already exists') , 'info');
		}
	}
}

function AddIndex($Columns, $Table, $Name, $db) {
	if (DB_table_exists($Table, $db)) {
		$sql="SHOW INDEX FROM " . $Table . " WHERE Key_name='".$Name."'";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result)==0) {
			$sql="ALTER TABLE `".$Table."` ADD INDEX ".$Name." (`".$Columns[0]."`";
			for ($i=1; $i<sizeOf($Columns); $i++) {
				$sql.=",".$Columns[$i];
			}
			$sql.=")";
			$response=executeSQL($sql, $db, False);
			if ($response==0) {
				OutputResult( _('The index has been inserted') , 'success');
			} else {
				OutputResult( _('The column could not be inserted') , 'error');
			}
		} else {
			OutputResult( _('The column already exists') , 'info');
		}
	}
}

function DropIndex($Table, $Name, $db) {
	if (DB_table_exists($Table, $db)) {
		$sql="SHOW INDEX FROM " . $Table . " WHERE Key_name='".$Name."'";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result)!=0) {
			$sql="ALTER TABLE `".$Table."` DROP INDEX ".$Name;
			$response=executeSQL($sql, $db, False);
			if ($response==0) {
				OutputResult( _('The index has been droppeed') , 'success');
			} else {
				OutputResult( _('The index could not be dropped') , 'error');
			}
		} else {
			OutputResult( _('The index does not exist') , 'info');
		}
	}
}

function DropColumn($Column, $Table, $db) {
	global $SQLFile;
	if (DB_table_exists($Table, $db)) {
		$sql="desc ".$Table." ".$Column;
		$result = DB_query($sql, $db);
		if (isset($SQLFile) or DB_num_rows($result)!=0) {
			$response=executeSQL("ALTER TABLE `".$Table."` DROP `".$Column, $db, False);
			if ($response==0) {
				OutputResult( _('The column').' '.$Column.' '._('has been removed') , 'success');
			} else {
				OutputResult( _('The column').' '.$Column.' '._('could not be removed') , 'error');
			}
		} else {
			OutputResult( _('The column').' '.$Column.' '._('is already removed') , 'info');
		}
	}
}

function ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size, $db) {
	$sql="SELECT CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$Column."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]<>$Size) {
		$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$Column." ".$Column." ".$Type." ".$Null." DEFAULT '".$Default."'", $db, False);
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been changed') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('could not be changed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('is already changed') , 'info');
	}
}

function ChangeColumnName($OldName, $Table, $Type, $Null, $Default, $NewName, $db, $AutoIncrement='') {
	$OldSQL="SELECT CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$OldName."'";
	$OldResult=DB_query($OldSQL, $db);
	$NewSQL="SELECT CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$NewName."'";
	$NewResult=DB_query($NewSQL, $db);
	if (DB_num_rows($OldResult)>0 and DB_num_rows($NewResult)==0) {
		if ($AutoIncrement=='') {
			$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$OldName." ".$NewName." ".$Type." ".$Null." DEFAULT '".$Default."'", $db, False);
		} else {
			$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$OldName." ".$NewName." ".$Type." ".$Null." ".$AutoIncrement, $db, False);
		}
		if ($response==0) {
			OutputResult( _('The column').' '.$OldName.' '._('has been renamed').' '.$NewName , 'success');
		} else {
			OutputResult( _('The column').' '.$OldName.' '._('could not be renamed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$OldName.' '._('is already changed') , 'info');
	}
}

function ChangeColumnType($Column, $Table, $Type, $Null, $Default, $db) {
	$sql="SELECT DATA_TYPE
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$Column."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]<>$Type) {
		if ($Default=='') {
			$sql="ALTER TABLE ".$Table." CHANGE COLUMN ".$Column." ".$Column." ".$Type." ".$Null;
			$response=executeSQL($sql, $db, False);
		} else {
			$sql="ALTER TABLE ".$Table." CHANGE COLUMN ".$Column." ".$Column." ".$Type." ".$Null." DEFAULT '".$Default."'";
			$response=executeSQL($sql, $db, False);
		}
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been changed') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('in the table').' '.$Table.' '._('could not be changed to type').' '.$Type.' '._('and returned error number').' '.$response.'<br />'.$sql , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('is already changed') , 'info');
	}
}

function ChangeColumnDefault($Column, $Table, $Type, $Null, $Default, $db) {
	$sql="SELECT COLUMN_DEFAULT
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$Column."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]<>$Default) {
		$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$Column." ".$Column." ".$Type." ".$Null." DEFAULT '".$Default."'", $db, False);
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been changed') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('could not be changed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('is already changed') , 'info');
	}
}

function RemoveAutoIncrement($Column, $Table, $Type, $Null, $Default, $db) {
	$sql="SELECT COLUMN_DEFAULT
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$Column."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]<>$Default) {
		$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$Column." ".$Column." ".$Type." ".$Null." DEFAULT '".$Default."'", $db, False);
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been changed') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('could not be changed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('is already changed') , 'info');
	}
}

function NewConfigValue($ConfName, $ConfValue, $db) {
	$sql="SELECT confvalue
		FROM config
		WHERE confname='".$ConfName."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		$response=executeSQL("INSERT INTO `config` (`confname`, `confvalue`) VALUES ('".$ConfName."', '".$ConfValue."')", $db, False);
		if ($response==0) {
			OutputResult( _('The config value').' '.$ConfName.' '._('has been inserted') , 'success');
		} else {
			OutputResult( _('The config value').' '.$ConfName.' '._('could not be inserted') , 'error');
		}
	} else {
		OutputResult( _('The config value').' '.$ConfName.' '._('is in') , 'info');
	}
}

function ChangeConfigValue($ConfName, $NewConfigValue,$db) {
	$sql="SELECT confvalue
		FROM config
		WHERE confname='".$ConfName."'
			AND confvalue='".$NewConfigValue."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		$response=executeSQL("UPDATE `config` SET `confvalue`='".$NewConfigValue."' WHERE `confname`='".$ConfName."'", $db, False);
		if ($response==0) {
			OutputResult( _('The config value').' '.$ConfName.' '._('has been updated') , 'success');
		} else {
			OutputResult( _('The config value').' '.$ConfName.' '._('could not be updated') , 'error');
		}
	} else {
		OutputResult( _('The config value').' '.$ConfName.' '._('is already set to').' '.$NewConfigValue , 'info');
	}
}

function ChangeConfigName($OldConfName, $NewConfName,$db) {
	$sql="SELECT confvalue
		FROM config
		WHERE confname='".$NewConfName."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		$response=executeSQL("UPDATE `config` SET `confname`='".$NewConfName."' WHERE `confname`='".$OldConfName."'", $db, False);
		if ($response==0) {
			OutputResult( _('The config value').' '.$OldConfName.' '._('has been updated') , 'success');
		} else {
			OutputResult( _('The config value').' '.$OldConfName.' '._('could not be updated') , 'error');
		}
	} else {
		OutputResult( _('The config value').' '.$OldConfName.' '._('is already changed to').' '.$NewConfName , 'info');
	}
}

function DeleteConfigValue($ConfName,$db) {
	$sql="SELECT confvalue
		FROM config
		WHERE confname='".$ConfName."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		$response=executeSQL("DELETE FROM `config` WHERE `confname`='".$ConfName."'", $db, False);
		if ($response==0) {
			OutputResult( _('The config value').' '.$ConfName.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The config value').' '.$ConfName.' '._('could not be removed') , 'error');
		}
	} else {
		OutputResult( _('The config value').' '.$ConfName.' '._('is already removed') , 'info');
	}
}

function CreateTable($Table, $sql, $db) {
	$ShowSQL="SHOW TABLES WHERE Tables_in_".$_SESSION['DatabaseName']."='".$Table."'";
	$result=DB_query($ShowSQL, $db);

	if (DB_num_rows($result)==0) {
		DB_IgnoreForeignKeys($db);
		$response=executeSQL($sql . ' ENGINE=InnoDB DEFAULT CHARSET=utf8', $db, False);
		DB_ReinstateForeignKeys($db);
		if ($response==0) {
			OutputResult( _('The table').' '.$Table.' '._('has been created') , 'success');
		} else {
			OutputResult( _('The table').' '.$Table.' '._('could not be created') , 'error');
		}
	} else {
		OutputResult( _('The table').' '.$Table.' '._('already exists') , 'info');
	}
}

function ConstraintExists($Table, $Constraint, $db) {
	$sql="SELECT CONSTRAINT_NAME
		FROM information_schema.TABLE_CONSTRAINTS
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND CONSTRAINT_NAME='".$Constraint."'";
	$result=DB_query($sql, $db);
	if (DB_num_rows($result)==0) {
		return false;
	} else {
		return true;
	}
}

function DropConstraint($Table, $Constraint, $db) {
	if (ConstraintExists($Table, $Constraint, $db)) {
		$response=executeSQL("ALTER TABLE `".$Table."` DROP FOREIGN KEY `".$Constraint."`", $db, False);
		if ($response==0) {
			OutputResult( _('The constraint').' '.$Constraint.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The constraint').' '.$Constraint.' '._('could not be removed') , 'error');
		}
	} else {
		OutputResult( _('The constraint').' '.$Constraint.' '._('does not exist') , 'info');
	}
}

function AddConstraint($Table, $Constraint, $Field, $ReferenceTable, $ReferenceField, $db) {
	if (!ConstraintExists($Table, $Constraint, $db)) {
		if (gettype($Field)=='array') {
			$List=implode(',', $Field);
			$Field=$List;
		}
		if (gettype($ReferenceField)=='array') {
			$List=implode(',', $ReferenceField);
			$ReferenceField=$List;
		}
		$sql="ALTER TABLE ".$Table. " ADD CONSTRAINT ".$Constraint." FOREIGN KEY (".$Field.") REFERENCES ".$ReferenceTable." (".$ReferenceField.")";
		$response=executeSQL($sql, $db, False);
		if ($response==0) {
			OutputResult( _('The constraint').' '.$Constraint.' '._('has been added') , 'success');
		} else {
			OutputResult( _('The constraint').' '.$Constraint.' '._('could not be added').'<br />'.$sql , 'error');
		}
	} else {
		OutputResult( _('The constraint').' '.$Constraint.' '._('already exists') , 'info');
	}
}

function UpdateField($Table, $Field, $NewValue, $Criteria, $db) {
	global $SQLFile;
	if (DB_table_exists($Table, $db)) {
		$sql="desc ".$Table." ".$Field;
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_row($result);
	} else {
		$myrow[0]=0;
	}
	if ($myrow[0]!=0 or DB_num_rows($result)>0) {
		$sql="SELECT ".$Field." FROM ".$Table." WHERE ".$Criteria;
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_row($result);
		if ($myrow[0]!=$NewValue) {
			$sql="UPDATE ".$Table." SET ".$Field."='".$NewValue."' WHERE ".$Criteria;
			$response=executeSQL($sql, $db, False);
			if ($response==0) {
				OutputResult( _('The field').' '.$Field.' '._('has been updated') , 'success');
			} else {
				OutputResult( _('The field').' '.$Field.' '._('could not be updated').'<br />'.$sql , 'error');
			}
		} else {
			OutputResult( _('The field').' '.$Field.' '._('is already correct') , 'info');
		}
	} else if (isset($SQLFile)) {
		$response=executeSQL("UPDATE ".$Table." SET ".$Field."='".$NewValue."' WHERE ".$Criteria, $db, False);
	}
}

function DeleteRecords($Table, $Criteria, $db) {
	$sql="SELECT * FROM ".$Table." WHERE ".$Criteria;
	$result=DB_query($sql, $db);
	if (DB_num_rows($result)>0) {
		$response=executeSQL("DELETE FROM ".$Table." WHERE ".$Criteria, $db, False);
		if ($response==0) {
			OutputResult( _('Rows have been deleted from').' '.$Table , 'success');
		} else {
			OutputResult( _('Rows could not be deleted from').' '.$Table , 'error');
		}
	} else {
		OutputResult( _('There was nothing to delete from').' '.$Table , 'info');
	}
}

function DropTable($Table, $Field, $db) {
	$sql="SHOW tables WHERE Tables_in_".$_SESSION['DatabaseName']." ='".$Table."'";
	$result=DB_query($sql, $db);
	$CanDrop=False;
	if (DB_num_rows($result)>0) {
		$CanDrop=True;
		$sql="desc ".$Table." ".$Field;
		$result = DB_query($sql, $db);
		if (DB_num_rows($result)>0) {
			$CanDrop=True;
		} else {
			$CanDrop=False;
		}
	}
	if ($CanDrop) {
		$response=executeSQL("DROP TABLE IF EXISTS `".$Table."`", $db);
		if ($response==0) {
			OutputResult( _('The old table').' '.$Table.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The old table').' '.$Table.' '._('could not be removed') , 'error');
		}
	} else {
		OutputResult( _('The old table').' '.$Table.' '._('has already been removed') , 'info');
	}
}

function InsertRecord($Table, $CheckFields, $CheckValues, $Fields, $Values, $db) {
	if (DB_table_exists($Table, $db)) {
		$sql="SELECT * FROM ".$Table." WHERE ";
		for ($i=0;$i<sizeOf($CheckFields);$i++) {
			$sql = $sql.$CheckFields[$i]."='".$CheckValues[$i]."' AND ";
		}
		$sql=mb_substr($sql, 0, mb_strlen($sql)-5);
		$result=DB_query($sql, $db);
	}
	if (DB_num_rows($result)==0 or isset($SQLFile)) {
		$sql="INSERT INTO ".$Table." (";
		for ($i=0;$i<sizeOf($Fields);$i++) {
			$sql = $sql.$Fields[$i].",";
		}
		$sql=mb_substr($sql, 0, mb_strlen($sql)-1).") VALUES (";
		for ($i=0;$i<sizeOf($Values);$i++) {
			$sql = $sql."'".$Values[$i]."',";
		}
		$sql=mb_substr($sql, 0, mb_strlen($sql)-1).")";
		$response=executeSQL($sql, $db);
		if ($response==0) {
			OutputResult( _('The record has been inserted') , 'success');
		} else {
			OutputResult( _('The record could not be inserted').' '._('The sql used was').'<br />'.$sql , 'error');
		}
	} else {
		OutputResult( _('The record is already in the table') , 'info');
	}
}

function DropPrimaryKey($Table, $OldKey, $db) {
	$sql="SELECT COLUMN_NAME, TABLE_NAME FROM information_schema.key_column_usage WHERE table_name='".$Table."' AND constraint_name='primary' AND table_schema='".$_SESSION['DatabaseName']."'";
	$result = DB_query($sql, $db);
	$Total = DB_num_rows($result);
	$Fields = array();
	while ($myrow=DB_fetch_array($result)) {
		$Fields[] = $myrow['COLUMN_NAME'];
	}
	if ($Total==sizeOf($OldKey) and $Fields==$OldKey) {
		$sql="ALTER TABLE ".$Table." DROP PRIMARY KEY";
		$response=executeSQL($sql, $db);
		if ($response==0) {
			OutputResult( _('The primary key in').' '.$Table.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The primary key in').' '.$Table.' '._('could not be removed').'<br />'.$sql , 'error');
		}
	} else {
		OutputResult( _('The primary key in').' '.$Table.' '._('has already been removed') , 'info');
	}
}

function AddPrimaryKey($Table, $Fields, $db) {
	$sql="SELECT table_name FROM information_schema.key_column_usage WHERE table_name='".$Table."' AND constraint_name='primary' AND
		table_schema='".$_SESSION['DatabaseName']."'";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)!=sizeOf($Fields)) {
		$KeyString=implode(",", $Fields);
		$response=executeSQL("ALTER TABLE ".$Table." ADD PRIMARY KEY ( ".$KeyString." )", $db);
		if ($response==0) {
			OutputResult( _('The primary key in').' '.$Table.' '._('has been added') , 'success');
		} else {
			OutputResult( _('The primary key in').' '.$Table.' '._('could not be added') . '<br />' . "ALTER TABLE ".$Table." ADD PRIMARY KEY ( ".$KeyString." )" , 'error');
		}
	} else {
		OutputResult( _('The primary key in').' '.$Table.' '._('has already been added') , 'info');
	}
}

function RenameTable($OldName, $NewName, $db) {
	$Newsql="SHOW TABLES WHERE Tables_in_".$_SESSION['DatabaseName']."='".$NewName."'";
	$Newresult=DB_query($Newsql, $db);
	$Oldsql="SHOW TABLES WHERE Tables_in_".$_SESSION['DatabaseName']."='".$OldName."'";
	$Oldresult=DB_query($Oldsql, $db);

	if (DB_num_rows($Newresult)!=0 and DB_num_rows($Oldresult)!=0) {
		$response=executeSQL("DROP TABLE ".$OldName."", $db, False);
	}
	if (DB_num_rows($Newresult)==0) {
		$sql="RENAME TABLE ".$OldName." to ".$NewName;
		$response=executeSQL($sql, $db, False);
		if ($response==0) {
			OutputResult( _('The table').' '.$OldName.' '._('has been renamed to').' '.$NewName , 'success');
		} else {
			OutputResult( _('The table').' '.$OldName.' '._('could not be renamed to').' '.$NewName.'<br />'.$sql , 'error');
		}
	} else {
		OutputResult( _('The table').' '.$NewName.' '._('already exists') , 'info');
	}
}

function OutputResult($msg, $status) {
	if ($status=='error') {
		$_SESSION['Updates']['Errors']++;
		$_SESSION['Updates']['Messages'][]=$msg;
	} else if ($status=='success') {
		$_SESSION['Updates']['Successes']++;
	} else {
		$_SESSION['Updates']['Warnings']++;
	}
}

?>