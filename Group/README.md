<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>IPS-DeCONZ-Gruppen</h1>
	<h2>Installation</h2>
	Die Installation geschieht direkt über den Konfigurator. Die Instanz legt bei der Installation selbstständig die erforderlichen Variablen und Profile an und ist direkt einsatzfähig.
	<h2>Konfiguration</h2>
	Eine Konfiguration ist nicht erforderlich.
	<h2>Funktion</h2>
	Gruppen können im WebFront an- und ausgeschaltet werden.<br>
	Licht-Szenen lassen sich im WebFront ebenso ansteuern. Eine Anzeige der aktive Szene gibt es allerdings nicht.<br>
	Darüber hinaus gibt es einen umfangreichen Befehlsatz zur Ansteuerung der Gruppen und Szenen per Skript.
	<h2>Mögliche PHP-Befehle</h2>
	<table>
	  <tr>
		<td>1.</td>
		<td><b><i>Z2D_DimSet($ID, $Intansity)</i></b></td>
		<td>Gruppe auf einen bestimmten Wert dimmen</td>
	  </tr>
	  <tr>
		<td>2.</td>
		<td><b><i>Z2D_DimSetEx($ID, $Intansity, $Transitiontime)</i></b></td>
		<td>Wie DimSet zusätzlich mit einstellbarer Dimmdauer</td>
	  </tr>
	  <tr>
		<td>3.</td>
		<td><b><i>Z2D_DimUp($ID)<br>Z2D_DimDown($ID)<br>Z2D_DimStop($ID)<br></i></b></td>
		<td>Gruppen-Dimmer starten und stoppen</td>
	  </tr>
	  <tr>
		<td>4.</td>
		<td><b><i>Z2D_DimUpEx($ID, $Transitiontime)<br>Z2D_DimDownEx($ID, $Transitiontime)<br>Z2D_DimStop($ID)<br></i></b></td>
		<td>Wie DimUp/Down/Stop zusätzlich mit einstellbarer Dimmdauer</td>
	  </tr>
	  <tr>
		<td>5.</td>
		<td><b><i>Z2D_SetColorTemperature($ID, $value)</i></b></td>
		<td>Einstellung der Farbtemperatur</td>
	  </tr>
	  <tr>
		<td>6.</td>
		<td><b><i>Z2D_SetColorTemperatureEx($ID, $value, $Transitiontime)</i></b></td>
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
		<td><b><i>Z2D_SetColor($ID, $value)</i></b></td>
		<td>Einstellen der Farbe für eine Gruppe</td>
	  </tr>
	  <tr>
		<td>11.</td>
		<td><b><i>Z2D_SwitchMode($ID, $value)</i></b></td>
		<td>Geräte einer Gruppe ein/aus-schalten</td>
	  </tr>
	  <tr>
		<td>12.</td>
		<td><b><i>Z2D_SwitchScene($ID, $SceneID)</i></b></td>
		<td>Aktivierung einer Licht-Szene</td>
	  </tr>
	  <tr>
		<td>12.</td>
		<td><b><i>Z2D_SwitchScene($ID, $SceneID)</i></b></td>
		<td>Aktivierung einer Licht-Szene</td>
	  </tr>
	</table>
  </body>
</html>

