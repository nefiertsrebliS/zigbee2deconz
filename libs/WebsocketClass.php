<?php

/* * @addtogroup network
 * @{
 *
 * @package       Network
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2017 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */

require_once __DIR__ . '/loadTLS.php';
$autoloader = new AutoloaderTLS('PTLS');

$autoloader->register();

/**
 * Der Status der Verbindung.
 */
class WebSocketState
{
    const unknow = 0;
    const HandshakeSend = 1;
    const HandshakeReceived = 2;
    const Connected = 3;
    const init = 4;
    const TLSisSend = 5;
    const TLSisReceived = 6;
    const CloseSend = 7;
    const CloseReceived = 8;
    const Fin = 0x80;

    /**
     *  Liefert den Klartext zu einem Status.
     *
     * @param int $Code
     * @return string
     */
    public static function ToString(int $Code)
    {
        switch ($Code) {
            case self::unknow:
                return 'unknow';
            case self::HandshakeSend:
                return 'HandshakeSend';
            case self::HandshakeReceived:
                return 'HandshakeReceived';
            case self:: Connected:
                return 'Connected';
            case self:: init:
                return 'init';
            case self:: TLSisSend:
                return 'TLSisSend';
            case self:: TLSisReceived:
                return 'TLSisReceived';
        }
    }
}

class HTTP_ERROR_CODES
{
    const Web_Socket_Protocol_Handshake = 101;
    const Bad_Request = 400;
    const Unauthorized = 401;
    const Forbidden = 403;
    const Not_Found = 404;
    const Method_Not_Allowed = 405;
    const Not_Acceptable = 406;
    const Internal_Server_Error = 500;
    const Not_Implemented = 501;
    const Service_Unavailable = 503;

    public static function ToString(int $Code)
    {
        switch ($Code) {
            case 101: return '101 Web Socket Protocol Handshake';
            case 400: return '400 Bad Request';
            case 401: return '401 Unauthorized';
            case 403: return '403 Forbidden';
            case 404: return '404 Not Found';
            case 405: return '405 Method Not Allowed';
            case 406: return '406 Not Acceptable';
            case 500: return '500 Internal Server Error';
            case 501: return '501 Not Implemented';
            case 503: return '503 Service Unavailable';
            default: return $Code . ' Handshake error';
        }
    }
}

/**
 * Alle OpCodes für einen Websocket-Frame
 */
class WebSocketOPCode
{
    const continuation = 0x0;
    const text = 0x1;
    const binary = 0x2;
    const close = 0x8;
    const ping = 0x9;
    const pong = 0xA;

    /**
     *  Liefert den Klartext zu einem OPCode
     *
     * @param int $Code
     * @return string
     */
    public static function ToString(int $Code)
    {
        switch ($Code) {
            case self::continuation:
                return 'continuation';
            case self::text:
                return 'text';
            case self::binary:
                return 'binary';
            case self::close:
                return 'close';
            case self::ping:
                return 'ping';
            case self::pong:
                return 'pong';
            default:
                return bin2hex(chr($Code));
        }
    }
}

/**
 * Wert bei Maskierung
 */
class WebSocketMask
{
    const mask = 0x80;
}

/**
 * Ein Frame für eine Websocket Verbindung.
 */
class WebSocketFrame extends stdClass
{
    public $Fin = false;
    public $OpCode = WebSocketOPCode::continuation;
    public $Mask = false;
    public $MaskKey = "";
    public $Payload = "";
    public $PayloadRAW = "";
    public $Tail = null;

    /**
     * Erzeugt einen Frame anhand der übergebenen Daten.
     *
     * @param object|string|null|WebSocketOPCode Aus den übergeben Daten wird das Objekt erzeugt
     * @param string $Payload Das Payload wenn Frame den WebSocketOPCode darstellt.
     */
    public function __construct($Frame = null, $Payload = null)
    {
        if (is_null($Frame)) {
            return;
        }
        if (is_object($Frame)) {
            if ($Frame->DataID == '') { //GUID Virtual IO TX
                $this->Fin = true;
                $this->OpCode = WebSocketOPCode::text;
                $this->Payload = utf8_decode($Frame->Buffer);
            }
            if ($Frame->DataID == '') { //GUID textFrame
                $this->Fin = true;
                $this->OpCode = WebSocketOPCode::text;
                $this->Payload = utf8_decode($Frame->Buffer);
            }
            if ($Frame->DataID == '') { //GUID BINFrame
                $this->Fin = true;
                $this->OpCode = WebSocketOPCode::binary;
                $this->Payload = utf8_decode($Frame->Buffer);
            }
            return;
        }
        if (!is_null($Payload)) {
            $this->Fin = true;
            $this->OpCode = $Frame;
            $this->Payload = $Payload;
            return;
        }

        $this->Fin = ((ord($Frame[0]) & WebSocketState::Fin) == WebSocketState::Fin) ? true : false;
        $this->OpCode = (ord($Frame[0]) & 0x0F);
        $this->Mask = ((ord($Frame[1]) & WebSocketMask::mask) == WebSocketMask::mask) ? true : false;

        $len = ord($Frame[1]) & 0x7F;
        $start = 2;
        if ($len == 126) {
            $len = unpack("n", substr($Frame, 2, 2))[1];
            $start = 4;
        } elseif ($len == 127) {
            $len = unpack("J", substr($Frame, 2, 8))[1];
            $start = 10;
        }
        if ($this->Mask) {
            $this->MaskKey = substr($Frame, $start, 4);
            $start = $start + 4;
        }
        //Prüfen ob genug daten da sind !
        if (strlen($Frame) >= $start + $len) {
            $this->Payload = substr($Frame, $start, $len);
            if ($this->Mask and ($len > 0)) {
                for ($i = 0; $i < strlen($this->Payload); $i++) {
                    $this->Payload[$i] = $this->Payload[$i] ^ $this->MaskKey[$i % 4];
                }
            }
            $Frame = substr($Frame, $start + $len);
        }
        $this->Tail = $Frame;
    }

    /**
     * Liefert den Byte-String für den Versand an den IO-Parent
     *
     */
    public function ToFrame($Masked = false)
    {
        $Frame = chr(($this->Fin ? 0x80 : 0x00) | $this->OpCode);
        $len = strlen($this->Payload);
        $len2 = "";
        if ($len > 0xFFFF) {
            $len2 = pack("J", $len);
            $len = 127;
        } elseif ($len > 125) {
            $len2 = pack("n", $len);
            $len = 126;
        }
        $this->Mask = $Masked;
        if ($this->Mask and ($len > 0)) {
            $this->PayloadRAW = $this->Payload;
            $len = $len | WebSocketMask::mask;
            $this->MaskKey = openssl_random_pseudo_bytes(4);
            for ($i = 0; $i < strlen($this->Payload); $i++) {
                $this->Payload[$i] = $this->Payload[$i] ^ $this->MaskKey[$i % 4];
            }
        }
        $Frame .= chr($len);
        $Frame .= $len2;
        $Frame .= $this->MaskKey;
        $Frame .= $this->Payload;
        return $Frame;
    }
}

/**
 * Enthält die Daten eines Client.
 */
class Websocket_Client
{

    /**
     * IP-Adresse des Node.
     * @var string
     * @access public
     */
    public $ClientIP;

    /**
     * Port des Client
     * @var int
     * @access public
     */
    public $ClientPort;

    /**
     * Verbindungsstatus des Client
     * @var WebSocketState
     * @access public
     */
    public $State;

    /**
     * Letzer Zeitpunkt der Datenübertragung.
     * @var Timestamp
     * @access public
     */
    public $Timestamp;

    /**
     * True wenn Client TLS spricht-
     * @var UseTLS
     * @access public
     */
    public $UseTLS;

    /**
     * Liefert die Daten welche behalten werden müssen.
     * @access public
     */
    public function __sleep()
    {
        return array('ClientIP', 'ClientPort', 'State', 'Timestamp', 'UseTLS');
    }

    /**
     * Erzeugt ein Websocket_Client-Objekt aus den übergebenden Daten.
     *
     * @access public
     * @param string $ClientIP Die IP-Adresse des Clients.
     * @param int $ClientPort Der Empfangs-Port des Clients.
     * @param WebSocketState $State Der Status des Clients.
     * @param bool $UseTLS True wenn Client TLS nutzt, sonst false.
     */
    public function __construct(string $ClientIP, int $ClientPort, $State = WebSocketState::HandshakeReceived, $UseTLS = false)
    {
        $this->ClientIP = $ClientIP;
        $this->ClientPort = $ClientPort;
        $this->State = $State;
        $this->Timestamp = 0;
        $this->UseTLS = $UseTLS;
    }
}

/**
 * WebSocket_ClientList ist eine Klasse welche ein Array von Websocket_Clients enthält.
 *
 */
class WebSocket_ClientList
{

    /**
     * Array mit allen Items.
     * @var array
     * @access public
     */
    private $Items = array();

    /**
     * Liefert die Daten welche behalten werden müssen.
     * @access public
     */
    public function __sleep()
    {
        return array('Items');
    }

    /**
     * Update für einen Eintrag in $Items.
     * @access public
     * @param Websocket_Client $Client Das neue Client-Objekt
     */
    public function Update(Websocket_Client $Client)
    {
        $this->Items[$Client->ClientIP . $Client->ClientPort] = $Client;
    }

    /**
     * Löscht einen Eintrag aus $Items.
     * @access public
     * @param Websocket_Client $Client Der Index des zu löschenden Items.
     */
    public function Remove(Websocket_Client $Client)
    {
        if (isset($this->Items[$Client->ClientIP . $Client->ClientPort])) {
            unset($this->Items[$Client->ClientIP . $Client->ClientPort]);
        }
    }

    /**
     * Liefert einen bestimmten Eintrag aus den Items anhand der IP-Adresse.
     * @access public
     * @param Websocket_Client $Client Der zu suchende Client
     * @return Websocket_Client Das Original Objekt aus dem Buffer.
     */
    public function GetByIpPort(Websocket_Client $Client)
    {
        if (!isset($this->Items[$Client->ClientIP . $Client->ClientPort])) {
            return false;
        }
        $Client = $this->Items[$Client->ClientIP . $Client->ClientPort];
        return $Client;
    }

    /**
     * Liefert ein Array mit allen Clients.
     * @return Websocket_Client[] Ein Array mit allen Websocket_Client-Objekten.
     */
    public function GetClients()
    {
        $list = array();
        foreach ($this->Items as $Client) {
            $list[$Client->ClientPort . $Client->ClientPort] = $Client;
        }
        return $list;
    }

    /**
     * Liefert einen bestimmten Eintrag wo als nächstes das Timeout auftritt.
     * @access public
     * @param int $Offset Offset
     * @return Websocket_Client Der Client mit dem erstmöglichen Timeout.
     */
    public function GetNextTimeout($Offset = 0)
    {
        $Timestamp = time() + $Offset;
        $FoundClient = false;
        foreach ($this->Items as $Client) {
            if ($Client->Timestamp == 0) {
                continue;
            }
            if ($Client->Timestamp < $Timestamp) {
                $Timestamp = $Client->Timestamp;
                $FoundClient = $Client;
            }
        }
        return $FoundClient;
    }
}

/** @} */
