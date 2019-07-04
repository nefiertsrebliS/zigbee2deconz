<?php

declare(strict_types=1);

trait Zigbee2DeCONZHelper
{
    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Z2D_Brightness':
				if($Value > 0){
	                $this->DimSet($Value);
				}else{
	                $this->SwitchMode(false);
				}
                break;
            case 'Z2D_State':
                $this->SwitchMode($Value);
                break;
            case 'Z2D_colormode':
                $this->SwitchColorMode($Value);
                break;
            case 'Z2D_Color':
                $this->setColor($Value);
                break;
            case 'Z2D_ColorTemperature':
                $this->setColorTemperature($Value);
                break;
            case 'Z2D_heatsetpoint':
                $this->setTemperature($Value);
                break;
            case 'Update':
                eval ('$this->'.$Value.";");
                break;
            default:
                $this->SendDebug('Request Action', 'No Action defined: ' . $Ident, 0);
                break;
        }
    }

    public function DimSet(int $Intensity)
    {
		if($Intensity < 0)$Intensity = 0;
		if($Intensity > 100)$Intensity = 100;
		$Intensity = round($Intensity * 2.55);
		if($Intensity == 0){
			$Payload = '{"on":false}';
		}else{
			$Payload = '{"on":true,"bri":'.$Intensity.'}';
		}
        $this->SetStateDeconz($Payload);
    }

    public function DimUp()
    {
		$Payload = '{"on":true,"bri_inc":254, "transitiontime":60}';
        $this->SetStateDeconz($Payload);
    }

    public function DimDown()
    {
		$Payload = '{"on":true,"bri_inc":-254, "transitiontime":60}';
        $this->SetStateDeconz($Payload);
    }

    public function DimStop()
    {
		$Payload = '{"bri_inc":0}';
        $this->SetStateDeconz($Payload);
    }

    public function DimSetEx(int $Intensity, int $Transitiontime)
    {
		if($Intensity < 0)$Intensity = 0;
		if($Intensity > 100)$Intensity = 100;
		$Intensity = round($Intensity * 2.55);
		if($Intensity == 0){
			$Payload = '{"bri":0, "transitiontime":'.$Transitiontime.',"on":false}';
		}else{
			$Payload = '{"on":true,"bri":'.$Intensity.', "transitiontime":'.$Transitiontime.'}';
		}
        $this->SetStateDeconz($Payload);
    }

    public function DimUpEx(int $Transitiontime)
    {
		$Payload = '{"on":true,"bri_inc":254, "transitiontime":'.$Transitiontime.'}';
        $this->SetStateDeconz($Payload);
    }

    public function DimDownEx(int $Transitiontime)
    {
		$Payload = '{"on":true,"bri_inc":-254, "transitiontime":'.$Transitiontime.'}';
        $this->SetStateDeconz($Payload);
    }

    public function SetColorTemperature(int $value)
    {
		if($value < 2000)$value = 2000;
		if($value > 6500)$value = 6500;
		$value = round($value * (500-153)/(2000-6500) + 654);
		$Payload = '{"on":true,"ct":'.$value.'}';
        $this->SetStateDeconz($Payload);
    }

    public function ColorTemperatureUp()
    {
		$Payload = '{"on":true,"ct_inc":400, "transitiontime":60}';
        $this->SetStateDeconz($Payload);
    }

    public function ColorTemperatureDown()
    {
		$Payload = '{"on":true,"ct_inc":-400, "transitiontime":60}';
        $this->SetStateDeconz($Payload);
    }

    public function ColorTemperatureStop()
    {
		$Payload = '{"ct_inc":0}';
        $this->SetStateDeconz($Payload);
    }

    public function SetColorTemperatureEx(int $value, int $Transitiontime)
    {
		if($value < 2000)$value = 2000;
		if($value > 6500)$value = 6500;
		$value = round($value * (500-153)/(2000-6500) + 654);
		$Payload = '{"on":true,"ct":'.$value.', "transitiontime":'.$Transitiontime.'}';
        $this->SetStateDeconz($Payload);
    }

    public function ColorTemperatureUpEx(int $Transitiontime)
    {
		$Payload = '{"on":true,"ct_inc":400, "transitiontime":'.$Transitiontime.'}';
        $this->SetStateDeconz($Payload);
    }

    public function ColorTemperatureDownEx(int $Transitiontime)
    {
		$Payload = '{"on":true,"ct_inc":-400, "transitiontime":'.$Transitiontime.'}';
        $this->SetStateDeconz($Payload);
    }

    public function SwitchColorMode(int $value)
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

    public function SwitchMode(bool $value)
    {
        switch ($value) {
            case true:
			    $Payload = '{"on": true}';
                break;
            case false:
			    $Payload = '{"on": false}';
                break;
        }
        $this->SetStateDeconz($Payload);
    }

    public function SetColor(int $color)
    {
        $RGB = $this->HexToRGB($color);
        $cie = $this->RGBToCIE($RGB[0], $RGB[1], $RGB[2]);
		$data['on'] = true;
		$data['xy'] = array($cie['x'], $cie['y']);
		$data['bri'] = $cie['bri'];
        $this->SetStateDeconz(json_encode($data));
    }

    public function setTemperature(float $value)
    {
        if($value <  6)$value =  6;
        if($value > 30)$value = 30;
		$data['heatsetpoint'] = $value;
        $this->SetStateDeconz(json_encode($data));
    }

	private function SetStateDeconz($Payload)
	{
	    $type = $this->ReadPropertyString('DeviceType');
	    $id = $this->ReadPropertyString("DeviceID");
	    $Buffer['command'] = $type.'/'.$id.'/state';
	    $Buffer['method'] = 'PUT';
	    $Buffer['data'] = $Payload;

	    $Data['DataID'] = '{875B91AC-45F1-9757-30F6-BF71445B2BDB}';
	    $Data['Buffer'] = json_encode($Buffer, JSON_UNESCAPED_SLASHES);

	    $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);
        $this->SendDebug('Sended', $DataJSON, 0);
	    $this->SendDataToParent($DataJSON);
    }

	private function GetStateDeconz()
	{
	    $type = $this->ReadPropertyString('DeviceType');
	    $id = $this->ReadPropertyString("DeviceID");
	    $Buffer['command'] = $type.'/'.$id;
	    $Buffer['method'] = 'GET';
	    $Buffer['data'] = "";

	    $Data['DataID'] = '{875B91AC-45F1-9757-30F6-BF71445B2BDB}';
	    $Data['Buffer'] = json_encode($Buffer, JSON_UNESCAPED_SLASHES);

	    $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);
        $this->SendDebug('Sended', $DataJSON, 0);
	    $this->SendDataToParent($DataJSON);
    }

	protected function getConfigDeconz()
	{
	    $Buffer['command'] = '';
	    $Buffer['method'] = 'GET';
	    $Buffer['data'] = "";

	    $Data['DataID'] = '{875B91AC-45F1-9757-30F6-BF71445B2BDB}';
	    $Data['Buffer'] = json_encode($Buffer, JSON_UNESCAPED_SLASHES);

	    $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);
	    $this->SendDataToParent($DataJSON);
    }

	private function sendDeconz($Payload)
	{
	    $Data['DataID'] = '{875B91AC-45F1-9757-30F6-BF71445B2BDB}';
	    $Data['Buffer'] = $Payload;

	    $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);
		$this->SendDebug("Sended", $DataJSON, 0);
		$result = $this->SendDataToParent($DataJSON);
        $this->SendDebug(__FUNCTION__, $result, 0);
		return($result);
    }

    protected function HexToRGB($value)
    {
        $RGB = array();
        $RGB[0] = (($value >> 16) & 0xFF);
        $RGB[1] = (($value >> 8) & 0xFF);
        $RGB[2] = ($value & 0xFF);
        $this->SendDebug('HexToRGB', 'R: ' . $RGB[0] . ' G: ' . $RGB[1] . ' B: ' . $RGB[2], 0);
        return $RGB;
    }

    protected function RGBToCIE($red, $green, $blue)
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

    protected function CieToDec($cie)
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

    protected function send2Variable($Payload)
    {
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
				$this->SetStatus(104);
			}
	    }
    }
}

