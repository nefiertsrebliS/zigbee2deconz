<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>IPS-DeCONZ-Sensoren</h1>
	<h2>Installation</h2>
	Die Installation geschieht direkt über den Konfigurator. Die Instanz legt bei der Installation selbstständig die erforderlichen Variablen und Profile an und ist direkt einsatzfähig.
	<h2>Konfiguration</h2>
	Eine Konfiguration ist nicht erforderlich.    
	<h2>Funktion</h2>
	Der Funktionsumfang hängt vom Gerät ab.  Eine Bedienung ist bis auf wenige Ausnahmen nicht vorgesehen.<br>
	<h2>Mögliche PHP-Befehle</h2>
	<table>
	  <tr>
		<td>1.</td>
		<td><b><i>Z2D_SetTemperature($ID, $value)</i></b></td>
		<td>Einstellen der Solltemperatur für Heizungsregler</td>
	  </tr>
	  <tr>
		<td>2.</td>
		<td><b><i>Z2D_GetConfig($ID)</i></b></td>
		<td>Holt die Konfiguration des Sensors<br>
			Diese wird als JSON-String ausgegeben<br>
			Ist keine Konfiguration vorhanden wird false zurückgegeben</td>
	  </tr>
	  <tr>
		<td>3.</td>
		<td><b><i>Z2D_SetConfig($ID, $Parameter, $value)</i></b></td>
		<td>Konfiguration eines Sensor-Parameters<br>
			Der Erfolg der Änderung wird im Debugfenster angezeigt<br>
			Alternativ kann mit <i>Z2D_GetConfig($ID)</i> geprüft werden, ob die Änderung erfolgreich war</td>
	  </tr>
	</table>
  </body>
</html>

