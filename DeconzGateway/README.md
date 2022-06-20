<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>DeCONZ-Gateway</h1>
	<h2>Installation</h2>
	Das Gateway wird im Objektbaum unter "Splitter Instanzen" installiert. Es regelt die Kommunikation zwischen IP-Symcon und DeCONZ. Ohne Gateway ist eine Nutzung der Bibliothek nicht möglich.
	<h2>Konfiguration</h2>
	Die Konfiguration des Gateways ist selbsterklärend.
	<ol>
		<li>Bitte zunächst die Adresse und den Port von DeCONZ in der Form <i><b>http://</b>deconz-server:80</i> eintragen und die Änderungen speichern.</li>
		<li>Danach kann ein API-Key durch Betätigung der Taste "Get API-Key" erzeugt werden.</li>
		<li>Nach erfolgreicher Generierung eines gültigen API-Keys wird der Nachrichten-Port im Client-Socket eingetragen.</li>
		<li>Im Client-Socket die Kommunikation öffnen und Änderungen bestätigen.</li>
	</ol>
	Das Gateway sollte jetzt funktionieren und den Status "Verbunden" anzeigen.<br><br>
	Weiter geht es mit dem Konfigurator.
	<h2>Mögliche PHP-Befehle</h2>
	<table>
	  <tr>
		<td>1.</td>
		<td><b><i>Z2D_GetConfig($ID)</i></b></td>
		<td>Holt die Konfiguration des Gateways<br>
			Diese wird als JSON-String ausgegeben</td>
	  </tr>
	  <tr>
		<td>2.</td>
		<td><b><i>Z2D_SetConfig($ID, $Parameter, $Value)</i></b></td>
		<td>Konfiguration von Gateway-Parametern entsprechen der <a href="https://dresden-elektronik.github.io/deconz-rest-doc/endpoints/configuration/#modify-configuration">Parameterliste</a><br>
			Der Erfolg der Änderung wird im Debugfenster angezeigt und als JSON-String zurückgemeldet</td>
	  </tr>
	</table>
  </body>
</html>

