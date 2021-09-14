<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/DeconzHelper.php';

class Z2DGroup extends IPSModule
{
    use DeconzHelper;

#================================================================================================
    public function Create()
#================================================================================================
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{9013F138-F270-C396-09D6-43368E390C5F}');

        $this->RegisterPropertyString('DeviceID', "");
#	-----------------------------------------------------------------------------------
		$this->RegisterAttributeString('CommandList', "");
		$this->RegisterAttributeString('GroupLights', "");
		$this->RegisterAttributeFloat("LastUpdated", 0);
    }


#================================================================================================
    public function ApplyChanges()
#================================================================================================
    {
        //Never delete this line!
		parent::ApplyChanges();
			
#		Filter setzen

		if(strstr($this->ReadPropertyString("DeviceID"),":") === false){
			$Filter = '.*('.preg_quote('\"id\":\"').$this->ReadPropertyString("DeviceID").preg_quote('\"');
		}else{
			$Filter = '.*('.preg_quote('\"uniqueid\":\"').$this->ReadPropertyString("DeviceID").'.*'.preg_quote('\"');
		}

		if($this->ReadAttributeString('GroupLights') != ''){
			$GroupLights = json_decode($this->ReadAttributeString('GroupLights'));
			if(count($GroupLights)>0)$Filter .= '|'.preg_quote('\"uniqueid\":\"').$GroupLights[0].preg_quote('\"');
		}
		$Filter .= ').*';
		$this->SendDebug("Filter", $Filter, 0);
		$this->SetReceiveDataFilter($Filter);

		if($this->HasActiveParent()) $this->GetStateDeconz();
}

#================================================================================================
    public function ReceiveData($JSONString)
#================================================================================================
    {
        $Buffer = json_decode($JSONString)->Buffer;
        $this->SendDebug('Received', $Buffer, 0);
        $data = json_decode($Buffer);
		if(json_last_error() !== 0){
			$this->LogMessage($this->Translate("Instance")." #".$this->InstanceID.": ".$this->Translate("Received Data unreadable"),KL_ERROR);
			return;
		}

		$CommandList = json_decode($this->ReadAttributeString('CommandList'));
		if(!$CommandList)$CommandList = new \stdClass;
		if($data->r == "groups"){
			if (property_exists($data, 'state')) {
				$Command = "/groups/".$data->id."/action";
				$Payload = $data->state;
				if (property_exists($Payload, 'all_on')) {
					$this->RegisterVariableBoolean('Z2D_State', $this->Translate('all'), '~Switch', 0);
					$CommandList->on = $Command;
					$this->EnableAction('Z2D_State');
					$this->SetValue('Z2D_State', $Payload->all_on);
				}
				if (property_exists($Payload, 'any_on')) {
					$this->RegisterVariableBoolean('Z2D_AnyOn', $this->Translate('any on'), '~Switch', 10);
					$this->SetValue('Z2D_AnyOn', $Payload->any_on);
				}
			}

			if (property_exists($data, 'scenes')) {
				$Scenes = json_decode(json_encode($data->scenes),true);
				if(count($Scenes) > 0){
					$this->RegisterVariableInteger('Z2D_Scene', $this->Translate('Scene'), 'Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 20);
					$CommandList->scene = "/groups/".$data->id."/scenes/";
					$this->EnableAction('Z2D_Scene');
					if (!IPS_VariableProfileExists('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D')) {
						IPS_CreateVariableProfile ('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 1);
						IPS_SetVariableProfileIcon('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 'Bulb');
					}

#-------------------------------------------------------------------------------------
#	Fehlende Scenen im Profil ergänzen
#-------------------------------------------------------------------------------------

					$Assotiations = IPS_GetVariableProfile('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D')["Associations"];
					foreach($Scenes as $Scene){
						$key = array_search($Scene['id'], array_column($Assotiations, 'Value'));
						if($key !== false){
							if($Assotiations[$key]['Name'] != $Scene['name']) IPS_SetVariableProfileAssociation('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', $Scene['id'], $Scene['name'], '',-1);
						}else{
							IPS_SetVariableProfileAssociation('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', $Scene['id'], $Scene['name'], '',-1);
						}
					}

#-------------------------------------------------------------------------------------
#	In DeConz entfernte Scenen im Profil löschen
#-------------------------------------------------------------------------------------
				
					foreach($Assotiations as $Assotiation){
						$key = array_search($Assotiation['Value'], array_column($Scenes, 'id'));
						if($key === false) IPS_SetVariableProfileAssociation('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', $Assotiation['Value'], '', '',-1);
					}
				}
			}

			if (property_exists($data, 'lights')) {

				if(json_encode($data->lights) != $this->ReadAttributeString('GroupLights')){
					$this->WriteAttributeString('GroupLights', json_encode($data->lights));
					$this->ApplyChanges();
				}

			}
		}elseif($data->r == "lights"){
			if (property_exists($data, 'state')) {
				$Command = "/groups/".$this->ReadPropertyString('DeviceID')."/action";
				$Payload = $data->state;
				if (property_exists($Payload, 'bri')) {
					$this->RegisterVariableInteger('Z2D_Brightness', $this->Translate('Brightness'), '~Intensity.100', 130);
					$CommandList->bri = $Command;
					$this->EnableAction('Z2D_Brightness');
					$bri = ($Payload->bri>1)?round($Payload->bri/2.55):$Payload->bri;
					if (property_exists($Payload, 'on')) {
						if(!$Payload->on) $bri = 0;
					}
					$this->SetValue('Z2D_Brightness', $bri);
				}
				if (property_exists($Payload, 'ct')) {
					$this->RegisterVariableInteger('Z2D_ColorTemperature', $this->Translate('Color-Temperature'), 'ColorTemperature.Z2D', 120);
					$CommandList->ct = $Command;
					$this->EnableAction('Z2D_ColorTemperature');
					$value = $Payload->ct * (2000-6500)/(500-153) + 8485;
					$value = round($value, -2);
					$this->SetValue('Z2D_ColorTemperature', (int)$value);

					if (!IPS_VariableProfileExists('ColorTemperature.Z2D')) {
						IPS_CreateVariableProfile('ColorTemperature.Z2D', 1);
						IPS_SetVariableProfileIcon('ColorTemperature.Z2D', 'TurnLeft');
						IPS_SetVariableProfileText('ColorTemperature.Z2D', '', ' K');
						IPS_SetVariableProfileValues('ColorTemperature.Z2D', 2000, 6500, 100);
					}
				}
				if (property_exists($Payload, 'xy')) {
					$this->RegisterVariableInteger('Z2D_Color', $this->Translate('Color'), '~HexColor', 125);
					$CommandList->xy = $Command;
					$this->EnableAction('Z2D_Color');
					$cie['x'] = $Payload->xy[0];
					$cie['y'] = $Payload->xy[1];
					$cie['bri'] = $Payload->bri;
					$this->SetValue('Z2D_Color', $this->CieToDec($cie));
				}
				if (property_exists($Payload, 'colormode')) {
					if ((property_exists($Payload, 'xy') || property_exists($Payload, 'hs')) && property_exists($Payload, 'ct')){
						$this->RegisterVariableInteger('Z2D_colormode', $this->Translate('Colormode'), 'Colormode.Z2D', 110);
						$CommandList->colormode = $Command;
						$this->EnableAction('Z2D_colormode');
						switch ($Payload->colormode) {
							case "ct":
								$colormode = 1;
								@IPS_SetHidden($this->GetIDForIdent('Z2D_ColorTemperature'), false);
								@IPS_SetHidden($this->GetIDForIdent('Z2D_Color'), true);
								break;
							case "xy":
							case "hs":
								$colormode = 2;
								@IPS_SetHidden($this->GetIDForIdent('Z2D_ColorTemperature'), true);
								@IPS_SetHidden($this->GetIDForIdent('Z2D_Color'), false);
								break;
							default:
								$colormode = 0;
						}
						$this->SetValue('Z2D_colormode', $colormode);

						if (!IPS_VariableProfileExists('Colormode.Z2D')) {
							IPS_CreateVariableProfile('Colormode.Z2D', 1);
							IPS_SetVariableProfileIcon('Colormode.Z2D', 'TurnLeft');
							IPS_SetVariableProfileAssociation('Colormode.Z2D', 1, $this->Translate('Color-Temperature'), '',-1);
							IPS_SetVariableProfileAssociation('Colormode.Z2D', 2, $this->Translate('Multicolor'), '',-1);
							IPS_SetVariableProfileValues('Colormode.Z2D', 1, 2, 1);
						}
					}
				}
			}
		}
		$this->WriteAttributeString('CommandList', json_encode($CommandList));
	}
}
