<?php

namespace App\Controller;


use App\Form\TransportSubmitFormType;
use App\Service\TransportCargoesService;
use App\Service\TransportDocumentsService;
use App\Service\TransportEmailGenerator;
use Carbon\Carbon;
use Exception;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Airplane;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\DataObject\Transport;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    public function transportSubmitAction(Request $request, Translator $translator,
                                          TransportDocumentsService $documentsService,
                                          TransportCargoesService $cargoesService,
                                          TransportEmailGenerator $generator): Response
    {
        $form = $this->createForm(TransportSubmitFormType::class);
        $form->handleRequest($request);

        // getting airplane based on select field for cargo weight validation
        if($form->isSubmitted()) {
            $formData = $form->getData();
            $airplane = Airplane::getById($formData['airplane']);

            // resubmit form with computed option for validation
            if(!is_null($airplane)) {
                $form = $this->createForm(TransportSubmitFormType::class,
                    options: ['max_cargo_weight' => $airplane->getMaxCargoWeight()->getValue()]);
                $form->handleRequest($request);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $airplane = Airplane::getById($formData['airplane']);

            $transport = new Transport();
            $transport->setParent(Service::createFolderByPath('/upload/transports'));
            $transport->setKey('Transport-' . uniqid());

            $transport->setAirplane($airplane);
            $transport->setFrom($formData['from']);
            $transport->setTo($formData['to']);
            $transport->setDate(Carbon::parse($formData['date']));

            /** @var UploadedFile[] $files */
            $files = $form->get('documents')->getData();

            /** @var Asset[] $documents */
            $documents = [];

            // this condition is needed because the 'documents' field is not required
            // so the files must be processed only when uploaded
            if ($files) {
                $documents = $documentsService->create($files);
                $transport->setDocuments($documents);
            }

            $cargoes = $cargoesService->create($formData['cargoes']);
            $transport->setCargoes($cargoes);

            $transport->save();

            $mail = $generator->create($transport, $cargoes, $documents);
            $mail->send();

            $this->addFlash('success', $translator->trans('general.transport-submitted'));

            return $this->render('transport/transport_submit_success.html.twig',
                ['transport' => $transport, 'cargoes' => $cargoes]);
        }

        return $this->render('transport/transport_submit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
