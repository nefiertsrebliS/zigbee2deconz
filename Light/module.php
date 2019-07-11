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
		$this->RegisterTimer("Update", 60000,'IPS_RequestAction($_IPS["TARGET"], "Update", "GetStateDeconz()");');        

		IPS_Sleep(100);
		@$this->ApplyChanges();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

		@$this->GetStateDeconz();
    }

    public function ReceiveData($JSONString)
    {
        $Buffer = json_decode($JSONString)->Buffer;
		if(strpos($Buffer, $this->ReadPropertyString("DeviceID")) ===false)return;
        $this->SendDebug('Received', $Buffer, 0);

        $data = json_decode($Buffer);
		if(is_array($data)){
		    $this->SendDebug('Received', $Buffer, 0);
			foreach($data as $item){
				if (property_exists($item, 'error')) {
					echo "Device unreachable.";
					return;
				    $this->SetStatus(215);
	                trigger_error('Device unreachable.', E_USER_WARNING);
					break;
				}else{
				    $this->SetStatus(102);
				}
			}
		}else{
			if($this->GetBuffer("Microtimer")<>""){
				list($usec, $sec) = explode(" ", $this->GetBuffer("Microtimer"));
				$last = (float)$usec + (float)$sec; 
				list($usec, $sec) = explode(" ", microtime());
				$now = (float)$usec + (float)$sec; 
				if($now - $last < 0.25) return;
			}
			$this->SetBuffer("Microtimer", microtime());
		    $this->SendDebug('Received', $Buffer, 0);

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
						SetValue($this->GetIDForIdent('Z2D_colormode'), $colormode);

						if (!IPS_VariableProfileExists('Colormode.Z2D')) {
							IPS_CreateVariableProfile('Colormode.Z2D', 1);
							IPS_SetVariableProfileIcon('Colormode.Z2D', 'TurnLeft');
							IPS_SetVariableProfileAssociation('Colormode.Z2D', 1, $this->Translate('Color-Temperature'), '',-1);
							IPS_SetVariableProfileAssociation('Colormode.Z2D', 2, $this->Translate('Multicolor'), '',-1);
							IPS_SetVariableProfileValues('Colormode.Z2D', 1, 2, 1);
						}
					}
				}
				if (property_exists($Payload, 'bri')) {
				    $this->RegisterVariableInteger('Z2D_Brightness', $this->Translate('Brightness'), '~Intensity.100', 30);
				    $this->EnableAction('Z2D_Brightness');
					$bri = ($Payload->bri>1)?round($Payload->bri/2.55):$Payload->bri;
				    SetValue($this->GetIDForIdent('Z2D_Brightness'), $bri);
				}
				if (property_exists($Payload, 'ct')) {
				    $this->RegisterVariableInteger('Z2D_ColorTemperature', $this->Translate('Color-Temperature'), 'ColorTemperature.Z2D', 20);
				    $this->EnableAction('Z2D_ColorTemperature');
					$value = $Payload->ct * (2000-6500)/(500-153) + 8485;
					$value = round($value, -2);
				    SetValue($this->GetIDForIdent('Z2D_ColorTemperature'), (int)$value);

					if (!IPS_VariableProfileExists('ColorTemperature.Z2D')) {
					    IPS_CreateVariableProfile('ColorTemperature.Z2D', 1);
						IPS_SetVariableProfileIcon('ColorTemperature.Z2D', 'TurnLeft');
						IPS_SetVariableProfileText('ColorTemperature.Z2D', '', ' K');
						IPS_SetVariableProfileValues('ColorTemperature.Z2D', 2000, 6500, 100);
					}
				}
				if (property_exists($Payload, 'on')) {
				    $this->RegisterVariableBoolean('Z2D_State', $this->Translate('State'), '~Switch', 0);
				    $this->EnableAction('Z2D_State');
				    SetValueBoolean($this->GetIDForIdent('Z2D_State'), $Payload->on);
				    if(!$Payload->on)$this->SetValue('Z2D_Brightness', 0);
				}
				if (property_exists($Payload, 'xy')) {
				    $this->RegisterVariableInteger('Z2D_Color', $this->Translate('Color'), '~HexColor', 25);
				    $this->EnableAction('Z2D_Color');
					$cie['x'] = $Payload->xy[0];
					$cie['y'] = $Payload->xy[1];
					$cie['bri'] = $Payload->bri;
				    SetValue($this->GetIDForIdent('Z2D_Color'), $this->CieToDec($cie));
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
}
