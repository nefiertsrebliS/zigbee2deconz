<?php

declare(strict_types=1);

trait DeconzHelper
{
#=====================================================================================
    public function RequestAction($Ident, $Value)
#=====================================================================================
    {
        $IdentArray = explode("_",$Ident);
        switch ($IdentArray[1]) {
            case 'Brightness':
				if($Value > 0){
	                $this->DimSet($Value);
				}else{
	                $this->SwitchMode(false);
				}
                break;
            case 'State':
                $this->SwitchMode($Value);
                break;
            case 'colormode':
                $this->SwitchColorMode($Value);
                break;
            case 'Color':
                $this->setColor($Value);
                break;
            case 'ColorTemperature':
                $this->setColorTemperature($Value);
                break;
            case 'heatsetpoint':
                $this->setTemperature($Value);
                break;
            case 'offset':
                $this->setOffset($Value);
                break;
            case 'delay':
                $this->setDelay($Value);
                break;
            case 'sensitivity':
                $this->setSensitivity($Value);
                break;
            case 'Update':
                eval ('$this->'.$Value.";");
                break;
            case 'Scene':
                $this->SwitchScene($Value);
                break;
            case 'Alert':
                $this->SwitchAlert($Value);
                break;
            case 'Fanmode':
                $this->setFanmode($Value);
                break;
            default:
                $this->SendDebug('Request Action', 'No Action defined: ' . $Ident, 0);
                break;
        }
    }

#=====================================================================================
    public function DimSet(int $Intensity)
#=====================================================================================
    {
		if($Intensity < 0)$Intensity = 0;
		if($Intensity > 100)$Intensity = 100;
		$Intensity = round($Intensity * 2.55);
		if($Intensity == 0){
			$Payload = '{"on":false}';
		}else{
			$Payload = '{"on":true,"bri":'.$Intensity.'}';
		}
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function DimUp()
#=====================================================================================
    {
		$Payload = '{"on":true,"bri_inc":254, "transitiontime":60}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function DimDown()
#=====================================================================================
    {
		$Payload = '{"on":true,"bri_inc":-254, "transitiontime":60}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function DimStop()
#=====================================================================================
    {
		$Payload = '{"on":true,"bri_inc":0}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function DimSetEx(int $Intensity, int $Transitiontime)
#=====================================================================================
    {
		if($Intensity < 0)$Intensity = 0;
		if($Intensity > 100)$Intensity = 100;
		$Intensity = round($Intensity * 2.55);
		if($Intensity == 0){
			$Payload = '{"bri":0, "transitiontime":'.$Transitiontime.',"on":false}';
		}else{
			$Payload = '{"on":true,"bri":'.$Intensity.', "transitiontime":'.$Transitiontime.'}';
		}
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function DimUpEx(int $Transitiontime)
#=====================================================================================
    {
		$Payload = '{"on":true,"bri_inc":254, "transitiontime":'.$Transitiontime.'}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function DimDownEx(int $Transitiontime)
#=====================================================================================
    {
		$Payload = '{"on":true,"bri_inc":-254, "transitiontime":'.$Transitiontime.'}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function SetColorTemperature(int $value)
#=====================================================================================
    {
		if($value < 2000)$value = 2000;
		if($value > 6500)$value = 6500;
		$value = round($value * (500-153)/(2000-6500) + 654);
		$Payload = '{"on":true,"ct":'.$value.'}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function ColorTemperatureUp()
#=====================================================================================
    {
		$Payload = '{"on":true,"ct_inc":400, "transitiontime":60}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function ColorTemperatureDown()
#=====================================================================================
    {
		$Payload = '{"on":true,"ct_inc":-400, "transitiontime":60}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function ColorTemperatureStop()
#=====================================================================================
    {
		$Payload = '{"on":true,"ct_inc":0}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function SetColorTemperatureEx(int $value, int $Transitiontime)
#=====================================================================================
    {
		if($value < 2000)$value = 2000;
		if($value > 6500)$value = 6500;
		$value = round($value * (500-153)/(2000-6500) + 654);
		$Payload = '{"on":true,"ct":'.$value.', "transitiontime":'.$Transitiontime.'}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function ColorTemperatureUpEx(int $Transitiontime)
#=====================================================================================
    {
		$Payload = '{"on":true,"ct_inc":400, "transitiontime":'.$Transitiontime.'}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function ColorTemperatureDownEx(int $Transitiontime)
#=====================================================================================
    {
		$Payload = '{"on":true,"ct_inc":-400, "transitiontime":'.$Transitiontime.'}';
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function SwitchColorMode(int $value)
#=====================================================================================
    {
        switch ($value) {
            case 1:
			    $this->SetColorTemperature($this->GetValue('Z2D_ColorTemperature'));
				IPS_SetHidden($this->GetIDForIdent('Z2D_ColorTemperature'), false);
				IPS_SetHidden($this->GetIDForIdent('Z2D_Color'), true);
                break;
            case 2:
			    $this->SetColor($this->GetValue('Z2D_Color'));
				IPS_SetHidden($this->GetIDForIdent('Z2D_ColorTemperature'), true);
				IPS_SetHidden($this->GetIDForIdent('Z2D_Color'), false);
                break;
        }
    }

#=====================================================================================
    public function SwitchMode(bool $value)
#=====================================================================================
    {
        switch ($value) {
            case true:
			    $Payload = '{"on": true}';
                break;
            case false:
			    $Payload = '{"on": false}';
                break;
        }
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function SwitchScene(int $value)
#=====================================================================================
    {
        $CommandList = json_decode($this->ReadAttributeString('CommandList'));
        if(json_last_error() !== 0 ){
            $this->SendDebug("SwitchScene", "unknown Command", 0); 
            $this->GetStateDeconz();
            return;
        }
        if(!property_exists($CommandList, 'scene')){
            $this->SendDebug("SwitchScene", "unknown Command", 0); 
            $this->GetStateDeconz();
            return;
        }
        $this->SendParent($CommandList->scene.$value.'/recall', 'PUT', '');
    }

#=====================================================================================
    public function SwitchSceneByName(string $name)
#=====================================================================================
    {
        $Assotiations = IPS_GetVariableProfile('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D')["Associations"];
        $key = array_search($name, array_column($Assotiations, 'Name'));
        if($key !== false){
            $this->SwitchScene(intval($Assotiations[$key]['Value']));
        }else{            
            echo "Z2D_SwitchSceneByName: unknown Scene"; 
        }
    }

#=====================================================================================
    private function GetScenes(string $command)
#=====================================================================================
    {
        $Scenes = json_decode($this->SendParent($command, 'GET', ''));
        if(is_null($Scenes)){
            $this->UnregisterVariable('Z2D_Scene');
            if (IPS_VariableProfileExists('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D'))IPS_DeleteVariableProfile('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D');
            return false;
        }

        if (!IPS_VariableProfileExists('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D')) {
            IPS_CreateVariableProfile ('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 1);
            IPS_SetVariableProfileIcon('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 'Bulb');
        }
        $this->RegisterVariableInteger('Z2D_Scene', $this->Translate('Scene'), 'Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 20);
        $this->EnableAction('Z2D_Scene');

#-------------------------------------------------------------------------------------
#	obsolete Scenen im Profil löschen oder geänderte Namen anpassen
#-------------------------------------------------------------------------------------
				
        $Assotiations = IPS_GetVariableProfile('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D')["Associations"];
        foreach($Assotiations as $Assotiation){
            $num = "".$Assotiation['Value'];
            if (property_exists($Scenes, $num)) {
                if($Scenes->$num->name != $Assotiation['Name']){
                    IPS_SetVariableProfileAssociation('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', $num, $Scenes->$num->name, '',-1);
                }
            }else{
                IPS_SetVariableProfileAssociation('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', $num, '', '',-1);
            }
        }

#-------------------------------------------------------------------------------------
#	neue Scenen ins Profil schreiben 
#-------------------------------------------------------------------------------------

        foreach($Scenes as $id=>$Scene){
            $key = array_search($id, array_column($Assotiations, 'Value'));
            if($key === false || $Assotiations[$key]['Name'] != $Scene->name){
                IPS_SetVariableProfileAssociation('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', $id, $Scene->name, '',-1);
            }
        }

        return true;
    }

#=====================================================================================
    public function SwitchAlert(int $value)
#=====================================================================================
    {
        $data['alert'] = array('none', 'select', 'lselect')[$value];
        $Payload = json_encode($data);
        $this->SetDeconz($Payload);
    }

#=====================================================================================
    public function SetColor(int $color)
#=====================================================================================
    {
        $RGB = $this->HexToRGB($color);
        $cie = $this->RGBToCIE($RGB[0], $RGB[1], $RGB[2]);
		$data['on'] = true;
		$data['xy'] = array($cie['x'], $cie['y']);
		$data['bri'] = $cie['bri'];
        $this->SetDeconz(json_encode($data));
    }

#=====================================================================================
    public function SetColorEx(int $color, int $Transitiontime)
#=====================================================================================
    {
        $RGB = $this->HexToRGB($color);
        $cie = $this->RGBToCIE($RGB[0], $RGB[1], $RGB[2]);
		$data['on'] = true;
		$data['xy'] = array($cie['x'], $cie['y']);
		$data['bri'] = $cie['bri'];
		$data['transitiontime'] = $Transitiontime;
        $this->SetDeconz(json_encode($data));
    }

#=====================================================================================
    public function setTemperature(float $value)
#=====================================================================================
    {
        if($value <  6)$value =  6;
        if($value > 30)$value = 30;
		$this->SetValue('Z2D_heatsetpoint',$value);
		$this->SetConfig('heatsetpoint', (string) ($value * 100));
    }    

#=====================================================================================
    public function setSensitivity(int $value)
#=====================================================================================
    {
		if(!$this->GetIDForIdent("Z2D_sensitivitymax")){
		    if($value < 0) $value = 0;
		    if($value > 2) $value = 2;
		}else{
			$max = $this->GetValue('Z2D_sensitivitymax');
		    if($value < 0) $value = 0;
		    if($value > $max) $value = $max;
		}
		$data['sensitivity'] = $value;
        $this->SetDeconz(json_encode($data));
    }

#=====================================================================================
    public function setFanmode(int $value)
#=====================================================================================
    {
        if($value < 0) $value = 0;
        if($value >  4) $value =  4;
		$this->SetValue('Z2D_Fanmode',$value);
		$data['fanmode'] = array('off', 'auto', 'low', 'medium', 'high')[$value];
        $this->SetDeconz(json_encode($data));
    }

#=====================================================================================
    public function setOffset(float $value)
#=====================================================================================
    {
        if($value < -5) $value = -5;
        if($value >  5) $value =  5;
		$this->SetValue('Z2D_offset',$value);
		$this->SetConfig('offset', (string) ($value * 100));
    }

#=====================================================================================
    public function setDelay(int $value)
#=====================================================================================
    {
        if($value <   0)$value =  0;
        if($value > 65535)$value = 65535;
		$this->SetValue('Z2D_delay',$value);
		$this->SetConfig('delay', (string) ($value));
    }

#=====================================================================================
    public function SetConfig(string $parameter, string $value)
#=====================================================================================
    {
        $config = $this->GetConfig();
        if($config === false)return false;
        if(property_exists(json_decode($config), $parameter)){
            if(is_numeric(str_replace(",",".", $value))){
                $value = (float)str_replace(",",".", $value);
            }
            $id = $this->ReadPropertyString("DeviceID");
            $data[$parameter] = $value;
            $this->SendParent('sensors/'.$id.'/config', 'PUT', json_encode($data));
        }else{
            $this->SendDebug("SetConfig", "Parameter is not valid for this Instance", 0);
            return false;
        }
    }

#=====================================================================================
    public function GetConfig()
#=====================================================================================
    {
	    $id = $this->ReadPropertyString("DeviceID");
        $response = $this->SendParent('sensors/'.$id, 'GET', '');
		if(!$response)return(false);
        $data = json_decode($response);
        if(json_last_error() !== 0 )return(false);
        if(is_array($data)) return false;
        if (property_exists($data, 'config')) {
            $config = $data->config;
            return(json_encode($config));
        }else{
            return(false);
        }
    }

#=====================================================================================
    public function setAlert(string $value)
#=====================================================================================
    {
        if($value == "none" || $value == "select" || $value == "lselect"){
			$data['alert'] = $value;
		    $this->SetDeconz(json_encode($data));
        }else{
	        trigger_error('no valid Attribute', E_USER_NOTICE);
		}
    }

#=====================================================================================
    public function setColorloop(int $value)
#=====================================================================================
    {
        if($value <   0)$value =  0;
        if($value > 255)$value = 255;
		if($value ==  0){
			$data['effect'] = "none";
		}else{
			$data['on'] = true;
			$data['colorloopspeed'] = $value;
			$data['effect'] = "colorloop";
		}
	    $this->SetDeconz(json_encode($data));
    }

#=====================================================================================
    public function setJson(string $value)
#=====================================================================================
    {
        json_decode($value);
        if(json_last_error() == JSON_ERROR_NONE){
		    $this->SetDeconz($value);
        }else{
	        trigger_error('no valid json-String', E_USER_NOTICE);
		}
    }

#=====================================================================================
    protected function SetDeconz($Payload)
#=====================================================================================
    {
        $command = array_key_first(json_decode($Payload, true));
        $CommandList = json_decode($this->ReadAttributeString('CommandList'));
        if(json_last_error() !== 0 ){
            $this->SendDebug("SetDeconz", "unknown Command", 0); 
            $this->GetStateDeconz();
            return;
        }
        if(!property_exists($CommandList, $command)){
            $this->SendDebug("SetDeconz", "unknown Command", 0); 
            $this->GetStateDeconz();
            return;
        }
        $Result = $this->SendParent($CommandList->$command, 'PUT', $Payload);

		if(!$Result){
			$this->LogMessage($this->Translate("Instance")." #".$this->InstanceID.": Gateway-Server-Error",KL_ERROR);
			return;
		}

		$messages = json_decode($Result);
		foreach($messages as $message){
			if(property_exists($message, "error")){
#				$this->LogMessage($this->Translate("Instance")." #".$this->InstanceID.": ".$message->error->description,KL_ERROR);
                echo $message->error->description.chr(10);
			}
		}
    }

#=====================================================================================
    protected function GetStateDeconz()
#=====================================================================================
	{
        $DeviceID = $this->ReadPropertyString('DeviceID');
        if($DeviceID == '')return(false);
        $IDtype = (strstr($DeviceID, ":") !== false)?"uniqueid":"id";
        $response = $this->SendParent('', 'GET', '');
		if(!$response)return(false);
        $data = json_decode($response);
        foreach($data as $type => $items){
            foreach($items as $item){
                if(!@property_exists($item, $IDtype))continue;
                $ModuleID = IPS_GetInstance($this->InstanceID)['ModuleInfo']['ModuleID'];
                switch($ModuleID){
                    case "{6BC9ED7D-742A-4909-BDEB-6AD27B1F1A3E}":
                        if(strstr($item->$IDtype, $DeviceID) !== false){
                            $item->r = $type;
                            $result['Buffer'] = json_encode($item);
                            $this->ReceiveData(json_encode($result, JSON_UNESCAPED_SLASHES));
                        }
                    default:
                        if($item->$IDtype == $DeviceID){
                            if($type == "groups"){
                                if(count($item->lights) > 0)$index = $item->lights[0];
                                foreach($item->lights as $key=>$light){
                                    $item->lights[$key] = $data->lights->$light->uniqueid;
                                }
                            }
                            $item->r = $type;
                            $result['Buffer'] = json_encode($item);
                            $this->ReceiveData(json_encode($result, JSON_UNESCAPED_SLASHES));
                            if($type == "groups"){
                                if(count($item->lights) > 0){
                                    $data->lights->$index->r = "lights";
                                    $result['Buffer'] = json_encode($data->lights->$index);
                                    $this->ReceiveData(json_encode($result, JSON_UNESCAPED_SLASHES));
                                }
                            }
                        }
                }
            }
        }
    }


#=====================================================================================
    protected function SendParent(string $command, string $method, string $data)
#=====================================================================================
	{
        $Buffer['command'] = $command;
	    $Buffer['method'] = $method;
	    $Buffer['data'] = $data;

	    $Data['DataID'] = '{875B91AC-45F1-9757-30F6-BF71445B2BDB}';
	    $Data['Buffer'] = json_encode($Buffer, JSON_UNESCAPED_SLASHES);
	    $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);

        $this->SendDebug('Sended', $DataJSON, 0);
	    $response = $this->SendDataToParent($DataJSON);
        $this->SendDebug("Response", $response,0);

        if(IPS_GetInstance($this->InstanceID)['ModuleInfo'] ['ModuleID'] != '{D5D510EA-0158-B850-A700-AA824AF59DC3}'){
            if(strpos($response, 'success') !== false){
                $this->SetReachable(true);
            }elseif(strpos($response, 'not reachable') !== false){
                $this->SetReachable(false);
            }
        }

        return $response;
    }

#=====================================================================================
    protected function HexToRGB($value)
#=====================================================================================
    {
        $RGB = array();
        $RGB[0] = (($value >> 16) & 0xFF);
        $RGB[1] = (($value >> 8) & 0xFF);
        $RGB[2] = ($value & 0xFF);
        $this->SendDebug('HexToRGB', 'R: ' . $RGB[0] . ' G: ' . $RGB[1] . ' B: ' . $RGB[2], 0);
        return $RGB;
    }

#=====================================================================================
    protected function RGBToCIE($red, $green, $blue)
#=====================================================================================
    {
        $cie = array();
		$cie['bri'] = max($red, $green, $blue);

        $red = ($red > 0.5) ? pow($red, 2.4) : 1;
        $green = ($green > 0.5) ? pow($green, 2.4) : 1;
        $blue = ($blue > 0.5) ? pow($blue, 2.4) : 1;

        $X = $red * 0.664511 + $green * 0.154324 + $blue * 0.162028;
        $Y = $red * 0.283881 + $green * 0.668433 + $blue * 0.047685;
        $Z = $red * 0.000088 + $green * 0.072310 + $blue * 0.986039;

        $cie['x'] = round(($X / ($X + $Y + $Z)), 4);
        $cie['y'] = round(($Y / ($X + $Y + $Z)), 4);

        return $cie;
    }

#=====================================================================================
    protected function CieToDec($cie)
#=====================================================================================
    {
        $cie['z'] = 1 - $cie['x'] - $cie['y'];

        $red      = $cie['x'] *  1.65649 + $cie['y'] * -0.35485 + $cie['z'] * -0.25504;
        $green    = $cie['x'] * -0.7072  + $cie['y'] *  1.6554  + $cie['z'] *  0.03615;
        $blue     = $cie['x'] *  0.05171 + $cie['y'] * -0.12137 + $cie['z'] *  1.01153;

        $korr     = pow($cie['bri'],2.4)/max($red, $green, $blue);

        $red      = ($red   > 0) ? round(pow($red  *$korr, 1/2.4)) : 0;
        $green    = ($green > 0) ? round(pow($green*$korr, 1/2.4)) : 0;
        $blue     = ($blue  > 0) ? round(pow($blue *$korr, 1/2.4)) : 0;
        $this->SendDebug('CieToDec(RGB)', 'R: ' . $red . ' G: ' . $green . ' B: ' . $blue, 0);

		$hexred	  = (strlen(dechex($red))<2) ? "0".dechex($red) : dechex($red);
		$hexgreen = (strlen(dechex($green))<2) ? "0".dechex($green) : dechex($green);
		$hexblue  = (strlen(dechex($blue))<2) ? "0".dechex($blue) : dechex($blue);

        $dec      = hexdec($hexred.$hexgreen.$hexblue);

        return $dec;
    }
}

