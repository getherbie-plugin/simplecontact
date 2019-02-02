<?php

namespace herbie\plugin\simplecontact;

use Herbie\Configuration;
use Herbie\Environment;
use Herbie\Event;
use Herbie\EventManager;
use Herbie\PluginInterface;
use Herbie\TwigRenderer;
use Herbie\UrlGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tebe\HttpFactory\HttpFactory;

class SimplecontactPlugin implements PluginInterface, MiddlewareInterface
{
    private $config;
    private $environment;
    private $errors = [];
    private $events;
    private $httpFactory;
    private $request;
    private $twigRenderer;
    private $urlGenerator;

    /**
     * SimplecontactPlugin constructor.
     * @param Configuration $config
     * @param Environment $environment
     * @param HttpFactory $httpFactory
     * @param TwigRenderer $twigRenderer
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(
        Configuration $config,
        Environment $environment,
        HttpFactory $httpFactory,
        TwigRenderer $twigRenderer,
        UrlGenerator $urlGenerator
    ) {
        $this->config = $config;
        $this->environment = $environment;
        $this->httpFactory = $httpFactory;
        $this->twigRenderer = $twigRenderer;
        $this->urlGenerator = $urlGenerator;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        return $handler->handle($request);
    }

    /**
     * @param EventManager $events
     * @param int $priority
     */
    public function attach(EventManager $events, int $priority = 1): void
    {
        $this->events = $events;
        $events->attach('onTwigInitialized', [$this, 'onTwigInitialized'], $priority);
    }

    /**
     * @param Event $event
     */
    public function onTwigInitialized(Event $event)
    {
        /** @var Twig $twig */
        $twig = $event->getTarget();
        $twig->addFunction(
            new \Twig_SimpleFunction('simplecontact', [$this, 'simplecontact'], ['is_safe' => ['html']])
        );
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
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
        $environment = $this->environment;
        switch ($environment->getAction()) {
            case 'fail':
                $content = $config['messages']['fail'];
                break;
            case 'success':
                $content = $config['messages']['success'];
                break;
            default:
                $name = $this->config->get(
                    'plugins.config.simplecontact.template',
                    '@plugin/simplecontact/templates/form.twig'
                );
                $content = $this->twigRenderer->renderTemplate($name, [
                    'config' => $config,
                    'errors' => $this->errors,
                    'vars' => $this->filterFormData(),
                    'route' => $this->environment->getRoute()
                ]);
        }

        return $content;
    }

    /**
     * @return bool
     */
    private function validateFormData()
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
    private function filterFormData()
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
    private function sendEmail()
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
    private function getFormConfig()
    {
        $config = (array) $this->config->get('plugins.config.simplecontact.formConfig');
        $page = $this->request->getAttribute(HERBIE_REQUEST_ATTRIBUTE_PAGE);
        if (isset($page->simplecontact) && is_array($page->simplecontact)) {
            $config = (array)$page->simplecontact;
        }
        return $config;
    }

    /**
     * @param string $action
     */
    private function redirect($action)
    {
        $route = $this->environment->getRoute() . ':' . $action;
        $url = $this->urlGenerator->generateAbsolute($route);
        $response = $this->httpFactory->createResponse()->withHeader('Location', $url);
        $this->emitResponse($response);
        exit;
    }

    /**
     * @param ResponseInterface $response
     */
    private function emitResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        http_response_code($statusCode);
        foreach ($response->getHeaders() as $k => $values) {
            foreach ($values as $v) {
                header(sprintf('%s: %s', $k, $v), false);
            }
        }
        echo $response->getBody();
    }

}
