<?php

namespace App\Service;

use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class TransportDocumentsService
{

    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * @param UploadedFile[] $files
     * @return Asset[]
     * @throws \Exception
     */
    public function create(array $files): array {
        $documents = [];

        foreach ($files as $file)
        {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            $newAsset = new Asset();
            $newAsset->setFilename($newFilename);
            $newAsset->setData(file_get_contents($file->getPathname()));
            $newAsset->setParent(\Pimcore\Model\Asset\Service::createFolderByPath("/upload/documents"));

            $newAsset->save();

            $documents[] = $newAsset;
        }

        return $documents;
    }
}
