<?
	require_once __DIR__ . '/../libs/Zigbee2DeCONZHelper.php';

	class DeconzConfig extends IPSModule
	{
	    use Zigbee2DeCONZHelper;

		public function Create() 
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterAttributeString('Elements', "");
			$this->RegisterAttributeString('Parent', "{9013F138-F270-C396-09D6-43368E390C5F}");

			//Connect to available deconz gateway
			$this->ConnectParent($this->ReadAttributeString('Parent'));
		}

		public function Destroy(){
		    //Never delete this line!
		    parent::Destroy();

		}
    
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
		}
		
		public function ReceiveData($JSONString)
		{
			$this->SendDebug("Receive from Device", $JSONString, 0);
			$payload = json_decode($JSONString);
			$data = json_decode($payload->Buffer);

			if(is_array($data))return;
		    if (!property_exists($data, 'config'))return;

#----------------------------------------------------------------
#	Search for created Devices
#----------------------------------------------------------------

			$Parent = $this->ReadAttributeString('Parent');
			$Devices = IPS_GetInstanceListByModuleType(3); 			// all Devices

			$Created = array();
			foreach ($Devices as $Device){
				$ParentID = IPS_GetInstance($Device)['ConnectionID'];
				if($ParentID > 0){
					if(IPS_GetInstance($ParentID)['ModuleInfo']['ModuleID'] == $Parent){
					    $Config = json_decode(IPS_GetConfiguration($Device));
					    if (property_exists($Config, 'DeviceID')) {
					        $Created[$Config->DeviceID] = $Device;
					    }
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

		    if (count($Values) == 0)return;

			$json = json_encode($Values);
#			$this->SendDebug("Values", $json, 0);
			$this->WriteAttributeString("Elements", $json);

#			$this->SendDebug("Devices", print_r(json_decode($this->ReadPropertyString("Devices")),true), 0);
		}
	 
		public function GetConfigurationForm() {
			$this->GetConfigDeconz();
#			$this->SendDebug("State",$this->State,0);
			IPS_Sleep(500);
			$Values = $this->ReadAttributeString("Elements");		
			$Values = json_decode($Values);	
			$this->SendDebug("Elements", json_encode($Values), 0);
	        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
	        $form['actions'][0]['values'] = $Values;
			return json_encode($form);
		}
			
	}
?>
