<?php

namespace App\Controller;


use App\Form\TransportSubmitFormType;
use Carbon\Carbon;
use Exception;
use Pimcore\Model\DataObject\Airplane;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\DataObject\Transport;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransportController extends BaseController
{
    /**
     * @param Request $request
     * @param Translator $translator
     *
     * @return Response
     *
     * @throws Exception
     */
    public function transportSubmitAction(Request $request, Translator $translator)
    {
        $form = $this->createForm(TransportSubmitFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $airplane = Airplane::getById($formData['airplane']);

            $transport = new Transport();
            $transport->setParent(Service::createFolderByPath('/upload/transports'));
            $transport->setKey('Transport-' . time());

            $transport->setAirplane($airplane);
            $transport->setFrom('Gdańsk');
            $transport->setTo('Stuttgart');
            $transport->setDate(Carbon::parse($formData['date']));

            $transport->save();

            $this->addFlash('success', $translator->trans('general.transport-submitted'));

            return $this->render('transport/transport_submit_success.html.twig', ['transport' => $transport]);
        }

        return $this->render('transport/transport_submit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
