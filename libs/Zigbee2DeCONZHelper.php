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
		if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_Brightness',$Intensity);
		$Intensity = round($Intensity * 2.55);
		if($Intensity == 0){
			$Payload = '{"on":false}';
			if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_State',false);
		}else{
			$Payload = '{"on":true,"bri":'.$Intensity.'}';
			if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_State',true);
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
		if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_Brightness',$Intensity);
		$Intensity = round($Intensity * 2.55);
		if($Intensity == 0){
			$Payload = '{"bri":0, "transitiontime":'.$Transitiontime.',"on":false}';
			if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_State',false);
		}else{
			$Payload = '{"on":true,"bri":'.$Intensity.', "transitiontime":'.$Transitiontime.'}';
			if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_State',true);
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
		if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_ColorTemperature',$value);
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
		if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_ColorTemperature',$value);
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
		if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_colormode',$value);
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
		if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_State',$value);
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
		if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_Color',$value);
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
		if($this->ReadPropertyBoolean("Status"))$this->SetValue('Z2D_heatsetpoint',$value);
		$data['heatsetpoint'] = $value * 100;
        $this->SetStateDeconz(json_encode($data));
    }

	protected function SetStateDeconz($Payload)
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

	protected function GetStateDeconz()
	{
	    $type = $this->ReadPropertyString('DeviceType');
	    $id = $this->ReadPropertyString("DeviceID");
	    $Buffer['command'] = $type.'/'.$id;
	    $Buffer['method'] = 'GET';
	    $Buffer['data'] = "";

	    $Data['DataID'] = '{875B91AC-45F1-9757-30F6-BF71445B2BDB}';
	    $Data['Buffer'] = json_encode($Buffer, JSON_UNESCAPED_SLASHES);
	    $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);

	    $result['Buffer'] = $this->SendDataToParent($DataJSON);
		$this->ReceiveData(json_encode($result, JSON_UNESCAPED_SLASHES));
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
}

