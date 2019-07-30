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
	Die Installation der Bibliothek wird <a href="https://www.symcon.de/service/dokumentation/komponenten/verwaltungskonsole/module-store/">hier</a> beschrieben.
	<h2>Danksagung</h2>
	Das Gateway wurde auf Basis des Websocket-Client von Nall-Chan (<a href="https://github.com/Nall-chan/IPSNetwork">IPSNetwork</a>) verwirklicht. Großen Dank an Nall-Chan für die sehr gute Bibliothek und für die Erlaubnis, diese in meiner Bibliothek nutzen zu dürfen.
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
		<td>Neu: Gruppen und Scenen</td>
		<td>Neu: zusätzliche Detailinformationen im Konfigurator</td>
	  </tr>
	</table>
  </body>
</html>

