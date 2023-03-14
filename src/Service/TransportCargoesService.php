<?php

namespace App\Service;

use Pimcore\Model\DataObject\Cargo;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Pimcore\Model\DataObject\Service;

class TransportCargoesService
{
    /**
     * @param array $cargoes
     * @return Cargo[]
     * @throws \Exception
     */
    public function create(array $cargoes): array {
        /** @var Cargo[] $newCargoes */
        $newCargoes = [];
        $unit = Unit::getByAbbreviation("kg");

        foreach ($cargoes as $cargo) {
            $newCargo = new Cargo();
            $newCargo->setParent(Service::createFolderByPath('/upload/cargoes'));
            $newCargo->setKey('Cargo-' . uniqid());

            $newCargo->setName($cargo['name']);
            $newCargo->setWeight(new QuantityValue($cargo['weight'], $unit->getId()));
            $newCargo->setCargoType($cargo['cargoType']);

            $newCargo->save();
            $newCargoes[] = $newCargo;
        }

        return $newCargoes;
    }
}
