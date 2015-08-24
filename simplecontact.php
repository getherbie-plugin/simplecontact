<?php

use Herbie\DI;
use Herbie\Hook;
use Herbie\Menu;

class SimplecontactPlugin
{
    protected $config;

    protected $errors = [];

    public function __construct()
    {
        $this->config = DI::get('Config');
    }

    public function install()
    {
        if ((bool)$this->config->get('plugins.config.simplecontact.twig', false)) {
            Hook::attach('twigInitialized', [$this, 'addTwigFunction']);
        }
        if ((bool)$this->config->get('plugins.config.simplecontact.shortcode', true)) {
            Hook::attach('shortcodeInitialized', [$this, 'addShortcode']);
        }
    }

    public function addTwigFunction($twig)
    {
        $twig->addFunction(
            new \Twig_SimpleFunction('simplecontact', [$this, 'simplecontact'], ['is_safe' => ['html']])
        );
    }

    public function addShortcode($shortcode)
    {
        $shortcode->add('simplecontact', [$this, 'simplecontact']);
    }

    /**
     * @return string
     */
    public function simplecontact()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if ($this->validateFormData()) {
                if ($this->sendEmail()) {
                    $this->redirect('success');
                } else {
                    $this->redirect('fail');
                }
            }
        }

        $config =  $this->getFormConfig();
        $request = DI::get('Request');
        switch ($request->getAction()) {
            case 'fail':
                $content = $config['messages']['fail'];
                break;
            case 'success':
                $content = $config['messages']['success'];
                break;
            default:
                $template = $this->config->get(
                    'plugins.config.simplecontact.template',
                    '@plugin/simplecontact/templates/form.twig'
                );
                $content = DI::get('Twig')->render($template, [
                    'config' => $config,
                    'errors' => $this->errors,
                    'vars' => $this->filterFormData(),
                    'route' => $request->getRoute()
                ]);
        }

        return $content;
    }

    /**
     * @return bool
     */
    protected function validateFormData()
    {
        $config = $this->getFormConfig();
        $form_data = $this->filterFormData();
        extract($form_data); // name, email, message, antispam

        if (empty($name)) {
            $this->errors['name'] = $config['errors']['empty_field'];
        }
        if (empty($email)) {
            $this->errors['email'] = $config['errors']['empty_field'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = $config['errors']['invalid_email'];
        }
        if (empty($message)) {
            $this->errors['message'] = $config['errors']['empty_field'];
        }

        return empty($this->errors);
    }

    /**
     * @return array
     */
    protected function filterFormData()
    {
        $defaults = [
            'name' => '',
            'email' => '',
            'message' => '',
            'antispam' => ''
        ];
        $definition = [
            'name' => FILTER_SANITIZE_STRING,
            'email' => FILTER_SANITIZE_EMAIL,
            'message' => FILTER_SANITIZE_STRING,
            'antispam' => FILTER_SANITIZE_STRING
        ];
        $filtered = (array)filter_input_array(INPUT_POST, $definition);
        return array_merge($defaults, $filtered);
    }

    /**
     * @return bool
     */
    protected function sendEmail()
    {
        $form = $this->filterFormData();

        // Antispam
        if (!empty($form['antispam'])) {
            return true;
        }

        $config = $this->getFormConfig();

        $recipient  = $config['recipient'];
        $subject    = $config['subject'];

        $message = "{$form['message']}\n\n";
        $message .= "Name: {$form['name']}\n";
        $message .= "Email: {$form['email']}\n\n";

        $headers = "From: {$form['name']} <{$form['email']}>";

        return mail($recipient, $subject, $message, $headers);
    }

    /**
     * @return array
     */
    protected function getFormConfig()
    {
        $config = (array) $this->config->get('plugins.config.simplecontact');
        $page = DI::get('Page');
        if (isset($page->simplecontact) && is_array($page->simplecontact)) {
            $config = (array)$page->simplecontact;
        }
        return $config;
    }

    /**
     * @param string $action
     */
    protected function redirect($action)
    {
        $route = DI::get('Request')->getRoute() . ':' . $action;
        $twigExt = DI::get('Twig')->getEnvironment()->getExtension('herbie');
        $twigExt->functionRedirect($route);
    }
}

(new SimplecontactPlugin())->install();
