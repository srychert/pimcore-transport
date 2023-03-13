<?php

namespace App\Service;

use Pimcore\Mail;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Cargo;
use Pimcore\Model\DataObject\Transport;

class TransportEmailGenerator
{
    /**
     * @param Transport $transport
     * @param Cargo[] $cargoes
     * @param Asset[] $documents
     * @return Mail
     */
    public function create(Transport $transport, array $cargoes, array $documents = []): Mail
    {
        $mail = new Mail();

        $mail->to($transport->getAirplane()->getEmail());
        $mail->subject('Transport');

        $mail->setParams([
            'from' => $transport->getFrom(),
            'to' => $transport->getTo(),
            'airplane' => $transport->getAirplane()->getName(),
            'date' => $transport->getDate(),
            'cargoes' => $cargoes,
        ]);

        $mail->html("
            <srtong>From:</strong> {{ from }}<br>
            <srtong>To:</strong>  {{ to }}<br>
            <srtong>Airplane:</strong>  {{ airplane }}<br>
            <srtong>Date:</strong>  {{ date.toDateString }}<br>
            <srtong>Cargoes:</strong>  <br>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Weight</th>
                    <th>Type</th>
                 </tr>
                {% for cargo in cargoes %}
                    <tr>
                        <td>{{ cargo.name }}</td>
                        <td>{{ cargo.weight }}</td>
                        <td>{{ cargo.cargoType }}</td>
                    </tr>
                {% endfor %}
            </table>");

        foreach ($documents as $document) {
            $mail->attach($document->getData(), $document->getFilename(), $document->getMimeType());
        }

        return $mail;
    }
}
