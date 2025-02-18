# Token Downloads Bundle for Contao CMS

Ein Downloads Inhaltselement, das dynamisch generiert und eingebaut wird.

## Use Case

Du willst Downloads auf Deiner Site anbieten, die nicht im Menü verlinkt sind. Außerdem willst Du mehrere unterschiedliche 
Downloads Seiten für verschiedene Adressaten anbieten. Diese Downloads sollen nur für einen begrenzten Zeitraum zur 
Verfügung stehen.

Mit Contao Bordmitteln kannst Du dies erreichen, indem Du

* mehrere Seiten anlegst
  * bei diesen jeweils "Im Menü verstecken" auswählst (und bei "In der HTML-Sitemap zeigen" die Option "Nie anzeigen" auswählst)
  * auf diesen Seiten ein "Downloads" Inhaltselement einfügst
  * diese Seiten zu gegebener Zeit wieder löscht (oder zumindest unter "Veröffentlichung" die Option "Anzeigen bis" 
    verwendest)

Mit dem "Token Downloads" Inhaltselement wird dies vereinfacht:

* Du legst nur noch eine Downloads-Seite an in der Du ein Inhaltselement vom Typ "Token Downloads" platzierst 
* Token (Teil des Downloads-Links) werden unter einem eigenen Backend-Menüpunkt verwaltet. Zu jedem Token kannst Du dort
  (wie im Contao-eigenen Downloads Inhaltselement):
  * eine Überschrift festlegen
  * die Dateien auswählen, die zum Download angeboten werden sollen
  * die Veröffentlichung festlegen ("Download veöffentlichen", "Anzeigen ab" und "Anzeigen bis")

Darüber hinaus wird:

* Bei einem Zugriff auf eine solche Seite
  * Bei nicht veröffentlichten Elementen ("Download veöffentlichen" nicht gewählt oder Einstellungen in "Anzeigen ab" und
    "Anzeigen bis" so, daß der Download noch nicht bzw. nicht mehr angezeigt wird) eine Erklärung angezeigt.
  * Das Zugriffsdatum auf solche Seiten wird protokolliert (in der Verwaltung des Tokens und im Contao-Systemlog).
    Protokolliert wird jeweils nur das Datum und der Status, des Seitenaufrufs (Downloads selbst werden nicht 
    protokolliert, hier könntest Du aber einen 
    [response listener](https://docs.contao.org/dev/reference/hooks/postDownload/#body-inner) implementieren um dies
    selbst umzusetzen.
 
## Ideen und Anregungen

Lege gerne ein Ticket an!

