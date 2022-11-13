# Herbie Simplecontact Plugin

Simplecontact is a [Herbie](https://github.com/getherbie) plugin that allows you to add a simple contact form to your website.
Such a form consists of the fields name, email and message.

## Installation

The plugin is installed via Composer.

	$ composer require getherbie/plugin-simplecontact

After that, the plugin can be activated in the configuration file.

    plugins:
        enable:
            - simplecontact

## Configuration

Under `plugins.simplecontact.config` the following options are available.

The email address to which the form will be sent:

    recipient: (string)

Aliased template path to custom form template:

    template: (string)

## Notes

If you have enabled page caching, you must disable it.

    ---
    title: Contact form
    cached: false
    ---

Also, the global config `components.pageRendererMiddleware.cache` must be set to false.
Otherwise the page would be loaded from the page cache.

The form is then rendered using the twig function of the same name.

    {{ simplecontact() }}

Additional content can be placed before or after the function call. 
A complete contact page looks like this:

    ---
    title: Contact form
    cached: false
    ---

    # Contact form

    Please fill in all fields of the contact form:
    
    {{ simplecontact() }}

    We can also be reached via email or phone at...    

## Demo

A live demo can be viewed at <https://herbie.tebe.ch/contact>.
