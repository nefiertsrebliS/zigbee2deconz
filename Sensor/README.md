<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>DeCONZ-Sensoren</h1>
	<h2>Installation</h2>
	Die Installation geschieht direkt über den Konfigurator. Die Instanz legt bei der Installation selbstständig die erforderlichen Variablen und Profile an und ist direkt einsatzfähig.<br>
	<b>Achtung:</b> Sensoren, die einem <i>DeCONZ-Device</i> zugeordnet sind, erscheinen nur dann im Konfigurator, wenn Sie als Instanz angelegt wurden.
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
	  <tr>
		<td>4.</td>
		<td><b><i>Z2D_setSensitivity($ID, $value)</i></b></td>
		<td>Einstellen der Sensor-Empfindlichkeit</td>
	  </tr>
	  <tr>
		<td>5.</td>
		<td><b><i>Z2D_setOffset($ID, $value)</i></b></td>
		<td>Einstellen des Temperatur-Offsets z.B. bei Aqara Temperatursensoren</td>
	  </tr>
	  <tr>
		<td>6.</td>
		<td><b><i>Z2D_setDelay($ID, $value)</i></b></td>
		<td>Einstellen der Ausschaltverzögerung von Bewegungsmeldern</td>
	  </tr>
	  <tr>
		<td>7.</td>
		<td><b><i>Z2D_isReachable($ID)</i></b></td>
		<td>Fragt ab, ob das Gerät erreichbar ist</td>
	  </tr>
	  <tr>
		<td>8.</td>
		<td><b><i>Z2D_setMode($ID, $value)</i></b></td>
		<td>Setzt die Lüfterstufe von IKEA Starkvind (0...6)</td>
	  </tr>
	  <tr>
		<td>9.</td>
		<td><b><i>Z2D_GetDeviceInfo($ID)</i></b></td>
		<td>Holt die Informationen zum Gerät inklusive Untergeräten<br>
			Diese werden als JSON-String ausgegeben<br>
			Ist keine Information vorhanden wird false zurückgegeben</td>
	  </tr>
	  <tr>
		<td>10.</td>
		<td><b><i>Z2D_GetCommandList($ID)</i></b></td>
		<td>Holt die Liste der zur Verfügung stehenden DeCONZ-Befehle<br>
			Diese werden als JSON-String ausgegeben</td>
	  </tr>
	  <tr>
		<td>11.</td>
		<td><b><i>Z2D_SetCommandList($ID, $Attribut, $Befehl)</i></b></td>
		<td>DeCONZ-Befehle editieren <b>Achtung: nur für Experten!</b><br>
			Sollte es bei der Ausführung von Befehlen zu Problemen kommen,<br>
			so können diese mit diesem Befehl ggf. behoben werden.<br>
			Attribut und Befehl kann man sich mit <i>Z2D_GetCommandList</i> angucken.<br>
			Bleibt der Befehl leer, so wird das Attribut aus der Commandlist gelöscht</td>
	  </tr>
	  <tr>
		<td>12.</td>
		<td><b><i>Z2D_SetDisplayFlipped($ID, $value)</i></b></td>
		<td>Für Thermostate: Anzeige drehen [true, false]</td>
	  </tr>
	  <tr>
		<td>13.</td>
		<td><b><i>Z2D_SetExternalWindowOpen($ID, $value)</i></b></td>
		<td>Für Thermostate: Fenster offen [true, false]</td>
	  </tr>
	</table>
  </body>
</html>

