<?php

namespace App\Command;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Airplane;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'transport:create-data', description: 'Create pimcore airplane DataObjects and form document')]
class CreateDataCommand extends Command
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $airplanes = [
            [
                'key' => 'AirbusA380',
                'name' => 'Airbus A380',
                'maxCargoWeight' => 35000,
                'email' => 'airbus@lemonmind.com'
            ],
            [
                'key' => 'Boeing747',
                'name' => 'Boeing 747',
                'maxCargoWeight' => 38000,
                'email' => 'boeing@lemonmind.com'
            ]
        ];

        $unit = new Unit();
        $unit->setAbbreviation('kg');
        $unit->setLongname('kilogram');

        $unit->save();

        foreach ($airplanes as $airplane) {
            $newAirplane = new Airplane();
            $newAirplane->setParent(DataObject\Service::createFolderByPath('/'));
            $newAirplane->setKey($airplane['key']);

            $newAirplane->setName($airplane['name']);
            $newAirplane->setMaxCargoWeight(new QuantityValue($airplane['maxCargoWeight'], $unit->getId()));
            $newAirplane->setEmail($airplane['email']);

            $newAirplane->setPublished(true);
            $newAirplane->save();
            sleep(1);
        }

        \Pimcore\Model\Translation::importTranslationsFromFile(__DIR__ . '/../../export_messages_translations.csv');

        $page = new \Pimcore\Model\Document\Page();
        $page->setParentId(1);
        $page->setKey('form');
        $page->setController('App\Controller\TransportController::transportSubmitAction');
        $page->setPublished(true);
        $page->save();

        return Command::SUCCESS;
    }
}
