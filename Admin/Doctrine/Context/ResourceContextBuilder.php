<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminBundle\Admin\Doctrine\Context;

use FSi\Bundle\AdminBundle\Admin\Context\ContextBuilderInterface;
use FSi\Bundle\AdminBundle\Admin\ElementInterface;
use FSi\Bundle\AdminBundle\Admin\Doctrine\ResourceElement;
use FSi\Bundle\AdminBundle\Exception\ContextBuilderException;
use FSi\Bundle\ResourceRepositoryBundle\Repository\MapBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;

/**
 * @author Norbert Orzechowicz <norbert@fsi.pl>
 */
class ResourceContextBuilder implements ContextBuilderInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var \FSi\Bundle\ResourceRepositoryBundle\Repository\MapBuilder
     */
    protected $mapBuilder;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \FSi\Bundle\ResourceRepositoryBundle\Repository\MapBuilder $builder
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @param \Symfony\Component\Routing\Router $router
     */
    public function __construct(EventDispatcherInterface $dispatcher, FormFactory $formFactory, Router $router, MapBuilder $builder = null)
    {
        $this->dispatcher = $dispatcher;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->mapBuilder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($route, ElementInterface $element)
    {
        if ($route !== $this->getSupportedRoute()) {
            return false;
        }

        if (!$element instanceof ResourceElement) {
            return false;
        }

        if (!isset($this->mapBuilder)) {
            throw new ContextBuilderException("MapBuilder is missing. Make sure that FSiResourceRepositoryBundle is registered in AppKernel");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function buildContext(ElementInterface $element)
    {
        $context = new ResourceContext($this->dispatcher, $element, $this->mapBuilder, $this->formFactory, $this->router);

        return $context;
    }

    /**
     * @return string
     */
    protected function getSupportedRoute()
    {
        return 'fsi_admin_resource';
    }
}
