<?php
// src/Dto/OrderOutput.php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Dto\TradeItemInput;
use ApiPlatform\Core\Annotation\ApiProperty;

final class TradeInput {

    /**
     * @var $items TradeItemInput[]
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *              "type"="array",
     *              "items":{
     *                  "type"="object",
     *                  "properties"={
     *                      "type" = {
     *                          "type"="string",
     *                          "enum"={"DEPOSIT","WITHDRAW"},
     *                          "example"="DEPOSIT"
     *                      },
     *                      "container_id" = {
     *                          "type"="integer",
     *                          "example"="1"
     *                      },
     *                      "quantity" = {
     *                          "type"="integer",
     *                          "example"="3"
     *                      }
     *                  }
     *              }
     *          }
     *     }
     * )
     * @Groups({"trade:write"})
     */
    public array $items;

    /**
     * @var $code_from_qr string
     * @Groups({"trade:write"})
     */
    public string $code_from_qr;

}