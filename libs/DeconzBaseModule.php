<?php

declare(strict_types=1);

trait DeconzBaseModule
{

#=====================================================================================
    public function Create()
#=====================================================================================
    {
#		Never delete this line!
        parent::Create();

		$this->ConnectParent('{9013F138-F270-C396-09D6-43368E390C5F}');

        $this->RegisterPropertyString('DeviceID', "");
		$this->RegisterPropertyBoolean('ShowReachable', false);
#	-----------------------------------------------------------------------------------
		$this->RegisterAttributeString('CommandList', "");
		$this->RegisterAttributeBoolean('reachable', true);
		$this->RegisterAttributeBoolean('ColorTemp', false);
		$this->RegisterAttributeBoolean('Color', false);
	}

#=====================================================================================
    public function ApplyChanges()
#=====================================================================================
    {
#		Filter setzen
		$Filter = " ";
		if($this->ReadPropertyString("DeviceID") != "")$Filter = '.*'.preg_quote('\"uniqueid\":\"').$this->ReadPropertyString("DeviceID").'.*'.preg_quote('\"').'.*';
		$this->SendDebug("Filter", $Filter, 0);
		$this->SetReceiveDataFilter($Filter);

#		Never delete this line!
		parent::ApplyChanges();

		if($this->HasActiveParent())$this->GetStateDeconz();
    }

#=====================================================================================
    public function ReceiveData($JSONString)
#=====================================================================================
    {
        $this->SendDebug('Received', $JSONString, 0);
		$Buffer = json_decode($JSONString)->Buffer;
        $data = json_decode($Buffer);
		if(json_last_error() !== 0 || !property_exists($data, 'r')){
			$this->LogMessage($this->Translate("Instance")." #".$this->InstanceID.": ".$this->Translate("Received Data unreadable"),KL_ERROR);
			return;
		}

		if($data->r == "lights"){
			if (property_exists($data, 'state')) {
				$Command = "/".$data->r."/".$data->uniqueid."/state";
				$Payload = $data->state;
				if (property_exists($Payload, 'on')) {
					$this->RegisterVariableBoolean('Z2D_State', $this->Translate('State'), '~Switch', 0);
					$this->SetCommandListEx('on', $Command, false);
					$this->EnableAction('Z2D_State');
					$this->SetValue('Z2D_State', $Payload->on);
				}
				if (property_exists($Payload, 'bri')) {
					$this->RegisterVariableInteger('Z2D_Brightness', $this->Translate('Brightness'), '~Intensity.100', 30);
					$this->SetCommandListEx('bri', $Command, false);
					$this->EnableAction('Z2D_Brightness');
					$bri = ($Payload->bri>1)?round($Payload->bri/2.55):$Payload->bri;
					if (property_exists($Payload, 'on')) {
						if(!$Payload->on) $bri = 0;
					}
					$this->SetValue('Z2D_Brightness', $bri);
				}
				if (property_exists($Payload, 'ct')) {
					if (!IPS_VariableProfileExists('ColorTemperature.Z2D')) {
						IPS_CreateVariableProfile('ColorTemperature.Z2D', 1);
						IPS_SetVariableProfileIcon('ColorTemperature.Z2D', 'TurnLeft');
						IPS_SetVariableProfileText('ColorTemperature.Z2D', '', ' K');
						IPS_SetVariableProfileValues('ColorTemperature.Z2D', 2000, 6500, 100);
					}

					$this->RegisterVariableInteger('Z2D_ColorTemperature', $this->Translate('Color-Temperature'), 'ColorTemperature.Z2D', 20);
					$this->WriteAttributeBoolean('ColorTemp', true);
					$this->SetCommandListEx('ct', $Command, false);
					$this->EnableAction('Z2D_ColorTemperature');
					$value = $Payload->ct * (2000-6500)/(500-153) + 8485;
					$value = round($value, -2);
					$this->SetValue('Z2D_ColorTemperature', (int)$value);
				}
				if (property_exists($Payload, 'xy')) {
					$this->RegisterVariableInteger('Z2D_Color', $this->Translate('Color'), '~HexColor', 25);
					$this->WriteAttributeBoolean('Color', true);
					$this->SetCommandListEx('xy', $Command, false);
					$this->EnableAction('Z2D_Color');
					$cie['x'] = $Payload->xy[0];
					$cie['y'] = $Payload->xy[1];
					$cie['bri'] = $Payload->bri;
					$this->SetValue('Z2D_Color', $this->CieToDec($cie));
				}
				if (property_exists($Payload, 'colormode')) {
					if ((property_exists($Payload, 'xy') || property_exists($Payload, 'hs')) && property_exists($Payload, 'ct')){
						if (!IPS_VariableProfileExists('Colormode.Z2D')) {
							IPS_CreateVariableProfile('Colormode.Z2D', 1);
							IPS_SetVariableProfileIcon('Colormode.Z2D', 'TurnLeft');
							IPS_SetVariableProfileAssociation('Colormode.Z2D', 1, $this->Translate('Color-Temperature'), '',-1);
							IPS_SetVariableProfileAssociation('Colormode.Z2D', 2, $this->Translate('Multicolor'), '',-1);
							IPS_SetVariableProfileValues('Colormode.Z2D', 1, 2, 1);
						}

						$this->RegisterVariableInteger('Z2D_colormode', $this->Translate('Colormode'), 'Colormode.Z2D', 10);
						$this->SetCommandListEx('colormode', $Command, false);
						$this->EnableAction('Z2D_colormode');
						$hasColorTemp = $this->ReadAttributeBoolean('ColorTemp');
						$hasColor = $this->ReadAttributeBoolean('Color');
						switch ($Payload->colormode) {
							case "ct":
								$colormode = 1;
								if($hasColorTemp)IPS_SetHidden($this->GetIDForIdent('Z2D_ColorTemperature'), false);
								if($hasColor)IPS_SetHidden($this->GetIDForIdent('Z2D_Color'), true);
								break;
							case "xy":
							case "hs":
								$colormode = 2;
								if($hasColorTemp)IPS_SetHidden($this->GetIDForIdent('Z2D_ColorTemperature'), true);
								if($hasColor)IPS_SetHidden($this->GetIDForIdent('Z2D_Color'), false);
								break;
							default:
								$colormode = 0;
						}
						$this->SetValue('Z2D_colormode', $colormode);
					}
				}
				if (property_exists($Payload, 'reachable')) {
					$this->SetReachable($Payload->reachable);
				}
				if (property_exists($Payload, 'alert')) {
					if (property_exists($data, 'type')){
						if($data->type == 'Warning device'){
							if (!IPS_VariableProfileExists('Alert.Z2D')) {
								IPS_CreateVariableProfile('Alert.Z2D', 1);
								IPS_SetVariableProfileAssociation("Alert.Z2D", 0, $this->Translate('Off'), "", -1);
								IPS_SetVariableProfileAssociation("Alert.Z2D", 1, $this->Translate('Short On'), "", -1);
								IPS_SetVariableProfileAssociation("Alert.Z2D", 2, $this->Translate('On'), "", -1);
								IPS_SetVariableProfileIcon('Alert.Z2D', 'Alert');
							};
							$this->RegisterVariableInteger('Z2D_Alert', $this->Translate('Alarm'), 'Alert.Z2D');
							$this->EnableAction('Z2D_Alert');
							$value = array_search($Payload->alert, array('none', 'select', 'lselect'));
							if($value !== false)$this->SetValue('Z2D_Alert', $value);
							$this->SetCommandListEx('alert', $Command, false);
						}
					} 
				}
				if (property_exists($Payload, 'battery')) {
					$this->RegisterVariableInteger('Z2D_Battery', $this->Translate('Battery'), '~Battery.100');
					$this->SetValue('Z2D_Battery', $Payload->battery);
				}
			}
		}elseif($data->r == "sensors"){
			if (property_exists($data, 'state')) {
				$Command = "/".$data->r."/".$data->uniqueid."/state";
				$Payload = $data->state;

				if (property_exists($Payload, 'buttonevent')) {
					if (property_exists($Payload, 'gesture') && ($Payload->gesture == 7 || $Payload->gesture == 8)) {
						if (!IPS_VariableProfileExists('Angle.Z2D')) {
							IPS_CreateVariableProfile('Angle.Z2D', 2);
							IPS_SetVariableProfileIcon('Angle.Z2D', 'Repeat');
							IPS_SetVariableProfileText('Angle.Z2D', '', ' °');
							IPS_SetVariableProfileDigits('Angle.Z2D', 2);
						}
						$this->RegisterVariableFloat('Z2D_angle', $this->Translate('Angle'), 'Angle.Z2D');
						$this->SetValue('Z2D_angle', round($Payload->buttonevent / 100 ,2));
					}else{
						$this->RegisterVariableInteger('Z2D_Event', $this->Translate('Event'), '');
						$this->SetValue('Z2D_Event', $Payload->buttonevent);

						$button = (int)($Payload->buttonevent / 1000);
						$state  = $Payload->buttonevent % 1000;

						if (!IPS_VariableProfileExists('ButtonEvent.Z2D')) {
							IPS_CreateVariableProfile('ButtonEvent.Z2D', 1);
							IPS_SetVariableProfileIcon('ButtonEvent.Z2D', 'Power');
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 0, $this->Translate('Initial Press'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 1, $this->Translate('Hold'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 2, $this->Translate('Release after press'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 3, $this->Translate('Release after hold'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 4, $this->Translate('Double press'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 5, $this->Translate('Triple press'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 6, $this->Translate('Quadruple press'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 7, $this->Translate('Shake'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 8, $this->Translate('Drop'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 9, $this->Translate('Tilt'), '',-1);
							IPS_SetVariableProfileAssociation('ButtonEvent.Z2D',10, $this->Translate('Many press'), '',-1);
						}

						$this->RegisterVariableInteger('Z2D_Button_'.$button, $this->Translate('Button')." ".$button, 'ButtonEvent.Z2D');
						$this->SetValue('Z2D_Button_'.$button, $state);
					}
				}
				if (property_exists($Payload, 'gesture')) {
					if (!IPS_VariableProfileExists('Gesture.Z2D')) {
						IPS_CreateVariableProfile('Gesture.Z2D', 1);
						IPS_SetVariableProfileIcon('Gesture.Z2D', 'Repeat');
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 0, $this->Translate('Move'), '',-1);
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 1, $this->Translate('Shake'), '',-1);
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 2, $this->Translate('Drop'), '',-1);
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 3, $this->Translate('Tilt').' 90°', '',-1);
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 4, $this->Translate('Tilt').' 180°', '',-1);
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 5, $this->Translate('Move'), '',-1);
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 6, '2x '.$this->Translate('Knock'), '',-1);
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 7, $this->Translate('Rotate Clockwise'), '',-1);
						IPS_SetVariableProfileAssociation('Gesture.Z2D', 8, $this->Translate('Rotate Counter Clockwise'), '',-1);
					}
					$this->RegisterVariableInteger('Z2D_Gesture', $this->Translate('Gesture'), 'Gesture.Z2D');
					$this->SetValue('Z2D_Gesture', $Payload->gesture);
				}
				if (property_exists($Payload, 'carbonmonoxide')) {
					$this->RegisterVariableBoolean('Z2D_Carbonmonoxide', $this->Translate('Carbonmonoxide'), '~Alert');
					$this->SetValue('Z2D_Carbonmonoxide', $Payload->carbonmonoxide);
				}
				if (property_exists($Payload, 'dark')) {
					$this->RegisterVariableBoolean('Z2D_dark', $this->Translate('dark'), '~Switch');
					$this->SetValue('Z2D_dark', $Payload->dark);
				}
				if (property_exists($Payload, 'fire')) {
					$this->RegisterVariableBoolean('Z2D_fire', $this->Translate('Fire'), '~Alert');
					$this->SetValue('Z2D_fire', $Payload->fire);
				}
				if (property_exists($Payload, 'daylight')) {
					$this->RegisterVariableBoolean('Z2D_daylight', $this->Translate('Daylight'), '~Switch');
					$this->SetValue('Z2D_daylight', $Payload->daylight);
				}
				if (property_exists($Payload, 'lowbattery')) {
					$this->RegisterVariableBoolean('Z2D_lowbattery', $this->Translate('Battery'), '~Battery');
					$this->SetValue('Z2D_lowbattery', $Payload->lowbattery);
				}
				if (property_exists($Payload, 'presence')) {
					$this->RegisterVariableBoolean('Z2D_presence', $this->Translate('Presence'), '~Presence');
					$this->SetValue('Z2D_presence', $Payload->presence);
				}
				if (property_exists($Payload, 'open')) {
					$this->RegisterVariableBoolean('Z2D_open', $this->Translate('open'), '~Window');
					$this->SetValue('Z2D_open', $Payload->open);
				}
				if (property_exists($Payload, 'on')) {
					$this->RegisterVariableBoolean('Z2D_on', $this->Translate('on'), '~Switch');
					$this->SetValue('Z2D_on', $Payload->on);
				}
				if (property_exists($Payload, 'alarm')) {
					$this->RegisterVariableBoolean('Z2D_alarm', $this->Translate('Alarm'), '~Switch');
					$this->SetValue('Z2D_alarm', $Payload->alarm);
					$this->SetCommandListEx('alarm', $Command, false);
				}
				if (property_exists($Payload, 'tampered')) {
					$this->RegisterVariableBoolean('Z2D_tampered', $this->Translate('tampered'), '~Alert');
					$this->SetValue('Z2D_tampered', $Payload->tampered);
				}
				if (property_exists($Payload, 'water')) {
					$this->RegisterVariableBoolean('Z2D_water', $this->Translate('Water'), '~Alert');
					$this->SetValue('Z2D_water', $Payload->water);
				}
				if (property_exists($Payload, 'vibration')) {
					$this->RegisterVariableBoolean('Z2D_vibration', $this->Translate('Vibration'), '~Alert');
					$this->SetValue('Z2D_vibration', $Payload->vibration);
				}
				if (property_exists($Payload, 'orientation')) {
					$this->RegisterVariableString('Z2D_orientation', $this->Translate('Orientation'), '');
					$this->SetValue('Z2D_orientation', json_encode($Payload->orientation));
				}
				if (property_exists($Payload, 'vibrationstrength')) {
					$this->RegisterVariableInteger('Z2D_vibrationstrength', $this->Translate('Vibrationstrength'), '');
					$this->SetValue('Z2D_vibrationstrength', $Payload->vibrationstrength);
				}
				if (property_exists($Payload, 'tiltangle')) {
					if (!IPS_VariableProfileExists('TiltAngle.Z2D')) {
						IPS_CreateVariableProfile('TiltAngle.Z2D', 1);
						IPS_SetVariableProfileIcon('TiltAngle.Z2D', 'TurnLeft');
						IPS_SetVariableProfileText('TiltAngle.Z2D', '', ' °');
						IPS_SetVariableProfileValues('TiltAngle.Z2D', 0, 360, 0);
					}

					$this->RegisterVariableInteger('Z2D_tiltangle', $this->Translate('Tiltangle'), 'TiltAngle.Z2D');
					$this->SetValue('Z2D_tiltangle', $Payload->tiltangle);
				}
				if (property_exists($Payload, 'humidity')) {
					$this->RegisterVariableFloat('Z2D_humidity', $this->Translate('Humidity'), '~Humidity.F');
					$this->SetValue('Z2D_humidity', $Payload->humidity / 100.0);
				}
				if (property_exists($Payload, 'lux')) {
					if(!@IPS_GetObjectIDByIdent('Z2D_lux', $this->InstanceID)) $this->RegisterVariableInteger('Z2D_lux', $this->Translate('Illumination'), '~Illumination');
					$this->SetValue('Z2D_lux', $Payload->lux);
				}
				if (property_exists($Payload, 'lightlevel')) {
					if(!@IPS_GetObjectIDByIdent('Z2D_lightlevel', $this->InstanceID)) $this->RegisterVariableInteger('Z2D_lightlevel', $this->Translate('Illumination'), '~Illumination');
					$this->SetValue('Z2D_lightlevel', $Payload->lightlevel);
				}
				if (property_exists($Payload, 'pressure')) {
					$this->RegisterVariableFloat('Z2D_pressure', $this->Translate('Airpressure'), '~AirPressure.F');
					$this->SetValue('Z2D_pressure', $Payload->pressure);
				}
				if (property_exists($Payload, 'temperature')) {
					$this->RegisterVariableFloat('Z2D_temperature', $this->Translate('Temperature'), '~Temperature');
					$this->SetValue('Z2D_temperature', $Payload->temperature / 100.0);
				}
				if (property_exists($Payload, 'consumption')) {
					$this->RegisterVariableFloat('Z2D_consumption', $this->Translate('Consumption'), '~Electricity');
					$this->SetValue('Z2D_consumption', round($Payload->consumption / 1000 ,3));
				}
				if (property_exists($Payload, 'power')) {
					$this->RegisterVariableFloat('Z2D_power', $this->Translate('Power'), '~Watt.14490');
					$this->SetValue('Z2D_power', $Payload->power);
				}
				if (property_exists($Payload, 'voltage')) {
					$this->RegisterVariableFloat('Z2D_voltage', $this->Translate('Voltage'), '~Volt');
					$this->SetValue('Z2D_voltage', $Payload->voltage);
				}
				if (property_exists($Payload, 'valve')) {
					$this->RegisterVariableInteger('Z2D_valve', $this->Translate('Valve'), '~Intensity.255');
					$this->SetValue('Z2D_valve', $Payload->valve);
				}
				if (property_exists($Payload, 'current')) {
					if (!IPS_VariableProfileExists('Ampere.Z2D')) {
						IPS_CreateVariableProfile('Ampere.Z2D', 2);
						IPS_SetVariableProfileIcon('Ampere.Z2D', 'Electricity');
						IPS_SetVariableProfileText('Ampere.Z2D', '', ' A');
						IPS_SetVariableProfileDigits('Ampere.Z2D', 2);
					}

					$this->RegisterVariableFloat('Z2D_current', $this->Translate('Current'), 'Ampere.Z2D');

					$this->SetValue('Z2D_current', round($Payload->current / 1000 ,3));
				}
				if (property_exists($Payload, 'reachable')) {
					$this->SetReachable($Payload->reachable);
				}
				if (property_exists($Payload, 'battery')) {
					$this->RegisterVariableInteger('Z2D_Battery', $this->Translate('Battery'), '~Battery.100');
					$this->SetValue('Z2D_Battery', $Payload->battery);
				}
				if (property_exists($Payload, 'pm2_5')) {
					if (!IPS_VariableProfileExists('Airquality.Z2D')) {
						IPS_CreateVariableProfile('Airquality.Z2D', 1);
						IPS_SetVariableProfileIcon('Airquality.Z2D', 'Factory');
						IPS_SetVariableProfileAssociation('Airquality.Z2D',  0, $this->Translate('excellent'), '',-1);
						IPS_SetVariableProfileAssociation('Airquality.Z2D', 11, $this->Translate('good'), '',-1);
						IPS_SetVariableProfileAssociation('Airquality.Z2D', 21, $this->Translate('moderate'), '',-1);
						IPS_SetVariableProfileAssociation('Airquality.Z2D', 26, $this->Translate('poor'), '',-1);
						IPS_SetVariableProfileAssociation('Airquality.Z2D', 51, $this->Translate('unhealthy'), '',-1);
						IPS_SetVariableProfileAssociation('Airquality.Z2D', 76, $this->Translate('out of scale'), '',-1);
						IPS_SetVariableProfileAssociation('Airquality.Z2D', 65535, $this->Translate('unknown'), '',-1);
					}
					$this->RegisterVariableInteger('Z2D_airquality', $this->Translate('Airquality'), 'Airquality.Z2D');
					$this->SetValue('Z2D_airquality', $Payload->pm2_5);
				}
				if (property_exists($Payload, 'speed')) {
					if (!IPS_VariableProfileExists('Speed.Z2D')) {
						IPS_CreateVariableProfile('Speed.Z2D', 1);
						IPS_SetVariableProfileIcon('Speed.Z2D', 'Speedo');
						IPS_SetVariableProfileText('Speed.Z2D', '', ' %');
					}

					$this->RegisterVariableInteger('Z2D_speed', $this->Translate('Speed'), 'Speed.Z2D');
					$this->SetValue('Z2D_speed', $Payload->speed);
				}
				if (property_exists($Payload, 'targetdistance')) {
					if (!IPS_VariableProfileExists('Distance.Z2D')) {
						IPS_CreateVariableProfile('Distance.Z2D', 2);
						IPS_SetVariableProfileIcon('Distance.Z2D', 'Gauge');
						IPS_SetVariableProfileText('Distance.Z2D', '', ' m');
					}
					$this->RegisterVariableFloat('Z2D_targetdistance', $this->Translate('Distance to target'), 'Distance.Z2D');
					$this->SetValue('Z2D_targetdistance', $Payload->targetdistance / 100);
				}
				if (property_exists($Payload, 'deviceruntime')) {
					if (!IPS_VariableProfileExists('RunTime.Z2D')) {
						IPS_CreateVariableProfile('RunTime.Z2D', 1);
						IPS_SetVariableProfileIcon('RunTime.Z2D', 'Clock');
						IPS_SetVariableProfileText('RunTime.Z2D', '', ' min');
					}

					$this->RegisterVariableInteger('Z2D_deviceruntime', $this->Translate('Runtime').' '.$this->Translate('Device'), 'RunTime.Z2D');
					$this->SetValue('Z2D_deviceruntime', $Payload->deviceruntime);
				}
				if (property_exists($Payload, 'filterruntime')) {
					if (!IPS_VariableProfileExists('RunTime.Z2D')) {
						IPS_CreateVariableProfile('RunTime.Z2D', 1);
						IPS_SetVariableProfileIcon('RunTime.Z2D', 'Clock');
						IPS_SetVariableProfileText('RunTime.Z2D', '', ' min');
					}

					$this->RegisterVariableInteger('Z2D_filterruntime', $this->Translate('Runtime').' '.$this->Translate('Filter'), 'RunTime.Z2D');
					$this->SetValue('Z2D_filterruntime', $Payload->filterruntime);
				}
				if (property_exists($Payload, 'replacefilter')) {
					if (!IPS_VariableProfileExists('Replace.Z2D')) {
						IPS_CreateVariableProfile('Replace.Z2D', 0);
						IPS_SetVariableProfileIcon('Replace.Z2D', 'Repeat');
						IPS_SetVariableProfileAssociation('Replace.Z2D', 0, 'ok', '',-1);
						IPS_SetVariableProfileAssociation('Replace.Z2D', 0, $this->Translate('replace'), '',-1);
					}

					$this->RegisterVariableBoolean('Z2D_replacefilter', $this->Translate('Filter'), 'Replace.Z2D');
					$this->SetValue('Z2D_replacefilter', $Payload->replacefilter);
				}
			}
	
			if (property_exists($data, 'config')) {
				$Command = "/".$data->r."/".$data->uniqueid."/config";
				$Payload = $data->config;
				if (property_exists($Payload, 'battery')) {
					$this->RegisterVariableInteger('Z2D_Battery', $this->Translate('Battery'), '~Battery.100');
					$this->SetValue('Z2D_Battery', $Payload->battery);
				}
				if (property_exists($Payload, 'reachable')) {
					$this->SetReachable($Payload->reachable);
				}
				if (property_exists($Payload, 'temperature')) {
					$this->RegisterVariableFloat('Z2D_temperature', $this->Translate('Temperature'), '~Temperature.Room');
					$this->SetValue('Z2D_temperature', $Payload->temperature / 100.0);
				}
				if (property_exists($Payload, 'heatsetpoint')) {
					$this->RegisterVariableFloat('Z2D_heatsetpoint', $this->Translate('Heat Setpoint'), '~Temperature.Room');
					$this->SetCommandListEx('heatsetpoint', $Command, false);
					$this->EnableAction('Z2D_heatsetpoint');
					$this->SetValue('Z2D_heatsetpoint', $Payload->heatsetpoint / 100.0);
				}
				if (property_exists($Payload, 'offset')) {
					$this->RegisterVariableFloat('Z2D_offset', $this->Translate('Offset'), '~Temperature.Room');
					$this->SetCommandListEx('offset', $Command, false);
					$this->EnableAction('Z2D_offset');
					$this->SetValue('Z2D_offset', $Payload->offset / 100.0);
				}
				if (property_exists($Payload, 'displayflipped')) {
					if (!IPS_VariableProfileExists('Display.Z2D')) {
						IPS_CreateVariableProfile('Display.Z2D', 0);
						IPS_SetVariableProfileIcon('Display.Z2D','Repeat');
						IPS_SetVariableProfileAssociation('Display.Z2D', false, $this->Translate('normal'), '',-1);
						IPS_SetVariableProfileAssociation('Display.Z2D', true, $this->Translate('flipped'), '',-1);
					}
					$this->RegisterVariableBoolean('Z2D_displayflipped', $this->Translate('Display'), 'Display.Z2D');
					$this->SetCommandListEx('displayflipped', $Command, false);
					$this->EnableAction('Z2D_displayflipped');
					$this->SetValue('Z2D_displayflipped', $Payload->displayflipped);
				}
				if (property_exists($Payload, 'externalwindowopen')) {
					$this->RegisterVariableBoolean('Z2D_externalwindowopen', $this->Translate('Window'), '~Window');
					$this->SetCommandListEx('externalwindowopen', $Command, false);
					$this->EnableAction('Z2D_externalwindowopen');
					$this->SetValue('Z2D_externalwindowopen', $Payload->externalwindowopen);
				}
				if (property_exists($Payload, 'externalsensortemp')) {
					if(!IPS_VariableProfileExists("Temperature.Z2D")){
						IPS_CreateVariableProfile("Temperature.Z2D", 2);
						IPS_SetVariableProfileIcon("Temperature.Z2D", "Temperature");
						IPS_SetVariableProfileText("Temperature.Z2D", "", "°C");
						IPS_SetVariableProfileDigits("Temperature.Z2D", 1);
					}
					$this->RegisterVariableFloat('Z2D_externalsensortemp', $this->Translate('external Roomtemperature'), 'Temperature.Z2D');
					$this->SetCommandListEx('externalsensortemp', $Command, false);
					$this->EnableAction('Z2D_externalsensortemp');
					$this->SetValue('Z2D_externalsensortemp', $Payload->externalsensortemp / 100.0);
				}
				if (property_exists($Payload, 'delay')) {
					if (!IPS_VariableProfileExists('Delay.Z2D')) {
						IPS_CreateVariableProfile('Delay.Z2D', 1);
						IPS_SetVariableProfileIcon('Delay.Z2D','Hourglass');
						IPS_SetVariableProfileText('Delay.Z2D', '', ' s');
						IPS_SetVariableProfileValues('Delay.Z2D', 0, 65535, 1);
					}
					$this->RegisterVariableInteger('Z2D_delay', $this->Translate('Switch off Hesitation'), 'Delay.Z2D');
					$this->SetCommandListEx('delay', $Command, false);
					$this->EnableAction('Z2D_delay');
					$this->SetValue('Z2D_delay', $Payload->delay);
				}
				if (property_exists($Payload, 'sensitivitymax')) {
					$this->RegisterVariableInteger('Z2D_sensitivitymax', 'max. '.$this->Translate('Sensitivity'), '');
					$this->SetValue('Z2D_sensitivitymax', $Payload->sensitivitymax);
				}
				if (property_exists($Payload, 'sensitivity')) {
					if (!IPS_VariableProfileExists('Sensitivity.Z2D')) {
						IPS_CreateVariableProfile('Sensitivity.Z2D', 1);
						IPS_SetVariableProfileAssociation('Sensitivity.Z2D', 0, $this->Translate('Low'), '',-1);
						IPS_SetVariableProfileAssociation('Sensitivity.Z2D', 1, $this->Translate('Medium'), '',-1);
						IPS_SetVariableProfileAssociation('Sensitivity.Z2D', 2, $this->Translate('High'), '',-1);
					}
					if (property_exists($Payload, 'sensitivitymax') && $Payload->sensitivitymax == 2) {
						$this->RegisterVariableInteger('Z2D_sensitivity', $this->Translate('Sensitivity'), 'Sensitivity.Z2D');
					}else{
						$this->RegisterVariableInteger('Z2D_sensitivity', $this->Translate('Sensitivity'), '');
					}
					$this->SetCommandListEx('sensitivity', $Command, false);
					$this->EnableAction('Z2D_sensitivity');
					$this->SetValue('Z2D_sensitivity', $Payload->sensitivity);
				}
				if (property_exists($Payload, 'triggerdistance')) {
					$this->RegisterVariableInteger('Z2D_triggerdistance', $this->Translate('Triggerdistance'), '');
					$this->SetCommandListEx('triggerdistance', $Command, false);
					$this->EnableAction('Z2D_triggerdistance');
					$this->SetValue('Z2D_triggerdistance', $Payload->triggerdistance);
				}
				if (property_exists($Payload, 'fadingtime')) {
					$this->RegisterVariableInteger('Z2D_fadingtime', $this->Translate('Target disapperance delay time'), '');
					$this->SetCommandListEx('duration', $Command, false);
					$this->EnableAction('Z2D_fadingtime');
					$this->SetValue('Z2D_fadingtime', $Payload->fadingtime);
				}
				if (property_exists($Payload, 'mode')) {
					if (!IPS_VariableProfileExists('Mode.Z2D')) {
						IPS_CreateVariableProfile('Mode.Z2D', 1);
						IPS_SetVariableProfileIcon('Mode.Z2D', 'Intensity');
						IPS_SetVariableProfileAssociation('Mode.Z2D', 0, $this->Translate('Off'), '',-1);
						IPS_SetVariableProfileAssociation('Mode.Z2D', 1, $this->Translate('Auto'), '',-1);
						IPS_SetVariableProfileAssociation('Mode.Z2D', 2, $this->Translate('Level').' 1', '',-1);
						IPS_SetVariableProfileAssociation('Mode.Z2D', 3, $this->Translate('Level').' 2', '',-1);
						IPS_SetVariableProfileAssociation('Mode.Z2D', 4, $this->Translate('Level').' 3', '',-1);
						IPS_SetVariableProfileAssociation('Mode.Z2D', 5, $this->Translate('Level').' 4', '',-1);
						IPS_SetVariableProfileAssociation('Mode.Z2D', 6, $this->Translate('Level').' 5', '',-1);
					}

					$this->RegisterVariableInteger('Z2D_Mode', $this->Translate('Fanmode'), 'Mode.Z2D');
					$this->EnableAction('Z2D_Mode');
					$this->SetCommandListEx('mode', $Command, false);
					$mode = array_search($Payload->mode, array('off', 'auto', 'speed_1', 'speed_2', 'speed_3', 'speed_4', 'speed_5'));
					if($mode !== false) $this->SetValue('Z2D_Mode', $mode);
				}
			}
		}
	}

	#=====================================================================================
	private function SetReachable($reachable)
	#=====================================================================================
    {
		$this->WriteAttributeBoolean('reachable', $reachable);
		if(!$this->ReadPropertyBoolean('ShowReachable'))return;
		if (!IPS_VariableProfileExists('Reachable.Z2D')) {
			IPS_CreateVariableProfile('Reachable.Z2D', 0);
			IPS_SetVariableProfileAssociation("Reachable.Z2D", false, 'Offline', "", 0xFF0000);
			IPS_SetVariableProfileAssociation("Reachable.Z2D", true, 'Online', "", 0x00FF00);
		};
		$this->RegisterVariableBoolean('Z2D_Reachable', $this->Translate('reachable'), 'Reachable.Z2D');
		$this->SetValue('Z2D_Reachable', $reachable);
	}

	#=====================================================================================
	public function isReachable()
	#=====================================================================================
    {
		return $this->ReadAttributeBoolean('reachable');
	}
}
