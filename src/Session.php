<?php
namespace Panadas\SessionPlugin;

use Panadas\Event\Event;
use Panadas\Framework\Application;
use Panadas\Framework\ApplicationAwareInterface;
use Panadas\Framework\ApplicationAwareTrait;
use Panadas\Session\DataStructure\SessionParams;
use Panadas\Session\Session as BaseSession;

class Session extends BaseSession implements ApplicationAwareInterface
{

    use ApplicationAwareTrait;

    public function __construct(
        Application $application,
        \SessionHandlerInterface $handler,
        SessionParams $params = null
    ) {
        parent::__construct($handler, $params);

        $this->setApplication($application);

        $application->before("handle", [$this, "beforeHandleEvent"]);
    }

    public function beforeHandleEvent(Event $event)
    {
        $application = $event->getPublisher();
        $logger = $application->getServices()->get("logger");

        if ($this->isCookieSecure() && !$event->getParams()->get("request")->isSecure()) {
            if (null !== $logger) {
                $logger->info("Session not opened for insecure connection");
            }

            return;
        }

        $this->open();

        if (null !== $logger) {
            $logger->info("Session opened with ID: {$this->getId()}");
        }
    }
}
