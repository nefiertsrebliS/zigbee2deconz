<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/Zigbee2DeCONZHelper.php';

class Z2DLightSwitch extends IPSModule
{
    use Zigbee2DeCONZHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{9013F138-F270-C396-09D6-43368E390C5F}');

        $this->RegisterPropertyString('DeviceID', "");
        $this->RegisterPropertyBoolean('Status', false);
        $this->RegisterPropertyString('DeviceType', "lights");
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

		if($this->HasActiveParent())$this->GetStateDeconz();
			
#		Filter setzen
		$this->SetReceiveDataFilter('.*'.preg_quote('\"uniqueid\":\"').$this->ReadPropertyString("DeviceID").preg_quote('\"').'.*');
    }

    public function ReceiveData($JSONString)
    {
        $Buffer = json_decode($JSONString)->Buffer;
        $this->SendDebug('Received', $Buffer, 0);
        $data = json_decode($Buffer);
		if(json_last_error() !== 0){
			$this->LogMessage($this->Translate("Instance")." #".$this->InstanceID.": ".$this->Translate("Received Data unreadable"),KL_ERROR);
			return;
		}

	    if (property_exists($data, 'state')) {
			$Payload = $data->state;
			if (property_exists($Payload, 'colormode')) {
				if ((property_exists($Payload, 'xy') || property_exists($Payload, 'hs')) && property_exists($Payload, 'ct')){
					$this->RegisterVariableInteger('Z2D_colormode', $this->Translate('Colormode'), 'Colormode.Z2D', 10);
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
			if (property_exists($Payload, 'on')) {
			    $this->RegisterVariableBoolean('Z2D_State', $this->Translate('State'), '~Switch', 0);
			    $this->EnableAction('Z2D_State');
			    $this->SetValue('Z2D_State', $Payload->on);
			}
			if (property_exists($Payload, 'bri')) {
			    $this->RegisterVariableInteger('Z2D_Brightness', $this->Translate('Brightness'), '~Intensity.100', 30);
			    $this->EnableAction('Z2D_Brightness');
				$bri = ($Payload->bri>1)?round($Payload->bri/2.55):$Payload->bri;
				if (property_exists($Payload, 'on')) {
				    if(!$Payload->on) $bri = 0;
				}
			    $this->SetValue('Z2D_Brightness', $bri);
			}
			if (property_exists($Payload, 'ct')) {
			    $this->RegisterVariableInteger('Z2D_ColorTemperature', $this->Translate('Color-Temperature'), 'ColorTemperature.Z2D', 20);
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
			    $this->RegisterVariableInteger('Z2D_Color', $this->Translate('Color'), '~HexColor', 25);
			    $this->EnableAction('Z2D_Color');
				$cie['x'] = $Payload->xy[0];
				$cie['y'] = $Payload->xy[1];
				$cie['bri'] = $Payload->bri;
			    $this->SetValue('Z2D_Color', $this->CieToDec($cie));
			}
			if (property_exists($Payload, 'reachable')) {
				if($Payload->reachable){
					$this->SetStatus(102);
				}else{
					$this->SetStatus(215);
				}
			}
		}
    }
}
