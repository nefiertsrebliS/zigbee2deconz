<?php

require_once(__DIR__ . "/../libs/NetworkTraits.php");
require_once(__DIR__ . "/../libs/WebsocketClass.php");  // diverse Klassen

/*
	modifizierter WebSocket-Client auf Basis von IPSNetwork von Michael Tröger
	https://github.com/Nall-chan/IPSNetwork
 */

class DeconzGateway extends IPSModule
{
    use DebugHelper,
        InstanceStatus,
        BufferHelper;

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
#        $this->RegisterPropertyBoolean("Open", false);
        $this->RegisterPropertyString("URL", "http://my-DeCONZ-Server");
        $this->RegisterAttributeString("ApiKey", "");
        $this->RegisterPropertyInteger("SendPort", 80);
        $this->RegisterAttributeInteger("wsPort", 0);

#	Die folgenden Properties sind aktuell nicht modifizierbar oder werden nicht genutzt
        $this->RegisterPropertyInteger("Version", 13);
        $this->RegisterPropertyString("Protocol", "");
        $this->RegisterPropertyString("Origin", "");
        $this->RegisterPropertyInteger("PingInterval", 0);
        $this->RegisterPropertyString("PingPayload", "");
        $this->RegisterPropertyInteger("Frame", WebSocketOPCode::text);
        $this->RegisterPropertyBoolean("BasisAuth", false);
        $this->RegisterPropertyString("Username", "");
        $this->RegisterPropertyString("Password", "");
#	-----------------------------------------------------------------------------------
        $this->Buffer = '';
        $this->State = WebSocketState::unknow;
        $this->WaitForPong = false;
        $this->TLSBuffers = array();
        $this->UseTLS = false;
        $this->RegisterTimer('KeepAlive', 0, 'Z2D_Keepalive($_IPS[\'TARGET\']);');
        $this->OldURL = '';
		$this->RegisterTimer("Update", 600000,'Z2D_UpdateChildren($_IPS["TARGET"]);');        

    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        if ($this->State == WebSocketState::init) {
            return;
        }
        switch ($Message) {
            case IPS_KERNELMESSAGE:
                if ($Data[0] != KR_READY) {
                    break;
                }
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
            case IPS_KERNELSHUTDOWN:
                $this->SendDisconnect();
                break;
            case FM_DISCONNECT:
                $this->RegisterParent();
                $this->State = WebSocketState::unknow; // zum abmelden ist es schon zu spät, da Verbindung weg ist.
                break;
            case FM_CONNECT:
                $this->ForceRefresh();
                break;
            case IM_CHANGESTATUS:
                if ($SenderID == $this->ParentID) {
					switch($Data[0]){
						case IS_ACTIVE:
							$this->SetStatus($Data[0]);
	                        $this->ForceRefresh();
							break;
						case IS_INACTIVE:
							$this->SetStatus($Data[0]);
	                        $this->State = WebSocketState::unknow;
							break;
						case 200:
							$this->SetStatus(IS_EBASE + 3);
	                        $this->State = WebSocketState::unknow;
							break;
						default:
	                        $this->State = WebSocketState::unknow;
							break;
					}
                }
                break;
        }
    }

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady()
    {
        @$this->ApplyChanges();
    }

    /**
     * Wird ausgeführt wenn sich der Parent ändert.
     */
    protected function ForceRefresh()
    {
        $this->ApplyChanges();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function GetConfigurationForParent()
    {
        $Config['Host'] = (string) parse_url($this->ReadPropertyString('URL'), PHP_URL_HOST);
        $Config['Port'] = $this->ReadAttributeInteger('wsPort');
        if ($Config['Host'] == '') {
            $Config['Open'] = false;
        }
        if (($Config['Port'] < 1) || ($Config['Port'] > 65536)) {
            $Config['Open'] = false;
        }
        return json_encode($Config);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();

        if ((float) IPS_GetKernelVersion() < 4.2) {
            $this->RegisterMessage(0, IPS_KERNELMESSAGE);
        } else {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
            $this->RegisterMessage(0, IPS_KERNELSHUTDOWN);
        }

        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);

        if (IPS_GetKernelRunlevel() <> KR_READY) {
            return;
        }

#-------------------------------------------------------------------------------------
#	Konfiguration auf korrekte URL inkl. Port und API-Key checken
#-------------------------------------------------------------------------------------

		if(!$this->CheckURL() || $this->ReadAttributeInteger("wsPort") == 0)return;

#-------------------------------------------------------------------------------------
#	wenn Konfiguration i.O. kann der Client-Socket erzeugt werden
#-------------------------------------------------------------------------------------

        $this->RequireParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}");

        $this->SetTimerInterval('KeepAlive', 0);

        $OldState = $this->State;


        if ((($OldState != WebSocketState::unknow) and ($OldState != WebSocketState::Connected)) or ($OldState == WebSocketState::init)) {
            return;
        }

        $ParentID = $this->RegisterParent();

        if ($OldState == WebSocketState::Connected) {
            $Result = $this->SendDisconnect();
            $this->SendDebug('Result SendDisconnect', ($Result ? "true" : "false"), 0);
        }

        $this->Buffer = '';
        $this->TLSBuffer = '';
        $this->State = WebSocketState::init;

        $Open = IPS_GetProperty($ParentID, 'Open');

        $NewState = IS_ACTIVE;

        if (!$Open) {
            $NewState = IS_INACTIVE;
        } else {
		    if (($this->ReadPropertyInteger('PingInterval') != 0) and ($this->ReadPropertyInteger('PingInterval') < 5)) {
		        $NewState = IS_EBASE + 4;
		        $Open = false;
		        trigger_error('Ping interval to small', E_USER_NOTICE);
		    }
        }

        if ($ParentID == 0) {
            if ($Open) {
                $NewState = IS_INACTIVE;
                $Open = false;
            }
        }

        if ($Open) {
            if ($this->HasActiveParent()) {
                if ($this->UseTLS) {
                    if (!$this->CreateTLSConnection()) {
                        $this->SetStatus(IS_EBASE + 3);
                        $this->State = WebSocketState::unknow;
                        return;
                    }
                }

                $ret = $this->InitHandshake();
                if ($ret !== true) {
                    $NewState = IS_EBASE + 3;
                }
            } else {
                $NewState = IS_EBASE + 1;
            }
        }

        if ($NewState != IS_ACTIVE) {
            $this->State = WebSocketState::unknow;
            $this->SetTimerInterval('KeepAlive', 0);
        } else {
            $this->SetTimerInterval('KeepAlive', $this->ReadPropertyInteger('PingInterval') * 1000);
        }

        $this->SetStatus($NewState);
    }

    ################## PRIVATE

    /**
     * Baut eine TLS Verbindung auf.
     *
     * @access private
     * @return boolean True wenn der TLS Handshake erfolgreich war.
     */
    private function CreateTLSConnection()
    {
        $TLSconfig = TLSContext::getClientConfig([]);
        $TLS = TLSContext::createTLS($TLSconfig);
        $this->SendDebug('TLS start', '', 0);
        $loop = 1;
        $SendData = $TLS->decode();
        $this->SendDebug('Send TLS Handshake ' . $loop, $SendData, 0);
        $this->State = WebSocketState::TLSisSend;
        $JSON['DataID'] = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
        $JSON['Buffer'] = utf8_encode($SendData);
        $JsonString = json_encode($JSON);
        parent::SendDataToParent($JsonString);
        while (!$TLS->isHandshaked() && ($loop < 10)) {
            $loop++;
            $Result = $this->WaitForResponse(WebSocketState::TLSisReceived);
            if ($Result === false) {
                $this->SendDebug('TLS no answer', '', 0);
                trigger_error('TLS no answer', E_USER_NOTICE);
                break;
            }
            $this->State = WebSocketState::TLSisSend;

            $this->SendDebug('Get TLS Handshake', $Result, 0);
            try {
                $TLS->encode($Result);
                if ($TLS->isHandshaked()) {
                    break;
                }
            } catch (TLSAlertException $e) {
                if (strlen($out = $e->decode())) {
                    $JSON['DataID'] = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
                    $JSON['Buffer'] = utf8_encode($SendData);
                    $JsonString = json_encode($JSON);
                    parent::SendDataToParent($JsonString);
                }
                trigger_error($e->getMessage(), E_USER_NOTICE);
                return false;
            }

            $SendData = $TLS->decode();
            if (strlen($SendData) > 0) {
                $this->SendDebug('TLS loop ' . $loop, $SendData, 0);
                $JSON['DataID'] = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
                $JSON['Buffer'] = utf8_encode($SendData);
                $JsonString = json_encode($JSON);
                parent::SendDataToParent($JsonString);
            } else {
                $this->SendDebug('TLS waiting loop ' . $loop, $SendData, 0);
            }
        }
        if (!$TLS->isHandshaked()) {
            return false;
        }
        $this->Multi_TLS = $TLS;
        $this->SendDebug('TLS ProtocolVersion', $TLS->getDebug()->getProtocolVersion(), 0);
        $UsingCipherSuite = explode("\n", $TLS->getDebug()->getUsingCipherSuite());
        unset($UsingCipherSuite[0]);
        foreach ($UsingCipherSuite as $Line) {
            $this->SendDebug(trim(substr($Line, 0, 14)), trim(substr($Line, 15)), 0);
        }
        return true;
    }

    /**
     * Baut eine WebSocket Verbindung zu einem Server auf.
     *
     * @access private
     * @return boolean True wenn WebSocket Verbindung besteht.
     */
    private function InitHandshake()
    {
        $URL = parse_url($this->ReadPropertyString('URL'));
        if (!isset($URL['path'])) {
            $URL['path'] = "/";
        }

        $SendKey = base64_encode(openssl_random_pseudo_bytes(12));
        $Key = base64_encode(sha1($SendKey . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
        $Header[] = 'GET ' . $URL['path'] . ' HTTP/1.1';
        $Header[] = 'Host: ' . $URL['host'];
        if ($this->ReadPropertyBoolean("BasisAuth")) {
            $realm = base64_encode($this->ReadPropertyString("Username") . ':' . $this->ReadPropertyString("Password"));
            $Header[] = 'Authorization: Basic ' . $realm;
        }
        $Header[] = 'Upgrade: websocket';
        $Header[] = 'Connection: Upgrade';

        $Origin = $this->ReadPropertyString('Origin');
        if ($Origin <> "") {
            if ($this->ReadPropertyInteger('Version') >= 13) {
                $Header[] = 'Origin: ' . $Origin;
            } else {
                $Header[] = 'Sec-WebSocket-Origin: ' . $Origin;
            }
        }
        $Protocol = $this->ReadPropertyString('Protocol');
        if ($Protocol <> "") {
            $Header[] = 'Sec-WebSocket-Protocol: ' . $Protocol;
        }

        $Header[] = 'Sec-WebSocket-Key: ' . $SendKey;
        $Header[] = 'Sec-WebSocket-Version: ' . $this->ReadPropertyInteger('Version');
        $Header[] = "\r\n";
        $SendData = implode("\r\n", $Header);
        $this->SendDebug('Send Handshake', $SendData, 0);
        $this->State = WebSocketState::HandshakeSend;
        try {
            if ($this->UseTLS) {
                $TLS = $this->Multi_TLS;
                $SendData = $TLS->output($SendData)->decode();
                $this->Multi_TLS = $TLS;
                $this->SendDebug('Send TLS', $SendData, 0);
            }
            $JSON['DataID'] = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
            $JSON['Buffer'] = utf8_encode($SendData);
            $JsonString = json_encode($JSON);
            parent::SendDataToParent($JsonString);
            // Antwort lesen
            $Result = $this->WaitForResponse(WebSocketState::HandshakeReceived);
            if ($Result === false) {
                throw new Exception('no answer');
            }

            $this->SendDebug('Get Handshake', $Result, 0);

            if (preg_match("/HTTP\/1.1 (\d{3}) /", $Result, $match)) {
                if ((int) $match[1] <> 101) {
                    throw new Exception(HTTP_ERROR_CODES::ToString((int) $match[1]));
                }
            }

            if (preg_match("/Connection: (.*)\r\n/", $Result, $match)) {
                if (strtolower($match[1]) != 'upgrade') {
                    throw new Exception('Handshake "Connection upgrade" error');
                }
            }

            if (preg_match("/Upgrade: (.*)\r\n/", $Result, $match)) {
                if (strtolower($match[1]) != 'websocket') {
                    throw new Exception('Handshake "Upgrade websocket" error');
                }
            }

            if (preg_match("/Sec-WebSocket-Accept: (.*)\r\n/", $Result, $match)) {
                if ($match[1] <> $Key) {
                    throw new Exception('Sec-WebSocket not match');
                }
            }
        } catch (Exception $exc) {
            $this->State = WebSocketState::unknow;
            return false;
        }
        $this->State = WebSocketState::Connected;
        return true;
    }

    /**
     * Dekodiert die empfangenen Daten und sendet sie an die Childs bzw. bearbeitet die Anfrage.
     *
     * @access private
     * @param WebSocketFrame $Frame Ein Objekt welches einen kompletten Frame enthält.
     */
    private function DecodeFrame(WebSocketFrame $Frame)
    {
        $this->SendDebug('Receive', $Frame, ($Frame->OpCode == WebSocketOPCode::continuation) ? $this->PayloadTyp - 1 : $Frame->OpCode - 1);

        switch ($Frame->OpCode) {
            case WebSocketOPCode::ping:
                $this->SendPong($Frame->Payload);
                return;
            case WebSocketOPCode::close:
                $this->SendDebug('Receive', 'Server send stream close !', 0);
                $this->State = WebSocketState::CloseReceived;
                $result = $this->SendDisconnect();
                $this->SetStatus(IS_EBASE + 1);
                return;
            case WebSocketOPCode::text:
            case WebSocketOPCode::binary:
                $this->PayloadTyp = $Frame->OpCode;
                $Data = $Frame->Payload;
                break;
            case WebSocketOPCode::continuation:
                $Data = $this->PayloadReceiveBuffer . $Frame->Payload;
                break;
            case WebSocketOPCode::pong:
                $this->Handshake = (string) $Frame->Payload;
                $this->WaitForPong = true;
                return;
            default:
                return;
        }

        if ($Frame->Fin) {
            $this->SendDataToChilds($Data); // RAW Childs
        } else {
            $this->PayloadReceiveBuffer = $Data;
        }
    }

    /**
     * Senden einen Pong als Antwort an den Server.
     *
     * @access private
     * @param string $Payload Der Payload welche mit dem Pong versendet wird.
     */
    private function SendPong(string $Payload = null)
    {
        $this->Send($Payload, WebSocketOPCode::pong);
    }

    /**
     * Sendet einen Disconnect Frame an den Server.
     *
     * @return boolean True wenn gesendet bzw. erwartet Antwort eingetroffen ist.
     */
    private function SendDisconnect()
    {
        if ($this->State == WebSocketState::CloseReceived) {
            $this->SendDebug('Send', 'Answer Server stream close !', 0);
            $this->Send("", WebSocketOPCode::close);
            $this->State = WebSocketState::unknow;
            return true;
        }
        $this->SendDebug('Send', 'Client send stream close !', 0);
        $this->State = WebSocketState::CloseSend;
        $this->Send("", WebSocketOPCode::close);
        $result = ($this->WaitForResponse(WebSocketState::CloseReceived) !== false);
        $this->State = WebSocketState::unknow;
        return $result;
    }

    /**
     * Versendet RawData mit OpCode an den IO.
     *
     * @access private
     * @param string $RawData
     * @param WebSocketOPCode $OPCode
     */
    private function Send(string $RawData, int $OPCode, $Fin = true)
    {
        $WSFrame = new WebSocketFrame($OPCode, $RawData);
        $WSFrame->Fin = $Fin;
        $Frame = $WSFrame->ToFrame(true);
        $this->SendDebug('Send', $WSFrame, 0);
        $this->SendDataToParent($Frame);
    }

    /**
     * Wartet auf eine Handshake-Antwort.
     *
     * @access private
     */
    private function WaitForResponse(int $State)
    {
        for ($i = 0; $i < 500; $i++) {
            if ($this->State == $State) {
                $Handshake = $this->Handshake;
                $this->Handshake = "";
                return $Handshake;
            }
            IPS_Sleep(5);
        }
        return false;
    }

    /**
     * Wartet auf einen Pong.
     *
     * @access private
     */
    private function WaitForPong()
    {
        for ($i = 0; $i < 500; $i++) {
            if ($this->WaitForPong === true) {
                $this->WaitForPong = false;
                $Handshake = $this->Handshake;
                $this->Handshake = "";
                return $Handshake;
            }
            IPS_Sleep(5);
        }
        return false;
    }

    ################## DATAPOINTS CHILDS

    /**
     * Interne Funktion des SDK. Nimmt Daten von Childs entgegen und sendet Diese weiter.
     *
     * @access public
     * @param string $JSONString
     * @result bool true wenn Daten gesendet werden konnten, sonst false.
     */
    public function ForwardData($JSONString)
    {
        if ($this->State <> WebSocketState::Connected) {
            return false;
        }

        $Data = json_decode($JSONString);
        if ($Data->DataID == "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}") { //Raw weitersenden
            $this->SendText(utf8_decode($Data->Buffer));
	        return true;
        }
        if ($Data->DataID == "{BC49DE11-24CA-484D-85AE-9B6F24D89321}") { // WSC send
            $this->Send(utf8_decode($Data->Buffer), $Data->FrameTyp, $Data->Fin);
	        return true;
        }
        if ($Data->DataID == "{875B91AC-45F1-9757-30F6-BF71445B2BDB}") { // HTTP send
            return $this->SendToDeconz($Data->Buffer);
        }
    }

    /**
     * Sendet die Rohdaten an die Childs.
     *
     * @access private
     * @param string $RawData
     */
    private function SendDataToChilds(string $RawData)
    {
        $JSON['DataID'] = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';
        $JSON['Buffer'] = utf8_encode($RawData);
        $Data = json_encode($JSON);
        $this->SendDataToChildren($Data);

        $JSON['DataID'] = '{C51A4B94-8195-4673-B78D-04D91D52D2DD}'; // WSC Receive
        $JSON['FrameTyp'] = $this->PayloadTyp;
        $Data = json_encode($JSON);
        $this->SendDataToChildren($Data);
    }

    ################## DATAPOINTS PARENT

    /**
     * Empfängt Daten vom Parent.
     *
     * @access public
     * @param string $JSONString Das empfangene JSON-kodierte Objekt vom Parent.
     * @result bool True wenn Daten verarbeitet wurden, sonst false.
     */
    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);
        if ($this->UseTLS) {
            $Data = $this->TLSBuffer . utf8_decode($data->Buffer);

            if (($this->State == WebSocketState::TLSisSend) or ($this->State == WebSocketState::TLSisReceived)) {
                $this->WaitForResponse(WebSocketState::TLSisSend);
                $this->TLSBuffer = "";
                $this->SendDebug('Receive TLS Handshake', $Data, 0);
                $this->Handshake = $Data;
                $this->State = WebSocketState::TLSisReceived;
                return;
            }

            if ((ord($Data[0]) >= 0x14) && (ord($Data[0]) <= 0x18) && (substr($Data, 1, 2) == "\x03\x03")) {
                $TLSData = $Data;
                $Data = "";
                $TLS = $this->Multi_TLS;
                while (strlen($TLSData) > 0) {
                    $len = unpack("n", substr($TLSData, 3, 2))[1] + 5;
                    if (strlen($TLSData) >= $len) {
                        $Part = substr($TLSData, 0, $len);
                        $TLSData = substr($TLSData, $len);
                        $this->SendDebug('Receive TLS Frame', $Part, 0);
                        $TLS->encode($Part);
                        $Data .= $TLS->input();
                    } else {
                        break;
                    }
                }
                $this->Multi_TLS = $TLS;
                $this->TLSBuffer = $TLSData;
                if (strlen($TLSData) > 0) {
                    $this->SendDebug('Receive TLS Part', $TLSData, 0);
                }
            } else { // Anfang (inkl. Buffer) paßt nicht
                $this->TLSBuffer = "";
                return;
            }
        } else {
            $Data = utf8_decode($data->Buffer);
        }

        $Data = $this->Buffer . $Data;
        if ($Data == "") {
            return;
        }
        switch ($this->State) {
            case WebSocketState::HandshakeSend:
                if (strpos($Data, "\r\n\r\n") !== false) {
                    $this->Handshake = $Data;
                    $this->State = WebSocketState::HandshakeReceived;
                    $Data = "";
                } else {
                    $this->SendDebug('Receive inclomplete Handshake', $Data, 0);
                }
                $this->Buffer = $Data;
                break;
            case WebSocketState::Connected:
                $this->SendDebug('ReceivePacket', $Data, 1);
                while (true) {
                    if (strlen($Data) < 2) {
                        break;
                    }
                    $Frame = new WebSocketFrame($Data);
                    if ($Data == $Frame->Tail) {
                        break;
                    }
                    $Data = $Frame->Tail;
                    $Frame->Tail = null;
                    $this->DecodeFrame($Frame);
                }
                $this->Buffer = $Data;
                break;
            case WebSocketState::CloseSend:
                $this->SendDebug('Receive', 'Server answer client stream close !', 0);
                $this->State = WebSocketState::CloseReceived;
                break;
        }
    }

    /**
     * Sendet ein Paket an den Parent.
     *
     * @access protected
     * @param string $Data
     */
    protected function SendDataToParent($Data)
    {
        $JSON['DataID'] = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
        if ($this->UseTLS) {
            $TLS = $this->Multi_TLS;
            $this->SendDebug('Send TLS', $Data, 0);
            $Data = $TLS->output($Data)->decode();
            $this->Multi_TLS = $TLS;
        }
        $JSON['Buffer'] = utf8_encode($Data);
        $JsonString = json_encode($JSON);
		if (!$this->HasActiveParent()) {
			echo "Fehler: Übergeordnete Instanzen sind nicht aktiv";
			return;
		}        $this->SendDebug('Send Packet', $Data, 1);
        parent::SendDataToParent($JsonString);
    }

    ################## PUBLIC

    /**
     * Versendet RawData mit OpCode an den IO.
     *
     * @access public
     * @param string $Text
     */
    public function SendText(string $Text)
    {
        if ($this->State <> WebSocketState::Connected) {
            trigger_error("Not connected", E_USER_NOTICE);
            return false;
        }
        $this->Send($Text, $this->ReadPropertyInteger('Frame'));
        return true;
    }

    /**
     * Versendet ein String
     *
     * @access public
     * @param bool $Fin
     * @param int $OPCode
     * @param string $Text
     */
    public function SendPacket(bool $Fin, int $OPCode, string $Text)
    {
        if (($OPCode < 0) || ($OPCode > 2)) {
            trigger_error('OpCode invalid', E_USER_NOTICE);
            return false;
        }
        if ($this->State <> WebSocketState::Connected) {
            trigger_error("Not connected", E_USER_NOTICE);
            return false;
        }
        $this->Send($Text, $OPCode, $Fin);
        return true;
    }

    /**
     * Wird durch den Timer aufgerufen und senden einen Ping an den Server.
     *
     * @access public
     */
    public function Keepalive()
    {
        $result = @$this->SendPing($this->ReadPropertyString('PingPayload'));
        if ($result !== true) {
            $this->SetStatus(IS_EBASE + 1);
            $this->SetTimerInterval('KeepAlive', 0);
            trigger_error('Ping timeout', E_USER_NOTICE);
        }
    }

    /**
     * Versendet einen Ping an den Server.
     *
     * @access public
     * @param string $Text Der zu versendene Payload im Ping.
     * @return bool True wenn Ping bestätigt wurde, sonst false.
     */
    public function SendPing(string $Text)
    {
        $this->Send($Text, WebSocketOPCode::ping);
        $Result = $this->WaitForPong();
        if ($Result === false) {
            trigger_error('Timeout', E_USER_NOTICE);
            return false;
        }

        if ($Result != $Text) {
            trigger_error('Wrong pong received', E_USER_NOTICE);
            return false;
        }
        return true;
    }

######################################################################################
#	GetDeconzApiKey
#
#	Erzeugt ein gültiges Key-Paar auf dem Server und in IPS.
######################################################################################

    public function GetDeconzApiKey()
    {
		$Buffer['command'] = 'GetApiKey';
		$Buffer['method'] = 'POST';
		$Buffer['data'] = '{"devicetype":"ips"}';

		$response = $this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));
		$this->SendDebug("API-Key Response", $response, 0);

		if(!$response){
			$this->SetStatus(202);
			return;
		}
		
		foreach(json_decode($response) as $item){
			if(isset($item->error)){
				if(isset($item->error->description)){
					$this->SetStatus(205);
				}
			}
			if(isset($item->success)){
				if(isset($item->success->username)){
					$API_Key	= $item->success->username;

					$this->WriteAttributeString("ApiKey", $API_Key);
					if ($this->HasActiveParent()) {
						$this->SetStatus(102); 
					}else{
						if($this->ParentID >0){
							$this->SetStatus(104); 
						}else{
							$this->SetStatus(200); 
						}
					}
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

######################################################################################
#	UpdateChildren
#
#	Holt die Konfiguration und schickt den jeweiligen Auszug an die Children weiter
######################################################################################

    public function UpdateChildren()
    {
		$Buffer['command'] = '';
		$Buffer['method'] = 'GET';
		$Buffer['data'] = '';

		$response = $this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));
		$this->SendDebug("UpdateChildren", $response, 0);

		$data = json_decode(utf8_decode($response));
        if (property_exists($data, 'lights')) {
			foreach ($data->lights as $item){
				$JSON['DataID'] = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';
				$JSON['Buffer'] = json_encode($item);
				$Data = json_encode($JSON);
				$this->SendDataToChildren($Data);
			}
        }
		
        if (property_exists($data, 'sensors')) {
			foreach ($data->sensors as $item){
				$JSON['DataID'] = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';
				$JSON['Buffer'] = json_encode($item);
				$Data = json_encode($JSON);
				$this->SendDataToChildren($Data);
			}
        }
		
        if (property_exists($data, 'groups')) {
			foreach ($data->groups as $item){
				$JSON['DataID'] = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';
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
		

######################################################################################
#	GetDeconzConfiguration
#
#	Holt die Konfiguration und setzt, wenn erforderlich, den gültigen WebSocket-Port
######################################################################################

    protected function GetDeconzConfiguration()
    {
		$Buffer['command'] = '';
		$Buffer['method'] = 'GET';
		$Buffer['data'] = '';

		$response = $this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));
		$this->SendDebug("GetDeconzConfiguration", $response, 0);

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

######################################################################################
#	CheckURL
#
#	Sendet eine Prüfanfrage an den Server.
#	Die Auswertung der Antwort erfolgt in SendToDeConz.
######################################################################################

    private function CheckURL()
    {
		$Buffer['command'] = '';
		$Buffer['method'] = 'GET';
		$Buffer['data'] = '';
		return $this->SendToDeconz(json_encode($Buffer, JSON_UNESCAPED_SLASHES));
    }

######################################################################################
#	SendToDeconz
#
#	Sendet alle Aufträge und Anfrage an den Server.
#	und prüft die korrekte Konfiguration.
######################################################################################

    private function SendToDeconz($json)
    {
		$payload= json_decode($json);
		$command= $payload->command;
		$method	= $payload->method;
		$data	= $payload->data;

		$host	= parse_url($this->ReadPropertyString("URL"), PHP_URL_HOST);
		$port	= $this->ReadPropertyInteger("SendPort");
		if ($port == 0) {
			$this->SetStatus(202);
			return false;
		}

		if ($command == "GetApiKey") {
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

		if ($err) {
			$this->SetStatus(202);
			return false;
		} else {
			if(strpos($response,'unauthorized user')===false){
				if($this->ParentID > 0){
					$this->SetStatus(IPS_GetProperty($this->ParentID, 'Open')?102:104);
				}
				return utf8_encode($response);
			}else{
				$this->SetStatus(206);
				return false;
			}
		}
    }

}
/** @} */
