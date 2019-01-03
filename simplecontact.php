<?php

namespace herbie\plugin\simplecontact;

use herbie\plugin\shortcode\classes\Shortcode;
use herbie\plugin\twig\classes\HerbieExtension;
use herbie\plugin\twig\classes\Twig;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

class SimplecontactPlugin extends \Herbie\Plugin
{
    protected $errors = [];

    /**
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $config = $this->herbie->getConfig();
        if ((bool)$config->get('plugins.config.simplecontact.twig', false)) {
            $events->attach('onTwigInitialized', [$this, 'onTwigInitialized'], $priority);
        }
        if ((bool)$config->get('plugins.config.simplecontact.shortcode', true)) {
            $events->attach('onShortcodeInitialized', [$this, 'onShortcodeInitialized'], $priority);
        }
    }

    /**
     * @param EventInterface $event
     */
    public function onTwigInitialized(EventInterface $event)
    {
        /** @var Twig $twig */
        $twig = $event->getTarget();
        $twig->addFunction(
            new \Twig_SimpleFunction('simplecontact', [$this, 'simplecontact'], ['is_safe' => ['html']])
        );
    }

    /**
     * @param EventInterface $event
     */
    public function onShortcodeInitialized(EventInterface $event)
    {
        /** @var Shortcode $shortcode */
        $shortcode = $event->getTarget();
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
        $environment = $this->herbie->getEnvironment();
        switch ($environment->getAction()) {
            case 'fail':
                $content = $config['messages']['fail'];
                break;
            case 'success':
                $content = $config['messages']['success'];
                break;
            default:
                $template = $this->herbie->getConfig()->get(
                    'plugins.config.simplecontact.template',
                    '@plugin/simplecontact/templates/form.twig'
                );
                $content = $this->herbie->getTwig()->render($template, [
                    'config' => $config,
                    'errors' => $this->errors,
                    'vars' => $this->filterFormData(),
                    'route' => $this->herbie->getRoute()
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
        $config = (array) $this->herbie->getConfig()->get('plugins.config.simplecontact.formconfig');
        $page = $this->herbie->getPage();
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
        $route = $this->herbie->getRoute() . ':' . $action;
        /** @var HerbieExtension $twigExt */
        $twigExt = $this->herbie->getTwig()
            ->getEnvironment()
            ->getExtension('herbie\\plugin\\twig\\classes\\HerbieExtension');
        $twigExt->functionRedirect($route);
    }
}
