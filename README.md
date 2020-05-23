<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>IPS-Zigbee-Bibliothek für die Einbindung von Zigbee-Geräten in IP-Symcon via DeCONZ</h1>
	<h2>Grundsätzliches</h2>
	Die Bibliothek regelt die Kommunikation zwischen der DeCONZ-Rest-API von Dresden Elektronik und IP-Symcon. Zur Nutzung ist die Hardware von Dresden Elektronik und eine funktionsfähige Installation der Software DeCONZ erforderlich. Die Handhabung von Hard- und Software, das Einlernen kompatibler Geräte so wie eine Liste kompatibler Geräte ist <a href="https://www.dresden-elektronik.de/funktechnik/products/software/pc/deconz/">hier</a> sehr gut beschrieben. Hierauf wird in der Anleitung nicht weiter eingegangen.<br><br>
	<b>Bitte umbedingt die aktuelle Soft- und Firmware benutzen. Ältere Versionen der DeCONZ-Rest-API bieten noch keinen Websocket-Server, der für die Umsetzung benötigt wird.</b><br><br>	
	Die Installation der Bibliothek wird <a href="https://www.symcon.de/service/dokumentation/komponenten/verwaltungskonsole/module-store/">hier</a> beschrieben.
	<h2>Danksagung</h2>
	Das Gateway wurde auf Basis des Websocket-Client von Nall-Chan (<a href="https://github.com/Nall-chan/IPSNetwork">IPSNetwork</a>) verwirklicht. Großen Dank an Nall-Chan für die sehr gute Bibliothek und für die Erlaubnis, diese in meiner Bibliothek nutzen zu dürfen.
	<h2>Lizenz</h2>
	<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/"><img alt="Creative Commons Lizenzvertrag" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png" /></a><br />Dieses Werk ist lizenziert unter einer <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/">Creative Commons Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 4.0 International Lizenz</a>
	<h2>Changelog</h2>
	<table>
	  <tr>
		<td>V1.00 &nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Grundversion</td>
	  </tr>
	  <tr>
		<td>V1.01</td>
		<td>Neu: erweiterter Funktionsumfang<br>
			Neu: deutlich vereinfachte Konfiguration des Gateways</td>
	  </tr>
	  <tr>
		<td>V1.02</td>
		<td>Fix: Fehler bei der Konfiguration des Gateways<br>
			Neu: Emulation des Status</td>
	  </tr>
	  <tr>
		<td>V1.03</td>
		<td>Fix: Optimierung des Gateways<br>
			Fix: Übersetzung vervollständigt</td>
	  </tr>
	  <tr>
		<td>V1.04</td>
		<td>Fix: Gatewayfehler bei Erstellung Konfigurator ohne Gateway</td>
	  </tr>
	  <tr>
		<td>V1.05</td>
		<td>Fix: Umlaute werden falsch dargestellt<br>
			Fix: Offline-Status wird minütlich angezeigt<br>
			Fix: Falsche Werte Temperatur und Feuchtigkeit</td>
	  </tr>
	  <tr>
		<td>V1.06</td>
		<td>Fix: Abstürze im Zusammenhang mit Alexa</td>
	  </tr>
	  <tr>
		<td>V1.07</td>
		<td>Fix: Problem bei Symcon-Neustart</td>
	  </tr>
	  <tr>
		<td>V1.08</td>
		<td>Neu: Gruppen und Licht-Szenen<br>
			Neu: zusätzliche Detailinformationen im Konfigurator</td>
	  </tr>
	  <tr>
		<td>V1.09</td>
		<td>Fix: Verbesserter Support großer Installationen<br>
			Fix: Update "Unreachable"<br>
			Fix: Abbrüche beim Anlegen von Devices<br>
			Neu: Zusätzliche Parameter (Sensitivity, Offset,...)</td>
	  </tr>
	  <tr>
		<td>V1.10</td>
		<td>Neu: Zusätzliche Parameter (Alert, Effect)<br>
			Neu: Mehrere Parameter per JSON-String gleichzeitig einstellen</td>
	  </tr>
	  <tr>
		<td>V1.11</td>
		<td>Fix: Lightlevel</td>
	  </tr>
	  <tr>
		<td>V1.12</td>
		<td>Fix: Konfigurator - Instanz existiert nicht</td>
	  </tr>
	  <tr>
		<td>V1.13</td>
		<td>Fix: Darstellung von Umlauten in Gruppen</td>
	  </tr>
	  <tr>
		<td>V1.14</td>
		<td>Fix: Zucken der Helligkeitsanzeige in Stellung aus</td>
	  </tr>
	  <tr>
		<td>V1.15</td>
		<td>Neu: Gruppen-Slider für Helligkeit</td>
	  </tr>
	  <tr>
		<td>V1.16</td>
		<td>Neu: Abfragen und Setzen von Konfigurationen</td>
	  </tr>
	  <tr>
		<td>V1.17</td>
		<td>Fix: Undefined Property lastupdated</td>
	  </tr>
	  <tr>
		<td>V1.18</td>
		<td>Fix: SetSensitivity<br>
			Neu: Vibrationstrength, Tiltangle, Orientation</td>
	  </tr>
	</table>
  </body>
</html>

