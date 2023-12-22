<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>IPS-Zigbee-Bibliothek für die Einbindung von Zigbee-Geräten in IP-Symcon via DeCONZ</h1>
	<h2>Grundsätzliches</h2>
	Die Bibliothek regelt die Kommunikation zwischen der DeCONZ-Rest-API von Dresden Elektronik und IP-Symcon. Zur Nutzung ist die Hardware von Dresden Elektronik und eine funktionsfähige Installation der Software DeCONZ erforderlich. Die Handhabung von Hard- und Software, das Einlernen kompatibler Geräte so wie eine Liste kompatibler Geräte ist <a href="https://www.dresden-elektronik.de/funk/software/deconz.html">hier</a> sehr gut beschrieben. Hierauf wird in der Anleitung nicht weiter eingegangen.<br><br>
	<b>Bitte umbedingt die aktuelle Soft- und Firmware benutzen. Ältere Versionen der DeCONZ-Rest-API bieten noch keinen Websocket-Server, der für die Umsetzung benötigt wird.</b><br><br>
	Die Installation der Bibliothek wird <a href="https://www.symcon.de/service/dokumentation/komponenten/verwaltungskonsole/module-store/">hier</a> beschrieben.
	<h2>Dokumentation</h2>
	<a href="https://github.com/nefiertsrebliS/zigbee2deconz/tree/master/DeconzGateway#readme">für das Gateway</a><br>
	<a href="https://github.com/nefiertsrebliS/zigbee2deconz/tree/master/Device#readme">für Geräte</a><br>
	<a href="https://github.com/nefiertsrebliS/zigbee2deconz/tree/master/Group#readme">für die Gruppen</a><br>
	<a href="https://github.com/nefiertsrebliS/zigbee2deconz/tree/master/Sensor#readme">für die Sensoren</a>
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
	  <tr>
		<td>V1.19</td>
		<td>Fix: SetHeatpoint, SetOffset<br>
			Neu: Valve-Position</td>
	  </tr>
	  <tr>
		<td>V1.20</td>
		<td>Fix: Variablentyp SetConfig</td>
	  </tr>
	  <tr>
		<td>V1.21</td>
		<td>Fix: Probleme ab DeCONZ-Version 2.05.79<br>
			Fix: Variablentyp SetSensitivity/SetOffset<br>
			Fix: Teilweise fehlendes Profil Sensitivity<br>
			Fix: Batteriestatus für Ikea Kadrilj</td>
	  </tr>
	  <tr>
		<td>V1.22</td>
		<td>Neu: Unterstützung des Xiaomi Mi Aqara Zauberwürfel<br>
			Neu: Config Delay für Bewegungsmelder</td>
	  </tr>
	  <tr>
		<td>V1.23</td>
		<td>Fix: Fehler in Konfigurator-Liste bei Einsatz mehrerer Gateways</td>
	  </tr>
	  <tr>
		<td>V1.24</td>
		<td>Neu: Z2D_SetColorEx</td>
	  </tr>
	  <tr>
		<td>V2.00</td>
		<td><b>Voraussetzung IP-Symcon Version 5.5</b><br>
			Neu: Umstellung auf nativen IPS-Websocket-Client<br>
			Neu: Maßnahmen zur Reduzierung der Prozessorlast<br>
			Neu: Log-Meldungen bei Fehlern</td>
	  </tr>
	  <tr>
		<td>V2.01</td>
		<td>Fix: PHP-Error Gateway</td>
	  </tr>
      <tr>
		<td>V2.02</td>
		<td>Fix: PHP-Error bei Verbindungsstörung zum Server</td>
	  </tr>
      <tr>
		<td>V2.03</td>
		<td>Fix: Profilname doppelt belegt</td>
	  </tr>
      <tr>
		<td>V2.04</td>
		<td>Fix: IPS_GetConfigurationForm</td>
	  </tr>
      <tr>
		<td>V2.05</td>
		<td>Neu: Entfall regelmäßige Statusabfrage</td>
	  </tr>
      <tr>
		<td>V3.00</td>
		<td>Neu: Geräte mit zugeordneten Sensoren werden in einer gemeinsamen Instanz angelegt<br>
			Neu: Gruppen können komplett aus dem Webfront gesteuert werden<br>
			Fix: Leer Gruppen werden im Konfigurator automatisch ausgeblendet</td>
	  </tr>
      <tr>
		<td>V3.01</td>
		<td>Fix: Falsche Gateway-GUID</td>
	  </tr>
      <tr>
		<td>V3.02</td>
		<td>Neu: Mehrere Gateways können auf einen DeCONZ-Server zugreifen</td>
	  </tr>
      <tr>
		<td>V3.03</td>
		<td>Neu: Multisensoren werden in einer gemeinsamen Instanz angelegt</td>
	  </tr>
      <tr>
		<td>V3.04</td>
		<td>Fix: GetConfig und SetConfig<br>
			Fix: Temperaturprofil</td>
	  </tr>
      <tr>
		<td>V3.05</td>
		<td>Neu: Gerätezusammenfassung im Konfigurator auswählbar<br>
			Fix: einige Geräte wurden nicht zusammengefasst</td>
	  </tr>
      <tr>
		<td>V3.06</td>
		<td>Neu: Konfiguration von Gateway-Parametern<br>
			Fix: Anlegen von Geistervariablen</td>
	  </tr>
      <tr>
		<td>V3.07</td>
		<td>Neu: Unterstützung von Warning-Devices</td>
	  </tr>
      <tr>
		<td>V3.08</td>
		<td>Fix: Fehler beim ersten Aufruf von Variablenprofilen<br>
			Fix: Fehlermeldungen bei nicht erreichbaren Geräten<br>
			Fix: Spaltenbreite im Konfigurator</td>
	  </tr>
      <tr>
		<td>V3.09</td>
		<td>Fix: Gruppenszenen werden nicht aktualisiert<br>
			Neu: SwitchSceneByName</td>
	  </tr>
      <tr>
		<td>V3.10</td>
		<td>Fix: Fehler beim Aufruf von Z2D_setSensitivity</td>
	  </tr>
      <tr>
		<td>V3.11</td>
		<td>Neu: Berücksichtigung von Millisekunden beim Update von Tastendrücken</td>
	  </tr>
      <tr>
		<td>V3.12</td>
		<td>Fix: Update bei zusammengefassten Geräten teilweise fehlerhaft</td>
	  </tr>
      <tr>
		<td>V3.13</td>
		<td>Fix: unknown Command bei DimStop, ColorTemperatureStop</td>
	  </tr>
      <tr>
		<td>V3.14</td>
		<td>Entfall Entprellung von Tastendrücken</td>
	  </tr>
      <tr>
		<td>V3.15</td>
		<td>Neu: Variable reachable anstelle Status 215<br>
			Neu: Z2D_isReachable</td>
	  </tr>
      <tr>
		<td>V3.16</td>
		<td>Neu: Unterstützung von IKEA Starkvind<br>
			Neu: Battery für Lights/Switches</td>
	  </tr>
      <tr>
		<td>V3.17</td>
		<td>Fix: SetReachable-Error</td>
	  </tr>
      <tr>
		<td>V3.18</td>
		<td>Fix: neues Profil für IKEA Starkvind</td>
	  </tr>
      <tr>
		<td>V3.19</td>
		<td>Fix: GetConfig, SetConfig<br>
			Neu: GetDeviceInfo</td>
	  </tr>
      <tr>
		<td>V3.20</td>
		<td>Neu: GetCommandList<br>
			Neu: SetCommandList<br>
			Fix: Timeout bei curl-Aufruf</td>
	  </tr>
      <tr>
		<td>V3.21</td>
		<td>Fix: floatvalue bei dechex()</td>
	  </tr>
      <tr>
		<td>V3.22</td>
		<td>Fix: Kompatibilität zu IPS V7<br>
			Fix: strstr-Problem bei fehlender uniqueID</td>
	  </tr>
      <tr>
		<td>V3.23</td>
		<td>Neu: displayflipped, externalwindowopen</td>
	  </tr>
	</table>
  </body>
</html>
