<?php

class DeconzGateway extends IPSModule
{

#=====================================================================================
    public function Create()
#=====================================================================================
    {
        parent::Create();
        $this->RegisterPropertyString("URL", "http://my-DeCONZ-Server");
        $this->RegisterAttributeString("ApiKey", "");
        $this->RegisterPropertyInteger("SendPort", 80);
        $this->RegisterAttributeInteger("wsPort", 0);

		$this->RegisterTimer("Update", 600000,'Z2D_UpdateChildren($_IPS["TARGET"]);');        
    }

#=====================================================================================
    public function ApplyChanges()
#=====================================================================================
    {
        parent::ApplyChanges();
		if($this->CheckURL()===false)return;
		$this->ForceParent("{D68FD31F-0E90-7019-F16C-1949BD3079EF}");
    }
		
#=====================================================================================
    public function GetConfigurationForParent()
#=====================================================================================
    {
		if($this->CheckURL()===false)return;
        $Config['URL'] = "ws://".(string)parse_url($this->ReadPropertyString('URL'), PHP_URL_HOST);
		if($this->ReadAttributeInteger('wsPort') <> 0)$Config['URL'] .=":".$this->ReadAttributeInteger('wsPort');
        $Config['VerifyCertificate'] = false;
        return json_encode($Config);
    }

#=====================================================================================
    public function ReceiveData($JSONString)
#=====================================================================================
    {
        $data = json_decode($JSONString);
		$this->SendDebug("Received", $data->Buffer, 0);
		$message = json_decode($data->Buffer);
		if(property_exists($message, "r")){
			switch($message->r){
				case "lights":
					$JSON['DataID'] = '{C51A4B94-8195-4673-B78D-04D91D52D2DD}';
					break;
				case "sensors":
					$JSON['DataID'] = '{D7B089F0-6AFD-8861-2226-07B675D951B1}';
					break;
				case "groups":
					$JSON['DataID'] = '{24BE3EC7-6166-9E37-906E-A8286E97582E}';
					break;
				default:
					return;
			}
			$JSON['Buffer'] = $data->Buffer;
			$Data = json_encode($JSON);
			$this->SendDataToChildren($Data);
		}
    }

#=====================================================================================
    public function ForwardData($JSONString)

#	Leitet Aufträge der Clients an den DeCONZ-Server weiter.
#=====================================================================================
    {
        $Data = json_decode($JSONString);

        if ($Data->DataID == "{875B91AC-45F1-9757-30F6-BF71445B2BDB}"){
            return $this->SendToDeconz($Data->Buffer);
        }

		if ($Data->DataID == "{F51DECC3-17B8-C099-0EAF-A911EB2CDFB8}"){
            $result = $this->SendToDeconz($Data->Buffer);
			if($result === false)return false;
			$JSON['DataID'] = '{6871E068-2C89-B91C-8709-3133BBCCD5B2}';
			$JSON['Buffer'] = $result;
			$Data = json_encode($JSON);
			return $this->SendDataToChildren($Data)[0];
        }
    }

#=====================================================================================
    public function GetDeconzApiKey()

#	Erzeugt ein gültiges Key-Paar auf dem Server und in IPS.
#=====================================================================================
    {
		$Buffer['command'] = 'GetApiKey';
		$Buffer['method'] = 'POST';
		$Buffer['data'] = '{"devicetype":"ips"}';

		$response = $this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));
		$this->SendDebug("API-Key Response", $response, 0);

		if(!$response)return;
		
		foreach(json_decode($response) as $item){
			if(isset($item->success)){
				if(isset($item->success->username)){
					$API_Key	= $item->success->username;

					$this->WriteAttributeString("ApiKey", $API_Key);
					$this->SetStatus(102); 
					$this->SendDebug("API-Key", "set successfully", 0);
					$config = $this->GetDeconzConfiguration();

#-------------------------------------------------------------------------------------
#	veraltete Keys auf dem Server löschen
#-------------------------------------------------------------------------------------

					if (property_exists($config, 'whitelist')) {
						$whitelist = $config->whitelist;
						$key	= $this->ReadAttributeString("ApiKey");
						foreach($whitelist as $ApiKey => $item){
							if($item->name == "ips" && $ApiKey <> $key){
								$Buffer['command'] = 'config/whitelist/'.$ApiKey;
								$Buffer['method'] = 'DELETE';
								$Buffer['data'] = '';
								$this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));
							}
						}
					}
					$this->ApplyChanges();
				}
			}
		}
    }

#=====================================================================================
    public function UpdateChildren()

#	Holt die Konfiguration und schickt den jeweiligen Auszug an die Children weiter
#=====================================================================================
    {
		$Buffer['command'] = '';
		$Buffer['method'] = 'GET';
		$Buffer['data'] = '';

		$response = $this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));
		$this->SendDebug("UpdateChildren", $response, 0);

		$data = json_decode($response);
		if(!is_object($data))return;
        if (property_exists($data, 'lights')) {
			foreach ($data->lights as $item){
				$JSON['DataID'] = '{C51A4B94-8195-4673-B78D-04D91D52D2DD}';
				$JSON['Buffer'] = json_encode($item);
				$Data = json_encode($JSON);
				$this->SendDataToChildren($Data);
			}
        }
		
        if (property_exists($data, 'sensors')) {
			foreach ($data->sensors as $item){
				$JSON['DataID'] = '{D7B089F0-6AFD-8861-2226-07B675D951B1}';
				$JSON['Buffer'] = json_encode($item);
				$Data = json_encode($JSON);
				$this->SendDataToChildren($Data);
			}
        }
		
        if (property_exists($data, 'groups')) {
			foreach ($data->groups as $item){
				$JSON['DataID'] = '{24BE3EC7-6166-9E37-906E-A8286E97582E}';
				$new =  new stdClass();
		        $new->id = $item->id;
				$new->r = "groups";
				$new->scenes = $item->scenes;
				$new->state = $item->state;

				$JSON['Buffer'] = json_encode($new);
				$Data = json_encode($JSON);
				$this->SendDataToChildren($Data);
			}
        }
	}

#=====================================================================================
    protected function GetDeconzConfiguration()

#	Holt die Konfiguration und setzt, wenn erforderlich, den gültigen WebSocket-Port
#=====================================================================================
    {
		$Buffer['command'] = '';
		$Buffer['method'] = 'GET';
		$Buffer['data'] = '';

		$response = $this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));

		$config = json_decode($response);
        if (property_exists($config, 'config')) {
		    if (property_exists($config->config, 'websocketport')) {
				$wsPort		= $config->config->websocketport;
				if($wsPort <> $this->ReadAttributeInteger("wsPort")){
					$this->WriteAttributeInteger("wsPort", $wsPort);
					$this->ApplyChanges();
					$this->SendDebug("WebSocket", "set Port successfully", 0);
				}
		    }
			return $config->config;
        }
		
    }

#=====================================================================================
    private function CheckURL()

#	Sendet eine Prüfanfrage an den Server.
#	Die Auswertung der Antwort erfolgt in SendToDeConz.
#=====================================================================================
    {
		$Buffer['command'] = '';
		$Buffer['method'] = 'GET';
		$Buffer['data'] = '';
		return $this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));
    }

#=====================================================================================
    private function SendToDeconz($json)

#	Sendet alle Aufträge und Anfrage an den Server.
#	und setzt den Status des Gateways.
#=====================================================================================
    {
		$payload= json_decode($json);
		$command= $payload->command;
		$method	= $payload->method;
		$data	= $payload->data;

		$host	= parse_url($this->ReadPropertyString("URL"), PHP_URL_HOST);
		$port	= $this->ReadPropertyInteger("SendPort");
		if($port == 0){
			$this->SetStatus(104);
			return false;
		}

		if($command == "GetApiKey"){
			$url	= "http://".$host.":".$port."/api/";
		}else{
			$key	= $this->ReadAttributeString("ApiKey");
			$url	= "http://".$host.":".$port."/api/".$key."/".$command;
		}

		$curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_URL => $url,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		    CURLOPT_CUSTOMREQUEST => $method,
		    CURLOPT_POSTFIELDS => $data,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		$messages = json_decode($response);

		if($err) {
			$this->SetStatus(104);
			return false;
		}
		if(json_last_error() !== 0) {
			$this->SetStatus(104);
			return false;
		}

		foreach($messages as $message){
			if(!is_object($message))return $response;
			if(property_exists($message, "error")){
				switch($message->error->description){
					case "link button not pressed":
						$this->SetStatus(205);
						$this->SendDebug("Response", "link button not pressed", 0);
						return false;
					case "unauthorized user":
						$this->SetStatus(206);
						return false;
					default:
						$this->SetStatus(102);
						return $response;
				}
			}else{
				$this->SetStatus(102);
				return $response;
			}

		}
    }
}

