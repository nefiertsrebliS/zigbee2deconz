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
				    $this->SetStatus(205);
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
		        $this->send2Variable($data->state);
		    }
		}

    }
}
