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

    subject: "Kontaktformular"
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
      success: "Deine Nachricht wurde versendet."
      error: "Bitte alle Felder ausfüllen."
      fail: "Das Nachricht konnte nicht übermittelt werden."
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


## Anwendung

Falls du den Seitencache aktiviert hast, musst du das `nocache`-Flag setzen. Die Seite würde sonst aus dem Seitencache
geladen werden.

    ---
    title: Kontakt
    nocache: 1
    ---

Das Formular renderst du dann über den gleichnamigen Shortcode:

    [simplecontact]
    
Vor oder nach dem Funktionsaufruf kannst du weiteren Inhalt platzieren. Eine komplette Kontaktseite für deine Website 
sieht also wie folgt aus:

    ---
    title: Kontakt
    nocache: 1
    simplecontact:
        subject: "Kontaktformular"
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
          success: "Deine Nachricht wurde versendet."
          error: "Bitte alle Felder ausfüllen."
          fail: "Das Nachricht konnte nicht übermittelt werden."
        errors:
          empty_field: "Dies ist ein Pflichtfeld"
          invalid_email: "Die eingegebene E-Mail ist ungültig"
    ---

    # Kontakt

    Bitte fülle alle Felder des Kontaktformulars aus:
    
    [simplecontact]

    Du kannst uns auch via E-Mail oder Telefon erreichen.    
    

## Demo

<http://www.getherbie.org/kontakt>
