<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\FSi\Bundle\AdminBundle\Admin\Doctrine\Context;

use FSi\Bundle\AdminBundle\Admin\Doctrine\CRUDElement;
use FSi\Bundle\AdminBundle\Event\CRUDEvents;
use FSi\Component\DataIndexer\DoctrineDataIndexer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class EditContextSpec extends ObjectBehavior
{
    private $data;

    function let(EventDispatcher $dispatcher, CRUDElement $element, Form $form, Router $router, DoctrineDataIndexer $indexer)
    {
        $this->data = new \stdClass();
        $element->createForm($this->data)->willReturn($form);
        $this->beConstructedWith($dispatcher, $element, $router, $this->data);
        $element->getDataIndexer()->willReturn($indexer);
        $indexer->getIndex($this->data)->willReturn(1);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('FSi\Bundle\AdminBundle\Admin\Doctrine\Context\EditContext');
    }

    function it_is_context()
    {
        $this->shouldBeAnInstanceOf('FSi\Bundle\AdminBundle\Admin\Context\ContextInterface');
    }

    function it_have_array_data(CRUDElement $element)
    {
        $element->getOption('crud_edit_title')->shouldBeCalled();

        $this->getData()->shouldBeArray();
        $this->getData()->shouldHaveKeyInArray('form');
        $this->getData()->shouldHaveKeyInArray('element');
        $this->getData()->shouldHaveKeyInArray('id');
        $this->getData()->shouldHaveKeyInArray('title');
    }

    function it_has_template(CRUDElement $element)
    {
        $element->hasOption('template_crud_edit')->willReturn(true);
        $element->getOption('template_crud_edit')->willReturn('this_is_edit_template.html.twig');
        $this->hasTemplateName()->shouldReturn(true);
        $this->getTemplateName()->shouldReturn('this_is_edit_template.html.twig');
    }

    function it_handle_request_with_POST_and_return_redirect_response(EventDispatcher $dispatcher, CRUDElement $element,
          Request $request, Form $form, Router $router)
    {
        $dispatcher->dispatch(
            CRUDEvents::CRUD_EDIT_CONTEXT_POST_CREATE,
            Argument::type('FSi\Bundle\AdminBundle\Event\FormEvent')
        )->shouldBeCalled();

        $request->isMethod('POST')->shouldBeCalled()->willReturn(true);

        $dispatcher->dispatch(
            CRUDEvents::CRUD_EDIT_FORM_REQUEST_PRE_SUBMIT,
            Argument::type('FSi\Bundle\AdminBundle\Event\FormEvent')
        )->shouldBeCalled();

        $form->submit($request)->shouldBeCalled();

        $dispatcher->dispatch(
            CRUDEvents::CRUD_EDIT_FORM_REQUEST_POST_SUBMIT,
            Argument::type('FSi\Bundle\AdminBundle\Event\FormEvent')
        )->shouldBeCalled();

        $form->isValid()->willReturn(true);

        $dispatcher->dispatch(
            CRUDEvents::CRUD_EDIT_ENTITY_PRE_SAVE,
            Argument::type('FSi\Bundle\AdminBundle\Event\FormEvent')
        )->shouldBeCalled();

        $form->getData()->willReturn($this->data);
        $element->save(Argument::any())->shouldBeCalled();

        $dispatcher->dispatch(
            CRUDEvents::CRUD_EDIT_ENTITY_POST_SAVE,
            Argument::type('FSi\Bundle\AdminBundle\Event\FormEvent')
        )->shouldBeCalled();

        $element->getId()->willReturn('element_id');

        $router->generate('fsi_admin_crud_list', array(
            'element' => 'element_id',
        ))->shouldBeCalled()->willReturn('redirect_list_url');

        $this->handleRequest($request)->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse');
    }

    function it_handle_request_without_POST_and_return_response(EventDispatcher $dispatcher, Request $request)
    {
        $dispatcher->dispatch(
            CRUDEvents::CRUD_EDIT_CONTEXT_POST_CREATE,
            Argument::type('FSi\Bundle\AdminBundle\Event\FormEvent')
        )->shouldBeCalled();

        $dispatcher->dispatch(
            CRUDEvents::CRUD_EDIT_RESPONSE_PRE_RENDER,
            Argument::type('FSi\Bundle\AdminBundle\Event\FormEvent')
        )->shouldBeCalled();

        $request->isMethod('POST')->shouldBeCalled()->willReturn(false);

        $this->handleRequest($request)->shouldReturn(null);
    }

    public function getMatchers()
    {
        return array(
            'haveKeyInArray' => function($subject, $key) {
                if (!is_array($subject)) {
                    return false;
                }

                return array_key_exists($key, $subject);
            },
        );
    }
}
