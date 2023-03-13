<?php

namespace App\Controller;


use App\Form\TransportSubmitFormType;
use Carbon\Carbon;
use Exception;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Airplane;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\DataObject\Transport;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function transportSubmitAction(Request $request, Translator $translator, SluggerInterface $slugger)
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
            $documents = [];

            // this condition is needed because the 'documents' field is not required
            // so the files must be processed only when uploaded
            if ($files) {
                foreach ($files as $file)
                {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                    $newAsset = new \Pimcore\Model\Asset();
                    $newAsset->setFilename($newFilename);
                    $newAsset->setData(file_get_contents($file->getPathname()));
                    $newAsset->setParent(\Pimcore\Model\Asset\Service::createFolderByPath("/upload/documents"));

                    $newAsset->save();

                    $documents[] = $newAsset;
                }

                $transport->setDocuments($documents);
            }

            $cargoes = $formData['cargoes'];
            $newCargoes = [];
            $unit = DataObject\QuantityValue\Unit::getByAbbreviation("kg");
            foreach ($cargoes as $cargo) {
                $newCargo = new DataObject\Cargo();
                $newCargo->setParent(Service::createFolderByPath('/upload/cargoes'));
                $newCargo->setKey('Cargo-' . uniqid());

                $newCargo->setName($cargo['name']);
                $newCargo->setWeight(new DataObject\Data\QuantityValue($cargo['weight'], $unit->getId()));
                $newCargo->setCargoType($cargo['cargoType']);

                $newCargo->save();
                $newCargoes[] = $newCargo;
            }

            $transport->setCargoes($newCargoes);

            $transport->save();

            $this->addFlash('success', $translator->trans('general.transport-submitted'));

            return $this->render('transport/transport_submit_success.html.twig',
                ['transport' => $transport, 'cargoes' => $cargoes]);
        }

        return $this->render('transport/transport_submit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
