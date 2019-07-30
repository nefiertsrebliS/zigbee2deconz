<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/Zigbee2DeCONZHelper.php';

class Z2DGroup extends IPSModule
{
    use Zigbee2DeCONZHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{9013F138-F270-C396-09D6-43368E390C5F}');

        $this->RegisterPropertyString('DeviceID', "");
        $this->RegisterPropertyBoolean('Status', false);
        $this->RegisterPropertyString('DeviceType', "groups");
		$this->RegisterTimer("Update", 60000,'IPS_RequestAction($_IPS["TARGET"], "Update", "GetStateDeconz()");');        

		IPS_Sleep(100);
		@$this->ApplyChanges();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

		@$this->GetStateDeconz();
			
#		Filter setzen
		$this->SetReceiveDataFilter(".*id.*".$this->ReadPropertyString("DeviceID").".*r.*groups.*");
    }

    public function ReceiveData($JSONString)
    {
        $Buffer = json_decode($JSONString)->Buffer;
        $this->SendDebug('Received', utf8_decode($Buffer), 0);
        $data = json_decode(utf8_decode($Buffer));
		if(is_array($data)){
			foreach($data as $item){
				if (property_exists($item, 'error')) {
					echo "Device unreachable.";
					break;
				}else{
				    $this->SetStatus(102);
				}
			}
		}else{
		    if (property_exists($data, 'state')) {
				$Payload = $data->state;
				if (property_exists($Payload, 'all_on')) {
				    $this->RegisterVariableBoolean('Z2D_State', $this->Translate('all'), '~Switch', 0);
				    $this->EnableAction('Z2D_State');
				    SetValueBoolean($this->GetIDForIdent('Z2D_State'), $Payload->all_on);
				}
				if (property_exists($Payload, 'any_on')) {
				    $this->RegisterVariableBoolean('Z2D_AnyOn', $this->Translate('any on'), '~Switch', 0);
				    SetValueBoolean($this->GetIDForIdent('Z2D_AnyOn'), $Payload->any_on);
				}
		    }

		    if (property_exists($data, 'scenes')) {
				$Payload = $data->scenes;
				if(count($Payload) > 0){
					$this->RegisterVariableInteger('Z2D_Scene', $this->Translate('Scene'), 'Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 5);
					$this->EnableAction('Z2D_Scene');
					if (!IPS_VariableProfileExists('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D')) {
						IPS_CreateVariableProfile ('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 1);
						IPS_SetVariableProfileIcon('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', 'Bulb');
					}
					foreach($Payload as $scene){
						$this->SendDebug("Scene",$scene->name,0);
						IPS_SetVariableProfileAssociation('Scenes.'.$this->ReadPropertyString('DeviceID').'.Z2D', $scene->id, $scene->name, '',-1);
					}
				}
		    }
		}

    }
}
