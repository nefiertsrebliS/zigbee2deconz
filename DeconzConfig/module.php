<?php
	class DeconzConfig extends IPSModule
	{

#=====================================================================================
		public function Create() 
#=====================================================================================
		{
			//Never delete this line!
			parent::Create();
	        $this->RegisterAttributeString("elements", "");

			//Connect to available deconz gateway
			$this->ConnectParent("{9013F138-F270-C396-09D6-43368E390C5F}");
		}

#=====================================================================================
		public function Destroy(){
#=====================================================================================
		    //Never delete this line!
		    parent::Destroy();

		}
    
#=====================================================================================
		public function ApplyChanges()
#=====================================================================================
		{
			//Never delete this line!
			parent::ApplyChanges();
		}

#=====================================================================================
	    public function ReceiveData($JSONString)
#=====================================================================================
		{
		    $data = json_decode($JSONString);
			$this->SendDebug("ReceiveData", $data->Buffer, 0);
		    $this->WriteAttributeString("elements", $data->Buffer);
			return true;
		}
		
#=====================================================================================
		private function GetElements()
#=====================================================================================
		{
			$Buffer['command'] 	= '';
			$Buffer['method'] 	= 'GET';
			$Buffer['data'] 	= '';

			$Data['DataID'] 	= '{F51DECC3-17B8-C099-0EAF-A911EB2CDFB8}';
			$Data['Buffer'] 	= json_encode($Buffer, JSON_UNESCAPED_SLASHES);

			$result	= $this->SendDataToParent(json_encode($Data, JSON_UNESCAPED_SLASHES));
		    if (!$result)return;
			$data = json_decode($this->ReadAttributeString("elements"));
			if(json_last_error() !== 0 || !property_exists($data, 'config')){
				$this->LogMessage($this->Translate("Instance")." #".$this->InstanceID.": ".$this->Translate("Received Data unreadable"),KL_ERROR);
				return;
			}

#----------------------------------------------------------------
#	Search for created Devices
#----------------------------------------------------------------

			$GatewayID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
			$Devices = IPS_GetInstanceListByModuleType(3); 			// all Devices

			$Created = array();
			foreach ($Devices as $Device){
				$ParentID = IPS_GetInstance($Device)['ConnectionID'];
                if($ParentID == $GatewayID){
					    $Config = json_decode(IPS_GetConfiguration($Device));
					    if (property_exists($Config, 'DeviceID')) {
					        $Created[$Config->DeviceID] = $Device;
					    }
                }
			}

#----------------------------------------------------------------
#	Built Array for Configurator
#----------------------------------------------------------------

			$Values = array();		
		    $type = 'lights';
		    if (property_exists($data, $type)) {
				$items = $data->$type;
				foreach($items as $item){
					$ID	= 0;
					if(isset($Created[$item->uniqueid])) $ID = $Created[$item->uniqueid];
					$Values[] = [
						'instanceID' => $ID,
						'name'       => $item->name,
						'DeviceID'   => $item->uniqueid,
						'Manufacturer'   => $item->manufacturername,
						'modelID'   => $item->modelid,
						'DetailType'   => $item->type,
						'DeviceType' => $type,
						'create'	 => 
						[
							"moduleID" => "{309E76BB-9027-24A8-FACE-FC45D198C1CD}",
							"configuration" => [
								"DeviceID" => $item->uniqueid,
								"DeviceType" => $type
							]
						]
					];
				}
			}

		    $type = 'sensors';
		    if (property_exists($data, $type)) {
				$items = $data->$type;
				foreach($items as $item){
					$ID	= 0;
					if(isset($Created[$item->uniqueid])) $ID = $Created[$item->uniqueid];
					$Values[] = [
						'instanceID' => $ID,
						'name'       => $item->name,
						'DeviceID'   => $item->uniqueid,
						'Manufacturer'   => $item->manufacturername,
						'modelID'   => $item->modelid,
						'DetailType'   => $item->type,
						'DeviceType' => $type,
						'create'	 => 
						[
							"moduleID" => "{60F3A8DF-5953-4B9E-CB5A-EF7769E3C9FA}",
							"configuration" => [
								"DeviceID" => $item->uniqueid,
								"DeviceType" => $type
							]
						]
					];
				}
			}

		    $type = 'groups';
		    if (property_exists($data, $type)) {
				$items = $data->$type;
				foreach($items as $item){
					$ID	= 0;
					if(isset($Created[$item->id])) $ID = $Created[$item->id];
					$Values[] = [
						'instanceID' => $ID,
						'name'       => $item->name,
						'DeviceID'   => $item->id,
						'Manufacturer'   => "",
						'modelID'   => "",
						'DetailType'   => $item->type,
						'DeviceType' => $type,
						'create'	 => 
						[
							"moduleID" => "{D5D510EA-0158-B850-A700-AA824AF59DC3}",
							"configuration" => [
								"DeviceID" => $item->id,
								"DeviceType" => $type
							]
						]
					];
				}
			}

			return json_encode($Values);
		}
	 
#=====================================================================================
		public function GetConfigurationForm() {
#=====================================================================================
			$Values = json_decode($this->GetElements());	
			$this->SendDebug("Elements", json_encode($Values), 0);
	        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
	        $form['actions'][0]['values'] = $Values;
			return json_encode($form);
		}
			
	}
