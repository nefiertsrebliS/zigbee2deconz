<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>DeCONZ-Device</h1>
	<h2>Installation</h2>
	Die Installation geschieht direkt über den Konfigurator. Die Instanz legt bei der Installation selbstständig die erforderlichen Variablen und Profile an und ist direkt einsatzfähig.<br>
	Hat das Gerät zugeordnete Sensoren, so werden diese in die Instanz des Gerätes integriert.
	<h2>Konfiguration</h2>
	Eine Konfiguration ist nicht erforderlich.
	<h2>Funktion</h2>
	Der Funktionsumfang hängt vom Gerät ab.  Die Bedienung erfolgt im WebFront.<br>
	Darüber hinaus gibt es einen umfangreichen Befehlsatz zur Ansteuerung der Geräte per Skript.
	Der Befehlsatz zur Ansteuerung der zugeordneten Sensoren ist im README.md der Sensoren zu finden.
	<h2>Mögliche PHP-Befehle</h2>
	<table>
	  <tr>
		<td>1.</td>
		<td><b><i>Z2D_DimSet($ID, $Intansity)</i></b></td>
		<td>Lampe auf einen bestimmten Wert dimmen</td>
	  </tr>
	  <tr>
		<td>2.</td>
		<td><b><i>Z2D_DimSetEx($ID, $Intansity, $Transitiontime)</i></b></td>
		<td>Wie DimSet zusätzlich mit einstellbarer Dimmdauer</td>
	  </tr>
	  <tr>
		<td>3.</td>
		<td><b><i>Z2D_DimUp($ID)<br>Z2D_DimDown($ID)<br>Z2D_DimStop($ID)<br></i></b></td>
		<td>Dimmer starten und stoppen</td>
	  </tr>
	  <tr>
		<td>4.</td>
		<td><b><i>Z2D_DimUpEx($ID, $Transitiontime)<br>Z2D_DimDownEx($ID, $Transitiontime)<br>Z2D_DimStop($ID)<br></i></b></td>
		<td>Wie DimUp/Down/Stop zusätzlich mit einstellbarer Dimmdauer</td>
	  </tr>
	  <tr>
		<td>5.</td>
		<td><b><i>Z2D_setColorTemperature($ID, $value)</i></b></td>
		<td>Einstellung der Farbtemperatur</td>
	  </tr>
	  <tr>
		<td>6.</td>
		<td><b><i>Z2D_setColorTemperatureEx($ID, $value, $Transitiontime)</i></b></td>
		<td>Einstellung der Farbtemperatur zusätzlich mit einstellbarer Dauer</td>
	  </tr>
	  <tr>
		<td>7.</td>
		<td><b><i>Z2D_ColorTemperatureUp($ID)<br>Z2D_ColorTemperatureDown($ID)<br>Z2D_ColorTemperatureStop($ID)<br></i></b></td>
		<td>Änderung der Farbtemperatur starten und stoppen</td>
	  </tr>
	  <tr>
		<td>8.</td>
		<td><b><i>Z2D_ColorTemperatureUpEx($ID, $Transitiontime)<br>Z2D_ColorTemperatureDownEx($ID, $Transitiontime)<br>Z2D_ColorTemperatureStop($ID)<br></i></b></td>
		<td>Änderung der Farbtemperatur starten und stoppen zusätzlich mit einstellbarer Dauer</td>
	  </tr>
	  <tr>
		<td>9.</td>
		<td><b><i>Z2D_SwitchColorMode($ID, $value)</i></b></td>
		<td>Umstellung zwischen Farbe und Farbtemperatur</td>
	  </tr>
	  <tr>
		<td>10.</td>
		<td><b><i>Z2D_setColor($ID, $Color)<br>Z2D_setColorEx($ID, $Color, $Transitiontime)</i></b></td>
		<td>Einstellen einer Lampenfarbe (Color als Integer-Wert)</td>
	  </tr>
	  <tr>
		<td>11.</td>
		<td><b><i>Z2D_SwitchMode($ID, $value)</i></b></td>
		<td>Lampe oder Schalter ein/aus-schalten</td>
	  </tr>
	  <tr>
		<td>12.</td>
		<td><b><i>Z2D_setAlert($ID, $value)</i></b></td>
		<td>Temporärer Alarm (none, select, lselect)</td>
	  </tr>
	  <tr>
		<td>13.</td>
		<td><b><i>Z2D_setColorloop($ID, $value)</i></b></td>
		<td>Einen Colorloop ausführen (0-255, 0 = aus)</td>
	  </tr>
	  <tr>
		<td>14.</td>
		<td><b><i>Z2D_setJson($ID, $value)</i></b></td>
		<td>Setzen mehrerer Parameter über einen JSON-String</td>
	  </tr>
	  <tr>
		<td>15.</td>
		<td><b><i>Z2D_SwitchAlert($ID, $value)</i></b></td>
		<td>Nur für Warning-Devices: Schaltet den Alarm ein/aus [0,1,2]</td>
	  </tr>
	  <tr>
		<td>16.</td>
		<td><b><i>Z2D_isReachable($ID)</i></b></td>
		<td>Fragt ab, ob das Gerät erreichbar ist</td>
	  </tr>
	  <tr>
		<td>17.</td>
		<td><b><i>Z2D_GetDeviceInfo($ID)</i></b></td>
		<td>Holt die Informationen zum Gerät inklusive Untergeräten<br>
			Diese werden als JSON-String ausgegeben<br>
			Ist keine Information vorhanden wird false zurückgegeben</td>
	  </tr>
	  <tr>
		<td>18.</td>
		<td><b><i>Z2D_GetCommandList($ID)</i></b></td>
		<td>Holt die Liste der zur Verfügung stehenden DeCONZ-Befehle<br>
			Diese werden als JSON-String ausgegeben</td>
	  </tr>
	  <tr>
		<td>19.</td>
		<td><b><i>Z2D_SetCommandList($ID, $Attribut, $Befehl)</i></b></td>
		<td>DeCONZ-Befehle editieren <b>Achtung: nur für Experten!</b><br>
			Sollte es bei der Ausführung von Befehlen zu Problemen kommen,<br>
			so können diese mit diesem Befehl ggf. behoben werden.<br>
			Attribut und Befehl kann man sich mit <i>Z2D_GetCommandList</i> angucken.<br>
			Bleibt der Befehl leer, so wird das Attribut aus der Commandlist gelöscht</td>
	  </tr>
	  <tr>
		<td>20.</td>
		<td><b><i>Z2D_SetDisplayFlipped($ID, $value)</i></b></td>
		<td>Für Thermostate: Anzeige drehen [true, false]</td>
	  </tr>
	  <tr>
		<td>21.</td>
		<td><b><i>Z2D_SetExternalWindowOpen($ID, $value)</i></b></td>
		<td>Für Thermostate: Fenster offen [true, false]</td>
	  </tr>
	  <tr>
		<td>22.</td>
		<td><b><i>Z2D_SetExternalSensorTemp($ID, $value)</i></b></td>
		<td>Für Thermostate: Meldet die Raumtemperatur eines externen Sensors an das Thermostat</td>
	  </tr>
	  <tr>
		<td>23.</td>
		<td><b><i>Z2D_Open($ID, $value)</i></b></td>
		<td>true => 0%, false => 100%</td>
	  </tr>
	  <tr>
		<td>24.</td>
		<td><b><i>Z2D_StopMotion($ID)</i></b></td>
		<td>Stoppt die aktuelle Aktion</td>
	  </tr>
	  <tr>
		<td>25.</td>
		<td><b><i>Z2D_Lift($ID, $value)</i></b></td>
		<td>Setzt die Position der Jallousie</td>
	  </tr>
	  <tr>
		<td>26.</td>
		<td><b><i>Z2D_Tilt($ID, $value)</i></b></td>
		<td>Setzt den Lamellenwinkel der Jallousie</td>
	  </tr>
	</table>
  </body>
</html>

