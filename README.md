# Herbie SimpleContact Plugin

`SimpleContact` ist ein [Herbie](http://github.com/getherbie/herbie) Plugin, mit dem du deine Website mit einem 
einfachen Kontaktformular mit den Feldern Name, E-Mail und Nachricht ausstattest.

## Installation

Das Plugin installierst du via Composer.

	$ composer require getherbie/plugin-simplecontact

Danach aktivierst du das Plugin in der Konfigurationsdatei.

    plugins:
        enable:
            - simplecontact


## Konfiguration

Unter `plugins.config.simplecontact` stehen dir die folgenden Optionen zur Verfügung:

    # template path to twig template
    template: @plugin/simplecontact/templates/form.twig

    # enable shortcode
    shortcode: true

    # enable twig function
    twig: false


## Formular-Konfiguration

Mit der Installation und der Konfiguration würde das Kontaktformular eigentlich schon funktionieren. 
Allerdings werden einige Formular-Konfigurationen benötigt, damit das Formular zum Beispiel in der Websitesprache 
angezeigt wird, oder damit das Plugin weiss, an welche E-Mailadresse es die Anfrage senden soll. Du konfigurierst also 
die Übersetzungen der Formularfelder, Fehler- und Erfolgsmeldungen und die Mailadresse des Empfängers.

Folgende Formular-Konfigurationen musst du also noch vornehmen: 

    subject: "Anfrage Kontaktformular"
    recipient: "me@example.com"
    fields:
      name:
        label: "Dein Name"
        placeholder:
      email:
        label: "Deine E-Mail"
        placeholder:
      message:
        label: "Deine Nachricht"
        placeholder:
      antispam:
        label: "Antispam"
        placeholder:
      submit:
        label: "Formular absenden"
    messages:
      success: "Danke! Deine Nachricht wurde versendet."
      error: "Fehler: Bitte vervollständige das Formular und probier's nochmal."
      fail: "Fehler: Die Nachricht konnte nicht übermittelt werden."
    errors:
      empty_field: "Dies ist ein Pflichtfeld"
      invalid_email: "Die eingegebene E-Mail ist ungültig"


Du kannst die Konfiguration für das Kontaktformular entweder in den Seiteneigenschaften selber oder in der
Konfiguration für Plugins unter `plugins.config.simplecontact` vornehmen.

In den Seiteneigenschaften sieht die Konfiguration in reduzierter Form wie folgt aus:

    ---
    title: Kontakt
    nocache: 1
    simplecontact:
        subject: "Kontaktanfrage"
        recipient: "me@example.com"
        fields:
        ...        
    ---

In der Site-Konfiguration entsprechend wie folgt:

    plugins:
        config:
            simplecontact:
                subject: "Kontaktanfrage"
                recipient: "me@example.com"
                fields:
                ...   


## Demo

<http://www.getherbie.org/kontakt>
