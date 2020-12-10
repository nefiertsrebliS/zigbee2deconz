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
        $this->RegisterPropertyInteger('BrightnessID', 0);
#	-----------------------------------------------------------------------------------
        $this->RegisterAttributeInteger("State", 0);

    }

#================================================================================================
#	ApplyChanges
#================================================================================================

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->RegisterMessage($this->InstanceID, IM_CHANGESTATUS);
        $this->RegisterMessage(@IPS_GetInstance($this->InstanceID)['ConnectionID'], IM_CHANGESTATUS);

		if(@$this->ReadPropertyInteger('BrightnessID') > 0){
			$this->BrightnessID = $this->ReadPropertyInteger('BrightnessID');
	        $this->RegisterMessage (@$this->ReadPropertyInteger('BrightnessID'), VM_UPDATE);
		    $this->RegisterVariableInteger('Z2D_Brightness', $this->Translate('Brightness'), '~Intensity.100', 30);
		    $this->EnableAction('Z2D_Brightness');
		}

		@$this->GetStateDeconz();
			
#		Filter setzen
		$this->SetReceiveDataFilter('.*'.preg_quote('\"id\":\"').$this->ReadPropertyString("DeviceID").preg_quote('\",\"r\":\"groups\"').'.*');
    }

#================================================================================================
#	MessageSink
#================================================================================================

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message) {
            case IM_CHANGESTATUS:
				if($SenderID == @IPS_GetInstance($this->InstanceID)['ConnectionID']){
					if($Data[0] >= 200)$Data[0] = 215;
					$state = max($Data[0], $this->ReadAttributeInteger("State"));
					if($state <> $this->GetStatus())$this->SetStatus($state);
				}
				if($SenderID == $this->InstanceID){
					if($Data[0] == 102) $this->GetStateDeconz();
				}
                break;
            case VM_UPDATE:
				if($SenderID == $this->ReadPropertyInteger('BrightnessID')){
				    $this->SetValue('Z2D_Brightness', $Data[0]);
				}else{
					UnregisterMessage($SenderID, "VM_UPDATE");
				}
                break;
        }
    }

#================================================================================================
#	ReceiveData
#================================================================================================

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
			if (property_exists($Payload, 'all_on')) {
			    $this->RegisterVariableBoolean('Z2D_State', $this->Translate('all'), '~Switch', 0);
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
    }
}
