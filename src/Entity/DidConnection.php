<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use VC4SM\Bundle\Controller\SendOffer;

// todo: schema
/**
 * @ApiResource(
 *     collectionOperations={
 *       "get",
 *       "send_offer" => {
 *         "method" => "POST",
 *         "path" => "/did_connections/send_offer",
 *         "controller" => SendOffer::class,
 *       }
 *     },
 *     itemOperations={
 *       "get",
 *       "put",
 *       "delete"
 *     },
 *     iri="https://schema.org/Place",
 *     normalizationContext={"groups"={"DidConnection:output"}, "jsonld_embed_context"=true},
 *     denormalizationContext={"groups"={"DidConnection:input"}, "jsonld_embed_context"=true}
 * )
 */
class DidConnection
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $identifier;

    /**
     * @ApiProperty(iri="https://schema.org/name")
     * @Groups({"DidConnection:output", "DidConnection:input"})
     *
     * @var string
     */
    private $name;

    /**
     * todo: schema.
     * @Groups({"DidConnection:output", "DidConnection:input"})
     *
     * @var string
     */
    private $invitation;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getInvitation(): string
    {
        return $this->invitation;
    }

    public function setInvitation(string $invitation): void
    {
        $this->invitation = $invitation;
    }
}
