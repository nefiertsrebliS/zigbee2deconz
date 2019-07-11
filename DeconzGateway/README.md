<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>IPS-DeCONZ-Gateway</h1>
	<h2>Installation</h2>
	Das Gateway wird im Objektbaum unter "Splitter Instanzen" installiert. Es regelt die Kommunikation zwischen IP-Symcon und DeCONZ. Ohne Gateway ist eine Nutzung der Bibliothek nicht möglich.
	<h2>Konfiguration</h2>
	Die Konfiguration des Gateways ist selbsterklärend.
	<ol>
		<li>Bitte zunächst die Adresse und den Port von DeCONZ eintragen und die Änderungen speichern.</li>
		<li>Danach kann ein API-Key durch Betätigung der Taste "Get API-Key" erzeugt werden.</li>
		<li>Nach erfolgreicher Generierung eines gültigen API-Keys wird automatisch der Nachrichten-Port eingetragen die Kommunikation geöffnet.</li>
		<li>Gegenenfalls müssen Änderungen am Client-Socket bestätigt werden.</li>
	</ol>
	Das Gateway sollte jetzt funktionieren und den Status "Verbunden" anzeigen.<br><br>
	Weiter geht es mit dem Konfigurator.
  </body>
</html>

