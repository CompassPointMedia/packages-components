<?php
/*
we set dataset parameters - datasetDefaultParams[dataset]
we have calls to change - changeParams({dataset})[paramname]
and we have the output - datasetParams
normally this is called in precoding but we can call earlier and override

NOTE that session.userSettings will be something like invoiceShowVoids[:n].  userSettings must be used "as is"  by the programming; they are not meant to be parsed for the value of varnode or varkey
*/

if($datasetDefaultParams[$dataset] && !$datasetParamsCalled){
	//do it
	foreach($datasetDefaultParams[$dataset] as $param=>$default){
		$param=explode(':',$param);
		$key=$param[1];
		$param=$param[0];
		//this is what is passed
		if(!$paramCheck[$dataset]){
			$paramCheck[$dataset]=true;
			$object=($changeParams[$dataset] ? $changeParams[$dataset] : $changeParams);
		}
		if(strlen($x=$object[$param])){
			q("REPLACE INTO bais_settings SET UserName='$datasetUserName', vargroup='$dataset',varnode='$param',varkey='$key',varvalue='".addslashes($x)."'");
			//this can also be called globally as just userSettings[]
			$_SESSION['userSettings'][$dataset.$param.($key?':'.$key:'')]=$x;
		}else if($refreshUserSettings){
			//note we can't call to change a parameter and reset in the same operation with this structure
			if($a=q("SELECT CONCAT( varnode, IF(varkey!='',':',''), varkey) AS settingName, varvalue as settingValue FROM bais_settings WHERE UserName='$datasetUserName' AND vargroup='$dataset' AND varnode='$param' AND varkey='$key'", O_ROW)){
				// we have from database, OK
			}else{
				q("REPLACE INTO bais_settings SET UserName='$datasetUserName', vargroup='$dataset',varnode='$param',varkey='$key',varvalue='".addslashes($default)."'");
				$a['settingName']=$param.($key?':'.$key:'');
				$a['settingValue']=$default;
			}
			$_SESSION['userSettings'][$dataset.$a['settingName']]=$a['settingValue'];
		}else{
			q("REPLACE INTO bais_settings SET UserName='$datasetUserName', vargroup='$dataset',varnode='$param',varkey='$key',varvalue='".addslashes($x)."'");
			$_SESSION['userSettings'][$dataset.$param.($key?':'.$key:'')]=$default;
		}
	}
}
?>